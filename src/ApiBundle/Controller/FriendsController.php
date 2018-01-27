<?php

namespace ApiBundle\Controller;


use AppBundle\Entity\AuthToken;
use AppBundle\Entity\CityFile;
use AppBundle\Entity\Credentials;
use AppBundle\Entity\Files;
use AppBundle\Entity\User;
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

class FriendsController extends FOSRestController
{

    // Accepter une demande d'amitier
    /**
     * @Rest\PUT("/auth/user/friends/accept")
     * @return Response
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Accepter une demande d'amiter",
     *  statusCodes={
     *     200="the query is ok",
     *     401= "The connection is required",
     *     403= "Access Denied"
     *
     *  },
     *  parameters={
     *     {"name"="id", "dataType"="int", "required"=true, "description"="Accepter une demande d'amitier "},
     *     {"name"="idUser", "dataType"="int", "required"=true, "description"="L'identifiant de l'utilisateur "},
     *  }
     * )
     */
    public function acceptAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $id = $request->get("id");
        $idUser = $request->get("idUser");

        /** @var \AppBundle\Entity\Request $ask */
        $ask = $em->getRepository('AppBundle:Request')->find($id);
       /** @var User $user */
        $user = $em->getRepository('AppBundle:User')->find($idUser);

        $ask->setState(true);
        $ask->setDecision("1");
        $em->flush();

        if($request->get('page')!=null && $request->get('page')=="listFriend")
        {

            /** @var User $user */
            $user = $em->getRepository('AppBundle:User')->find($idUser);

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
        $recieversHelp = $em->getRepository("AppBundle:Request")->findBy(["receiver"=>$user],["createDate"=>"DESC"]);

        $recievers =null;
       if($recieversHelp!=null)
       {
           /** @var \AppBundle\Entity\Request $requests */
           foreach($recieversHelp  as $requests )
           {
              if(!$requests->getState() and $requests->getDecision()=="0" ){
                  $recievers[] = [
                      "photoReciever"=>$em->getRepository("AppBundle:UserPhoto")->findOneBy(["user"=>$requests->getReceiver(),"isProfile"=>true],["updateDate"=>"DESC"]),
                      "photoApplicant"=>$em->getRepository("AppBundle:UserPhoto")->findOneBy(["user"=>$requests->getApplicant(),"isProfile"=>true],["updateDate"=>"DESC"]),
                      "request"=>$requests
                  ];
              }
           }
       }


        $applicantsHelp = $em->getRepository("AppBundle:Request")->findBy(["applicant"=>$user],["createDate"=>"DESC"]);

        $applicants =null;
        if($applicantsHelp!=null)
        {
            /** @var \AppBundle\Entity\Request $requests */
            foreach($applicantsHelp  as $requests )
            {
                if(!$requests->getState() and $requests->getDecision()=="0" ) {
                    $applicants[] = [
                        "photoReciever" => $em->getRepository("AppBundle:UserPhoto")->findOneBy(["user" => $requests->getReceiver(), "isProfile" => true], ["updateDate" => "DESC"]),
                        "photoApplicant" => $em->getRepository("AppBundle:UserPhoto")->findOneBy(["user" => $requests->getApplicant(), "isProfile" => true], ["updateDate" => "DESC"]),
                        "request" => $requests
                    ];
                }
            }

        }
        return $this->json(["applicant"=>$applicants, "recievers"=>$recievers,"user"=>$ask->getApplicant()]);

    }


    // Decliner une demande d'amitier
    /**
     * @Rest\PUT("/auth/user/friends/decline")
     * @return Response
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Decliner une demande d'amiter",
     *  statusCodes={
     *     200="the query is ok",
     *     401= "The connection is required",
     *     403= "Access Denied"
     *
     *  },
     *  parameters={
     *     {"name"="id", "dataType"="int", "required"=true, "description"="Decliner une demande d'amitier "},
     *     {"name"="idUser", "dataType"="int", "required"=true, "description"="L'identifiant de l'utilisateur "},
     *  }
     * )
     */
    public function declineAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $id = $request->get("id");
        $idUser = $request->get("idUser");
        $decision = $request->get("decision");
        /** @var \AppBundle\Entity\Request $ask */
        $ask = $em->getRepository('AppBundle:Request')->find($id);
        /** @var User $user */
        $user = $em->getRepository('AppBundle:User')->find($idUser);
        $ask->setState(false);
        $ask->setState($decision);
        $em->flush();
       //decision 2 : supprimer, decision 3:  bloque decision 4: ignorer

