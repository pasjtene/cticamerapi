<?php

namespace ApiBundle\Controller;

use AppBundle\Entity\AuthToken;
use AppBundle\Entity\Credentials;
use AppBundle\Entity\Geolocation;
use AppBundle\Entity\ip2locationlite;
use AppBundle\Entity\User;
use AppBundle\Entity\UserProfile;
use AppBundle\Tools\HelpersController;
use AppBundle\Tools\SecurityController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\FOSRestController;
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
use Symfony\Component\Validator\Constraints\DateTime;

class DefaultController extends FOSRestController
{



    // Fonction pour initialiser le user systeme et le token de base

    /**
     * @Rest\Get("/app")
     * @return Response
     * @ApiDoc(
     *  resource=true,
     *  description="Récupérer le token de base pour l'aplication  et  les informations pour la localisation du  user",
     *  statusCodes={
     *     200="Retourné quand tout est OK !"
     *  }
     * )
     */
    public function getAppAuthAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        /** @var User $app */
        $app = $em->getRepository("AppBundle:User")->findOneByemail("app@funglobe.com");
        if(!$app)
        {
            $app =  $this->init()->getUser();
        }
        /** @var AuthToken $authtoken */
        $authtoken = $em->getRepository("AppBundle:AuthToken")->findOneBy(["user"=>$app],["id"=>"DESC"]);

        $authtoken->setCreatedAt(new \DateTime());

        $em->flush();

