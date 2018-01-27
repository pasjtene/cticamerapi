<?php
/**
 * Created by PhpStorm.
 * User: tene
 * Date: 21/06/2017
 * Time: 12:47
 */

namespace ApiBundle\Controller;

use ApiBundle\Task\DeletePictureTask;
use AppBundle\Entity\AuthToken;
use AppBundle\Entity\Settings;
use AppBundle\Entity\User;
use AppBundle\Tools\FunglobeUtils;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use  Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AccountStatusException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use AppBundle\Entity\UserPhoto;
use AppBundle\Entity\Files;
define('ITEM_PER_PAGE', 10);

class UserController extends FOSRestController
{
    /**
     * @Rest\Get("/delete/{email}")
     * @return Response
     * @ApiDoc(
     *  resource=true,
     *  description="Route temporaire pour effacer une utilisateur via son email, sans etre authentifie",
     *  statusCodes={
     *     200="the query is ok",
     *     401= "The connection is required",
     *     403= "Access Denied"
     *  }
     * )
     */
    public function deleteUserAction($email)
    {

        //you  can continue if you have a good privileges
        //$this->isgrantUser("ROLE_MODERATOR");
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AppBundle:User')
            ->findOneBy(['email'=>$email]);
        $em->remove($user);
        $em->flush();

        return $this->json($user);
    }

    /**
     * @Rest\Put("/auth/user/{id}")
     * @return Response
     * @ApiDoc(
     *  resource=true,
     *  description="User Update action ",
     *  statusCodes = {
     *      200 = "Updated (seems to be OK)",
     *      400 = "Bad request (see messages)",
     *      401 = "Unauthorized, you must login first",
     *      404 = "Not found",
     *  },
     *  parameters={
     *     {"name"="username", "dataType"="string", "required"=true, "description"="User  name  or email  adress"},
     *     {"name"="password", "dataType"="string", "required"=true, "description"="the password"}
     *  }
     * )
     */
    public function updateUserAction(Request $request)
    {
        //you  can continious if you have a good privileges
        $this->isgrantUser("ROLE_MEMBER");

        $em =$this->getDoctrine()->getManager();

        /** @var User $user */
        $user =$em->getRepository('AppBundle:User')
            ->find($request->get('id')); // L'identifiant en tant que paramètre n'est plus nécessaire


        if (empty($user)) {
            return $this->userNotFound();
        }

        // Si l'utilisateur veut changer son mot de passe
        if (!empty($user->getPlainPassword())) {
            $password = $this->encodePassword(new User(), $user->getPassword(), $user->getSalt());
            $user->setPassword($password);
        }
        $user = $this->fillUser($request,$user);
        $em->merge($user);
        $em->flush();
        $em->detach($user);

        $token = $this->authenticateUser($user);

        return $this->json($user);
    }

    /**
     * @Rest\Get("/auth/members")
     * @return Response
     * @ApiDoc(
     *  resource=true,
     *  description="Récupérer la liste des membres ",
     *  statusCodes={
     *     200="Retourné quand tout est OK !"
     *  }
     * )
     */
    public function membersAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Settings $settings */
        $settings = $em->getRepository("AppBundle:Settings")->findOneBy([]);

        $property = $request->get('property');
        $order = $request->get('order');
        $keyword = $request->get('keyword');

        $itemPerPage = is_object($settings) ? $settings->getUserPerPage() : ITEM_PER_PAGE;
        $page = $request->get('page');

        $page = $page == null ? 1 : $page;

        $allMembers = $em->getRepository('AppBundle:User')->findUsers(null, null, $property, $order, $keyword);

        $userCount = sizeof($allMembers);

        $paginationNumber = round(floatval($userCount) / floatval($itemPerPage), 0, PHP_ROUND_HALF_UP);
        $paginationNumber = $paginationNumber < 1 || $paginationNumber < $page ? 1 : $paginationNumber;

        $page = $page > $paginationNumber ? $paginationNumber : $page;
        $offset = ($page-1) * $itemPerPage;

        $members = FunglobeUtils::paginate($allMembers, $itemPerPage, $offset);

