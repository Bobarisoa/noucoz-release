<?php

namespace Web\FrontBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="pays_noucoze")
 */
class Pays
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id_pays;

    /**
     * @ORM\Column(type="string", length=50)
     * @Assert\NotBlank()
     * @Assert\Length(min=2)
     */
    protected $nom_pays;

    /**
     * Get id_pays
     *
     * @return integer 
     */
    public function getIdPays()
    {
        return $this->id_pays;
    }

    /**
     * Set nom_pays
     *
     * @param string $nomPays
     * @return Pays
     */
    public function setNomPays($nomPays)
    {
        $this->nom_pays = $nomPays;
    
        return $this;
    }

    /**
     * Get nom_pays
     *
     * @return string 
     */
    public function getNomPays()
    {
        return $this->nom_pays;
    }
}