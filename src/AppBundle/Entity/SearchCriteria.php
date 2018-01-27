<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SearchCriteria
 *
 * @ORM\Table(name="search_criteria")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SearchCriteriaRepository")
 */
class SearchCriteria
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
     * @ORM\Column(name="matchMinAge", type="integer", nullable=true)
     */
    private $matchMinAge;

    /**
     * @var int
     *
     * @ORM\Column(name="matchMaxAge", type="integer", nullable=true)
     */
    private $matchMaxAge;

    /**
     * @var string
     *
     * @ORM\Column(name="matchSex", type="string", length=255, nullable=true)
     */
    private $matchSex;

    /**
     * @var array
     *
     * @ORM\Column(name="professions", type="array", nullable=true)
     */
    private $professions;

    /**
     * @var float
     *
     * @ORM\Column(name="matchDistanceMin", type="float", nullable=true)
     */
    private $matchDistanceMin;

    /**
     * @var float
     *
     * @ORM\Column(name="matchDistanceMax", type="float", nullable=true)
     */
    private $matchDistanceMax;

    /**
     * @var array
     *
     * @ORM\Column(name="matchCities", type="array", nullable=true)
     */
    private $matchCities;

    /**
     * @var array
     *
     * @ORM\Column(name="matchCountries", type="array", nullable=true)
     */
    private $matchCountries;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createDate", type="datetime")
     */
    private $createDate;



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
     * Set matchMinAge
     *
     * @param integer $matchMinAge
     *
     * @return SearchCriteria
     */
    public function setMatchMinAge($matchMinAge)
    {
        $this->matchMinAge = $matchMinAge;

        return $this;
    }

    /**
     * Get matchMinAge
     *
     * @return integer
     */
    public function getMatchMinAge()
    {
        return $this->matchMinAge;
    }

    /**
     * Set matchMaxAge
     *
     * @param integer $matchMaxAge
     *
     * @return SearchCriteria
     */
    public function setMatchMaxAge($matchMaxAge)
    {
        $this->matchMaxAge = $matchMaxAge;

        return $this;
    }

    /**
     * Get matchMaxAge
     *
     * @return integer
     */
    public function getMatchMaxAge()
    {
        return $this->matchMaxAge;
    }

    /**
     * Set matchSex
     *
     * @param string $matchSex
     *
     * @return SearchCriteria
     */
    public function setMatchSex($matchSex)
    {
        $this->matchSex = $matchSex;

        return $this;
    }

    /**
     * Get matchSex
     *
     * @return string
     */
    public function getMatchSex()
    {
        return $this->matchSex;
    }

    /**
     * Set professions
     *
     * @param array $professions
     *
     * @return SearchCriteria
     */
    public function setProfessions($professions)
    {
        $this->professions = $professions;

        return $this;
    }

    /**
     * Get professions
     *
     * @return array
     */
    public function getProfessions()
    {
        return $this->professions;
    }

    /**
     * Set matchDistanceMin
     *
     * @param float $matchDistanceMin
     *
     * @return SearchCriteria
     */
    public function setMatchDistanceMin($matchDistanceMin)
    {
        $this->matchDistanceMin = $matchDistanceMin;

        return $this;
    }

    /**
     * Get matchDistanceMin
     *
     * @return float
     */
    public function getMatchDistanceMin()
    {
        return $this->matchDistanceMin;
    }

    /**
     * Set matchDistanceMax
     *
     * @param float $matchDistanceMax
     *
     * @return SearchCriteria
     */
    public function setMatchDistanceMax($matchDistanceMax)
    {
        $this->matchDistanceMax = $matchDistanceMax;

        return $this;
    }

    /**
     * Get matchDistanceMax
     *
     * @return float
     */
    public function getMatchDistanceMax()
    {
        return $this->matchDistanceMax;
    }

    /**
     * Set matchCities
     *
     * @param array $matchCities
     *
     * @return SearchCriteria
     */
    public function setMatchCities($matchCities)
    {
        $this->matchCities = $matchCities;

        return $this;
    }

    /**
     * Get matchCities
     *
     * @return array
     */
    public function getMatchCities()
    {
        return $this->matchCities;
    }

    /**
     * Set matchCountries
     *
     * @param array $matchCountries
     *
     * @return SearchCriteria
     */
    public function setMatchCountries($matchCountries)
    {
        $this->matchCountries = $matchCountries;

        return $this;
    }

    /**
     * Get matchCountries
     *
     * @return array
     */
    public function getMatchCountries()
    {
        return $this->matchCountries;
    }

    /**
     * Set createDate
     *
     * @param \DateTime $createDate
     *
     * @return SearchCriteria
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
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return SearchCriteria
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
}
