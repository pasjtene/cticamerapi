<?php

namespace ApiBundle\Task;

use Doctrine\ORM\EntityManager;

class FindUserPicturesTask
{
    /** @var  EntityManager */
    private $em;

    public function __construct($em){
        $this->em = $em;
    }

    /**
     * @param int $memberId
     *
     * @return array
     */
    public function run($memberId)
    {
        $pictures = $this->em->getRepository('AppBundle:UserPhoto')->findBy(['user' => $memberId], ['createDate' => 'DESC']);

        return $pictures;
    }
}