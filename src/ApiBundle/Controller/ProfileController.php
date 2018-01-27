<?php

namespace ApiBundle\Controller;


use AppBundle\Entity\AuthToken;
use AppBundle\Entity\CityFile;
use AppBundle\Entity\Credentials;
use AppBundle\Entity\Files;
use AppBundle\Entity\SchoolLive;
use AppBundle\Entity\User;
use AppBundle\Entity\UserMessage;
use AppBundle\Entity\UserPhoto;
use AppBundle\Entity\UserProfile;
use AppBundle\Tools\HelpersController;
use AppBundle\Tools\SecurityController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use  Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AccountStatusException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Validator\Constraints\DateTime;

class ProfileController extends FOSRestController
{


    /**
     * @Rest\Get("/auth/user/base")
     * @return Response
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Retourne toutes les éléments de bases pour la partie user",
     *  statusCodes={
     *     200="the query is ok",
     *     401= "The connection is required",
     *     403= "Access Denied"
     *
     *  },
     *  parameters={
     *     {"name"="id", "dataType"="integer", "required"=true, "description"="L'identifiant de l'utilisateur connecté "},
     *  }
     * )
     */
    public function baseprofileAction(Request $request)
    {

        $id = $request->get("id");

        $em = $this->getDoctrine()->getManager();

        /** @var User $user */
        $user = $em->getRepository("AppBundle:User")->find($id);


        //recuperer la liste des photos de profiles
        $listeProfilesPhotos =  $em->getRepository("AppBundle:UserPhoto")->findBy(["isProfile" => true], ["updateDate" => "DESC"]);

        //recuperer la liste des photos de profiles
        $listeProfiles =  $em->getRepository("AppBundle:UserProfile")->findBy([], ["createDate" => "DESC"]);

        $users =[];
        $list = $em->getRepository("AppBundle:User")->findBy([],["joinDate"=>"DESC"]);

        /** @var User $member */
        foreach($list as $member)
        {
            $array = ["user"=>$member,
                        "profile"=>$this->getProfileUser($listeProfiles,$member),
                        "photoProfile"=>$this->getProfileUserPhotos($listeProfilesPhotos,$member),
                        "photos"=>$this->getProfileUserPhotosList($listeProfilesPhotos,$member)
                     ];
            $users[] = $array;
        }

        $vips =[];
        $list = $em->getRepository("AppBundle:User")->findBy(["isVip"=>true],["joinDate"=>"DESC"]);
        /** @var User $member */
        foreach($list as $member)
        {
            $array = ["user"=>$member,
                "profile"=>$this->getProfileUser($listeProfiles,$member),
                "photoProfile"=>$this->getProfileUserPhotos($listeProfilesPhotos,$member),
                "photos"=>$this->getProfileUserPhotosList($listeProfilesPhotos,$member)
            ];
            $vips[] = $array;
        }

        $recieversHelp = $em->getRepository("AppBundle:Request")->findBy(["receiver"=>$user],["createDate"=>"DESC"]);

        $recievers =null;
         /** @var \AppBundle\Entity\Request $requests */
        foreach($recieversHelp  as $requests )
        {
            if(!$requests->getState() and $requests->getDecision()=="0" ){
                $recievers[] = [
                    "photoReciever"=>$this->getProfileUserPhotos($listeProfilesPhotos,$requests->getReceiver()),
                    "photoApplicant"=>$this->getProfileUserPhotos($listeProfilesPhotos,$requests->getApplicant()),
                    "request"=>$requests
                ];
            }
        }


        $applicantsHelp = $em->getRepository("AppBundle:Request")->findBy(["applicant"=>$user],["createDate"=>"DESC"]);

        $applicants =null;
         /** @var \AppBundle\Entity\Request $requests */
        foreach($applicantsHelp  as $requests )
        {
            if(!$requests->getState() and $requests->getDecision()=="0" ) {
                $applicants[] = [
                    "photoReciever"=>$this->getProfileUserPhotos($listeProfilesPhotos,$requests->getReceiver()),
                    "photoApplicant"=>$this->getProfileUserPhotos($listeProfilesPhotos,$requests->getApplicant()),
                    "request"=>$requests
                ];
            }
        }


        $listUserMessages =  $em->getRepository("AppBundle:UserMessage")->findBy(["receiver"=>$user],["readDate"=>"DESC"]);
        $recieverMessages =null;
        /** @var UserMessage $userMessage */
        foreach($listUserMessages as $userMessage)
        {
            if($userMessage->getIsSee()==null  || !$userMessage->getIsSee())
            {
                $friend = $userMessage->getMessage()->getSender();
                $friendProfile = $this->getProfileUserPhotos($listeProfilesPhotos,$friend);
                $userProfile = $this->getProfileUserPhotos($listeProfilesPhotos, $user);
                $recieverMessages[] = [
                    "id"=>$userMessage->getMessage()->getId(),
                    "friendProfile" => $friendProfile,
                    "userProfile" => $userProfile,
                    "friend" => $friend,
                    "userMessage" => $userMessage
                ];
            }
        }

        //regrouper les resultats

        $listHelp = $recieverMessages;
        $recieverMessages =null;

       if($listHelp!=null)
       {

           foreach($users as $userHelp)
           {
               /** @var User $member */
               $member = $userHelp['user'];
               $countMessage = 0;
               $array =null;
               foreach($listHelp as $userMessageHelp)
               {
                   /** @var User $friend */
                   $friend = $userMessageHelp['friend'];
                   if($friend->getId() == $member->getId())
                   {
                       $countMessage++;
                       $array = [
                           "id"=>$userMessageHelp['id'],
                           "friendProfile" => $userMessageHelp['friendProfile'],
                           "userProfile" => $userMessageHelp['userProfile'],
                           "friend" => $userMessageHelp['friend'],
                           "count" => $countMessage,
                           "userMessage" => $userMessageHelp['userMessage']
                       ];
                   }

               }

               if($countMessage>0)
               {
                   $recieverMessages[] = $array;
               }

           }
       }


        $array =[
            //liste des demandes d'amitiers pour le users connecte
            "applicants"=>$applicants,
            // liste des invitations pour le users connecte
            "recievers"=> $recievers,
            //user connecte
            "user"=> $user,
            //liste des messages recue pour le users connecte
            "recieveMessages"=>  $recieverMessages,
            //liste des messages envoyés pour le users connecte
            "sendMessages"=>$em->getRepository("AppBundle:UserMessage")->getSendMessage(["sender_id"=>$user->getId()]),
            //liste des photos des utilisatdeurs
            "photos"=> $em->getRepository("AppBundle:UserPhoto")->findBy(["user"=>$user],["createDate"=>"DESC"]),
            //liste des photos de profiles  du  user en connecte
            "profilePhotos"=> $this->getProfileUserPhotosList($listeProfilesPhotos,$user),
            // parametres de configurations
             "config"=>$em->getRepository("AppBundle:SearchCriteria")->findOneBy(["user"=>$user],["createDate"=>"DESC"]),
             // liste des utilisateurs complete avec photo et profile
            "users"=>$users,
            // liste des utilisateurs complete vips avec photo et profile
             "vips"=>$vips
        ];


        return $this->json($array);
    }


