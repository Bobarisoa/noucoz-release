<?php

namespace Web\FrontBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Friendship
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Invitation
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\CoreBundle\Entity\Utilisateur", inversedBy="followers")
     * @ORM\JoinColumn(name="utilisateur", referencedColumnName="id")
     */
    protected $utilisateur;

    /**
     * @ORM\ManyToOne(targetEntity="App\CoreBundle\Entity\Utilisateur", inversedBy="myFriends")
     * @ORM\JoinColumn(name="ami", referencedColumnName="id")
     */
    protected $ami;

    /**
     * @ORM\Column(type="integer")
     */
    protected $etat;

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
     * Set etat
     *
     * @param integer $etat
     * @return Invitation
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
     * Set utilisateur
     *
     * @param \App\CoreBundle\Entity\Utilisateur $utilisateur
     * @return Invitation
     */
    public function setUtilisateur(\App\CoreBundle\Entity\Utilisateur $utilisateur = null)
    {
        $this->utilisateur = $utilisateur;
    
        return $this;
    }

    /**
     * Get utilisateur
     *
     * @return \App\CoreBundle\Entity\Utilisateur 
     */
    public function getUtilisateur()
    {
        return $this->utilisateur;
    }

    /**
     * Set ami
     *
     * @param \App\CoreBundle\Entity\Utilisateur $ami
     * @return Invitation
     */
    public function setAmi(\App\CoreBundle\Entity\Utilisateur $ami = null)
    {
        $this->ami = $ami;
    
        return $this;
    }

    /**
     * Get ami
     *
     * @return \App\CoreBundle\Entity\Utilisateur 
     */
    public function getAmi()
    {
        return $this->ami;
    }
}