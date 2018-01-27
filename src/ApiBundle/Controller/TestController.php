<?php

namespace ApiBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\FOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class TestController extends FOSRestController
{
    /**
     * @Rest\Post("/auth/count/vips")
     * @return Response
     * @Method({"GET"})
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Retourner  le nombre des utilisateurs vips ",
     *  statusCodes={
     *     200="the query is ok",
     *     401= "The connection is required",
     *     403= "Access Denied"
     *
     *  },
     *  parameters={
     *     {"name"="id", "dataType"="integer", "required"=true, "description"="L'identifiant de l'utilisateur connecté "},
     *     {"name"="state", "dataType"="bool", "required"=true, "description"="L'etat des utilisateurs a selectionner"}
     *  }
     * )
     */


    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $liste = $em->getRepository('AppBundle:User')->findByisVip(true);
        $this->json($liste);
        $count = count($liste);
        return $this->json($count);

    }


}
