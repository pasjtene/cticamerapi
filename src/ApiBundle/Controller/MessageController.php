<?php

namespace ApiBundle\Controller;


use AppBundle\Entity\AuthToken;
use AppBundle\Entity\Credentials;
use AppBundle\Entity\Message;
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
class MessageController extends FOSRestController
{


    /**
     * @Rest\Get("/auth/Message/all")
     * @return Response
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Recuperer tous les messages d'un user",
     *  statusCodes={
     *     200="the query is ok",
     *     401= "The connection is required",
     *     403= "Access Denied"
     *
     *  },
     *  parameters={
     *     {"name"="id", "dataType"="integer", "required"=true, "description"="l'identifiant  de l'utilisateur connecté "}
     *  }
     * )
     */
    public function getAllAction(Request $request)
    {
       $id  =$request->get('id');
        $em = $this->getDoctrine()->getManager();

        /** @var User $user */
        $user = $em->getRepository('AppBundle:User')->find($id);


        //recuperer la liste des photos de profiles
       $listeProfiles =  $em->getRepository("AppBundle:UserPhoto")->findOneBy(["isProfile" => true], ["updateDate" => "DESC"]);


        // on recupere tous l'historique des messages
        $listUserMessages =  $em->getRepository("AppBundle:UserMessage")->findBy([], ["id" => "ASC"]);

        // on recupere tous les messages envoyes au user connecte
        $ListRecieveMessages =  $this->getReceiveMessages($listUserMessages,$user);

        if($ListRecieveMessages!=null)
        {
            /** @var UserMessage $userMessage */
            foreach($ListRecieveMessages  as $userMessage )
            {
                /** @var User $friend */
                $friend = $userMessage->getMessage()->getSender();
                $friendProfile = $this->getProfile($listeProfiles,$friend);
                $userProfile = $this->getProfile($listeProfiles,$user);
                $isSender = false;

                $listMessage[] = [
                    "friendProfile" => $friendProfile,
                    "userProfile" => $userProfile,
                    "friend" => $friend,
                    "isSender" => $isSender,
                    "userMessage" => $userMessage
                ];
            }
        }


        // on recupere tous les messages envoyes par user connecte
        $ListSendMessages =  $em->getRepository("AppBundle:Message")->findBy(["sender" => $user], ["id" => "ASC"]);


        if($ListSendMessages!=null)
        {
            /** @var Message $message */
            foreach($ListSendMessages  as $message )
            {

                $userMessages = $this->getUserMessages($listUserMessages,$message);

               if($userMessages!=null)
               {
                   foreach($userMessages as $userMessage)
                   {
                       /** @var User $friend */
                       $friend = $userMessage->getReceiver();
                       $friendProfile = $this->getProfile($listeProfiles,$friend);
                       $userProfile = $this->getProfile($listeProfiles,$user);
                       $isSender = true;

                       $listMessage[] = [
                           "friendProfile" => $friendProfile,
                           "userProfile" => $userProfile,
                           "friend" => $friend,
                           "isSender" => $isSender,
                           "userMessage" => $userMessage
                       ];
                   }
               }
            }
        }

        //liste d'aide
        $listHelp = null;
        $listHelp2 = null;
        if($listMessage!=null)
        {
            for($i=0;$i<count($listMessage);$i++)
            {
                $listHelp[] =$listMessage[$i]['id'];
            }
            sort($listHelp);

            foreach($listHelp as $id) {
                foreach ($listMessage as $item)
                {
                    if ($item['id'] == $id)
                    {
                        $listHelp2[] = $item;
                    }
                }

            }
            $listMessage = $listHelp2;
        }


        return $this->json(["recievers"=>$ListRecieveMessages,"messages"=>$listMessage,"user"=>$user]);
    }




