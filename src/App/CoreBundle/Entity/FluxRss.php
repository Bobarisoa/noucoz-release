<?php

namespace App\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\ExecutionContextInterface;

/**
 * FluxRss
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="App\CoreBundle\Entity\FluxRssRepository")
 * @UniqueEntity(fields="nameSite", message="Cette nom existe déjà !")
 */
class FluxRss
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name_site", type="string", length=255)
     * @Assert\NotNull(
     *     message = "Le champ ne doit pas être vide."
     * )
     */
    private $nameSite;
   
    /**
     * @var string
     *
     * @ORM\Column(name="link_flux", type="string", length=255)
     */
    private $linkFlux;

    /**
     * @var string
     *
     * @ORM\Column(name="category_site", type="string", length=255)
     */
    private $categorySite;
    
   /**
     * @var string
     *
     * @ORM\Column(name="pays", type="string", length=255)
     */
    private $pays;
 

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
     * Set nameSite
     *
     * @param string $nameSite
     * @return FluxRss
     */
    public function setNameSite($nameSite)
    {
        $this->nameSite = $nameSite;
    
        return $this;
    }

    /**
     * Get nameSite
     *
     * @return string 
     */
    public function getNameSite()
    {
        return $this->nameSite;
    }

    /**
     * Set linkFlux
     *
     * @param string $linkFlux
     * @return FluxRss
     */
    public function setLinkFlux($linkFlux)
    {
        $this->linkFlux = $linkFlux;
    
        return $this;
    }

    /**
     * Get linkFlux
     *
     * @return string 
     */
    public function getLinkFlux()
    {
        return $this->linkFlux;
    }

    /**
     * Set categorySite
     *
     * @param string $categorySite
     * @return FluxRss
     */
    public function setCategorySite($categorySite)
    {
        $this->categorySite = $categorySite;
    
        return $this;
    }

    /**
     * Get categorySite
     *
     * @return string 
     */
    public function getCategorySite()
    {
        return $this->categorySite;
    }

    /**
     * Set pays
     *
     * @param string $pays
     * @return FluxRss
     */
    public function setPays($pays)
    {
        $this->pays = $pays;
    
        return $this;
    }

    /**
     * Get pays
     *
     * @return string 
     */
    public function getPays()
    {
        return $this->pays;
    }
}