        $datas['page'] = intval($page);
        $datas['itemPerPage'] = $itemPerPage > $userCount ? $userCount : $itemPerPage;
        $datas['offset'] = $offset;
        $datas['paginationCount'] = $paginationNumber;
        $datas['pageNext'] = $paginationNumber > 1 && $page < $paginationNumber ? $page+1 : $page;
        $datas['pagePrev'] = intval($page > 1 ? $page-1 : $page);
        $datas['users'] = $members;
        $datas['total'] = $userCount;

        return $this->json($datas);
    }

    /**
     * @Rest\Delete("/auth/members/delete/{memberList}")
     * @return Response
     * @ApiDoc(
     *  resource=true,
     *  description="Récupérer la liste des membres ",
     *  statusCodes={
     *     200="Retourné quand tout est OK !"
     *  }
     * )
     */
    public function deleteMembersAction(Request $request)
    {
        //$LoggerInterface $logge;
        $logger = $this->get('logger');
        $em = $this->getDoctrine()->getManager();

        $membersToDelete = explode(",", $request->get('memberList'));
        $logger->critical("Members to delete: ".print_r($membersToDelete, TRUE));

        foreach ($membersToDelete as $memberId)
        {
            $user = $em->getRepository('AppBundle:User')
                ->findOneBy(['id'=>$memberId]);

            $pictureIds = $em->getRepository('AppBundle:UserPhoto')->findUserPictures($memberId);
            $firstPicture  = $em->getRepository("AppBundle:UserPhoto")->findOneByuser($user);

            //Delete pictures folder for this user
            $file = new Files();
            $folder = $firstPicture->getFolder();
            $file->deleteDir($folder);
            $logger->info("Deleting the pictures folder " .$folder ." for member " .$user->getFullnameAndcountry());

            //$deleteTask = new DeletePictureTask($em);
            //$deleteTask->run($pictureIds);

            $em->remove($user);
            $em->flush();
        }

        $array = $em->getRepository("AppBundle:User")->findAll();
        return $this->json($array);
    }

    /**
     * @Rest\Get("/auth/members/{id}")
     * @return Response
     * @ApiDoc(
     *  resource=true,
     *  description="Récupérer un membre ",
     *  statusCodes={
     *     200="Retourné quand tout est OK !"
     *  }
     * )
     */
    public function findMembersAction(Request $request, $id)
    {
        $em =$this->getDoctrine()->getManager();
        $user = $em->getRepository('AppBundle:User')->findOneBy(['id' => $id]);

        if(!is_object($user)){
            throw new NotFoundResourceException("Member not found in our database !");
        }

        $userProfile = $em->getRepository('AppBundle:UserProfile')->findOneBy(['user' => $id]);

        return $this->json(['user' => $user, 'profile' => $userProfile]);
    }

    /**
     * @Rest\Put("/auth/member/{id}/role")
     * @return Response
     * @ApiDoc(
     *  resource=true,
     *  description="Update member role ",
     *  statusCodes = {
     *      200 = "Updated (seems to be OK)",
     *      400 = "Bad request (see messages)",
     *      401 = "Unauthorized, you must login first",
     *      404 = "Not found",
     *  },
     *  parameters={
     *     {"name"="role", "dataType"="string", "required"=true, "description"="The new role of the user"}
     *  }
     * )
     */
    public function updateMemberRoleAction(Request $request, $id)
    {
        //you  can continious if you have a good privileges
        //$this->isgrantUser("ROLE_ADMIN");

        $em =$this->getDoctrine()->getManager();

        /** @var User $user */
        $user =$em->getRepository('AppBundle:User')->find($id); // L'identifiant en tant que paramètre n'est plus nécessaire


        if (empty($user)) {
            return new Response("Member not found !", 404);
        }

        $user->setRoles([$request->get('role')]);
        $em->flush();
        $em->detach($user);

        return $this->json($user);
    }

    /**
     * @Rest\Put("/auth/members/lock")
     * @return Response
     * @ApiDoc(
     *  resource=true,
     *  description="Bloquer ou débloquer des utilisateurs",
     *  statusCodes={
     *     200="Retourné quand tout est OK !"
     *  }
     * )
     */
    public function lockMembersAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $membersToLock = explode(",", $request->get('members'));
        $action = $request->get('action') == "set" ? false : true;

        foreach ($membersToLock as $memberId) {
            /** @var User $user */
            $user = $em->getRepository('AppBundle:User')->find($memberId);
            $user->setEnabled($action);
            $em->flush();
        }

        return $this->json([$membersToLock]);
    }

    /**
     * @Rest\Put("/auth/members/vip")
     * @return Response
     * @ApiDoc(
     *  resource=true,
     *  description="Définir si un utilisateur est VIP ou non",
     *  statusCodes={
     *     200="Retourné quand tout est OK !"
     *  }
     * )
     */
    public function vipMembersAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $membersToLock = explode(",", $request->get('members'));
        $action = $request->get('action') == "set" ? true : false;

        foreach ($membersToLock as $memberId) {
            /** @var User $user */
            $user = $em->getRepository('AppBundle:User')->find(intval($memberId));
            $user->setIsVip($action);
            $em->flush();
        }

        return $this->json(["Success"]);
    }

    /**
     * @Rest\Put("/auth/members/country")
     * @return Response
     * @ApiDoc(
     *  resource=true,
     *  description="Changer le pays d'un utilisateur",
     *  statusCodes={
     *     200="Retourné quand tout est OK !"
     *  }
     * )
     */
    public function changeMembersCountryAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $memberId = $request->get('mid');
        $country = $request->get('country');

        /** @var User $user */
        $user = $em->getRepository('AppBundle:User')->find(intval($memberId));
        $user->setCountry($country);
        $em->flush();

        return $this->json(["Success"]);
    }

    // action for logout
    /**
     * @Rest\View(statusCode=Response::HTTP_NO_CONTENT)
     * @Rest\Post("/auth/logout/{token}")
     * @return Response
     * @ApiDoc(
     *  resource=true,
     *  description="User Update action ",
     *  statusCodes = {
     *      200 = "Updated (seems to be OK)",
     *      400 = "Bad request (see messages)",
     *      401 = "Unauthorized, you must login first",
     *      404 = "Not found",
     *  }
     * )
     */
    public function logoutAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        /* @var $authToken AuthToken */
        $authToken = $em->getRepository('AppBundle:AuthToken')
            ->findOneBy(['value' => $request->get('token')]);

        $this->get('logger')->info($request->get('token'));

        /** @var User $connectedUser */
        $connectedUser = $this->getUser();

        if (!is_null($authToken)) {

            /** @var User $user */
            $user = $em->getRepository('AppBundle:User')->find(intval($connectedUser->getId()));
            $user->setIsOnline(false);
            $em->flush();

            $em->remove($authToken);
            $em->flush();

            /** @var Session $session */
            $session = $this->get('session');
            $session->set("auth-current",null);

            return $this->json(["delete token for username  is ". $connectedUser->getUsername()]);

        } else {
            return $this->tokenOruserNotFound();
        }
    }


    // Recuper le auth correspondant au  user app
    private function isgrantUser($role){
        if(!$this->getUser())
        {
            throw new  AuthenticationException();
        }
        $service = $this->get('security.authorization_checker');
        if ($service->isGranted($role) === FALSE) {
            throw new AccessDeniedException();
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
    private  function  fillUser(Request $request, User $user)    {

        $val = $request->request;

        $username = $val->get('email');

        // set  user with  application values
        $user->setEmail($val->get('email'))->setType($val->get('type'))->setGender($val->get('gender'))
            ->setBirthDate($val->get('birthDate'))->setFirstName($val->get('firstname'))->setCountry($val->get('country'))
            ->setGender($val->get('profession'))->setUsernameCanonical($username)->setEmailCanonical($val->get('email'));

        $user->setEnabled(true)->setIsEmailVerified(false)->setBirthDate(new \DateTime())->setRoles(["ROLE_MEMBER"])
            ->setUsername($username)->setIsOnline(false)->setIsVip(false)->setJoinDate(new \DateTime());
        return $user;
    }


    // exeception for user not  found
    private function userNotFound()
    {
        return \FOS\RestBundle\View\View::create(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
    }


    // exeception for user  or token not  found
    private function tokenOruserNotFound()
    {
        return \FOS\RestBundle\View\View::create(['message' => 'User or token not found'], Response::HTTP_NOT_FOUND);
    }




    private function invalidCredentials()
    {
        return \FOS\RestBundle\View\View::create(['message' => 'Password or Login is bad'], Response::HTTP_BAD_REQUEST);
    }

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

}