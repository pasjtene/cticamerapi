<?php

namespace ApiBundle\Controller;

use ApiBundle\Task\DeletePictureTask;
use ApiBundle\Task\FindUserPicturesTask;
use AppBundle\Entity\Files;
use AppBundle\Entity\Settings;
use AppBundle\Entity\UserPhoto;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PictureController extends FOSRestController
{
    /**
     * @Rest\Get("/auth/pictures")
     * @return Response
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Récupérer les photos des utilisateurs de l'application",
     *  statusCodes={
     *     200="the query is ok",
     *     401= "The connection is required"
     *  }
     * )
     */
    public function getPicturesAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Settings $settings */
        $settings = $em->getRepository("AppBundle:Settings")->findOneBy([]);

        $property = $request->get('property');
        $order = $request->get('order');

        $itemPerPage = is_object($settings) ? $settings->getPicturePerPage() : ITEM_PER_PAGE;
        $page = $request->get('page');
        $uid = $request->get('uid');

        $page = $page == null ? 1 : $page;

        if(is_null($uid)){
            $allPictures = $em->getRepository('AppBundle:UserPhoto')->findAll();
        }else{
            $allPictures = $em->getRepository('AppBundle:UserPhoto')->findBy(['user' =>$uid]);
        }

        $pictureCount = sizeof($allPictures);

        $paginationNumber = round(floatval($pictureCount) / floatval($itemPerPage), 0, PHP_ROUND_HALF_UP);
        $paginationNumber = $paginationNumber < 1 || $paginationNumber < $page ? 1 : $paginationNumber;

        $page = $page > $paginationNumber ? $paginationNumber : $page;
        $offset = ($page-1) * $itemPerPage;

        $pictures = $em->getRepository('AppBundle:UserPhoto')->findPictures($itemPerPage, $offset, $property, $order, $uid);

        $datas['page'] = intval($page);
        $datas['itemPerPage'] = $itemPerPage > $pictureCount ? $pictureCount : $itemPerPage;
        $datas['offset'] = $offset;
        $datas['paginationCount'] = $paginationNumber;
        $datas['pageNext'] = $paginationNumber > 1 && $page < $paginationNumber ? $page+1 : $page;
        $datas['pagePrev'] = intval($page > 1 ? $page-1 : $page);
        $datas['pictures'] = $pictures;
        $datas['total'] = $pictureCount;

        return $this->json($datas);
    }

    /**
     * @Rest\Put("/auth/pictures/change")
     * @return Response
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Rendre privé les photos des utilisateurs",
     *  statusCodes={
     *     200="the query is ok",
     *     401= "The connection is required"
     *  },
     *  parameters={
     *     {"name"="pictures", "dataType"="array", "required"=true, "description"="Tableau d'identifiants des photos à modifier la visibilité"},
     *     {"name"="action", "dataType"="integer", "required"=true, "description"="Indique s'il faut rendre privé ou non"}
     *  }
     * )
     */
    public function changePictureVisibilityAction(Request $request)
    {
        $visibility = ["private", "public"];
        $em = $this->getDoctrine()->getManager();

        $params = json_decode($request->getContent());
        $pictures = $params->pictures;
        $action = intval($params->action);

        foreach ($pictures as $pictureId)
        {
            /** @var UserPhoto $picture */
            $picture = $em->getRepository('AppBundle:UserPhoto')->find($pictureId);

            if(is_object($picture)){
                $picture->setVisibility($visibility[$action]);
                $em->flush();
            }
        }

        return $this->json([]);
    }

    /**
     * @Rest\Delete("/auth/pictures")
     * @return Response
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Supprimer les photos des utilisateurs",
     *  statusCodes={
     *     200="the query is ok",
     *     401= "The connection is required"
     *  },
     *  parameters={
     *     {"name"="pictures", "dataType"="array", "required"=true, "description"="Tableau d'identifiants des photos à supprimer"}
     *  }
     * )
     */
    public function deletePicturesAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $params = json_decode($request->getContent());

        $deleteTask = new DeletePictureTask($em);
        $deleteTask->run($params->pictures);

        return $this->json(["Success"]);
    }

    /**
     * @Rest\Get("/auth/members/{id}/pictures")
     * @return Response
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Récupérer les photos d'un utilisateur",
     *  statusCodes={
     *     200="the query is ok",
     *     401= "The connection is required"
     *  }
     * )
     */
    public function getUsersPicturesAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $task = new FindUserPicturesTask($em);
        $pictures = $task->run($id);

        return $this->json(['pictures' => $pictures]);
    }

}