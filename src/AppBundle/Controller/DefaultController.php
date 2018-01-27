<?php

namespace AppBundle\Controller;

use AppBundle\Entity\AuthToken;
use AppBundle\Entity\Files;
use AppBundle\Entity\Geolite;
use AppBundle\Entity\GeoliteIp;
use AppBundle\Entity\Geolocation;
use AppBundle\Entity\ip2locationlite;
use AppBundle\Entity\PasswordReset;
use AppBundle\Entity\User;
use AppBundle\Entity\UserPhoto;
use AppBundle\Entity\UserProfile;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;

class DefaultController extends FOSRestController
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {

        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);
    }





    /**
     * @Route("/test/{email}", name="testpage")
     */
    public function testAction(Request $request, $email)
    {

        $code = $this->sendMail($email, $this->getParameter('mailer_user'), "I am  just  a test", "good work");
        // replace this example code with whatever you need
        return $this->json("Veillez consulter la boite mail <a href=''>". $email."<a>");
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


    /**
     * @Route("/update", name="update_info")
     */
    public function updateAction(Request $request)
    {

      $em = $this->getDoctrine()->getManager();
        $listUser  = $em->getRepository("AppBundle:User")->findAll();
        $listProfile  = $em->getRepository("AppBundle:UserProfile")->findAll();

        /** @var Geolocation $geolocation */
        $geolocation = $this->geolocation();

        if($listUser!=null)
        {
            /** @var User $user */
            foreach($listUser as $user)
            {
                $test = false;
                if($listProfile!=null)
                {
                    /** @var UserProfile $profile */
                    foreach($listProfile as $profile)
                    {
                        if($profile->getUser()->getId()==$user->getId())
                        {
                            $test= true;
                        }
                    }
                }
                if(!$test)
                {
                    $geo = new Geolocation();
                    $geo = $geolocation;
                    $newprofile = new UserProfile();
                    $newprofile->setUser($user);
                    $newprofile->setCreateDate(new \DateTime());
                    $newprofile->setGeolocation($geo);
                    $em->persist($newprofile);
                    $em->flush();
                    $em->detach($newprofile);
                }

                if($user->getJoinReason()==null)
                {
                    $user->setJoinReason(rand(1,3));
                    $em->flush();
                }
            }
        }
        return  $this->json(["result"=>"WIN"]);
    }



    // fonction pour supprimer un dossier connaissant  l'id du  user
    /**
     * @Route("/deleteDir/{id}", name="app_deleteDir")
     */
    public function deleteDirAction($id,Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var User $user */
        $user  = $em->getRepository("AppBundle:User")->find($id);
        //on recupere au  moins une photo du  user
        /** @var UserPhoto $firstPicture */
        $firstPicture  = $em->getRepository("AppBundle:UserPhoto")->findOneByuser($user);
        if($firstPicture!=null)
        {
            $file = new Files();
            //on supprime le dossier des photos du  user en passant  le dossier en question en parametre
            $file->deleteDir($firstPicture->getFolder());
        }
        return  $this->json(["result"=>"DELETE AS SUCCESSFULL"]);
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

    public  function sendMail($to, $from, $body,$subjet)
    {
        // ->setReplyTo('xxx@xxx.xxx')

        $message = \Swift_Message::newInstance()
            ->setSubject($subjet)
            ->setFrom($from) // 'info@achgroupe.com' => 'Achgroupe : Course en ligne '
            ->setTo($to)
            ->setBody($body)
            //'MyBundle:Default:mail.html.twig'
            ->setContentType('text/html');
        return $this->get('mailer')->send($message);

    }
    //fonction pour inialiser quelques utilisateurs
    public function saveUser()
    {

       $user = new User();
        $user->setPlainPassword("admin");
        $password = $this->encodePassword(new User(), $user->getPlainPassword(), $user->getSalt());
        $user->setConfirmPassword(hash('sha256',$user->getPassword()));
        $user->setPassword($password);
        $user->setConfirmPassword(hash('sha256',$user->getPlainPassword()))->setCountry("BE");
        $user->setEnabled(true)->setIsEmailVerified(false)->setEmail("contact@funglobe.com")->setBirthDate(new \DateTime())->setRoles(["ROLE_ADMIN"])
            ->setFirstName("Admin")->setGender("M")->setIsOnline(false)->setIsVip(false)->setType("System")->setUsername("admin")->setJoinDate(new \DateTime());

        $em = $this->getDoctrine()->getManager();
        $exist = $em->getRepository('AppBundle:User')->findOneByemail($user->getEmail());
        if($exist==null)
        {
            $em->persist($user);
        }

        $em->flush();
        $em->detach($user);

        $user = new User();
        $user->setPlainPassword("moderator");
        $password = $this->encodePassword(new User(), $user->getPlainPassword(), $user->getSalt());
        $user->setConfirmPassword(hash('sha256',$user->getPassword()));
        $user->setPassword($password);
        $user->setConfirmPassword(hash('sha256',$user->getPlainPassword()))->setCountry("CA");
        $user->setEnabled(true)->setIsEmailVerified(false)->setEmail("info@funglobe.com")->setBirthDate(new \DateTime())->setRoles(["ROLE_MODERATOR"])->setUsername("moderator")
            ->setFirstName("Moderator")->setGender("F")->setIsOnline(false)->setIsVip(false)->setType("System")->setJoinDate(new \DateTime());

        $em = $this->getDoctrine()->getManager();
        $exist = $em->getRepository('AppBundle:User')->findOneByemail($user->getEmail());
        if($exist==null)
        {
            $em->persist($user);
        }

        $em->flush();
        $em->detach($user);
    }

    // fonction  pour creer l'application et le token de base ET Creer quelques user
    /**
     * @Route("/init", name="app_init")
     */
    public function initAction(Request $request)
    {
        $authtoken = $this->init();
        $this->saveUser();
        return $this->json($authtoken);
    }


    // Initialise l'utilisateur systèmes
    public function  init()
    {
        $user = new User();
        $user->setPlainPassword("app");
        $password = $this->encodePassword(new User(), $user->getPlainPassword(), $user->getSalt());
        $user->setConfirmPassword(hash('sha256',$user->getPassword()));
        $user->setPassword($password);
        $user->setConfirmPassword(hash('sha256',$user->getPlainPassword()))->setCountry("DE");
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



    // encode le mot  de passe
    public function encodePassword($object, $password, $salt)
    {
        $factory = $this->get('security.encoder_factory');
        $encoder = $factory->getEncoder($object);
        $password = $encoder->encodePassword($password, $salt);

        return $password;
    }


    //Charge toutes les villes et pays contenu dans le fichier passe en parametre dans une liste
    /**
     * @Route("/init/location", name="geolitelocation")
     */
    public function GeoliteLocation()
    {
        $files = new Files();
        $directory = "geolite";
        $initialDirectory = str_replace("//","/", str_replace("\\","/",$files->getAbsolutPath_other($directory)));
        $file_name  =$initialDirectory."IP2PROXY-LITE-PX3.csv";
        $file = fopen($file_name, "r+");
        $em = $this->getDoctrine()->getManager();
        //$countRow = substr_count( $file, "\n" );
        while ($row = fgets($file)) {
                $geolite =new Geolite();
                $tab = explode(",",$row);
                $geolite->setIpFrom($tab[0]);
                $geolite->setIpTo($tab[1]);
                $geolite->setProxyType($tab[2]);
                $geolite->setCountryCode($tab[3]);
                $geolite->setCountry($tab[4]);
                $geolite->setRegion($tab[5]);
                $geolite->setCity($tab[6]);
                $em->persist($geolite);
                $em->flush();
        }
        fclose($file);
        $array =[];


        $array['items'] = $em->getRepository("AppBundle:Geolite")->findAll();
        return $this->render("AppBundle:Default:index.html.twig",$array);
    }



    //Charge toutes les villes et pays contenu dans le fichier passe en parametre dans une liste (adresse ip)
    /**
     * @Route("/init/block", name="geolitblock")
     */
    public function GeoliteBlock()
    {
        $files = new Files();
        $directory = "Geolite";
        $initialDirectory = str_replace("//","/", str_replace("\\","/",$files->getAbsolutPath_other($directory)));
        $file_name  =$initialDirectory."GeoLiteCity-Blocks.csv";
        $file = fopen($file_name, "r+");
        $em = $this->getDoctrine()->getManager();
        //$countRow = substr_count( $file, "\n" );
        $begin =1;

        while ($row = fgets($file)) {
            if($begin>2)
            {
                $geolite =new GeoliteIp();
                $tab = explode(",",$row);
                $geolite->setStartIpNum($tab[0]);
                $geolite->setEndIpNum($tab[1]);
                $geolite->setLocId($tab[2]);
                $exit = $em->getRepository("AppBundle:GeoliteIp")->findOneBystartIpNum($geolite->getStartIpNum());
                if(!is_object($exit))
                {
                    $em->persist($geolite);
                    $em->flush();
                }
            }
            //var_dump("city : ".$geolite->getCity()." | region :".$geolite->getRegion())
            $begin++;
        }
        fclose($file);
        $array =[];


        $array['items'] = $em->getRepository("AppBundle:GeoliteIp")->findAll();
        return $this->render("AppBundle:Default:geolite-block.html.twig",$array);
    }




}