        if($request->get('page')!=null && $request->get('page')=="listFriend")
        {

            /** @var User $user */
            $user = $em->getRepository('AppBundle:User')->find($idUser);

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
        $recieversHelp = $em->getRepository("AppBundle:Request")->findBy(["receiver"=>$user],["createDate"=>"DESC"]);

        $recievers =null;
        if($recieversHelp!=null)
        {
            /** @var \AppBundle\Entity\Request $requests */
            foreach($recieversHelp  as $requests )
            {
                if(!$requests->getState() and $requests->getDecision()=="0" ){
                    $recievers[] = [
                        "photoReciever"=>$em->getRepository("AppBundle:UserPhoto")->findOneBy(["user"=>$requests->getReceiver(),"isProfile"=>true],["updateDate"=>"DESC"]),
                        "photoApplicant"=>$em->getRepository("AppBundle:UserPhoto")->findOneBy(["user"=>$requests->getApplicant(),"isProfile"=>true],["updateDate"=>"DESC"]),
                        "request"=>$requests
                    ];
                }
            }
        }


        $applicantsHelp = $em->getRepository("AppBundle:Request")->findBy(["applicant"=>$user],["createDate"=>"DESC"]);

        $applicants =null;
        if($applicantsHelp!=null)
        {
            /** @var \AppBundle\Entity\Request $requests */
            foreach($applicantsHelp  as $requests )
            {
                if(!$requests->getState() and $requests->getDecision()=="0" ) {
                    $applicants[] = [
                        "photoReciever" => $em->getRepository("AppBundle:UserPhoto")->findOneBy(["user" => $requests->getReceiver(), "isProfile" => true], ["updateDate" => "DESC"]),
                        "photoApplicant" => $em->getRepository("AppBundle:UserPhoto")->findOneBy(["user" => $requests->getApplicant(), "isProfile" => true], ["updateDate" => "DESC"]),
                        "request" => $requests
                    ];
                }
            }

        }
        return $this->json(["applicant"=>$applicants, "recievers"=>$recievers,"user"=>$ask->getApplicant()]);

    }









    // Decliner une demande d'amitier
    /**
     * @Rest\Delete("/auth/user/friends/remove")
     * @return Response
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Decliner une demande d'amiter",
     *  statusCodes={
     *     200="the query is ok",
     *     401= "The connection is required",
     *     403= "Access Denied"
     *
     *  },
     *  parameters={
     *     {"name"="id", "dataType"="int", "required"=true, "description"="Decliner une demande d'amitier "},
     *     {"name"="idUser", "dataType"="int", "required"=true, "description"="L'identifiant de l'utilisateur "},
     *  }
     * )
     */
    public function removeFriendAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $id = $request->get("id");
        $idUser = $request->get("idUser");

        /** @var \AppBundle\Entity\Request $ask */
        $ask = $em->getRepository('AppBundle:Request')->find($id);

        /** @var User $user */
        $user = $em->getRepository('AppBundle:User')->find($idUser);

        $em->remove($ask);
        $em->flush();

        if($request->get('page')!=null && $request->get('page')=="friends")
        {
            $data["id"]= $user->getId();
            $ListHelp = $em->getRepository("AppBundle:Request")->getFriends($data);
            $listUsers =[];
            if($ListHelp!=null)
            {
                /** @var \AppBundle\Entity\Request $requests */
                foreach($ListHelp  as $requests )
                {
                    $listUsers[] = [
                        "photoReciever" => $em->getRepository("AppBundle:UserPhoto")->findOneBy(["user" => $requests->getReceiver(), "isProfile" => true], ["updateDate" => "DESC"]),
                        "photoApplicant" => $em->getRepository("AppBundle:UserPhoto")->findOneBy(["user" => $requests->getApplicant(), "isProfile" => true], ["updateDate" => "DESC"]),
                        "request" => $requests
                    ];
                }

            }
            return $this->json(['user'=>$user,'listUsers'=>$listUsers]);
        }

        if($request->get('page')!=null && $request->get('page')=="listFriend")
        {

            /** @var User $user */
            $user = $em->getRepository('AppBundle:User')->find($idUser);

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
        $recieversHelp = $em->getRepository("AppBundle:Request")->findBy(["receiver"=>$user],["createDate"=>"DESC"]);

        $recievers =null;
        if($recieversHelp!=null)
        {
            /** @var \AppBundle\Entity\Request $requests */
            foreach($recieversHelp  as $requests )
            {
                if(!$requests->getState() and $requests->getDecision()=="0" ){
                    $recievers[] = [
                        "photoReciever"=>$em->getRepository("AppBundle:UserPhoto")->findOneBy(["user"=>$requests->getReceiver(),"isProfile"=>true],["updateDate"=>"DESC"]),
                        "photoApplicant"=>$em->getRepository("AppBundle:UserPhoto")->findOneBy(["user"=>$requests->getApplicant(),"isProfile"=>true],["updateDate"=>"DESC"]),
                        "request"=>$requests
                    ];
                }
            }
        }


        $applicantsHelp = $em->getRepository("AppBundle:Request")->findBy(["applicant"=>$user],["createDate"=>"DESC"]);

        $applicants =null;
        if($applicantsHelp!=null)
        {
            /** @var \AppBundle\Entity\Request $requests */
            foreach($applicantsHelp  as $requests )
            {
                if(!$requests->getState() and $requests->getDecision()=="0" ) {
                    $applicants[] = [
                        "photoReciever" => $em->getRepository("AppBundle:UserPhoto")->findOneBy(["user" => $requests->getReceiver(), "isProfile" => true], ["updateDate" => "DESC"]),
                        "photoApplicant" => $em->getRepository("AppBundle:UserPhoto")->findOneBy(["user" => $requests->getApplicant(), "isProfile" => true], ["updateDate" => "DESC"]),
                        "request" => $requests
                    ];
                }
            }

        }
        return $this->json(["applicant"=>$applicants, "recievers"=>$recievers,"user"=>$ask->getApplicant()]);

    }


