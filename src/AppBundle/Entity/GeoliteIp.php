<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * GeoliteIp
 *
 * @ORM\Table(name="geolite_ip")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\GeoliteIpRepository")
 */
class GeoliteIp
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var float
     *
     * @ORM\Column(name="startIpNum", type="float")
     */
    private $startIpNum;

    /**
     * @var float
     *
     * @ORM\Column(name="endIpNum", type="float")
     */
    private $endIpNum;

    /**
     * @var int
     *
     * @ORM\Column(name="locId", type="integer")
     */
    private $locId;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set startIpNum
     *
     * @param float $startIpNum
     *
     * @return GeoliteIp
     */
    public function setStartIpNum($startIpNum)
    {
        $this->startIpNum = $startIpNum;

        return $this;
    }

    /**
     * Get startIpNum
     *
     * @return float
     */
    public function getStartIpNum()
    {
        return $this->startIpNum;
    }

    /**
     * Set endIpNum
     *
     * @param float $endIpNum
     *
     * @return GeoliteIp
     */
    public function setEndIpNum($endIpNum)
    {
        $this->endIpNum = $endIpNum;

        return $this;
    }

    /**
     * Get endIpNum
     *
     * @return float
     */
    public function getEndIpNum()
    {
        return $this->endIpNum;
    }

    /**
     * Set locId
     *
     * @param integer $locId
     *
     * @return GeoliteIp
     */
    public function setLocId($locId)
    {
        $this->locId = $locId;

        return $this;
    }

    /**
     * Get locId
     *
     * @return int
     */
    public function getLocId()
    {
        return $this->locId;
    }
}
