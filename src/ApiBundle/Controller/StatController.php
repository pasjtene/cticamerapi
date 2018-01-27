<?php
/**
 * Created by PhpStorm.
 * User: tene
 * Date: 21/06/2017
 * Time: 12:47
 */

namespace ApiBundle\Controller;
use AppBundle\Entity\Message;
use AppBundle\Entity\UserPhoto;
use Nelmio\ApiDocBundle\Controller\ApiDocController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use AppBundle\Entity\User;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\IsNull;


class StatController extends FOSRestController
{
    // Recuperer le nombre de user et  les vips
    /**
     * @Rest\Get("/auth/count")
     * @return Response
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Recuperer le nombre de user et  les vips",
     *  statusCodes={
     *     200="the query is ok",
     *     401= "The connection is required",
     *     403= "Access Denied"
     *
     *  }
     * )
     */
    public function countUserAction()
    {
        $em = $this->getDoctrine()->getManager();
        $list = $em->getRepository('AppBundle:User')
            ->findAll();
        $countuser = count($list);
        $countvip = $this->countVips($list);
        $joinTodays = $this->joinToday($list);
        $membersWhoJoinedYesterdays = $this->membersWhoJoinedYesterday($list);
        $membersWhoJoinedfromoneWeeks =$this->membersWhoJoinedfromoneWeek($list);
        $membersWhoJoinedfromoneMonths =$this->membersWhoJoinedfromoneMonth($list);



        /*
        $list = $em->getRepository('AppBundle:User') ->findByisVip(true);
        $countvip = count($list);
        */

        /*
         * $listpicture=$em->getRepository('AppBundle:UserPhoto');
         *
         *
         */
        return $this->json(['countuser'=>$countuser, 'countvip'=>$countvip, 'joinTodays'=>$joinTodays,
            'membersWhoJoinedYesterdays'=>$membersWhoJoinedYesterdays, 'membersWhoJoinedfromoneWeeks'=>$membersWhoJoinedfromoneWeeks,
            'membersWhoJoinedfromoneMonths'=>$membersWhoJoinedfromoneMonths
            ]);
    }
    //counts the number of VIPS from the supplied list of members
    private function countVips($memberList){
        $vips =0;
        foreach($memberList as $user){
            if ($user->getIsVip() === true){
                $vips++;
            }
        };
        return $vips;
    }
    //counts the number  of members who joint FunGlobe today
    private function joinToday($list){
        $logger = $this->get("logger");
        $today = date("Y/m/d");
        $logger->info("today is " . $today);
        $listjoint =0;
        /** @var User $user */
        foreach($list as $user){
            $d = $user->getJoinDate();
            $logger->critical($d->format('Y/m/d'));
            if ($d->format("Y/m/d")=== $today){
                $listjoint++;
            }
        };
        return $listjoint;
    }
    //counts the number  of members who joint FunGlobe yesterday
    private function membersWhoJoinedYesterday($memberArray){
        $logger = $this->get("logger");
        $yesterday = date("Y/m/d", mktime(0,0,0,date("m"),date("d")-1,date("Y")));
        $logger->info("The date Yesterday was " . $yesterday);
        $numberOfMembersJoinedYesterday = 0;
        /** @var User $jointday */
        forEach($memberArray as $user){
            $d = $user->getJoinDate();
            if ($d->format("Y/m/d")=== $yesterday){
                $numberOfMembersJoinedYesterday++;
            }
        };
        return $numberOfMembersJoinedYesterday;
    }
    //counts the number  of members who joint FunGlobe from one week
    private function membersWhoJoinedfromoneWeek($memberweekArray){
        $logger = $this->get("logger");
        $oneweek = date("Y/m/d", mktime(0,0,0,date("m"),date("d")-7,date("Y")));
        $logger->info("The date from one week was " . $oneweek);
        $numberOfMembersJoinedfromoneWeek = 0;
        /** @var User $jointday */
        forEach($memberweekArray as $user){
            $d = $user->getJoinDate();
            if ($d->format("Y/m/d")>= $oneweek){
                $numberOfMembersJoinedfromoneWeek++;
            }
        };
        return $numberOfMembersJoinedfromoneWeek;
    }
    //counts the number  of members who joint FunGlobe from one month
    private function membersWhoJoinedfromoneMonth($membermonthArray){
        $logger = $this->get("logger");
        $onemonth = date("Y/m/d", mktime(0,0,0,date("m")-1,date("d"),date("Y")));
        $logger->info("The date from one month was " . $onemonth);
        $numberOfMembersJoinedfromoneMonth = 0;
        /** @var User $jointmonth */
        forEach($membermonthArray as $user){
            $d = $user->getJoinDate();
            if ($d->format("Y/m/d") >= $onemonth){
                $numberOfMembersJoinedfromoneMonth++;
            }
        };
        return $numberOfMembersJoinedfromoneMonth;
    }



