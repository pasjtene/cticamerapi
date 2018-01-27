<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Message
 *
 * @ORM\Table(name="message")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MessageRepository")
 */
class Message
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
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Message", mappedBy="messageParent", cascade={"persist", "remove"})
     */
    private $subMessages;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Message", inversedBy="subMessages")
     * @ORM\JoinColumn(name="message_parent_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $messageParent;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $sender;

    /**
     *@ORM\OneToMany(targetEntity="AppBundle\Entity\File", mappedBy="message", cascade={"persist"})
     *@ORM\JoinColumn(nullable=true)
     */
    private $files;


    /**
     * @var string
     *
     * @ORM\Column(name="ip", type="text")
     */
    private $ip;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text")
     */
    private $content;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createDate", type="datetimetz")
     */
    private $createDate;

    /**
     * @var bool
     *
     * @ORM\Column(name="isValid", type="boolean")
     */
    private $isValid;

    //permet d'afficher le message avec un decoupage
    private $contentTuncate;

    public function getContentTuncate()
    {
        $name = $this->content;
        $this->contentTuncate = substr($name,0,35);
        $this->contentTuncate = strlen($name)>36? $this->contentTuncate.'...' : $this->contentTuncate;
        return $this->contentTuncate;
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
     * Set content
     *
     * @param string $content
     *
     * @return Message
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set createDate
     *
     * @param \DateTime $createDate
     *
     * @return Message
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
     * Set isValid
     *
     * @param boolean $isValid
     *
     * @return Message
     */
    public function setIsValid($isValid)
    {
        $this->isValid = $isValid;

        return $this;
    }

    /**
     * Get isValid
     *
     * @return bool
     */
    public function getIsValid()
    {
        return $this->isValid;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->subMessages = new \Doctrine\Common\Collections\ArrayCollection();
        $this->files = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add subMessage
     *
     * @param \AppBundle\Entity\Message $subMessage
     *
     * @return Message
     */
    public function addSubMessage(\AppBundle\Entity\Message $subMessage)
    {
        $this->subMessages[] = $subMessage;

        return $this;
    }

    /**
     * Remove subMessage
     *
     * @param \AppBundle\Entity\Message $subMessage
     */
    public function removeSubMessage(\AppBundle\Entity\Message $subMessage)
    {
        $this->subMessages->removeElement($subMessage);
    }

    /**
     * Get subMessages
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSubMessages()
    {
        return $this->subMessages;
    }

    /**
     * Set messageParent
     *
     * @param \AppBundle\Entity\Message $messageParent
     *
     * @return Message
     */
    public function setMessageParent(\AppBundle\Entity\Message $messageParent = null)
    {
        $this->messageParent = $messageParent;

        return $this;
    }

    /**
     * Get messageParent
     *
     * @return \AppBundle\Entity\Message
     */
    public function getMessageParent()
    {
        return $this->messageParent;
    }



    /**
     * Get sender
     *
     * @return \AppBundle\Entity\User
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * Set sender
     *
     * @param \AppBundle\Entity\User $sender
     *
     * @return Message
     */
    public function setSender(\AppBundle\Entity\User $sender)
    {
        $this->sender = $sender;

        return $this;
    }


    /**
     * Add file
     *
     * @param \AppBundle\Entity\File $file
     *
     * @return Message
     */
    public function addFile(\AppBundle\Entity\File $file)
    {
        $this->files[] = $file;

        return $this;
    }

    /**
     * Remove file
     *
     * @param \AppBundle\Entity\File $file
     */
    public function removeFile(\AppBundle\Entity\File $file)
    {
        $this->files->removeElement($file);
    }

    /**
     * Get files
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * Set ip
     *
     * @param string $ip
     *
     * @return Message
     */
    public function setIp($ip)
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * Get ip
     *
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }
}
