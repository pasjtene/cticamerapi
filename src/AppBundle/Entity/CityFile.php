<?php
/**
 * Created by PhpStorm.
 * User: Danick Takam
 * Date: 12/07/2017
 * Time: 17:00
 */

namespace AppBundle\Entity;


class CityFile
{
    private $city;
    private $accentCity;
    private $country;
    private $long;
    private $lart;
    private $region;
    private  $list;


    /**
     * Set city
     *
     * @param string $city
     *
     * @return CityFile
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get country
     *
     * @return string
     */
    public function getCity(){
        return $this->city;
    }

    /**
     * Get accentCity
     *
     * @return string
     */
    public function getAccentCity(){
        return $this->accentCity;
    }


    /**
     * Set accentCity
     *
     * @param string $accentCity
     *
     * @return CityFile
     */
    public function setAccentCity($accentCity)
    {
        $this->accentCity = $accentCity;

        return $this;
    }

    /**
     * Get country
     *
     * @return string
     */
    public function getCountry(){
        return $this->city;
    }


    /**
     * Set country
     *
     * @param string $country
     *
     * @return CityFile
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }


    /**
     * Get long
     *
     * @return string
     */
    public function getLong(){
        return $this->long;
    }


    /**
     * Set long
     *
     * @param string $long
     *
     * @return CityFile
     */
    public function setLong($long)
    {
        $this->long = $long;

        return $this;
    }



    /**
     * Get lart
     *
     * @return string
     */
    public function getLart(){
        return $this->city;
    }


    /**
     * Set lart
     *
     * @param string $lart
     *
     * @return CityFile
     */
    public function setLart($lart)
    {
        $this->lart = $lart;

        return $this;
    }


    /**
     * Get region
     *
     * @return string
     */
    public function getRegion(){
        return $this->region;
    }


    /**
     * Set region
     *
     * @param string $region
     *
     * @return CityFile
     */
    public function setRegion($region)
    {
        $this->region = $region;

        return $this;
    }





    /**
     * Add cityFile
     *
     * @param CityFile cityFile
     *
     * @return CityFile
     */
    public function addList($cityFile)
    {
        $this->list[] = $cityFile;

        return $this;
    }

    /**
     * Remove cityFile
     *
     * @param CityFile cityFile
     */
    public function removeList($cityFile)
    {
        $this->list->removeElement($cityFile);
    }

    /**
     * Get $list
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getList()
    {
        return $this->list;
    }



    //Charge toutes les villes et pays contenu dans le fichier passe en parametre dans une liste
   public function fill($file_directory,$file)
    {
        $file_name  =$file_directory.$file;
        $fileopen = fopen($file_name, "r+");

        while ($row = fgets($fileopen)) {
            $cityFile =new CityFile();
            $tab = explode(",",$row);
            $cityFile->setCountry($tab[1]);
            $cityFile->setRegion($tab[2]);
            $cityFile->setCity($tab[3]);
            $cityFile->setAccentCity($tab[3]);
            $cityFile->setLart($tab[4]);
            $cityFile->setLong($tab[5]);
            $this->addList($cityFile);
        }
        fclose($fileopen);
    }


    // retourne la liste des payer correspondants a une recherche specifique
    public function getCityByCountry($country)
    {
        if($this->list==null)
        {
            if($country!=null && $country!="")
            {
                $list = [];
                /** @var CityFile $cityfile */
                foreach($this->list as $cityfile)
                {
                    if(strtoupper($cityfile->getCountry())==$country)
                    {
                        $list[] = $cityfile;
                    }
                }

                return  $list;
            }
        }
        return $this->list;

    }

}