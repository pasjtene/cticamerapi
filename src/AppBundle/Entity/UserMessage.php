<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserMessage
 *
 * @ORM\Table(name="user_message")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserMessageRepository")
 */
class UserMessage
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
     * @var \DateTime
     *
     * @ORM\Column(name="readDate", type="datetime", nullable=true)
     */
    private $readDate;



    /**
     * @var bool
     *
     * @ORM\Column(name="isLocked", type="boolean")
     */
    private $isLocked;

    /**
     * @var bool
     *
     * @ORM\Column(name="isSee", type="boolean",nullable=true)
     */
    private $isSee;


    /**
     * @var bool
     *
     * @ORM\Column(name="sendRemove", type="boolean",nullable=true)
     */
    private $sendRemove;

    /**
     * @var bool
     *
     * @ORM\Column(name="recieverRemove", type="boolean",nullable=true)
     */
    private $recieverRemove;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User",cascade={"persist"})
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $receiver;



    /**
     * @var Message
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Message",cascade={"persist"})
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $message;





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
     * Set readDate
     *
     * @param \DateTime $readDate
     *
     * @return UserMessage
     */
    public function setReadDate($readDate)
    {
        $this->readDate = $readDate;

        return $this;
    }

    /**
     * Get readDate
     *
     * @return \DateTime
     */
    public function getReadDate()
    {
        return $this->readDate;
    }

    /**
     * Set isLocked
     *
     * @param boolean $isLocked
     *
     * @return UserMessage
     */
    public function setIsLocked($isLocked)
    {
        $this->isLocked = $isLocked;

        return $this;
    }

    /**
     * Get isLocked
     *
     * @return boolean
     */
    public function getIsLocked()
    {
        return $this->isLocked;
    }

    /**
     * Set receiver
     *
     * @param \AppBundle\Entity\User $receiver
     *
     * @return UserMessage
     */
    public function setReceiver(\AppBundle\Entity\User $receiver)
    {
        $this->receiver = $receiver;

        return $this;
    }

    /**
     * Get receiver
     *
     * @return \AppBundle\Entity\User
     */
    public function getReceiver()
    {
        return $this->receiver;
    }

    /**
     * Set message
     *
     * @param \AppBundle\Entity\Message $message
     *
     * @return UserMessage
     */
    public function setMessage(\AppBundle\Entity\Message $message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return \AppBundle\Entity\Message
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set isSee
     *
     * @param boolean $isSee
     *
     * @return UserMessage
     */
    public function setIsSee($isSee)
    {
        $this->isSee = $isSee;

        return $this;
    }

    /**
     * Get isSee
     *
     * @return boolean
     */
    public function getIsSee()
    {
        return $this->isSee;
    }

    /**
     * Set sendRemove
     *
     * @param boolean $sendRemove
     *
     * @return UserMessage
     */
    public function setSendRemove($sendRemove)
    {
        $this->sendRemove = $sendRemove;

        return $this;
    }

    /**
     * Get sendRemove
     *
     * @return boolean
     */
    public function getSendRemove()
    {
        return $this->sendRemove;
    }

    /**
     * Set recieverRemove
     *
     * @param boolean $recieverRemove
     *
     * @return UserMessage
     */
    public function setRecieverRemove($recieverRemove)
    {
        $this->recieverRemove = $recieverRemove;

        return $this;
    }

    /**
     * Get recieverRemove
     *
     * @return boolean
     */
    public function getRecieverRemove()
    {
        return $this->recieverRemove;
    }
}
