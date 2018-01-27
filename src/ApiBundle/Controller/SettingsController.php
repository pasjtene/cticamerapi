<?php

namespace ApiBundle\Controller;

use AppBundle\Entity\Settings;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SettingsController extends FOSRestController
{
    /**
     * @Rest\Get("/settings/init")
     * @return Response
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Initialiser les paramètres de l'application",
     *  statusCodes={
     *     200="the query is ok"
     *  }
     * )
     */
    public function initSettingsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $settings = new Settings();

        $em->persist($settings);
        $em->flush();

        return $this->json(['message' => "Settings initialized successfully !"]);
    }

    /**
     * @Rest\Get("/auth/settings")
     * @return Response
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Récupérer les paramètres de l'application",
     *  statusCodes={
     *     200="the query is ok",
     *     401= "The connection is required"
     *  }
     * )
     */
    public function getSettingsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $item = $em->getRepository("AppBundle:Settings")->findOneBy([]);

        return $this->json($item);
    }

    /**
     * @Rest\Put("/auth/settings")
     * @return Response
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Modifier les paramètres de l'application",
     *  statusCodes={
     *     200="the query is ok",
     *     401= "The connection is required"
     *  },
     *  parameters={
     *     {"name"="upp", "dataType"="integer", "required"=false, "description"="Représente le nombre d'utilisateur à afficher par page"},
     *     {"name"="pol", "dataType"="integer", "required"=false, "description"="Représente le nombre de point à chaque connexion "},
     *     {"name"="ppm", "dataType"="integer", "required"=false, "description"="Représente le nombre de point par message envoyé"},
     *     {"name"="pfu", "dataType"="integer", "required"=false, "description"="Représente le nombre de point par upload"},
     *     {"name"="dp", "dataType"="integer", "required"=false, "description"="Représente le nombre de point après l'inscription"},
     *     {"name"="pfv", "dataType"="integer", "required"=false, "description"="Représente le nombre de point pour le statut VIP"},
     *     {"name"="ppp", "dataType"="integer", "required"=false, "description"="Représente le nombre de photos à afficher par page"}
     *  }
     * )
     */
    public function updateSettingsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Settings $item */
        $item = $em->getRepository("AppBundle:Settings")->findOneBy([]);

        $item->setUserPerPage(intval($request->get('upp')));
        $item->setPointOnLogin(intval($request->get('pol')));
        $item->setPointPerMessage(intval($request->get('ppm')));
        $item->setPointForUpload(intval($request->get('pfu')));
        $item->setDefaultPoint(intval($request->get('dp')));
        $item->setPointForVip(intval($request->get('pfv')));
        $item->setPicturePerPage(intval($request->get('ppp')));

        $em->flush();

        return $this->json($item);
    }

}