    public function  getProfileUserPhotos($liste,User $user)
    {
        /** @var UserPhoto $profile */
        foreach($liste as $profile)
        {
            if($profile->getUser()->getId() == $user->getId())
            {
                return $profile;
            }
        }
        return null;
    }

    public function  getProfileUserPhotosList($liste,User $user)
    {
        $list =null;
        /** @var UserPhoto $profile */
        foreach($liste as $profile)
        {
            if($profile->getUser()->getId() == $user->getId())
            {
                $list[] = $profile;
            }
        }
        return $list;
    }

    public function  getProfileUser($liste,User $user)
    {
        /** @var UserProfile $profile */
        foreach($liste as $profile)
        {
            if($profile->getUser()->getId() == $user->getId())
            {
                return $profile;
            }
        }
        return null;
    }

    /**
     * @Rest\Get("/auth/user/city")
     * @return Response
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Retourne la liste des villes d'un pays",
     *  statusCodes={
     *     200="the query is ok",
     *     401= "The connection is required",
     *     403= "Access Denied"
     *
     *  },
     *  parameters={
     *     {"name"="country", "dataType"="string", "required"=true, "description"="Le pays à filtrer"},
     *  }
     * )
     */
    public function matchCityAction(Request $request)
    {


        return $this->json(["list"=>"Non gere "]);
    }


