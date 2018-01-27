<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserProfile
 *
 * @ORM\Table(name="user_profile")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserProfileRepository")
 */
class UserProfile
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
     * @var User
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User",cascade={"persist"})
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    protected $user;

    /**
     * @var Geolocation
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Geolocation",cascade={"persist"})
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    protected $geolocation;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createDate", type="datetime")
     */
    private $createDate;



    /**
     * @var string
     *
     * @ORM\Column(name="company", type="string", length=50, nullable=true)
     */
    private $company;

    /**
     * @var string
     *
     * @ORM\Column(name="aboutMe", type="text", nullable=true)
     */
    private $aboutMe;

    /**
     * @var string
     *
     * @ORM\Column(name="meetLike", type="text", nullable=true)
     */
    private $meetLike;

    /**
     * @var string
     *
     * @ORM\Column(name="maritalStatus", type="string", length=50, nullable=true)
     */
    private $maritalStatus;

    /**
     * @var int
     *
     * @ORM\Column(name="childNumber", type="integer", nullable=true)
     */
    private $childNumber;


    /**
     * @var array
     *
     * @ORM\Column(name="gpsPosition", type="array", nullable=true)
     */
    private $gpsPosition;




    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }


    /**
     * Set company
     *
     * @param string $company
     *
     * @return UserProfile
     */
    public function setCompany($company)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * Get company
     *
     * @return string
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Set gpsPosition
     *
     * @param array $gpsPosition
     *
     * @return UserProfile
     */
    public function setGpsPosition($gpsPosition)
    {
        $this->gpsPosition = $gpsPosition;

        return $this;
    }

    /**
     * Get gpsPosition
     *
     * @return array
     */
    public function getGpsPosition()
    {
        return $this->gpsPosition;
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return UserProfile
     */
    public function setUser(\AppBundle\Entity\User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set geolocation
     *
     * @param \AppBundle\Entity\Geolocation $geolocation
     *
     * @return UserProfile
     */
    public function setGeolocation(\AppBundle\Entity\Geolocation $geolocation)
    {
        $this->geolocation = $geolocation;

        return $this;
    }

    /**
     * Get geolocation
     *
     * @return \AppBundle\Entity\Geolocation
     */
    public function getGeolocation()
    {
        return $this->geolocation;
    }


    /**
     * Set createDate
     *
     * @param \DateTime $createDate
     *
     * @return UserProfile
     */
    public function setCreateDate($createDate)
    {
        $this->createDate = $createDate;

        return $this;
    }

    /**
     * Get createDate
     *
     * @return \DateTime
     */
    public function getCreateDate()
    {
        return $this->createDate;
    }

    /**
     * Set aboutMe
     *
     * @param string $aboutMe
     *
     * @return UserProfile
     */
    public function setAboutMe($aboutMe)
    {
        $this->aboutMe = $aboutMe;

        return $this;
    }

    /**
     * Get aboutMe
     *
     * @return string
     */
    public function getAboutMe()
    {
        return $this->aboutMe;
    }

    /**
     * Set meetLike
     *
     * @param string $meetLike
     *
     * @return UserProfile
     */
    public function setMeetLike($meetLike)
    {
        $this->meetLike = $meetLike;

        return $this;
    }

    /**
     * Get meetLike
     *
     * @return string
     */
    public function getMeetLike()
    {
        return $this->meetLike;
    }

    /**
     * Set maritalStatus
     *
     * @param string $maritalStatus
     *
     * @return UserProfile
     */
    public function setMaritalStatus($maritalStatus)
    {
        $this->maritalStatus = $maritalStatus;

        return $this;
    }

    /**
     * Get maritalStatus
     *
     * @return string
     */
    public function getMaritalStatus()
    {
        return $this->maritalStatus;
    }

    /**
     * Set childNumber
     *
     * @param integer $childNumber
     *
     * @return UserProfile
     */
    public function setChildNumber($childNumber)
    {
        $this->childNumber = $childNumber;

        return $this;
    }

    /**
     * Get childNumber
     *
     * @return integer
     */
    public function getChildNumber()
    {
        return $this->childNumber;
    }
}