    /**
     * @Rest\Get("/auth/Message/conversation")
     * @return Response
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Recuperer une conversation",
     *  statusCodes={
     *     200="the query is ok",
     *     401= "The connection is required",
     *     403= "Access Denied"
     *
     *  },
     *  parameters={
     *     {"name"="id", "dataType"="integer", "required"=true, "description"="l'identifiant  de l'utilisateur connecté "},
     *     {"name"="idFriend", "dataType"="integer", "required"=true, "description"="l'identifiant  de celui  avec qui  on cause"}
     *  }
     * )
     */
    public function getAction(Request $request)
    {
        $id  =$request->get('id');
        $idFriend  =$request->get('idFriend');
        $em = $this->getDoctrine()->getManager();

        /** @var User $user */
        $user = $em->getRepository('AppBundle:User')->find($id);

        /** @var User $myFriend */
        $myFriend = $em->getRepository('AppBundle:User')->find($idFriend);



        //recuperer la liste des photos de profiles
        $listeProfiles =  $em->getRepository("AppBundle:UserPhoto")->findBy(["isProfile" => true], ["updateDate" => "DESC"]);

        //la liste de tous les messages du  user
        $listMessage =[];


        $friendProfiles=null;
        $userProfiles =null;
        if($listeProfiles!=null)
        {
            $friendProfiles = $this->getProfile($listeProfiles, $myFriend);
            $userProfiles = $this->getProfile($listeProfiles, $user);
        }

        // on recupere tous l'historique des messages
        $listUserMessages =  $em->getRepository("AppBundle:UserMessage")->findBy([], ["id" => "ASC"]);

        // on recupere tous les messages envoyes au user connecte
        $ListRecieveMessages =  $this->getReceiveMessages($listUserMessages,$user);



        $listeRecievesCurrentUser = null;
        $listeRecievesCurrentUserNotSee = null;
        if($ListRecieveMessages!=null)
        {
            /** @var UserMessage $userMessage */
            foreach($ListRecieveMessages  as $userMessage )
            {

                /** @var User $friend */
                $friend = $userMessage->getMessage()->getSender();

                if($friend->getId() == $myFriend->getId())
                {
                    $userMessage->setIsSee(true);
                    $userMessage->setReadDate(new \DateTime());
                    $em->flush();
                    $em->detach($userMessage);

                    $friendProfile = $this->getProfile($listeProfiles,$friend);
                    $userProfile = $this->getProfile($listeProfiles,$user);
                    $isSender = false;

                    $item = [
                        "id"=>$userMessage->getMessage()->getId(),
                        "friendProfile" => $friendProfile,
                        "userProfile" => $userProfile,
                        "friend" => $friend,
                        "user" => $user,
                        "isSender" => $isSender,
                        "userMessage" => $userMessage
                    ];
                    $listMessage[] = $item;
                    $listeRecievesCurrentUser[] = $item;

                    if($userMessage->getIsSee()!=null && !$userMessage->getIsSee())
                    {
                        $listeRecievesCurrentUserNotSee[] = $item;
                    }
                }
            }
        }

        // on recupere tous les messages envoyes par user connecte
        $ListSendMessages =  $em->getRepository("AppBundle:Message")->findBy(["sender" => $user], ["id" => "ASC"]);

        if($ListSendMessages!=null)
        {
            /** @var Message $message */
            foreach($ListSendMessages  as $message )
            {
                $userMessages = $this->getUserMessages($listUserMessages,$message);

                if($userMessages!=null)
                {
                    foreach($userMessages as $userMessage)
                    {
                        /** @var User $friend */
                        $friend = $userMessage->getReceiver();
                        if($friend->getId() == $myFriend->getId()) {
                            $friendProfile = $this->getProfile($listeProfiles, $friend);
                            $userProfile = $this->getProfile($listeProfiles, $user);
                            $isSender = true;

                            $listMessage[] = [
                                "id"=>$userMessage->getMessage()->getId(),
                                "friendProfile" => $friendProfile,
                                "userProfile" => $userProfile,
                                "friend" => $friend,
                                "user" => $user,
                                "isSender" => $isSender,
                                "userMessage" => $userMessage
                            ];
                        }
                    }
                }
            }
        }

        //liste d'aide
        $listHelp = null;
        $listHelp2 = null;
        if($listMessage!=null)
        {
            for($i=0;$i<count($listMessage);$i++)
            {
                $listHelp[] =$listMessage[$i]['id'];
            }
            sort($listHelp);

            foreach($listHelp as $id) {
                foreach ($listMessage as $item)
                {
                    if ($item['id'] == $id)
                    {
                        $listHelp2[] = $item;
                    }
                }

            }
            $listMessage = $listHelp2;
        }

        return $this->json(["profileFriend"=>$friendProfiles, "profileUser"=>$userProfiles,"recievers"=>$listeRecievesCurrentUser,"notifyMessages"=>$listeRecievesCurrentUserNotSee,"messages"=>$listMessage,"user"=>$user,"friend"=>$myFriend]);
    }





