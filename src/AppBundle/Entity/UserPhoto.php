<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserPhoto
 *
 * @ORM\Table(name="user_photo")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserPhotoRepository")
 */
class UserPhoto
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;


    // est  une variable d'aide pour acceder aux images depuis le js (n'est pas present  dans la bd
    private $path;

    // est  une variable d'aide pour acceder aux dossiers d'images depuis le js (n'est pas present  dans la bd
    private $folder;


    /**
     * @return string
     */
    public function getPath()
    {
        if($this->getUser()!=null)
        {
            $file = new Files();
            $this->path = $this->hashname==null? null: $file->initialpath."photo/user".$this->getUser()->getId()."/".$this->hashname;
        }
        return $this->path;
    }

    public function path($id){
        $file = new Files();
        return $this->hashname==null? null: $file->initialpath."photo/user".$id."/".$this->hashname;
    }

    public function getFolder(){
        $file = new Files();
        $this->folder = $file->initialpath."photo/user".$this->user->getId();
        return $this->folder;
    }


    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User",cascade={"persist"})
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    protected $user;


    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=50)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="hashname", type="string", length=255)
     */
    private $hashname;

    /**
     * @var float
     *
     * @ORM\Column(name="size", type="float")
     */
    private $size;

    /**
     * @var string
     *
     * @ORM\Column(name="mimeType", type="string", length=25)
     */
    private $mimeType;

    /**
     * @var bool
     *
     * @ORM\Column(name="isValid", type="boolean")
     */
    private $isValid;


    /**
     * @var bool
     *
     * @ORM\Column(name="isProfile", type="boolean")
     */
    private $isProfile;

    /**
     * @var string
     *
     * @ORM\Column(name="visibility", type="string", length=25)
     */
    private $visibility;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updateDate", type="datetime",nullable=true)
     */
    private $updateDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createDate", type="datetime")
     */
    private $createDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="publishedDate", type="datetime", nullable=true)
     */
    private $publishedDate;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }



    /**
     * @return int
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param int $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getHashname()
    {
        return $this->hashname;
    }

    /**
     * @param string $hashname
     */
    public function setHashname($hashname)
    {
        $this->hashname = $hashname;
    }

    /**
     * @return float
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param float $size
     */
    public function setSize($size)
    {
        $this->size = $size;
    }

    /**
     * @return string
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * @param string $mimeType
     */
    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;
    }

    /**
     * @return boolean
     */
    public function getIsValid()
    {
        return $this->isValid;
    }

    /**
     * @param boolean $isValid
     */
    public function setIsValid($isValid)
    {
        $this->isValid = $isValid;
    }

    /**
     * @return string
     */
    public function getVisibility()
    {
        return $this->visibility;
    }

    /**
     * @param string $visibility
     */
    public function setVisibility($visibility)
    {
        $this->visibility = $visibility;
    }

    /**
     * @return \DateTime
     */
    public function getUpdateDate()
    {
        return $this->updateDate;
    }

    /**
     * @param \DateTime $updateDate
     */
    public function setUpdateDate($updateDate)
    {
        $this->updateDate = $updateDate;
    }

    /**
     * @return \DateTime
     */
    public function getCreateDate()
    {
        return $this->createDate;
    }

    /**
     * @param \DateTime $createDate
     */
    public function setCreateDate($createDate)
    {
        $this->createDate = $createDate;
    }


    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return UserPhoto
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
     * Set isProfile
     *
     * @param boolean $isProfile
     *
     * @return UserPhoto
     */
    public function setIsProfile($isProfile)
    {
        $this->isProfile = $isProfile;

        return $this;
    }

    /**
     * Get isProfile
     *
     * @return boolean
     */
    public function getIsProfile()
    {
        return $this->isProfile;
    }

    /**
     * Set publishedDate
     *
     * @param \DateTime $publishedDate
     *
     * @return UserPhoto
     */
    public function setPublishedDate($publishedDate)
    {
        $this->publishedDate = $publishedDate;

        return $this;
    }

    /**
     * Get publishedDate
     *
     * @return \DateTime
     */
    public function getPublishedDate()
    {
        return $this->publishedDate;
    }
}
