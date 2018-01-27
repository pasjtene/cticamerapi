<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Settings
 *
 * @ORM\Table(name="settings")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SettingsRepository")
 */
class Settings
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
     * @ORM\Column(name="upp", type="integer", options={"default":30})
     */
    private $userPerPage;

    /**
     * @var int
     *
     * @ORM\Column(name="pol", type="integer", options={"default":50})
     */
    private $pointOnLogin;

    /**
     * Nombre de point à soustraire par message envoyé
     *
     * @var int
     *
     * @ORM\Column(name="ppm", type="integer", options={"default":5})
     */
    private $pointPerMessage;

    /**
     * Nombre de point à donner après l'upload d'une photo
     *
     * @var int
     *
     * @ORM\Column(name="pfu", type="integer", options={"default":25})
     */
    private $pointForUpload;

    /**
     * Le nombre de point à donner à l'utilisateur après son inscription sur la plateforme
     *
     * @var int
     *
     * @ORM\Column(name="dp", type="integer", options={"default":500})
     */
    private $defaultPoint;

    /**
     * Le nombre de point à donner lorsque l'utilisateur opte pour un compte VIP
     *
     * @var int
     *
     * @ORM\Column(name="pfv", type="integer", options={"default":250})
     */
    private $pointForVip;

    /**
     * @var int
     *
     * @ORM\Column(name="ppp", type="integer", options={"default":30})
     */
    private $picturePerPage;


    public function __construct()
    {
        $this->userPerPage = 30;
        $this->pointOnLogin = 50;
        $this->pointPerMessage = 5;
        $this->pointForUpload = 20;
        $this->defaultPoint  = 500;
        $this->pointForVip = 250;
        $this->picturePerPage = 30;
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
     * Set userPerPage
     *
     * @param integer $userPerPage
     *
     * @return Settings
     */
    public function setUserPerPage($userPerPage)
    {
        $this->userPerPage = $userPerPage;

        return $this;
    }

    /**
     * Get userPerPage
     *
     * @return int
     */
    public function getUserPerPage()
    {
        return $this->userPerPage;
    }

    /**
     * Set pointOnLogin
     *
     * @param integer $pointOnLogin
     *
     * @return Settings
     */
    public function setPointOnLogin($pointOnLogin)
    {
        $this->pointOnLogin = $pointOnLogin;

        return $this;
    }

    /**
     * Get pointOnLogin
     *
     * @return integer
     */
    public function getPointOnLogin()
    {
        return $this->pointOnLogin;
    }

    /**
     * Set pointPerMessage
     *
     * @param integer $pointPerMessage
     *
     * @return Settings
     */
    public function setPointPerMessage($pointPerMessage)
    {
        $this->pointPerMessage = $pointPerMessage;

        return $this;
    }

    /**
     * Get pointPerMessage
     *
     * @return integer
     */
    public function getPointPerMessage()
    {
        return $this->pointPerMessage;
    }

    /**
     * Set pointForUpload
     *
     * @param integer $pointForUpload
     *
     * @return Settings
     */
    public function setPointForUpload($pointForUpload)
    {
        $this->pointForUpload = $pointForUpload;

        return $this;
    }

    /**
     * Get pointForUpload
     *
     * @return integer
     */
    public function getPointForUpload()
    {
        return $this->pointForUpload;
    }

    /**
     * Set defaultPoint
     *
     * @param integer $defaultPoint
     *
     * @return Settings
     */
    public function setDefaultPoint($defaultPoint)
    {
        $this->defaultPoint = $defaultPoint;

        return $this;
    }

    /**
     * Get defaultPoint
     *
     * @return integer
     */
    public function getDefaultPoint()
    {
        return $this->defaultPoint;
    }

    /**
     * Set pointForVip
     *
     * @param integer $pointForVip
     *
     * @return Settings
     */
    public function setPointForVip($pointForVip)
    {
        $this->pointForVip = $pointForVip;

        return $this;
    }

    /**
     * Get pointForVip
     *
     * @return integer
     */
    public function getPointForVip()
    {
        return $this->pointForVip;
    }
    
    /** 
     *Set picturePerPage
     *
     * @param integer $picturePerPage
     *
     * @return Settings
     */
    public function setPicturePerPage($picturePerPage)
    {
        $this->picturePerPage = $picturePerPage;

        return $this;
    }

    /**
     * Get picturePerPage
     *
     * @return int
     */
    public function getPicturePerPage()
    {
        return $this->picturePerPage;
    }
}