    /**
     * @Rest\Get("/auth/Message/notification")
     * @return Response
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Recuperer une conversation",
     *  statusCodes={
     *     200="the query is ok",
     *     401= "The connection is required",
     *     403= "Access Denied"
     *
     *  },
     *  parameters={
     *     {"name"="id", "dataType"="integer", "required"=true, "description"="l'identifiant  de l'utilisateur connecté "},
     *     {"name"="lastcount", "dataType"="integer", "required"=false, "description"="Recupere le nombre de messages envoye la derniere fois au user "},
     *     {"name"="idFriend", "dataType"="integer", "required"=true, "description"="l'identifiant  de celui  avec qui  on cause"}
     *  }
     * )
     */
    public function getCountAction(Request $request)
    {
        $id  =$request->get('id');
        $idFriend  =$request->get('idFriend');
        $lastcount  =$request->get('lastcount');
        $em = $this->getDoctrine()->getManager();

        /** @var User $user */
        $user = $em->getRepository('AppBundle:User')->find($id);

        $myFriend=null;
        $idFriend = (int)$idFriend;
        if($idFriend>0)
        {
            /** @var User $myFriend */
            $myFriend = $em->getRepository('AppBundle:User')->find($idFriend);
        }


        // on recupere tous l'historique des messages
        $listUserMessages =  $em->getRepository("AppBundle:UserMessage")->findBy(['receiver'=>$user], ["id" => "ASC"]);

        $count=0;
        $count = count($listUserMessages);
        $countMyFriendsMessage = 0;
        $countAllFriendMessage = 0;
        if($listUserMessages!=null)
        {
           if($myFriend!=null)
           {
               /** @var UserMessage $userMessage */
               foreach($listUserMessages as $userMessage)
               {
                   if($userMessage->getMessage()->getSender()->getId() == $myFriend->getId())
                   {
                       $countMyFriendsMessage++;
                   }
               }
           }

            /** @var UserMessage $userMessage */
            foreach($listUserMessages as $userMessage)
            {
                if($userMessage->getIsSee()!=null  && !$userMessage->getIsSee())
                {
                    $countAllFriendMessage++;
                }
            }
        }

        $notifyMessages =null;
        if($lastcount!=null);
        {
            $lastcount = (int)$lastcount;
            if($lastcount!=$countAllFriendMessage)
            {
               //recuperer la liste des photos de profiles
                $listeProfiles =  $em->getRepository("AppBundle:UserPhoto")->findBy(["isProfile" => true], ["updateDate" => "DESC"]);
                $listeUsers =  $em->getRepository("AppBundle:User")->findBy([], ["joinDate" => "DESC"]);

                /** @var UserMessage $userMessage */
                foreach($listUserMessages as $userMessage)
                {
                    if($userMessage->getIsSee()==null  || !$userMessage->getIsSee())
                    {
                        $friend = $userMessage->getMessage()->getSender();
                        $friendProfile = $this->getProfile($listeProfiles,$friend);
                        $userProfile = $this->getProfile($listeProfiles, $user);

                        $notifyMessages[] = [
                            "id"=>$userMessage->getMessage()->getId(),
                            "friendProfile" => $friendProfile,
                            "userProfile" => $userProfile,
                            "friend" => $friend,
                            "userMessage" => $userMessage
                        ];
                    }
                }


                //regrouper les resultats

                $listHelp = $notifyMessages;
                $notifyMessages =null;

                if($listHelp!=null)
                {

                    /** @var User $member */
                    foreach($listeUsers as $member)
                    {
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
                            $notifyMessages[] = $array;
                        }

                    }
                }

            }
        }
        return $this->json(["notifyMessages"=>$notifyMessages,"count"=>$count,"notifiyCountMessage"=>$countAllFriendMessage,"countMyFriendsMessage"=>$countMyFriendsMessage,"user"=>$user, "friend"=>$myFriend]);
    }



    /**
     * @Rest\Post("/auth/Message/add")
     * @return Response
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Recuperer une conversation",
     *  statusCodes={
     *     200="the query is ok",
     *     401= "The connection is required",
     *     403= "Access Denied"
     *
     *  },
     *  parameters={
     *     {"name"="id", "dataType"="integer", "required"=true, "description"="l'identifiant  de l'utilisateur connecté "},
     *     {"name"="content", "dataType"="string", "required"=false, "description"="Le contenu  du  message"},
     *     {"name"="fatherId", "dataType"="integer", "required"=false, "description"="L'identifiant  du  message parent"},
     *     {"name"="childId", "dataType"="integer", "required"=false, "description"="L'identifiant  du  message enfant"},
     *     {"name"="file", "dataType"="file", "required"=false, "description"="Le fichier attache au message"},
     *     {"name"="idFriend", "dataType"="integer", "required"=true, "description"="l'identifiant  de celui  avec qui  on cause"}
     *  }
     * )
     */
    public function postAction(Request $request)
    {
        $id  =$request->get('id');
        $idFriend  =$request->get('idFriend');
        $content  =$request->get('content');
        $fatherId  =$request->get('fatherId');
        $childId  =$request->get('childId');
        $em = $this->getDoctrine()->getManager();

        /** @var User $user */
        $user = $em->getRepository('AppBundle:User')->find($id);

        $fatherMessage =null;

        if($fatherMessage !=null)
        {
            /** @var Message $fatherMessage */
            $fatherMessage = $em->getRepository('AppBundle:Message')->find($fatherId);
        }


        $subMessage =null;

        if($subMessage !=null)
        {
            /** @var Message $subMessage */
            $subMessage = $em->getRepository('AppBundle:Message')->find($childId);
        }

        /** @var User $myFriend */
        $myFriend = $em->getRepository('AppBundle:User')->find($idFriend);


        // creer le message
         $newMessage = new Message();
         $newMessage->setCreateDate(new \DateTime());
         $newMessage->setContent($content);
         $newMessage->setIp($this->getIp());
         $newMessage->setIsValid(true);
         $newMessage->setSender($user);
        if($fatherMessage!=null)
        {
            $newMessage->setMessageParent($fatherMessage);
        }
        if($subMessage!=null)
        {
            $newMessage->addSubMessage($subMessage);
        }

        //creer userMessage
        $newUserMessage = new UserMessage();

        $newUserMessage->setIsLocked(false);
        $newUserMessage->setMessage($newMessage);
        $newUserMessage->setReceiver($myFriend);
        $newUserMessage->setIsSee(false);
        //$newUserMessage->setReadDate(new \DateTime());

        $em->persist($newUserMessage);
        $em->flush();
        $em->detach($newUserMessage);

        //recuperer le profile du  user
        $profile =  $em->getRepository("AppBundle:UserPhoto")->findOneBy(["user" =>$user,"isProfile" => true], ["updateDate" => "DESC"]);
        return $this->json(["userMessages"=>$newUserMessage,"user"=>$user,"friend"=>$myFriend,"profile"=>$profile]);
    }




    /**
     * @Rest\Put("/auth/Message/update")
     * @return Response
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Recuperer une conversation",
     *  statusCodes={
     *     200="the query is ok",
     *     401= "The connection is required",
     *     403= "Access Denied"
     *
     *  },
     *  parameters={
     *     {"name"="idUser", "dataType"="integer", "required"=true, "description"="l'identifiant  de l'utilisateur connecté "},
     *     {"name"="id", "dataType"="integer", "required"=true, "description"="l'identifiant  du message "},
     *     {"name"="content", "dataType"="string", "required"=false, "description"="Le contenu  du  message"},
     *     {"name"="fatherId", "dataType"="integer", "required"=false, "description"="L'identifiant  du  message parent"},
     *     {"name"="childId", "dataType"="integer", "required"=false, "description"="L'identifiant  du  message enfant"},
     *     {"name"="idFriend", "dataType"="integer", "required"=true, "description"="l'identifiant  de celui  avec qui  on cause"}
     *  }
     * )
     */
    public function putAction(Request $request)
    {
        $idUser  =$request->get('idUser');
        $id  =$request->get('id');
        $idFriend  =$request->get('idFriend');
        $content  =$request->get('content');
        $fatherId  =$request->get('fatherId');
        $childId  =$request->get('childId');
        $em = $this->getDoctrine()->getManager();

        /** @var User $user */
        $user = $em->getRepository('AppBundle:User')->find($idUser);

        $fatherMessage =null;

        if($fatherMessage !=null)
        {
            /** @var Message $fatherMessage */
            $fatherMessage = $em->getRepository('AppBundle:Message')->find($fatherId);
        }


        $subMessage =null;

        if($subMessage !=null)
        {
            /** @var Message $subMessage */
            $subMessage = $em->getRepository('AppBundle:Message')->find($childId);
        }

        /** @var User $myFriend */
        $myFriend = $em->getRepository('AppBundle:User')->find($idFriend);


        // creer le message
        /** @var Message $newMessage */
        $newMessage = $em->getRepository('AppBundle:Message')->find($id);
        $newMessage->setContent($content);
        $em->flush();
        $em->detach($newMessage);

        return $this->json(["message"=>$newMessage,"user"=>$user,"friend"=>$myFriend]);
    }




    /**
     * @Rest\Delete("/auth/Message/delete")
     * @return Response
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Supprime un message",
     *  statusCodes={
     *     200="the query is ok",
     *     401= "The connection is required",
     *     403= "Access Denied"
     *
     *  },
     *  parameters={
     *     {"name"="id", "dataType"="string", "required"=true, "description"="les identifiants des messages a suprimes sous forme de chaine de caractere "},
     *     {"name"="idUser", "dataType"="integer", "required"=true, "description"="l'identifiant  du userconnecte "}
     *  }
     * )
     */
    public function deleteAction(Request $request)
    {
        $tab = explode(';',$request->get('id'));
        $em = $this->getDoctrine()->getManager();

        $idUserConnecte = $request->get('idUser');
        $array =null;

        if($tab!=null)
        {
            $lists = $em->getRepository('AppBundle:UserMessage')->findBy([],["id"=>'DESC']);

            foreach($tab as $str)
            {
                if(((int)$str)>0)
                {
                    $id  =$str;
                    // creer le message
                    /** @var Message $newMessage */
                    $newMessage = $em->getRepository('AppBundle:Message')->find($id);
                    $array[]= $newMessage;

                    $userMessages = $this->getUserMessages($lists,$newMessage);

                    if($userMessages!=null)
                    {
                        $idUserConnecte = (int)$idUserConnecte;
                        /** @var UserMessage $userMessage */
                        foreach($userMessages as $userMessage)
                        {
                            if($userMessage->getReceiver()->getId()==$idUserConnecte)
                            {
                                $userMessage->setRecieverRemove(true);
                                if($userMessage->getSendRemove())
                                {
                                    $em->remove($userMessage);
                                    $em->flush();
                                    $em->detach($userMessage);
                                }
                                else
                                {
                                    $em->flush();
                                }
                            }
                            elseif($userMessage->getMessage()->getSender()->getId()==$idUserConnecte)
                            {
                                $userMessage->setSendRemove(true);
                                if($userMessage->getRecieverRemove())
                                {
                                    $em->remove($userMessage);
                                    $em->flush();
                                    $em->detach($userMessage);
                                }
                                else
                                {
                                    $em->flush();
                                }
                            }
                        }
                    }
                    $newUserMessages = $this->getUserMessages($lists,$newMessage);

                    if($newUserMessages==null)
                    {
                        $em->remove($newMessage);
                        $em->flush();
                        $em->detach($newMessage);
                    }
                }
            }
        }

        return $this->json(["message"=>$array]);
    }


    public function  getProfile($liste,User $user)
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

    public function  getUserMessages($liste,Message $message)
    {
        $datas =null;
        /** @var UserMessage $userMessage */
        foreach($liste as $userMessage)
        {
            if($userMessage->getMessage()->getId() == $message->getId())
            {
                $datas[]= $userMessage;
            }
        }
        return $datas;
    }




    public function  getReceiveMessages($liste,User $user)
    {
        $datas =null;
        /** @var UserMessage $userMessage */
        foreach($liste as $userMessage)
        {
            if($userMessage->getReceiver()->getId() == $user->getId())
            {
                $datas[]= $userMessage;
            }
        }
        return $datas;
    }


    /**
     * @Rest\Get("/auth/Message/friends/cuurent")
     * @return Response
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Retourne la liste des amis currents",
     *  statusCodes={
     *     200="the query is ok",
     *     401= "The connection is required",
     *     403= "Access Denied"
     *
     *  },
     *  parameters={
     *     {"name"="id", "dataType"="int", "required"=true, "description"="Identifiant  du  user connecte"},
     *  }
     * )
     */
    public function getCurrentFriendsAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $id = $request->get("id");
        /** @var User $user */
        $user = $em->getRepository('AppBundle:User')->find($id);


        $data["id"]= $user->getId();
        $ListHelp = $em->getRepository("AppBundle:Request")->getFriends($data);

        $listeProfile =$em->getRepository("AppBundle:UserPhoto")->findBy(["isProfile" => true], ["updateDate" => "DESC"]);
        $listUsers =[];
        if($ListHelp!=null)
        {
            /** @var \AppBundle\Entity\Request $requests */
            foreach($ListHelp  as $requests )
            {
                $friend = $requests->getApplicant()->getId()==$user->getId()? $requests->getReceiver(): $requests->getApplicant();
                $listUsers[] = [
                    "photoReciever" => $this->getProfile($listeProfile,$requests->getReceiver()) ,
                    "photoApplicant" => $this->getProfile($listeProfile,$requests->getApplicant()) ,
                    "request" => $requests,
                    "friend" => $friend
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
                $friendProfile = $this->getProfile($listeProfile,$friend);
                $userProfile = $this->getProfile($listeProfile, $user);
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


        $listUsersHelp =$listUsers;
        $listUsers = null;

        // return $this->json(['test'=>$listUserMessages],400);

        if($listUsersHelp!=null)
        {


            foreach($listUsersHelp as $member)
            {
                /** @var User $friend */
                $friend = $member['friend'];
                $countMessage = 0;
                $array =null;
                $userMessage = null;

                if($recieverMessages!=null)
                {
                    foreach($recieverMessages as $userMessageHelp)
                    {
                        /** @var User $cuurent */
                        $cuurent = $userMessageHelp['friend'];
                        if($cuurent->getId() == $friend->getId())
                        {
                            $countMessage++;
                        }

                    }

                }
                $listUsers[] = [
                    "photoReciever" => $member['photoReciever'] ,
                    "photoApplicant" => $member['photoApplicant'] ,
                    "request" => $member['request'],
                    "friend" => $member['friend'],
                    "count" => $countMessage,
                    "userMessage" => $userMessage,
                ];


            }
        }

        return $this->json(['user'=>$user,'listUsers'=>$listUsers]);
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