    // retourne la liste des photos d'un utilisateur en se basant  sur son id
    /**
     * @Rest\Get("/auth/user/photo/list")
     * @return Response
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Retourne la liste des photos d'un user ",
     *  statusCodes={
     *     200="the query is ok",
     *     401= "The connection is required",
     *     403= "Access Denied"
     *
     *  },
     *  parameters={
     *     {"name"="id", "dataType"="int", "required"=true, "description"="Identifiant du  user connecte"},
     *  }
     * )
     */
    public function getPhotosAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $id = $request->get("id");
        /** @var User $user */
        $user = $em->getRepository('AppBundle:User')->find($id);
        $list = $em->getRepository("AppBundle:UserPhoto")->findBy(["user"=>$user],["createDate"=>"DESC"]);
        return $this->json($list);
    }




    // retourne la liste des photos de profile d'un user en sebasant sur l'id
    // retourne la liste des photos d'un utilisateur en se basant  sur son id
    /**
     * @Rest\Get("/auth/user/photo/profile")
     * @return Response
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Retourne la liste des photos d'un user ",
     *  statusCodes={
     *     200="the query is ok",
     *     401= "The connection is required",
     *     403= "Access Denied"
     *
     *  },
     *  parameters={
     *     {"name"="id", "dataType"="int", "required"=true, "description"="Identifiant du  user connecte"},
     *  }
     * )
     */
    public function getProfilePhotosAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $id = $request->get("id");
        /** @var User $user */
        $user = $em->getRepository('AppBundle:User')->find($id);
        $list = $em->getRepository("AppBundle:UserPhoto")->findBy(["user"=>$user,"isProfile"=>true],["updateDate"=>"DESC"]);
        return $this->json($list);
    }





    // Suprime une photo en se basant  de son hashname et retourne la liste de ses nouvelles photos
    /**
     * @Rest\Delete("/auth/user/photo/delete")
     * @return Response
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Supprime la photo d'un utilisateur",
     *  statusCodes={
     *     200="the query is ok",
     *     401= "The connection is required",
     *     403= "Access Denied"
     *
     *  },
     *  parameters={
     *     {"name"="hashname", "dataType"="string", "required"=true, "description"="hashname de la photo"},
     *     {"name"="state", "dataType"="string", "required"=true, "description"="fournir l'information sur la page à  retourner"}
     *  }
     * )
     */
    public function deletePhotosAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $hashname = $request->get("hashanme");
        $state = $request->get("state");
        //return $this->json(["hashname"=>$hashname, "state"=>$state],400);
        /** @var UserPhoto $photo */
        $photo = $em->getRepository('AppBundle:UserPhoto')->findOneByhashname($hashname);
        /** @var User $user */
        $user = $photo->getUser();

        //supprimer la photo dans le serveur de fichier
        $fileName = $photo->getHashname();
        $file = new Files();
        $directory = "photo/user".$user->getId();
        $initialDirectory = str_replace("//","/", str_replace("\\","/",$file->getAbsolutPath($file->initialpath).$directory));
       // $path = $initialDirectory."/".$fileName;
        $file->delete($initialDirectory,$fileName);

        //supprimer la photo dans la bd
        $em->remove($photo);
        $em->flush();

        if($state=="list")
        {
            $list = $em->getRepository("AppBundle:UserPhoto")->findBy(["user"=>$user],["publishedDate"=>"DESC","updateDate"=>"DESC","createDate"=>"DESC"]);
        }
        else{
            $list = $em->getRepository("AppBundle:UserPhoto")->findBy(["user"=>$user,"isProfile"=>true],["publishedDate"=>"DESC","updateDate"=>"DESC"]);
        }
        return $this->json($list);
    }


    // publier ou  rendre un photo private et retourne la liste de ses nouvelles photos
    /**
     * @Rest\PUT("/auth/user/photo/published")
     * @return Response
     *
     * @ApiDoc(
     *  resource=true,
     *  description="publie ou rend une photo privee",
     *  statusCodes={
     *     200="the query is ok",
     *     401= "The connection is required",
     *     403= "Access Denied"
     *
     *  },
     *  parameters={
     *     {"name"="hashname", "dataType"="string", "required"=true, "description"="hashname de la photo"},
     *     {"name"="status", "dataType"="string", "required"=true, "description"="est un boolean qui  informe qui donne le choix entre rentre prive (0) ou publier "},
     *     {"name"="state", "dataType"="string", "required"=true, "description"="fournir l'information sur la page à  retourner"}
     *  }
     * )
     */
    public function publishedPhotosAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $hashname = $request->get("hashname");
        $status = $request->get("status");
        $state = $request->get("state");
        /** @var UserPhoto $photo */
        $photo = $em->getRepository('AppBundle:UserPhoto')->findOneByhashname($hashname);

        if($status=="0"|| $status==0)
        {
            $photo->setPublishedDate(null);
            $photo->setVisibility("private");
        }
        else{
            $photo->setPublishedDate(new \DateTime());
            $photo->setVisibility("public");
        }
        $em->flush();
        /** @var User $user */
        $user = $photo->getUser();
        if($state=="list")
        {
            $list = $em->getRepository("AppBundle:UserPhoto")->findBy(["user"=>$user],["updateDate"=>"DESC","createDate"=>"DESC"]);
        }
        else{
            $list = $em->getRepository("AppBundle:UserPhoto")->findBy(["user"=>$user,"isProfile"=>true],["updateDate"=>"DESC"]);
        }
        return $this->json($list);
    }




    // rendre une photo comme photo de profile et retourne la liste de ses nouvelles photos
    /**
     * @Rest\PUT("/auth/user/photo/setprofile")
     * @return Response
     *
     * @ApiDoc(
     *  resource=true,
     *  description="set la photo de profile",
     *  statusCodes={
     *     200="the query is ok",
     *     401= "The connection is required",
     *     403= "Access Denied"
     *
     *  },
     *  parameters={
     *     {"name"="hashname", "dataType"="string", "required"=true, "description"="hashname de la photo"},
     *     {"name"="state", "dataType"="string", "required"=true, "description"="fournir l'information sur la page à  retourner"}
     *  }
     * )
     */
    public function setProfilePhotosAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $hashname = $request->get("hashname");
        $state = $request->get("state");
        /** @var UserPhoto $photo */
        $photo = $em->getRepository('AppBundle:UserPhoto')->findOneByhashname($hashname);

        $photo->setIsProfile(true);
        $photo->setUpdateDate(new \DateTime());
        $photo->setPublishedDate(new \DateTime());
        $photo->setVisibility("public");
        $em->flush();
        /** @var User $user */
        $user = $photo->getUser();
        if($state=="list")
        {
            $list = $em->getRepository("AppBundle:UserPhoto")->findBy(["user"=>$user],["updateDate"=>"DESC","createDate"=>"DESC"]);
        }
        else{
            $list = $em->getRepository("AppBundle:UserPhoto")->findBy(["user"=>$user,"isProfile"=>true],["updateDate"=>"DESC"]);
        }
        return $this->json(["list"=>$list,"profile"=>$photo]);
    }






    // retourne le detail  sur le profile d'un user en se basant sur son email
    /**
     * @Rest\Get("/auth/user/photo/profile/detail")
     * @return Response
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Retourne le detail sur un user en se basant sur son adresse email ",
     *  statusCodes={
     *     200="the query is ok",
     *     401= "The connection is required",
     *     403= "Access Denied"
     *
     *  },
     *  parameters={
     *     {"name"="email", "dataType"="string", "required"=true, "description"="emmail  de l'utilisateur selectionnée "},
     *     {"name"="id", "dataType"="int", "required"=true, "description"="Identifiant  du  user connecte"},
     *  }
     * )
     */
    public function getDetailAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $tab = explode('-',$request->get("email"));

        $idfriend = count($tab)>0? $tab[count($tab)-1]:0;
        $id = $request->get("id");

        //return $this->json(['id'=>$id, 'email'=>$email],400);

        //recupere les infos du user selectionné
        /** @var User $user */
        $user = $em->getRepository('AppBundle:User')->findOneBy(['id'=>$idfriend],["id"=>"DESC"]);

        /** @var User $receiver */
        $receiver = $em->getRepository('AppBundle:User')->find($id);

        //recupere la liste des utilisateurs en  commun
        $data = ['applicant_id'=>$user->getId(), 'receiver_id'=>$receiver->getId()];
        $ListHelp = $em->getRepository("AppBundle:Request")->getCommunFriend($data);
        $listFriends =null;
        if($ListHelp!=null)
        {
            /** @var \AppBundle\Entity\Request $requests */
            foreach($ListHelp  as $requests )
            {
                $listFriends[] = [
                    "photoReciever" => $em->getRepository("AppBundle:UserPhoto")->findOneBy(["user" => $requests->getReceiver(), "isProfile" => true], ["updateDate" => "DESC"]),
                    "photoApplicant" => $em->getRepository("AppBundle:UserPhoto")->findOneBy(["user" => $requests->getApplicant(), "isProfile" => true], ["updateDate" => "DESC"]),
                    "request" => $requests
                ];
            }

        }

        // recupere  la liste des utilisateurs non commun
        $ListHelp = $em->getRepository("AppBundle:Request")->getAloneFriend($data);

        $listFriendsAlone =null;
        if($ListHelp!=null)
        {
            /** @var \AppBundle\Entity\Request $requests */
            foreach($ListHelp  as $requests )
            {
               if($requests->getReceiver()->getId()!=$receiver->getId() && $requests->getApplicant()->getId()!=$receiver->getId() )
               {
                   $listFriendsAlone[] = [
                       "photoReciever" => $em->getRepository("AppBundle:UserPhoto")->findOneBy(["user" => $requests->getReceiver(), "isProfile" => true], ["updateDate" => "DESC"]),
                       "photoApplicant" => $em->getRepository("AppBundle:UserPhoto")->findOneBy(["user" => $requests->getApplicant(), "isProfile" => true], ["updateDate" => "DESC"]),
                       "request" => $requests
                   ];
               }
            }

        }

        //recupere  la liste des photos
        $listPhotos = $em->getRepository("AppBundle:UserPhoto")->findBy(["user"=>$user,"visibility"=>"public"],["createDate"=>"DESC"]);

        //recupere la demande d'amitier
        /** @var \AppBundle\Entity\Request $ask */
        $ask = $em->getRepository('AppBundle:Request')->findOneBy(['applicant'=>$receiver,'receiver'=>$user],["id"=>"DESC"]);

        //recupere la recption  d'amitier
        /** @var \AppBundle\Entity\Request $reply */
        $reply = $em->getRepository('AppBundle:Request')->findOneBy(['applicant'=>$user,'receiver'=>$receiver],["id"=>"DESC"]);

        //recupere  la photo de profile du  user
        $list =$em->getRepository("AppBundle:UserPhoto")->findBy(["user"=>$user,"isProfile"=>true],["updateDate"=>"DESC"]);
        $profile = $list==null? null : $list[0];

        return $this->json(['ask'=>$ask,'reply'=>$reply,'user'=>$user, 'profile'=>$profile, 'listPhotos'=>$listPhotos, 'listFriends'=>$listFriends,  'listAloneFriends'=>$listFriendsAlone]);
    }




    // Ajouter une demande d'amitier
    /**
     * @Rest\Post("/auth/user/friend/ask")
     * @return Response
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Ajoute une nouvelle demande d'amitier ",
     *  statusCodes={
     *     200="the query is ok",
     *     401= "The connection is required",
     *     403= "Access Denied"
     *
     *  },
     *  parameters={
     *     {"name"="applicantId", "dataType"="int", "required"=true, "description"="l'identifiant de celui  qui  faire la demande (utilisateur connecte)"},
     *     {"name"="receiverEmail", "dataType"="string", "required"=true, "description"="L'email de celui  a qui  on faire la demande (utilisateur selectionné)"},
     *     {"name"="message", "dataType"="string", "required"=true, "description"="Message de demande d'amitier"},
     *  }
     * )
     */
    public function askFriendshipAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $applicantId = $request->get("applicantId");
        $tab = explode('-',$request->get("receiverEmail"));

        $idfriend = count($tab)>0? $tab[count($tab)-1]:0;

        $message = $request->get("message");

        //return $this->json(["applicant"=>$applicantId, "receiver"=>$receiverEmail, "message"=>$message],400);

        /** @var User $applicant */
        $applicant = $em->getRepository('AppBundle:User')->find($applicantId);

        /** @var User $receiver */
        $receiver = $em->getRepository('AppBundle:User')->findOneBy(['id'=>$idfriend], ["joinDate"=>'DESC']);

        $ask = new \AppBundle\Entity\Request();
        $ask->setCreateDate(new \DateTime());
        $ask->setApplicant($applicant);
        $ask->setReceiver($receiver);
        $ask->setMessage($message);
        $ask->setState(false);
        $ask->setDecision("0");
        $em->persist($ask);
        $em->flush();
        $em->detach($ask);

        if($request->get("page")!=null and $request->get("page")=='listFriend')
        {
            /** @var User $user */
            $user = $applicant;

            $ListHelp = $em->getRepository("AppBundle:Request")->findBy(['receiver'=>$user,"decision"=>"0","state"=>false],["createDate"=>"DESC"]);

            $listRecievers =null;
            if($ListHelp!=null)
            {
                /** @var \AppBundle\Entity\Request $requests */
                foreach($ListHelp  as $requests )
                {
                    $listRecievers[] = [
                        "photoReciever" => $em->getRepository("AppBundle:UserPhoto")->findOneBy(["user" => $requests->getReceiver(), "isProfile" => true], ["updateDate" => "DESC"]),
                        "photoApplicant" => $em->getRepository("AppBundle:UserPhoto")->findOneBy(["user" => $requests->getApplicant(), "isProfile" => true], ["updateDate" => "DESC"]),
                        "request" => $requests
                    ];
                }

            }


            $ListHelp = $em->getRepository("AppBundle:Request")->findBy(['applicant'=>$user,"decision"=>"0","state"=>false],["createDate"=>"DESC"]);

            $listApplicants =null;
            if($ListHelp!=null)
            {
                /** @var \AppBundle\Entity\Request $requests */
                foreach($ListHelp  as $requests )
                {
                    $listApplicants[] = [
                        "photoReciever" => $em->getRepository("AppBundle:UserPhoto")->findOneBy(["user" => $requests->getReceiver(), "isProfile" => true], ["updateDate" => "DESC"]),
                        "photoApplicant" => $em->getRepository("AppBundle:UserPhoto")->findOneBy(["user" => $requests->getApplicant(), "isProfile" => true], ["updateDate" => "DESC"]),
                        "request" => $requests
                    ];
                }

            }


            $ListHelp = $em->getRepository("AppBundle:Request")->findAll();

            $listUsers =  $em->getRepository("AppBundle:User")->findAll();


            $listMembers =null;
            if($ListHelp!=null )
            {
                /** @var User $member */
                foreach($listUsers as $member)
                {
                    $state =false;
                    /** @var \AppBundle\Entity\Request $requests */
                    foreach($ListHelp as $requests)
                    {
                        if($member->getId()==$requests->getApplicant()->getId() || $member->getId()==$requests->getReceiver()->getId())
                        {
                            $state =true;
                        }
                    }
                    if(!$state)
                    {
                        $listMembers[] = $member;
                    }
                }
            }
            else{
                $listMembers = $listUsers;
            }

            $listehelp1 = $listMembers;

            $listMembers =null;
            /** @var User $member */
            foreach($listehelp1 as $member)
            {
                $array = ["user"=>$member,
                    "profile"=>$em->getRepository("AppBundle:Profile")->findOneBy(["user"=>$member],["createDate"=>"DESC"]),
                    "photoProfile"=>$em->getRepository("AppBundle:UserPhoto")->findOneBy(["user"=>$member,"isProfile"=>true],["updateDate"=>"DESC"])];
                $listMembers[] = $array;
            }

            return $this->json(['user'=>$user,'listUsers'=>$listMembers, 'listApplicants'=>$listApplicants,  'listRecievers'=>$listRecievers]);
        }
        return $this->json(['ask'=>$ask]);
    }


    // Reponse a  une demande d'amitier
    /**
     * @Rest\Post("/auth/user/friend/reply")
     * @return Response
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Reponse a une nouvelle demande d'amitier ",
     *  statusCodes={
     *     200="the query is ok",
     *     401= "The connection is required",
     *     403= "Access Denied"
     *
     *  },
     *  parameters={
     *     {"name"="applicantId", "dataType"="int", "required"=true, "description"="l'identifiant de celui  qui  a fait la demande"},
     *     {"name"="receiverId", "dataType"="int", "required"=true, "description"="L'identifiant de celui  a qui  on a fait la demande la demande (utilisateur connecte)"},
     *     {"name"="decision", "dataType"="string", "required"=true, "description"="Decision de la reponse "},
     *  }
     * )
     */
    public function replyFriendshipAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $applicantId = $request->get("applicantId");
        $receiverId = $request->get("receiverId");
        $decision = $request->get("decision");

        /** @var User $applicant */
        $applicant = $em->getRepository('AppBundle:User')->find($applicantId);

        /** @var User $receiver */
        $receiver = $em->getRepository('AppBundle:User')->find($receiverId);

        $ask = new \AppBundle\Entity\Request();
       if($decision=="1"){
           $ask->setState(true);
       }
        $ask->setDecision($decision);
        $em->flush();
        $em->detach($ask);
        return $this->json(['ask'=>$ask]);
    }



    // retourne la liste des utilisateur vip
    public function getVips(){
        $data = ["vip"=>true];
        $em = $this->getDoctrine()->getManager();
        $list = $em->getRepository("AppBundle:User")->getVips($data);
        return $list;
    }

    // retourne la liste des demandes d'amitier
    public function getApplicant(User $user){
        $em = $this->getDoctrine()->getManager();
        $list = $em->getRepository("AppBundle:Request")->findBy(["applicant"=>$user],["createDate"=>"DESC"]);
        return $list;
    }

    // retourne la liste des invitations
    public function getReceiver(User $user){
        $em = $this->getDoctrine()->getManager();
        $list = $em->getRepository("AppBundle:Request")->findBy(["receiver"=>$user],["createDate"=>"DESC"]);
        return $list;
    }


    // retourne la liste des messages recues du  user connecté
    public function getRecievedMessage(User $user){
        $em = $this->getDoctrine()->getManager();
        $list = $em->getRepository("AppBundle:UserMessage")->findBy(["receiver"=>$user],["readDate"=>"DESC"]);
        return $list;
    }


    // retourne la liste des messages envoyées du  user connecté
    public function getSendMessage(User $user){
        $em = $this->getDoctrine()->getManager();
        $data = ["sender_id"=>$user->getId()];
        $list = $em->getRepository("AppBundle:UserMessage")->getSendMessage($data);
        return $list;
    }




    // retourne les paraemtres de recherches du  user connecté
    public function getConfig(User $user){
        $em = $this->getDoctrine()->getManager();
        $list = $em->getRepository("AppBundle:SearchCriteria")->findOneBy(["user"=>$user],["createDate"=>"DESC"]);
        return $list;
    }

    // retourne le profile  du  user connecté
    public function getProfile(User $user){
        $em = $this->getDoctrine()->getManager();
        $list = $em->getRepository("AppBundle:Profile")->findOneBy(["user"=>$user],["createDate"=>"DESC"]);
        return $list;
    }


    // retourne la liste des utilisateurs
    public function getUsers(){
        $em = $this->getDoctrine()->getManager();
        $list = $em->getRepository("AppBundle:User")->findBy(["createDate"=>"DESC"]);
        return $list;
    }

    // retourne la liste des users avec leur profile et  leurs photos
    public function getCompleteProfile(){

        $em = $this->getDoctrine()->getManager();
        $list =[];
        $users = $em->getRepository("AppBundle:User")->findBy(["joinDate"=>"DESC"]);

        /** @var User $user */
        foreach($users as $user)
        {
            $array = ["user"=>$user,
                "profile"=>$em->getRepository("AppBundle:Profile")->findOneBy(["user"=>$user],["createDate"=>"DESC"]),
                "photos"=>$em->getRepository("AppBundle:UserPhoto")->findBy(["user"=>$user],["createDate"=>"DESC"])];
            $list[] = $array;
        }
        return $list;
    }

    // retourne la liste des users vips avec leur profile et  leurs photos
    public function getCompleteProfileVips(){

        $em = $this->getDoctrine()->getManager();
        $list =[];
        $data = ["vip"=>true];
        $users = $em->getRepository("AppBundle:User")->getVips($data);

        /** @var User $user */
        foreach($users as $user)
        {
            $array = ["user"=>$user,
                "profile"=>$em->getRepository("AppBundle:Profile")->findOneBy(["user"=>$user],["createDate"=>"DESC"]),
                "photos"=>$em->getRepository("AppBundle:UserPhoto")->findBy(["user"=>$user],["createDate"=>"DESC"])];
            $list[] = $array;
        }
        return $list;
    }

    /**
     * @Rest\Get("/auth/user/compte")
     * @return Response
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Recupere  toutes les informations courant du   User connecte ",
     *  statusCodes={
     *     200="the query is ok",
     *     401= "The connection is required",
     *     403= "Access Denied"
     *
     *  },
     *  parameters={
     *     {"name"="id", "dataType"="int", "required"=true, "description"="Identifiant  de l'utilisateur connecte"},
     *  }
     * )
     */
    public function compteAction(Request $request)
    {

        //recuperer l'identifiant  du  user connecte
        $id = $request->get('id');
        
        //recuperer l'entity  mananger
        $em = $this->getDoctrine()->getManager();

        // recuperer le userprofile correspondant  au user connecte
        /** @var User $user */
        $user  = $em->getRepository('AppBundle:User')->find($id);
        /** @var UserProfile $userprofile */
        $userprofile = $em->getRepository('AppBundle:UserProfile')->findOneByuser($user);
        $photo = null;
        $allSchool = null;
        if($userprofile != null){
            /** @var UserPhoto $photo */

            //$photo = $em->getRepository('AppBundle:UserPhoto')->findOneByuser($userprofile->getUser());
            $photo = $em->getRepository("AppBundle:UserPhoto")->findOneBy(["user"=>$userprofile->getUser(),"isProfile"=>true],["updateDate"=>"DESC"]);

            $allSchool = $em->getRepository('AppBundle:SchoolLive')->findByuser($userprofile->getUser());
        }else{
            /** @var UserPhoto $photo */

            //$photo = $em->getRepository('AppBundle:UserPhoto')->findOneByuser($user);
            $photo = $em->getRepository("AppBundle:UserPhoto")->findOneBy(["user"=>$user,"isProfile"=>true],["updateDate"=>"DESC"]);
            $allSchool = $em->getRepository('AppBundle:SchoolLive')->findByuser($user);
        }
        //retourne les infos du   user connecte
        return $this->json(['user'=>$user, 'userProfile'=>$userprofile, 'photo' => $photo, 'schools' => $allSchool]);
    }

    /**
     * @Rest\Get("/auth/user/compte/editCountry")
     * @return Response
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Modifie le pays de l'utilisateur ",
     *  statusCodes={
     *     200="the query is ok",
     *     401= "The connection is required",
     *     403= "Access Denied"
     *
     *  },
     *  parameters={
     *     {"name"="id", "dataType"="int", "required"=true, "description"="Identifiant  de l'utilisateur connecte"},
     *  }
     * )
     */
    public function editCountryAction(Request $request)
    {

        //recuperer l'identifiant  du  user connecte
        $id = $request->get('id');
        $country = $request->get('pays');
        //recuperer l'entity  mananger
        $em = $this->getDoctrine()->getManager();

        // recuperer le userprofile correspondant  au user connecte
        /** @var User $user */
        $user  = $em->getRepository('AppBundle:User')->find($id);
        /** @var UserProfile $userprofile */
        $userprofile = $em->getRepository('AppBundle:UserProfile')->findOneByuser($user);

        if($userprofile != null){
            $userprofile->getUser()->setCountry($country);
        }else{
            $user->setCountry($country);
        }
        $em->flush();
        //retourne les infos du   user connecte
        return $this->json(['user'=>$user, 'userProfile'=>$userprofile]);
    }


     /**
     * @Rest\Get("/auth/user/compte/editProfildetail")
     * @return Response
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Modifie le detail de l'utilisateur ",
     *  statusCodes={
     *     200="the query is ok",
     *     401= "The connection is required",
     *     403= "Access Denied"
     *
     *  },
     *  parameters={
     *     {"name"="id", "dataType"="int", "required"=true, "description"="Identifiant  de l'utilisateur connecte"},
     *  }
     * )
     */
    public function editProfildetailAction(Request $request)
    {

        //recuperer l'identifiant  du  user connecte
        $id = $request->get('id');
        $city = $request->get('city');
        $email = $request->get('email');
        $lastName = $request->get('lastName');
        $firstName = $request->get('firstName');
        $profession = $request->get('profession');
        //recuperer l'entity  mananger
        $em = $this->getDoctrine()->getManager();

        // recuperer le userprofile correspondant  au user connecte
        /** @var User $user */
        $user  = $em->getRepository('AppBundle:User')->find($id);
        /** @var UserProfile $userprofile */
        $userprofile = $em->getRepository('AppBundle:UserProfile')->findOneByuser($user);

        if($userprofile != null){
            $userprofile->getUser()->setEmail($email)->setCity($city)
                ->setProfession($profession)
                ->setFirstName($firstName)->setLastName($lastName);
        }else{
            $user->setEmail($email)->setCity($city)
                ->setProfession($profession)
                ->setFirstName($firstName)->setLastName($lastName);
        }
        $em->flush();
        //retourne les infos du   user connecte
        return $this->json(['user'=>$user, 'userProfile'=>$userprofile]);
    }


      /**
     * @Rest\Get("/auth/user/compte/infoPerso")
     * @return Response
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Modifie les infos perso ",
     *  statusCodes={
     *     200="the query is ok",
     *     401= "The connection is required",
     *     403= "Access Denied"
     *
     *  },
     *  parameters={
     *     {"name"="id", "dataType"="int", "required"=true, "description"="Identifiant  de l'utilisateur connecte"},
     *  }
     * )
     */
    public function infoPersoAction(Request $request)
    {

        //recuperer l'identifiant  du  user connecte
        $id = $request->get('id');
        $lastName = $request->get('lastName');
        $firstName = $request->get('firstName');
        $sexe = $request->get('sexe');
        $phones[0] = $request->get('phones');
        $bd = str_replace("/", "-", $request->get('birthDate'));

        $date = new \DateTime($bd);
        //recuperer l'entity  mananger
        $em = $this->getDoctrine()->getManager();

        // recuperer le userprofile correspondant  au user connecte
        /** @var User $user */
        $user  = $em->getRepository('AppBundle:User')->find($id);
        /** @var UserProfile $userprofile */
        $userprofile = $em->getRepository('AppBundle:UserProfile')->findOneByuser($user);

        if($userprofile != null){
            $userprofile->getUser()->setGender($sexe)->setBirthDate($date)
                ->setFirstName($firstName)->setLastName($lastName)->setPhones($phones);
        }else{
            $user->setGender($sexe)->setBirthDate($date)
                ->setFirstName($firstName)->setLastName($lastName)->setPhones($phones);
        }
        $em->flush();
        //retourne les infos du   user connecte
        return $this->json(['user'=>$user, 'userProfile'=>$userprofile]);
    }


    /**
     * @Rest\Get("/auth/user/compte/deletePhone")
     * @return Response
     *
     * @ApiDoc(
     *  resource=true,
     *  description="retire un numero  a l'utilisateur ",
     *  statusCodes={
     *     200="the query is ok",
     *     401= "The connection is required",
     *     403= "Access Denied"
     *
     *  },
     *  parameters={
     *     {"name"="id", "dataType"="int", "required"=true, "description"="Identifiant  de l'utilisateur connecte"},
     *  }
     * )
     */
    public function deletePhoneAction(Request $request)
    {

        //recuperer l'identifiant  du  user connecte
        $id = $request->get('id');
        $phone = $request->get('phone');
        //recuperer l'entity  mananger
        $em = $this->getDoctrine()->getManager();

        // recuperer le userprofile correspondant  au user connecte
        /** @var User $user */
        $user  = $em->getRepository('AppBundle:User')->find($id);
        /** @var UserProfile $userprofile */
        $userprofile = $em->getRepository('AppBundle:UserProfile')->findOneByuser($user);

        if($userprofile != null){
            $ph = $userprofile->getUser()->getPhones();

            $userprofile->getUser()->setPhones($this->tableau_delete_value($ph, $phone));
        }else{
            $ph = $user->getPhones();

            $user->setPhones($this->tableau_delete_value($ph, $phone));
        }
        $em->flush();

        return $this->json(['user'=>$user, 'userProfile'=>$userprofile]);
    }


    /**
     * @Rest\Get("/auth/user/compte/addPhone")
     * @return Response
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Ajoute un numero  a l'utilisateur ",
     *  statusCodes={
     *     200="the query is ok",
     *     401= "The connection is required",
     *     403= "Access Denied"
     *
     *  },
     *  parameters={
     *     {"name"="id", "dataType"="int", "required"=true, "description"="Identifiant  de l'utilisateur connecte"},
     *  }
     * )
     */
    public function addPhoneAction(Request $request)
    {

        //recuperer l'identifiant  du  user connecte
        $id = $request->get('id');
        $phone = $request->get('phone');
        //recuperer l'entity  mananger
        $em = $this->getDoctrine()->getManager();

        // recuperer le userprofile correspondant  au user connecte
        /** @var User $user */
        $user  = $em->getRepository('AppBundle:User')->find($id);
        /** @var UserProfile $userprofile */
        $userprofile = $em->getRepository('AppBundle:UserProfile')->findOneByuser($user);

        if($userprofile != null){
            $ph = $userprofile->getUser()->getPhones();
            $ph[count($ph)] = $phone;
            $userprofile->getUser()->setPhones($ph);
        }else{
            $ph = $user->getPhones();
            $ph[count($ph)] = $phone;
            $user->setPhones($ph);
        }
        $em->flush();

        return $this->json(['user'=>$user, 'userProfile'=>$userprofile]);
    }


    /**
     * @Rest\Get("/auth/user/compte/delSchool")
     * @return Response
     *
     * @ApiDoc(
     *  resource=true,
     *  description="supprime letablissement de lutilisateur ",
     *  statusCodes={
     *     200="the query is ok",
     *     401= "The connection is required",
     *     403= "Access Denied"
     *
     *  },
     *  parameters={
     *     {"name"="id", "dataType"="int", "required"=true, "description"="Identifiant  de l'utilisateur connecte"},
     *     {"name"="idschool", "dataType"="int", "required"=true, "description"="Identifiant  de l'ecole"}
     *  }
     * )
     */
    public function delSchoolAction(Request $request)
    {

        $id = $request->get('id');
        $ids = $request->get('idschool');
        //recuperer l'entity  mananger
        $em = $this->getDoctrine()->getManager();
        // recuperer le userprofile correspondant  au user connecte
        /** @var User $user */
        $user  = $em->getRepository('AppBundle:User')->find($id);
        /** @var UserProfile $userprofile */
        $userprofile = $em->getRepository('AppBundle:UserProfile')->findOneByuser($user);

        if($userprofile != null){
            $u = $userprofile->getUser();
        }else{
            $u = $user;
        }
        $school = $em->getRepository('AppBundle:SchoolLive')->find($ids);
        $em->remove($school);
        $em->flush();

        $allSchool = $em->getRepository('AppBundle:SchoolLive')->findByuser($u);
        return $this->json(['schools'=>$allSchool]);
    }



    /**
     * @Rest\Get("/auth/user/compte/addSchool")
     * @return Response
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Ajoute un etablissement a lutilisateur ",
     *  statusCodes={
     *     200="the query is ok",
     *     401= "The connection is required",
     *     403= "Access Denied"
     *
     *  },
     *  parameters={
     *     {"name"="id", "dataType"="int", "required"=true, "description"="Identifiant  de l'utilisateur connecte"},
     *     {"name"="name", "dataType"="string", "required"=true, "description"="school name  de l'utilisateur connecte"},
     *     {"name"="location", "dataType"="string", "required"=true, "description"="location school  de l'utilisateur connecte"},
     *     {"name"="level", "dataType"="string", "required"=true, "description"="high level school  de l'utilisateur connecte"},
     *     {"name"="Qualification", "dataType"="string", "required"=true, "description"="Qualification  de l'utilisateur connecte"},
     *     {"name"="year", "dataType"="string", "required"=true, "description"="Annee du diplome"},
     *  }
     * )
     */
    public function addSchoolAction(Request $request)
    {

        //recuperer l'identifiant  du  user connecter et les details de a date
        $id = $request->get('id');
        $name = $request->get('name');
        $country = $request->get('country');
        $city = $request->get('city');
        $level = $request->get('level');
        $Qualification = $request->get('Qualification');
        $year = $request->get('year');
        $u = null;
        $school = new SchoolLive();
        //recuperer l'entity  mananger
        $em = $this->getDoctrine()->getManager();

        // recuperer le userprofile correspondant  au user connecte
        /** @var User $user */
        $user  = $em->getRepository('AppBundle:User')->find($id);
        /** @var UserProfile $userprofile */
        $userprofile = $em->getRepository('AppBundle:UserProfile')->findOneByuser($user);

        if($userprofile != null){
            $u = $userprofile->getUser();
        }else{
            $u = $user;
        }

        $school->setHighLevel($level)->setCountry($country)->setName($name)->setCity($city)
            ->setUser($u)->setYear($year)->setQualification($Qualification);
        $em->persist($school);
        $em->flush();

        $allSchool = $em->getRepository('AppBundle:SchoolLive')->findByuser($u);
        return $this->json(['schools'=>$allSchool]);
    }


     /**
     * @Rest\Get("/auth/user/compte/editSchool")
     * @return Response
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Ajoute un etablissement a lutilisateur ",
     *  statusCodes={
     *     200="the query is ok",
     *     401= "The connection is required",
     *     403= "Access Denied"
     *
     *  },
     *  parameters={
     *     {"name"="id", "dataType"="int", "required"=true, "description"="Identifiant  de l'utilisateur connecte"},
     *     {"name"="idSchool", "dataType"="int", "required"=true, "description"="Identifiant  de l'etablissement a modifier"},
     *     {"name"="name", "dataType"="string", "required"=true, "description"="school name  de l'utilisateur connecte"},
     *     {"name"="location", "dataType"="string", "required"=true, "description"="location school  de l'utilisateur connecte"},
     *     {"name"="level", "dataType"="string", "required"=true, "description"="high level school  de l'utilisateur connecte"},
     *     {"name"="Qualification", "dataType"="string", "required"=true, "description"="Qualification  de l'utilisateur connecte"},
     *     {"name"="year", "dataType"="string", "required"=true, "description"="Annee du diplome"},
     *  }
     * )
     */
    public function editSchoolAction(Request $request)
    {

        //recuperer l'identifiant  du  user connecter et les details de a date
        $id = $request->get('id');
        $idschool = $request->get('idschool');
        $name = $request->get('name');
        $country = $request->get('country');
        $city = $request->get('city');
        $level = $request->get('level');
        $Qualification = $request->get('Qualification');
        $year = $request->get('year');
        $u = null;

        //recuperer l'entity  mananger
        $em = $this->getDoctrine()->getManager();

        $school = $em->getRepository('AppBundle:SchoolLive')->find($idschool);
        // recuperer le userprofile correspondant  au user connecte
        /** @var User $user */
        $user  = $em->getRepository('AppBundle:User')->find($id);
        /** @var UserProfile $userprofile */
        $userprofile = $em->getRepository('AppBundle:UserProfile')->findOneByuser($user);

       /* if($userprofile != null){
            $u = $userprofile->getUser();
        }else{
            $u = $user;
        }*/

        $school->setHighLevel($level)->setCountry($country)->setName($name)->setCity($city)
            ->setUser($userprofile->getUser())->setYear($year)->setQualification($Qualification);

        $em->flush();

        $allSchool = $em->getRepository('AppBundle:SchoolLive')->findByuser($userprofile->getUser());
        return $this->json(['schools'=>$allSchool]);
    }


     /**
     * @Rest\Get("/auth/user/compte/editAdresse")
     * @return Response
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Modifie l'adresse de lutilisateur ",
     *  statusCodes={
     *     200="the query is ok",
     *     401= "The connection is required",
     *     403= "Access Denied"
     *
     *  },
     *  parameters={
     *     {"name"="id", "dataType"="int", "required"=true, "description"="Identifiant  de l'utilisateur connecte"},
     *  }
     * )
     */
    public function editAdresseAction(Request $request)
    {

        //recuperer l'identifiant  du  user connecter et les details de a date
        $id = $request->get('id');
        $city = $request->get('city');
        $profession = $request->get('profession');

        //recuperer l'entity  mananger
        $em = $this->getDoctrine()->getManager();

        // recuperer le userprofile correspondant  au user connecte
        /** @var User $user */
        $user  = $em->getRepository('AppBundle:User')->find($id);
        /** @var UserProfile $userprofile */
        $userprofile = $em->getRepository('AppBundle:UserProfile')->findOneByuser($user);

        if($userprofile != null){
            $userprofile->getUser()->setCity($city)->setProfession($profession);
        }else{
            $user->setCity($city)->setProfession($profession);
        }
        $em->flush();

        return $this->json(['user'=>$user, 'userProfile'=>$userprofile]);
    }


    /**
     * @Rest\Get("/auth/user/compte/about")
     * @return Response
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Modifie 'ABOUT' de lutilisateur ",
     *  statusCodes={
     *     200="the query is ok",
     *     401= "The connection is required",
     *     403= "Access Denied"
     *
     *  },
     *  parameters={
     *     {"name"="id", "dataType"="int", "required"=true, "description"="Identifiant  de l'utilisateur connecte"},
     *     {"name"="numberOfChill", "dataType"="string", "required"=true, "description"="nombre d'enfant  de l'utilisateur connecté"},
     *     {"name"="bio", "dataType"="string", "required"=true, "description"="a propos  de l'utilisateur connecté"},
     *     {"name"="meetLike", "dataType"="string", "required"=true, "description"="personne interessee  de l'utilisateur "},
     *     {"name"="maritalStatus", "dataType"="string", "required"=true, "description"="status matrimoniale  de l'utilisateur connecté"},
     *     {"name"="reason", "dataType"="string", "required"=true, "description"="raison  de l'utilisateur connecté"},
     *  }
     * )
     */
    public function aboutAction(Request $request)
    {

        //recuperer l'identifiant  du  user connecter et les details de a date
        $id = $request->get('id');
        $numberOfChill = $request->get('numberOfChill');
        $bio = $request->get('bio');
        $meetLike = $request->get('meetLike');
        $maritalStatus = $request->get('maritalStatus');
        $reason = $request->get('reason');



        //recuperer l'entity  mananger
        $em = $this->getDoctrine()->getManager();

        // recuperer le userprofile correspondant  au user connecte
        /** @var User $user */
        $user  = $em->getRepository('AppBundle:User')->find($id);
        /** @var UserProfile $userprofile */
        $userprofile = $em->getRepository('AppBundle:UserProfile')->findOneByuser($user);

        if($userprofile == null){
            /*$dc= new DefaultController();
            $u = new UserProfile();
            $u->setUser($user)->setAboutMe($bio)->setMaritalStatus($maritalStatus)->setGeolocation($dc->geolocation())
                ->setMeetLike($meetLike)->setChildNumber($numberOfChill)->setCreateDate(new \DateTime());
            $em->persist($u);*/
        }else{
            $user->setJoinReason($reason);
            $userprofile->setAboutMe($bio)->setUser($user)->setMaritalStatus($maritalStatus)->setMeetLike($meetLike)->setChildNumber($numberOfChill);
        }

        $em->flush();

        return $this->json(['user'=>$user, 'userProfile'=>$userprofile]);
    }

     /**
     * @Rest\Get("/auth/user/compte/editAccount")
     * @return Response
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Modifie le compte (email et username) de lutilisateur ",
     *  statusCodes={
     *     200="the query is ok",
     *     401= "The connection is required",
     *     403= "Access Denied"
     *
     *  },
     *  parameters={
     *     {"name"="id", "dataType"="int", "required"=true, "description"="Identifiant  de l'utilisateur connecte"},
     *     {"name"="email", "dataType"="string", "required"=true, "description"="email  de l'utilisateur connecté"},
     *     {"name"="userName", "dataType"="string", "required"=true, "description"="userName  de l'utilisateur connecté"},
     *  }
     * )
     */
    public function editAccountAction(Request $request)
    {

        //recuperer l'identifiant  du  user connecter et les details de a date
        $id = $request->get('id');
        $email = $request->get('email');
        $useName = $request->get('username');

        //recuperer l'entity  mananger
        $em = $this->getDoctrine()->getManager();

        // recuperer le userprofile correspondant  au user connecte
        /** @var User $user */
        $user  = $em->getRepository('AppBundle:User')->find($id);
        /** @var UserProfile $userprofile */
        $userprofile = $em->getRepository('AppBundle:UserProfile')->findOneByuser($user);

        if ($user->getEmail() != $email) {
            $oldemail = $em->getRepository('AppBundle:User')->findOneByemail($email);
            if ($oldemail != null) {
                return $this->json(['error' => 'The email already exists'], 200);
            } else {
                $user->setEmail($email)->setEmailCanonical($email);
            }
        }
        if ($user->getUsername() != $useName) {
            $olduser = $em->getRepository('AppBundle:User')->findOneByusername($useName);
            if ($olduser != null) {
                return $this->json(['error' => 'The user name already exists'], 200);
            } else {
                $user->setUsername($useName)->setUsernameCanonical($useName);
            }
        }
            $em->flush();

        return $this->json(['user'=>$user, 'userProfile'=>$userprofile]);
    }




    /**
     * @Rest\Get("/auth/user/compte/editpwd")
     * @return Response
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Modifie le mot de passe de lutilisateur ",
     *  statusCodes={
     *     200="the query is ok",
     *     401= "The connection is required",
     *     403= "Access Denied"
     *
     *  },
     *  parameters={
     *     {"name"="id", "dataType"="int", "required"=true, "description"="Identifiant  de l'utilisateur connecte"},
     *     {"name"="cpdw", "dataType"="string", "required"=true, "description"="mot de passe courant  de l'utilisateur connecté"},
     *     {"name"="npdw", "dataType"="string", "required"=true, "description"="nouveau mot de passe  de l'utilisateur connecté"}
     *  }
     * )
     */
    public function editpwdAction(Request $request)
    {

        //recuperer l'identifiant  du  user connecter et les details de a date
        $id = $request->get('id');
        $cpwd = $request->get('cpwd');
        $npwd = $request->get('npwd');

        //recuperer l'entity  mananger
        $em = $this->getDoctrine()->getManager();

        // recuperer le userprofile correspondant  au user connecte
        /** @var User $user */
        $user  = $em->getRepository('AppBundle:User')->find($id);
        /** @var UserProfile $userprofile */
        $userprofile = $em->getRepository('AppBundle:UserProfile')->findOneByuser($user);

//        if($userprofile != null){
//            $pwd =
//            $userprofile->getUser()->setEmail($email)->setUsername($useName)
//                ->setUsernameCanonical($useName)->setEmailCanonical($email);
//        }else{
//            $user->setEmail($email)->setUsername($useName)
//                ->setUsernameCanonical($useName)->setEmailCanonical($email);
//        }
//        $em->flush();

        return $this->json(['user'=>$user, 'userProfile'=>$userprofile]);
    }


    public function tableau_delete_value($array,$search) {
        $temp = null;

        for($i = 0; $i < count($array); $i++){
            if($array[$i]!=$search) $temp[count($temp)] = $array[$i];
        }
        return $temp;
    }

}