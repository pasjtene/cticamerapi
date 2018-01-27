<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Geolite
 *
 * @ORM\Table(name="geolite")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\GeoliteRepository")
 */
class Geolite
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
     * @var int
     *
     * @ORM\Column(name="ipFrom", type="integer")
     */
    private $ipFrom;

    /**
     * @var int
     *
     * @ORM\Column(name="ipTo", type="integer")
     */
    private $ipTo;

    /**
     * @var string
     *
     * @ORM\Column(name="proxyType", type="string", length=255)
     */
    private $proxyType;


    /**
     * @var string
     *
     * @ORM\Column(name="countryCode", type="string", length=255)
     */
    private $countryCode;


    /**
     * @var string
     *
     * @ORM\Column(name="country", type="string", length=255)
     */
    private $country;

    /**
     * @var string
     *
     * @ORM\Column(name="region", type="string", length=255)
     */
    private $region;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=255)
     */
    private $city;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }



    /**
     * Set ipFrom
     *
     * @param integer $ipFrom
     *
     * @return Geolite
     */
    public function setIpFrom($ipFrom)
    {
        $this->ipFrom = $ipFrom;

        return $this;
    }

    /**
     * Get ipFrom
     *
     * @return integer
     */
    public function getIpFrom()
    {
        return $this->ipFrom;
    }

    /**
     * Set ipTo
     *
     * @param integer $ipTo
     *
     * @return Geolite
     */
    public function setIpTo($ipTo)
    {
        $this->ipTo = $ipTo;

        return $this;
    }

    /**
     * Get ipTo
     *
     * @return integer
     */
    public function getIpTo()
    {
        return $this->ipTo;
    }

    /**
     * Set proxyType
     *
     * @param string $proxyType
     *
     * @return Geolite
     */
    public function setProxyType($proxyType)
    {
        $this->proxyType = $proxyType;

        return $this;
    }

    /**
     * Get proxyType
     *
     * @return string
     */
    public function getProxyType()
    {
        return $this->proxyType;
    }

    /**
     * Set countryCode
     *
     * @param string $countryCode
     *
     * @return Geolite
     */
    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    /**
     * Get countryCode
     *
     * @return string
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * Set country
     *
     * @param string $country
     *
     * @return Geolite
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set region
     *
     * @param string $region
     *
     * @return Geolite
     */
    public function setRegion($region)
    {
        $this->region = $region;

        return $this;
    }

    /**
     * Get region
     *
     * @return string
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * Set city
     *
     * @param string $city
     *
     * @return Geolite
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }
}
