<?php

namespace Web\FrontBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Amis
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Amis
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
     * @ORM\Column(name="nom", type="string", length=255)
     */
    private $nom;

    /**
     * @var integer
     *
     * @ORM\Column(name="etat", type="integer", nullable=true)
     */
    private $etat;

    /**
     * @var integer
     *
     * @ORM\Column(name="user", type="integer")
     */
    private $User;

    /**
     * @var integer
     *
     * @ORM\Column(name="poursuivre", type="integer", nullable=true)
     */
    private $Poursuivre;

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
     * Set nom
     *
     * @param string $nom
     * @return Amis
     */
    public function setNom($nom)
    {
        $this->nom = $nom;
    
        return $this;
    }

    /**
     * Get nom
     *
     * @return string 
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set etat
     *
     * @param integer $etat
     * @return Amis
     */
    public function setEtat($etat)
    {
        $this->etat = $etat;
    
        return $this;
    }

    /**
     * Get etat
     *
     * @return integer 
     */
    public function getEtat()
    {
        return $this->etat;
    }

    /**
     * Set User
     *
     * @param integer $User
     * @return Amis
     */
    public function setUser($User)
    {
        $this->User = $User;
    
        return $this;
    }

    /**
     * Get User
     *
     * @return integer 
     */
    public function gedUser()
    {
        return $this->idUser;
    }

    /**
     * Get User
     *
     * @return integer 
     */
    public function getUser()
    {
        return $this->User;
    }

    /**
     * Get Poursuivre
     *
     * @return integer 
     */
    public function getPoursuivre()
    {
        return $this->Poursuivre;
    }

     /**
     * Set Poursuivre
     *
     * @param integer $Poursuivre
     * @return Amis
     */
    public function setPoursuivre($Poursuivre)
    {
        $this->Poursuivre = $Poursuivre;
    
        return $this;
    }
}