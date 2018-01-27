<?php
/**
 * Created by PhpStorm.
 * User: Danick Takam
 * Date: 17/06/2017
 * Time: 23:55
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;


    /**
     * User
     *
     * @ORM\Table(name="auth_tokens")
     * @ORM\Entity(repositoryClass="AppBundle\Repository\AuthTokenRepository")
     */
    class AuthToken
    {
        /**
         * @ORM\Id
         * @ORM\Column(type="integer")
         * @ORM\GeneratedValue
         */
        protected $id;

        /**
         * @var string
         *
         * @ORM\Column(name="value", type="string", length=255)
         */
        protected $value;


        /**
         * @var \DateTime
         *
         * @ORM\Column(name="createdAt", type="datetime")
         */
        protected $createdAt;

        /**
         * @var User
         * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User",cascade={"persist"})
         * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
         */
        protected $user;



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
     * Set value
     *
     * @param string $value
     *
     * @return AuthToken
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return AuthToken
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return AuthToken
     */
    public function setUser(\AppBundle\Entity\User $user = null)
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
}
