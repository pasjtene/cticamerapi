<?php

namespace ApiBundle\Controller;

use ApiBundle\Task\DeletePictureTask;
use AppBundle\Entity\BlockedIP;
use AppBundle\Entity\Files;
use AppBundle\Entity\Settings;
use AppBundle\Entity\UserPhoto;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserIPController extends FOSRestController
{
    /**
     * @Rest\Get("/auth/blocked-ip")
     * @return Response
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Vérifier si une adresse IP est bloquée",
     *  statusCodes={
     *     200="the query is ok",
     *     401= "The connection is required"
     *  }
     * )
     */
    public function getIpAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $ip = $request->get('ip');

        /** @var BlockedIP $blockedIp */
        $blockedIp = $em->getRepository("AppBundle:BlockedIP")->findOneBy(['ip' => $ip]);


        return $this->json(['ip' => $blockedIp]);
    }

    /**
     * @Rest\Post("/auth/blocked-ip")
     * @return Response
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Ajouter une adresse IP dans la liste des adresses potentiellement dangereuse",
     *  statusCodes={
     *     200="the query is ok",
     *     401= "The connection is required"
     *  },
     *  parameters={
     *     {"name"="ip", "dataType"="string", "required"=true, "description"="Addresse IP à bloquer"}
     *  }
     * )
     */
    public function blockIpAction(Request $request)
    {
        $message = ["code" => 1];

        $em = $this->getDoctrine()->getManager();

        $params = json_decode($request->getContent());
        $ip = $params->ip;

        /** @var BlockedIP $blockedIp */
        $blockedIp = $em->getRepository("AppBundle:BlockedIP")->findOneBy(['ip' => $ip]);

        //L'addresse est déjà bloqué !
        if(is_object($blockedIp)){
            $message['code'] = 0;
        }
        else{
            $bip = new BlockedIP();
            $bip->setIp($ip);

            $em->persist($bip);
            $em->flush();
        }

        return $this->json($message);
    }

    /**
     * @Rest\Delete("/auth/blocked-ip")
     * @return Response
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Débloquer une adresse IP",
     *  statusCodes={
     *     200="the query is ok",
     *     401= "The connection is required"
     *  },
     *  parameters={
     *      {"name"="ip", "dataType"="string", "required"=true, "description"="Addresse IP à bloquer"}
     *  }
     * )
     */
    public function unBlockIpAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $params = json_decode($request->getContent());
        $ip = $params->ip;

        /** @var BlockedIP $blockedIp */
        $blockedIp = $em->getRepository("AppBundle:BlockedIP")->findOneBy(['ip' => $ip]);

        //L'addresse est déjà bloqué !
        if(is_object($blockedIp)){
           $em->remove($blockedIp);
           $em->flush();
        }

        return $this->json(['Success !']);
    }

}