        return $this->json($authtoken);
    }



    // Recuperer les parametre de localisation
    /**
     * @Rest\Get("/geolocation")
     * @return Response
     * @ApiDoc(
     *  resource=true,
     *  description="Recuperer les paramettre pour la localisation du  user qui  se connecte",
     *  statusCodes={
     *     200="Retourné quand tout est OK !"
     *  }
     * )
     */
    public function getGeolocationAction(Request $request)
    {

       try{
           //recuperation du  manager
           $em = $this->getDoctrine()->getManager();
          // return $this->json(["message"=>"Connexion not found"],400);
           if(!$this->isConnected()){
               if(!$this->isConnectedHttps()){
                   return $this->json(["message"=>"Connexion not found"],400);
               }
           }
           //recuperer les information  concernant la ville et le pays du  user.
           $geolocation = $this->geolocation();

           return $this->json($geolocation);
       }
       catch(Exception $ex)
       {
           // 4- recuperer l'erreur
           //$errors = $ipLite->getError();
               $this->json(['message'=>$ex->getMessage()],$ex->getCode());
       }
        return $this->json(["error"=>"connection not found"]);
    }

    function isConnected()
    {
        // use 80 for http or 443 for https protocol
        $connected = @fsockopen("www.example.com", 80);
        if ($connected){
            fclose($connected);
            return true;
        }
        return false;
    }

    function isConnectedHttps()
    {
        // use 80 for http or 443 for https protocol
        $connected = @fsockopen("www.example.com", 443);
        if ($connected){
            fclose($connected);
            return true;
        }
        return false;
    }

   public  function  geolocation()
    {
        // 1- instanciation de la classe
        $ipLite = new ip2locationlite();

        // 2- set  la cle
        $ipLite->setKey('b155f2730de74d66e36cdd82662dc276adb6f90473ac2c8bff03de4f7a575d6e');

        //3- recuperer le location
        //$locations = $ipLite->getCountry('41.202.219.77');
        $locations = $ipLite->getCity($this->getIp());
        //$location =null;

        //recuperer les information en cas de success
        $geolocation = new Geolocation();


        if (!empty($locations) && is_array($locations)) {
            $geolocation->setIpAddress($locations["ipAddress"]);
            $geolocation->setCountryCode($locations["countryCode"]);
            $geolocation->setCountryName($locations["countryName"]);
            $geolocation->setRegionName($locations["regionName"]);
            $geolocation->setCityName($locations["cityName"]);
            $geolocation->setLatitude($locations["latitude"]);
            $geolocation->setLongitude($locations["longitude"]);
            $geolocation->setTimeZone($locations["timeZone"]);
        }

        return $geolocation;
    }


    // Fonction pour confirme l'adresse email

    /**
     * @Rest\Put("/confirm/email")
     * @return Response
     * @ApiDoc(
     *  resource=true,
     *  description="Confirm l'adresse email  de l'utilisateur connecté ",
     *  statusCodes = {
     *      200 = "Updated (seems to be OK)",
     *      400 = "Bad request (see messages)",
     *      401 = "Unauthorized, you must login first",
     *      404 = "Not found",
     *  },
     *  parameters={
     *     {"name"="email", "dataType"="string", "required"=true, "description"="email  de l'utilisateur connecté"}
     *  }
     * )
     */
    public function ConfirmEmailAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

       // return $this->json(["email"=>$request->get("email")],400);
        /** @var User $user */
        $user = $em->getRepository("AppBundle:User")->findOneBy(["email"=>$request->get("email")],["id"=>"DESC"]);
        //return $this->json(["user"=>$user],400);
        if(is_object($user))
        {
            $user->setIsEmailVerified(true);
            $em->flush();
        }
        else{
            return $this->userNotFound();
        }

        return $this->json($user);
    }




    // fonction pour enregister un utilisateur

    /**
     * @Rest\Post("/auth/register")
     * @Rest\View
     * @ApiDoc(
     *  resource=true,
     *  description="Save a user ",
     *  statusCodes = {
     *      200 = "Updated (seems to be OK)",
     *      400 = "Bad request (see messages)",
     *      401 = "Unauthorized, you must login first",
     *      404 = "Not found",
     *  },
     *  parameters={
     *     {"name"="firtsname", "dataType"="string", "required"=true, "description"="user firstname "},
     *     {"name"="email", "dataType"="string", "required"=true, "description"="User email adresse"},
     *     {"name"="password", "dataType"="string", "required"=true, "description"="User password"},
     *     {"name"="lastName", "dataType"="string", "required"=false, "description"="User  last name"},
     *     {"name"="isOnline", "dataType"="boolean", "required"=true, "description"="user current  statut"},
     *     {"name"="birthDate", "dataType"="date", "required"=true, "description"="User  current  prosession"},
     *     {"name"="profession", "dataType"="string", "required"=false, "description"="Nom d'un utilisateur"},
     *     {"name"="type", "dataType"="string", "required"=true, "description"="User  type"},
     *     {"name"="relationshipStatus", "dataType"="string", "required"=false, "description"="User  relationship Status"},
     *     {"name"="joinReason", "dataType"="string", "required"=false, "description"="User  join reason"},
     *     {"name"="joinDate", "dataType"="datetime", "required"=false, "description"="Date where user signUp"},
     *     {"name"="isEmailVerified", "dataType"="boolean", "required"=true, "description"="Verify  email  adresse "},
     *     {"name"="isVip", "dataType"="boolean", "required"=true, "description"="privilege for user"},
     *     {"name"="gender", "dataType"="string", "required"=true, "description"="User gender"},
     *     {"name"="phones", "dataType"="array", "required"=false, "description"="User phones number"},
     *     {"name"="profileVisibility", "dataType"="array", "required"=false, "description"="List  autorisation options"}
     *  }
     * )
     */
    public function registerAction(Request $request)
    {
        $user =new User();
        $val = $request->request;
        $user = $this->fillUser($request, $user);
        $user->setPassword($val->get("password"));
        $password = $this->encodePassword(new User(), $user->getPassword(), $user->getSalt());
        $user->setConfirmPassword(hash('sha256',$user->getPassword()));
        $user->setPassword($password);
        $em = $this->getDoctrine()->getManager();


        ///email
        $params =json_decode($val->get("params")) ;
        $to  = $user->getEmail();
        $objet  = $message_body = $this->get('translator')->trans('form.help.emailConfirm.objet',[],'register');;
        $url  = $params->url;
        $urlPassword = $params->urlPassword;
        $name  = $params->name;
        $password  = $params->password;
        $logo  = $params->logo;
        $confirm  = $params->confirm;
        $locale  = $params->locale;
        $array = ["confirm"=>$confirm,"_locale"=>$locale,"email"=>$to, "name"=>$name, "password"=>$password,"urlPassword"=>$urlPassword, "url"=>$url,"logo"=>$logo, "key"=>md5($password.$to)];
        $user->setEmailToken($array);

        $from = $this->getParameter('mailer_user');
        $view = "ApiBundle:Mail:emailConfirm.html.twig";


        //recuperation du  manager
        $em = $this->getDoctrine()->getManager();

        $state = true;
        if(!$this->isConnected()){
            if(!$this->isConnectedHttps()){
                $state =false;
            }
        }
        if($state)
        {
            //recuperer les information  concernant la ville et le pays du  user.
            $geolocation = $this->geolocation();
            $profile = new UserProfile();
            $profile->setGpsPosition(["long"=>$geolocation->getLongitude(), "lart"=>$geolocation->getLatitude(), "country"=>$geolocation->getCountryName()]);
            $profile->setCreateDate(new \DateTime());
            $user->setCity($geolocation->getCityName());
            $profile->setGeolocation($geolocation);
            $profile->setUser($user);
            $em->persist($profile);
            $em->flush();
            $em->detach($profile);
        }
        else
        {
            //enregistrement  du  user
            $em->persist($user);
            $em->flush();
            $em->detach($user);
        }

        //envoi  du  mail
        $code = $this->sendMail($to,$from,$view,$array,$objet);

        /* @var $user User */
       /* $user =$em->getRepository('AppBundle:User')->findOneByemail($user->getEmail());
        $this->authenticateUser($user);
        */
        return $this->json($user);

    }



    // Action pour  renvoyer  l'email  à  nouveau

    /**
     * @Rest\Put("/auth/verify/email")
     * @return Response
     * @ApiDoc(
     *  resource=true,
     *  description="Envoi  a nouveau un email  a l'utilisateur connecté ",
     *  statusCodes = {
     *      200 = "Updated (seems to be OK)",
     *      400 = "Bad request (see messages)",
     *      401 = "Unauthorized, you must login first",
     *      404 = "Not found",
     *  },
     *  parameters={
     *     {"name"="email", "dataType"="string", "required"=true, "description"="email  de l'utilisateur connecté"}
     *  }
     * )
     */
    public function VerifyEmailAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var User $user */
        $user = $em->getRepository("AppBundle:User")->findOneBy(["email"=>$request->get("email")],["id"=>"DESC"]);

        ///email
        $params =$user->getEmailToken();
        $to  = $user->getEmail();
        $objet  = $message_body = $this->get('translator')->trans('form.help.emailConfirm.objet',[],'register');
        $url  = $params["url"];
        $urlPassword = $params["urlPassword"];
        $name  = $params["name"];
        $password  = $params["password"];
        $logo  = $params["logo"];
        $confirm  = $params["confirm"];
        $locale  = $params["_locale"];

        $from = $this->getParameter('mailer_user');
        $array = ["confirm"=>$confirm,"_locale"=>$locale,"email"=>$to, "name"=>$name, "password"=>$password,"urlPassword"=>$urlPassword, "url"=>$url,"logo"=>$logo, "key"=>md5($password.$to)];
        $view = "ApiBundle:Mail:emailConfirm.html.twig";
        $code = $this->sendMail($to,$from,$view,$array,$objet);

        return $this->json($user);
    }




    public  function sendMail($to, $from, $routeview, $parm,$subjet)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject($subjet)
            ->setFrom($from)
            ->setTo($to)
            ->setBody($this->renderView($routeview, $parm))
            ->setContentType('text/html');
        return $this->get('mailer')->send($message);

    }







    //action  pour authentifier un utilisateur
    /**
     * @Rest\Post("/auth/login")
     * @return Response
     * @ApiDoc(
     *  resource=true,
     *  description="authentificate use. the login can be : email adresse or username ",
     *  statusCodes = {
     *      200 = "Updated (seems to be OK)",
     *      400 = "Bad request (see messages)",
     *      401 = "Unauthorized, you must login first",
     *      404 = "Not found",
     *  },
     *  parameters={
     *     {"name"="_username", "dataType"="string", "required"=true, "description"="User  name  or email  adress"},
     *     {"name"="_password", "dataType"="string", "required"=true, "description"="the password"}
     *  }
     * )
     */
    public function loginAction(Request $request)
    {

        //$val= json_decode($request->getContent());
        $val= $request->request;
        $user = new User();
        $em = $this->getDoctrine()->getManager();
        /** @var User $user */
        $user = $em->getRepository("AppBundle:User")->findOneBy(["username"=> $val->get("_username"),"confirmPassword"=>hash('sha256',$val->get("_password"))],["id"=>"DESC"]);
        if(!$user)
        {
            $user = $em->getRepository("AppBundle:User")->findOneBy(["email"=> $val->get("_username"),"confirmPassword"=>hash('sha256',$val->get("_password"))],["id"=>"DESC"]);
        }

        if(!$user){
            return $this->invalidCredentials();
        }

        $user->setIsOnline(true);
        $em->flush();

        $auth = $this->authenticateUser($user);
        return $this->json($auth);
    }

    // Recuper le auth correspondant au  user app
    private function isgrantUser($role){

        $service = $this->get('security.authorization_checker');
        if ($service->isGranted($role) === FALSE) {
            throw new AccessDeniedException();
        }
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
    private  function  fillUser(Request $request, User $user)
    {
        $log = $logger = $this->get('logger');

        $val = $request->request;
        $username = $val->get("email");
        $bd = str_replace("/", "-", $val->get('birthDate'));

        $date = new \DateTime($bd);
        //$date = new \DateTime($val->get('birthDate'));
        // set  user with  application values
        $user->setEmail($val->get('email'))->setType($val->get('type'))
            ->setBirthDate($date)->setFirstName($val->get('firstname'))->setCountry($val->get('country'))
            ->setGender($val->get('profession'))->setUsernameCanonical($username)->setEmailCanonical($val->get('email'));

        $user->setEnabled(true)->setIsEmailVerified(false)->setRoles(["ROLE_MEMBER"])
            ->setUsername($username)->setIsOnline(true)->setIsVip(false)->setJoinDate(new \DateTime());

        $user->setGender($val->get('gender'));
        $user->setJoinReason($val->get('joinReason'));
        $user->setLastName($val->get('lastname'));

        //quelques logs pour verifier les valeurs des parametres
        $log->debug("The user gender is ".$val->get('gender'));
        $log->debug("The user email is ".$val->get('email'));
        $log->debug("The user himself is is ".$user);

        return $user;
    }


    // exeception for user not  found
    private function userNotFound()
    {
        return \FOS\RestBundle\View\View::create(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
    }




    private function invalidCredentials()
    {
        return \FOS\RestBundle\View\View::create(['message' => 'Password or Login is bad'], Response::HTTP_BAD_REQUEST);
    }



    // Initialise  l'utilisateur  système
    public function  init()
    {
        $user = new User();
        $user->setPlainPassword("app");
        $password = $this->encodePassword(new User(), $user->getPlainPassword(), $user->getSalt());
        $user->setConfirmPassword(hash('sha256',$user->getPassword()));
        $user->setPassword($password);
        $user->setConfirmPassword(hash('sha256',$user->getPlainPassword()))->setCountry("CM");
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

    /**
     * Récupérer la véritable adresse IP d'un visiteur
     */
    function getIp() {
        // IP si internet partagé
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }
        // IP derrière un proxy
        elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        // Sinon : IP normale
        else {
            return (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '');
        }
    }

}