    //Retourne la liste des demandes d'amitiers,  liste des recpetions de demandes d'amitier et  la liste des users correspondant  a un profiles par encore amis
    /**
     * @Rest\Get("/auth/user/friends")
     * @return Response
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Retourne la liste des demandes d'amitiers,  liste des recpetions de demandes d'amitier et  la liste des users correspondant  a un profiles par encore amis",
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
    public function getFriendsAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $id = $request->get("id");

        //return $this->json(['id'=>$id, 'email'=>$email],400);


        /** @var User $user */
        $user = $em->getRepository('AppBundle:User')->find($id);

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




    //Retourne la liste des amis du membre
    /**
     * @Rest\Get("/auth/user/friends/current")
     * @return Response
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Retourne la liste des demandes d'amitiers,  liste des recpetions de demandes d'amitier et  la liste des users correspondant  a un profiles par encore amis",
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
    public function getMyFriendsAction(Request $request){
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
                $listUsers[] = [
                    "photoReciever" => $this->getProfile($listeProfile,$requests->getReceiver()) ,
                    "photoApplicant" => $this->getProfile($listeProfile,$requests->getApplicant()) ,
                    "request" => $requests
                ];
            }

        }
        return $this->json(['user'=>$user,'listUsers'=>$listUsers]);
    }



    //Pourquoi cette route ne marche pas
    /**
     * @Rest\Get("/auth/user/friends/cuurent")
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
                $listUsers[] = [
                    "photoReciever" => $this->getProfile($listeProfile,$requests->getReceiver()) ,
                    "photoApplicant" => $this->getProfile($listeProfile,$requests->getApplicant()) ,
                    "request" => $requests
                ];
            }

        }
        return $this->json(['user'=>$user,'listUsers'=>$listUsers]);
    }


    public function  getProfile($liste,User $user)
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



    //Retourne la liste des demandes des utilisateurs correspondant  a la recherche
    /**
     * @Rest\Get("/auth/user/search")
     * @return Response
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Retourne la liste des demandes des utilisateurs correspondant  a la recherche",
     *  statusCodes={
     *     200="the query is ok",
     *     401= "The connection is required",
     *     403= "Access Denied"
     *
     *  },
     *  parameters={
     *     {"name"="search", "dataType"="string", "required"=true, "description"="Information a rechercher (nom , prenom ou pays)"},
     *     {"name"="id", "dataType"="string", "required"=true, "Identifiant du  user connecte)"},
     *  }
     * )
     */
    public function getUserSearchAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $id = $request->get("id");
        $search = $request->get("search");

        //return $this->json(['id'=>$id, 'email'=>$email],400);


        /** @var User $user */
        $user = $em->getRepository('AppBundle:User')->find($id);

        $ListHelp = $em->getRepository("AppBundle:Request")->findAll();

        $data['search'] = $search;
        $listUsers =  $em->getRepository("AppBundle:User")->getUsersSearch($data);

        $listMembers =null;
        if($ListHelp!=null and $listUsers!=null )
        {
            /** @var User $member */
            foreach($listUsers as $member)
            {
                $state =false;
                /** @var \AppBundle\Entity\Request $requests */
                foreach($ListHelp as $requests)
                {
                    if($member->getType()=="System" || $member->getId()==$requests->getApplicant()->getId() || $member->getId()==$requests->getReceiver()->getId())
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
        if($listehelp1!=null)
        {
            /** @var User $member */
            foreach($listehelp1 as $member)
            {
                $array = ["user"=>$member,
                    "profile"=>$em->getRepository("AppBundle:Profile")->findOneBy(["user"=>$member],["createDate"=>"DESC"]),
                    "photoProfile"=>$em->getRepository("AppBundle:UserPhoto")->findOneBy(["user"=>$member,"isProfile"=>true],["updateDate"=>"DESC"])];
                $listMembers[] = $array;
            }
        }

        return $this->json(['user'=>$user,'listUsers'=>$listMembers]);
    }


}