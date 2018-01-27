<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Namshi\JOSE\Base64\Base64UrlSafeEncoder;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use FOS\UserBundle\Model\User as BaseUser;
/**
 * User
 *
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserRepository")
 */
class User extends  BaseUser implements AdvancedUserInterface
{
    /**
     * @var int
     *
     *
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="firstName", type="string", length=255)
     */
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="lastName", type="string", length=255, nullable=true)
     */
    private $lastName;


    /**
     * @var boolean
     *
     * @ORM\Column(name="isOnline", type="boolean")
     */
    private $isOnline;


    /**
     * @var \DateTime
     *
     * @ORM\Column(name="birthDate", type="date")
     */
    private $birthDate;

    /**
     * @var string
     *
     * @ORM\Column(name="profession", type="string", length=255, nullable=true)
     */
    private $profession;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255)
     */
    private $type;


    /**
     * @var string
     *
     * @ORM\Column(name="relationshipStatus", type="string", length=255, nullable=true)
     */
    private $relationshipStatus;


    /**
     * @var string
     *
     * @ORM\Column(name="joinReason", type="string", length=255,nullable=true)
     */
    private $joinReason;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="joinDate", type="datetime", nullable=true)
     */
    private $joinDate;


    /**
     * @var boolean
     *
     * @ORM\Column(name="isEmailVerified", type="boolean")
     */
    private $isEmailVerified;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isVip", type="boolean")
     */
    private $isVip;

    /**
     * @var string
     *
     * @ORM\Column(name="gender", type="string", length=255, nullable=true)
     */
    private $gender;


    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=255, nullable=true)
     */
    private $city;



    /**
     * @var string
     *
     * @ORM\Column(name="country", type="string", length=255, nullable=true)
     */
    private $country;


    /**
     * @var array
     *
     * @ORM\Column(name="phones", type="array",nullable=true)
     */
    private $phones;


    /**
     * @var array
     *
     * @ORM\Column(name="profileVisibility", type="array",nullable=true)
     */
    private $profileVisibility;



    /**
     * @var array
     *
     * @ORM\Column(name="confirmPassword", type="array",nullable=true)
     */
    private $confirmPassword;




    /**
     * @var array
     *
     * @ORM\Column(name="emailToken", type="array",nullable=true)
     */
    private $emailToken;


    //permet d'afficher le nom avec un decoupage
    private $name;

    public function getName()
    {
        $name = $this->lastName.' '.$this->firstName;
       $this->name = substr($name,0,8);
        $this->name = strlen($name)>8? $this->name.'...' : $this->name;
        return $this->name;
    }


    //permet d'afficher le nom avec prenom
    private $fullname;

    public function getFullname()
    {
        $name = $this->lastName.' '.$this->firstName;
        $this->fullname = $name;

        return $this->fullname;
    }


    //permet d'afficher le nom  et  le pays
    private $fullnameAndcountry;

    public function getFullnameAndcountry()
    {
        $name = $this->lastName.' '.$this->firstName. ' : '.$this->country;
        $this->fullnameAndcountry = $name;

        return $this->fullnameAndcountry;
    }

    //get name or lastname
    private $lastNameOrFirstname;

    public function getLastNameOrFirstname()
    {
        $name = $this->lastName ==null ? $this->firstName : $this->lastName;
        $this->lastNameOrFirstname = $name;

        return $this->lastNameOrFirstname;
    }



    //permet d'afficher la cle concatenation du  prenom + nom +id
    private $key;

    public function getkey()
    {
        $name = $this->lastName.'-'.$this->firstName.'-'.$this->id;
        $name = str_replace(' ','-',$name);
        $this->key = $name;
        return $this->key;
    }

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
     * Set firstName
     *
     * @param string $firstName
     *
     * @return User
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     *
     * @return User
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set isOnline
     *
     * @param boolean $isOnline
     *
     * @return User
     */
    public function setIsOnline($isOnline)
    {
        $this->isOnline = $isOnline;

        return $this;
    }

    /**
     * Get isOnline
     *
     * @return boolean
     */
    public function getIsOnline()
    {
        return $this->isOnline;
    }

    /**
     * Set birthDate
     *
     * @param \DateTime $birthDate
     *
     * @return User
     */
    public function setBirthDate($birthDate)
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    /**
     * Get birthDate
     *
     * @return \DateTime
     */
    public function getBirthDate()
    {
        return $this->birthDate;
    }

    /**
     * Set profession
     *
     * @param string $profession
     *
     * @return User
     */
    public function setProfession($profession)
    {
        $this->profession = $profession;

        return $this;
    }

    /**
     * Get profession
     *
     * @return string
     */
    public function getProfession()
    {
        return $this->profession;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return User
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set relationshipStatus
     *
     * @param string $relationshipStatus
     *
     * @return User
     */
    public function setRelationshipStatus($relationshipStatus)
    {
        $this->relationshipStatus = $relationshipStatus;

        return $this;
    }

    /**
     * Get relationshipStatus
     *
     * @return string
     */
    public function getRelationshipStatus()
    {
        return $this->relationshipStatus;
    }

    /**
     * Set joinReason
     *
     * @param string $joinReason
     *
     * @return User
     */
    public function setJoinReason($joinReason)
    {
        $this->joinReason = $joinReason;

        return $this;
    }

    /**
     * Get joinReason
     *
     * @return string
     */
    public function getJoinReason()
    {
        return $this->joinReason;
    }

    /**
     * Set isConfirm
     *
     * @param boolean $isEmailVerified
     *
     * @return User
     */
    public function setIsEmailVerified($isEmailVerified)
    {
        $this->isEmailVerified = $isEmailVerified;

        return $this;
    }

    /**
     * Get isEmailVerified
     *
     * @return boolean
     */
    public function getIsEmailVerified()
    {
        return $this->isEmailVerified;
    }

    /**
     * Set isVip
     *
     * @param boolean $isVip
     *
     * @return User
     */
    public function setIsVip($isVip)
    {
        $this->isVip = $isVip;

        return $this;
    }

    /**
     * Get isVip
     *
     * @return boolean
     */
    public function getIsVip()
    {
        return $this->isVip;
    }

    /**
     * Set gender
     *
     * @param string $gender
     *
     * @return User
     */
    public function setGender($gender)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * Get gender
     *
     * @return string
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Set phones
     *
     * @param array $phones
     *
     * @return User
     */
    public function setPhones($phones)
    {
        $this->phones = $phones;

        return $this;
    }

    /**
     * Get phones
     *
     * @return array
     */
    public function getPhones()
    {
        return $this->phones;
    }

    /**
     * Set profileVisibility
     *
     * @param array $profileVisibility
     *
     * @return User
     */
    public function setProfileVisibility($profileVisibility)
    {
        $this->profileVisibility = $profileVisibility;

        return $this;
    }

    /**
     * Get profileVisibility
     *
     * @return array
     */
    public function getProfileVisibility()
    {
        return $this->profileVisibility;
    }


    /**
     * Set confirmPassword
     *
     * @param array $confirmPassword
     *
     * @return User
     */
    public function setConfirmPassword($confirmPassword)
    {
        $this->confirmPassword = $confirmPassword;

        return $this;
    }

    /**
     * Get confirmPassword
     *
     * @return array
     */
    public function getConfirmPassword()
    {
        return $this->confirmPassword;
    }

    /**
     * Set city
     *
     * @param string $city
     *
     * @return User
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

    /**
     * Set country
     *
     * @param string $country
     *
     * @return User
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
     * Set emailToken
     *
     * @param array $emailToken
     *
     * @return User
     */
    public function setEmailToken($emailToken)
    {
        $this->emailToken = $emailToken;

        return $this;
    }

    /**
     * Get emailToken
     *
     * @return array
     */
    public function getEmailToken()
    {
        return $this->emailToken;
    }

    /**
     * Set joinDate
     *
     * @param \DateTime $joinDate
     *
     * @return User
     */
    public function setJoinDate($joinDate)
    {
        $this->joinDate = $joinDate;

        return $this;
    }

    /**
     * Get joinDate
     *
     * @return \DateTime
     */
    public function getJoinDate()
    {
        return $this->joinDate;
    }
}