    //counts the number  of members of pictures
    // Recuperer le nombre de user et  les vips
    /**
     * @Rest\Get("/auth/count/picture")
     * @return Response
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Recuperer le nombre de user et  les vips",
     *  statusCodes={
     *     200="the query is ok",
     *     401= "The connection is required",
     *     403= "Access Denied"
     *
     *  }
     * )
     */
    public function NumberUserphotoAction()
    {
        $em = $this->getDoctrine()->getManager();
        //liste des photos des utilisateurs
        $listpicture = $em->getRepository('AppBundle:UserPhoto')
            ->findAll();
        //liste des utilisateurs dans la bd
        $userList = $em->getRepository('AppBundle:User')
            ->findAll();

        $numberpicture = count($listpicture);
        $NumberpictureTodays=$this->NumberpictureToday($listpicture);
        $NumberYesterdaypictures=$this->NumberYesterdaypicture($listpicture);
        $NumberpictureWeeks=$this->NumberpictureWeek($listpicture);
        $NumberpictureMonths=$this->NumberpictureMonth($listpicture);
        $NumberuserWithoutPictures=$this->NumberuserWithoutPicture($listpicture,$userList);
        $maxPictureUser = $this->userMaxPicture($listpicture,$userList);


        return $this->json(['numberpictures'=>$numberpicture, 'NumberpictureTodays'=>$NumberpictureTodays,
            'NumberYesterdaypictures'=>$NumberYesterdaypictures,'NumberpictureWeeks'=>$NumberpictureWeeks,
            'NumberpictureMonths'=>$NumberpictureMonths,'NumberuserWithoutPictures'=>$NumberuserWithoutPictures,
            'maxPictureUser'=>$maxPictureUser]);

    }
    //counts the number  of pictures for members who joint FunGlobe today
    private function NumberpictureToday($listpictureArray){
        $logger = $this->get("logger");
        $today = date("Y/m/d");
        $logger->info("today picture are " . $today);
        $picturetoday =0;
        /** @var UserPhoto $createDate */
        foreach($listpictureArray as $userphoto){
            $d = $userphoto->getCreateDate();
            if ($d->format("Y/m/d")=== $today){
                $picturetoday++;
            }
        };
        return $picturetoday;
    }
    //counts the number  of picture for members who joint FunGlobe yesterday
    private function NumberYesterdaypicture($listpictureArray){
        $logger = $this->get("logger");
        $yesterday = date("Y/m/d", mktime(0,0,0,date("m"),date("d")-1,date("Y")));
        $logger->info("The date Yesterday was " . $yesterday);
        $numberOfpictureYest = 0;
        /** @var User $jointday */
        forEach($listpictureArray as $userpicture){
            $d = $userpicture->getCreateDate();
            if ($d->format("Y/m/d")=== $yesterday){
                $numberOfpictureYest++;
            }
        };
        return $numberOfpictureYest;
    }
    //counts the picture of  members who joint FunGlobe from one week
    private function NumberpictureWeek($listpictureArray){
        $logger = $this->get("logger");
        $oneweek = date("Y/m/d", mktime(0,0,0,date("m"),date("d")-7,date("Y")));
        $logger->info("The date from one week was " . $oneweek);
        $numberpictureofWeek = 0;
        /** @var User $CreateDate */
        forEach($listpictureArray as $userphoto){
            $d = $userphoto->getCreateDate();
            if ($d->format("Y/m/d")>= $oneweek){
                $numberpictureofWeek++;
            }
        };
        return $numberpictureofWeek;
    }
    //counts the picture  of members who joint FunGlobe from one month
    private function NumberpictureMonth($listpictureArray){
        $logger = $this->get("logger");
        $onemonth = date("Y/m/d", mktime(0,0,0,date("m")-1,date("d"),date("Y")));
        $logger->info("The date from one month was " . $onemonth);
        $numberpictureofMonth = 0;
        /** @var UserPhoto $CreateDate */
        forEach($listpictureArray as $userphoto){
            $d = $userphoto->getCreateDate();
            if ($d->format("Y/m/d") >= $onemonth){
                $numberpictureofMonth++;
            }
        };
        return $numberpictureofMonth;
    }
    //counts the number of  users who don't have picture
    private function NumberuserWithoutPicture($UserPhotos,$userList){
        //variable qui permet  de compter le nombre de user qui  n'ont  pas de photo par defaut initialise à 0
        $userwithoutpic =0;

        //on parcourt la liste de tous les utilisateurs présent dans la base de donnée
        /** @var User $user */
        foreach($userList as $user)
        {
            //on teste si  l'utilisateur n'a pas de photo
            if(!$this->isUserPhoto($UserPhotos,$user->getId()))
            {
                //daéns ce sens on incremente notre compteur
                $userwithoutpic++;
            }
        }

        /** @var DateTime $userJointDate */
      // $userJointDate = $user->getJoinDate();

      // $month = $userJointDate->format("m");

        //on retourne le compteur
        return $userwithoutpic;
    }

