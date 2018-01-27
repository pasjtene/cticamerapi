<?php
/**
 * Created by PhpStorm.
 * User: tene
 * Date: 21/06/2017
 * Time: 12:47
 */

namespace ApiBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use  Symfony\Component\Security\Core\Exception\AccessDeniedException;



class pascalUserController extends FOSRestController
{

    /**
     * @Rest\Get("/auth/usersj")
     * @return Response
     * @ApiDoc(
     *  resource=true,
     *  description="Récupérer la liste des utilisateurs",
     *  statusCodes={
     *     200="the query is ok",
     *     401= "The connection is required",
     *     403= "Access Denied"
     *
     *  },
     *  parameters={
     *     {"name"="utilisateur_id", "dataType"="integer", "required"=true, "description"="Représente l'identifiant de l'administrateur à ajouter pour la classe"}
     *  }
     * )
     */
    public function indexAction(Request $request)
    {

        //you  can continious if you have a good privileges
        //$this->isgrantUser("ROLE_MODERATOR");


        $em = $this->getDoctrine()->getManager();
        $array = $em->getRepository("AppBundle:User")->findAll();
        return $this->json($array);
    }
}