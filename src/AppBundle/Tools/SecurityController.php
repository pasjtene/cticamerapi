<?php

namespace AppBundle\Tools;

use AppBundle\Entity\AuthToken;
use AppBundle\Entity\Credentials;
use AppBundle\Entity\User;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use  Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AccountStatusException;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class SecurityController extends Controller
{



    // authentifie un utilisateur et  cree une cle pour lui
    public function authenticateUser(UserInterface $user)
    {
        try {

            $tocken = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
            $this->get('security.token_storage')->setToken($tocken);
            $this->get('session')->set('_security_main',serialize($tocken));

            $authToken = new AuthToken();
            $authToken->setValue(base64_encode(random_bytes(50)));
            $authToken->setCreatedAt(new \DateTime('now'));
            $authToken->setUser($user);

            $em = $this->getDoctrine()->getManager();

            $auths = $em->getRepository("AppBundle:AuthToken")->findBy(["user"=>$this->getUser()],["id"=>"DESC"]);
            if($auths!=null)
            {
                /** @var AuthToken $auth */
                foreach($auths as $auth)
                {
                   $em->remove($auth);
                   $em->flush();
                }
            }

            $em->persist($authToken);
            $em->flush();
            $em->detach($authToken);

            /** @var Session $session */
            $session = $this->get('session');

            $session->set("auth-current",$authToken);
            return $authToken;

        } catch (AccountStatusException $ex) {
            return $this->json($ex->getMessage());
        }
    }


    // encode le mot  de passe
    public function encodePassword($object, $password, $salt)
    {
        $factory = $this->get('security.encoder_factory');
        $encoder = $factory->getEncoder($object);
        $password = $encoder->encodePassword($password, $salt);

        return $password;
    }


    // charge un utilisateur avec les informations envoyes dans l'application (a completer pour une modfification)
    public  function  fillUser(Request $request, User $user)
    {
        $val = $request->request;
        $tab = explode("@",$val->get("email"));
        $username = $tab==null?null:$tab[0];

        // set  user with  application values
        $user->setEmail($val->get('email'))->setType($val->get('type'))
            ->setBirthDate($val->get('birthDate'))->setFirstName($val->get('firstname'))
            ->setGender($val->get('profession'))->setUsernameCanonical($username)->setEmailCanonical($val->get('email'));

        $user->setEnabled(true)->setIsEmailVerified(false)->setBirthDate(new \DateTime())->setRoles(["ROLE_MEMBER"])
            ->setUsername($username)->setIsOnline(false)->setIsVip(false)->setJoinDate(new \DateTime());
        return $user;
    }


    // exeception for user not  found
    public function userNotFound()
    {
        return \FOS\RestBundle\View\View::create(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
    }




    public function invalidCredentials()
    {
        return \FOS\RestBundle\View\View::create(['message' => 'Password or Login is bad'], Response::HTTP_BAD_REQUEST);
    }





    // Recuper le auth correspondant au  user app
    private function  getAppAuth(){
        $em = $this->getDoctrine()->getManager();
        /** @var User $app */
        $app = $em->getRepository("AppBundle:User")->findOneByemail("app@funglobe.com");
        if(!$app)
        {
           $app =  $this->init()->getUser();
        }
        /** @var AuthToken $authtoken */
        $authtoken = $em->getRepository("AppBundle:AuthToken")->findOneBy(["user"=>$app],["id"=>"DESC"]);

        return $authtoken;
    }


    // Initialise deux utilisateurs systèmes
    public function  init()
    {
        $user = new User();
        $user->setPlainPassword("app");
        $password = $this->encodePassword(new User(), $user->getPlainPassword(), $user->getSalt());
        $user->setConfirmPassword(md5($user->getPassword()));
        $user->setPassword($password);
        $user->setConfirmPassword(md5($user->getPlainPassword()));
        $user->setEnabled(true)->setIsEmailVerified(true)->setEmail("app@funglobe.com")->setBirthDate(new \DateTime())->setRoles(["ROLE_APP"])
            ->setFirstName("App")->setGender("M")->setIsOnline(false)->setIsVip(true)->setType("System")->setUsername("app")->setJoinDate(new \DateTime());

        $authToken = new AuthToken();
        $authToken->setValue(base64_encode(random_bytes(50)));
        $authToken->setCreatedAt(new \DateTime('now'));

        $em = $this->getDoctrine()->getManager();
        $exist = $em->getRepository('AppBundle:User')->findOneByemail($user->getEmail());
        if($exist !=null)
        {
            $user =$exist;
        }
        $authToken->setUser($user);

        $em->persist($authToken);
        $em->flush();
        $em->detach($authToken);
        return $authToken;
    }

}