    // teste si  un user   a une photo (retourne true dans le meme sens et  false dans le sens contraire)
    private function isUserPhoto($userPhotos,$userId)
    {
        //parcourt  la liste des photos des utilisateurs
        /** @var UserPhoto $userPhoto */
        foreach($userPhotos as $userPhoto)
        {
            //si  l'identifiant  de l'utilisateur qui  a la photo courant  vos l'identifiant  du  user passe en parametre on retourne true
            if($userPhoto->getUser()->getId() ==$userId)
            {
                return true;
            }
        }
        //on retourne false dan le cas contraire
        return false;
    }


    // retourne le nombre de photo d'un user
    private function userCountPhoto($userPhotos,$userId)
    {
        $cpt=0;
        //parcourt  la liste des photos des utilisateurs
        /** @var UserPhoto $userPhoto */
        foreach($userPhotos as $userPhoto)
        {
            //si  l'identifiant  de l'utilisateur qui  a la photo courant  vos l'identifiant  du
            //  user passe en parametre on increùente le compteur
            if($userPhoto->getUser()->getId() ==$userId)
            {
                $cpt;
            }
        }
        //on retourne false dan le cas contraire
        return $cpt;
    }


    //get the user max picture
    private function userMaxPicture($userPhotos,$userList){

        //premier userphoto de la liste
        /** @var UserPhoto $userPhoto */
        $userPhoto =$userPhotos==null? null : $userPhotos[0];
        //utilisateur qui  a le max picture  par defaut  est le premier utilisateur de la liste de la bd
        /** @var User $userMax */
        $userMax = $userPhoto==null? null : $userPhoto->getUser();

        //recuprer le nombre de photo du  user (pour le first  user par defaut)
        $max = $userPhoto==null? 0 : $this->userCountPhoto($userPhotos,$userMax->getId());



        //on parcourt la liste de tous les utilisateurs présent dans la base de donnée
        /** @var User $user */
        foreach($userList as $user)
        {
            //on compare le nombre de photo du user courant avec celui  qui  a le plus grand nombre de photo actuelle
            if($max<$this->userCountPhoto($userPhotos,$user->getId()))
            {
                //on set  le max user count
               $max = $this->userCountPhoto($userPhotos,$user->getId());
                //on set  le user max photo
               $userMax = $user;
            }
        }
        //on retourne le compteur
        return $userMax;
    }
    //counts the number  of messages
    // Recuperer le nombre de message
    /**
     * @Rest\Get("/auth/count/message")
     * @return Response
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Recuperer le nombre de message des users",
     *  statusCodes={
     *     200="the query is ok",
     *     401= "The connection is required",
     *     403= "Access Denied"
     *
     *  }
     * )
     */
    public function NumberMessageAction()
    {
        $em = $this->getDoctrine()->getManager();
        //liste des messages des utilisateurs
        $listMessage = $em->getRepository('AppBundle:Message')
            ->findAll();
        $numberMessages = count($listMessage);
        $numberMessageTodays=$this->NumberMessageToday($listMessage);
        $numberMessageOfWeeks=$this->NumberMessageWeek($listMessage);
        $numberMessageMonths=$this->NumberMessageMonth($listMessage);

        return $this->json(['numberMessages'=>$numberMessages, '$numberMessageTodays'=>$numberMessageTodays,
            'numberMessageOfWeeks'=>$numberMessageOfWeeks,'numberMessageMonths'=>$numberMessageMonths]);

    }
    //counts the number  of pictures for members who joint FunGlobe today
    private function NumberMessageToday($listMessageArray){
        $logger = $this->get("logger");
        $today = date("Y/m/d");
        $logger->info("today Messages are " . $today);
        $NBmessagetoday =0;
        /** @var Message $createDate */
        foreach($listMessageArray as $usermessage){
            $d = $usermessage->getCreateDate();
            if ($d->format("Y/m/d")=== $today){
                $NBmessagetoday++;
            }
        };
        return $NBmessagetoday;
    }

    //counts the number of message send from one week
    private function NumberMessageWeek($listMessageeArray){
        $logger = $this->get("logger");
        $oneweek = date("Y/m/d", mktime(0,0,0,date("m"),date("d")-7,date("Y")));
        $logger->info("The message from one week was " . $oneweek);
        $numberMessageofWeek = 0;
        /** @var Message $CreateDate */
        forEach($listMessageeArray as $usermessage){
            $d = $usermessage->getCreateDate();
            if ($d->format("Y/m/d")>= $oneweek){
                $numberMessageofWeek++;
            }
        };

    return $numberMessageofWeek;
    }
    //counts the number of message send from one month
    private function NumberMessageMonth($listMessageArray){
        $logger = $this->get("logger");
        $onemonth = date("Y/m/d", mktime(0,0,0,date("m")-1,date("d"),date("Y")));
        $logger->info("The date from one month was " . $onemonth);
        $numbermessageofMonth = 0;
        /** @var Message $CreateDate */
        forEach($listMessageArray as $usermessage){
            $d = $usermessage->getCreateDate();
            if ($d->format("Y/m/d") >= $onemonth){
                $numbermessageofMonth++;
            }
        };
        return $numbermessageofMonth;
    }
}

