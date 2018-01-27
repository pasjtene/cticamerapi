<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 7/28/17
 * Time: 10:44 PM
 */

namespace ApiBundle\Task;


use AppBundle\Entity\UserPhoto;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Monolog\Logger;
use AppBundle\Entity\Files;



class DeletePictureTask
{

    /** @var  EntityManager */
    private $em;

    public function __construct($em){
        $this->em = $em;

    }

    /**
     * @param array $pictures
     */
    public function run($pictures)
    {

        $picFolder = null;
        $defaultRoot = __DIR__.'/../../../web/web/';
        foreach ($pictures as $pictureId)
        {

            $picture = $this->em->getRepository('AppBundle:UserPhoto')->find($pictureId);

            if(is_null($picFolder)){
                $picFolder = $defaultRoot.$picture->getFolder();
            }

            if(is_object($picture))
            {
                $path = $defaultRoot.$picture->getPath();

                if(file_exists($path)){
                    unlink($path);
                }

                $this->em->remove($picture);
                $this->em->flush();
            }
        }


        if(!is_null($picFolder) && is_dir($picFolder)){
            rmdir($picFolder);
        }

    }
}