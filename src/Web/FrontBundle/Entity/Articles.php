<?php
/**
 * Created by PhpStorm.
 * User: Yoyo
 * Date: 18/09/2015
 * Time: 13:41
 */

namespace Web\FrontBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="article_noucoze")
 */
class Articles
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Length(min=2)
     */
    protected $titre;
    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Length(min=2)
     */
    protected $source;
    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     * @Assert\Length(min=5)
     */
    protected $contenu;
    /**
     * @ORM\Column(type="string", length=50)
     * @Assert\NotBlank()
     */
    protected $auteur;
    /**
     * @ORM\Column(type="string", length=200)
     * @Assert\NotBlank()
     */
    protected $photo;
    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     */
    protected $notification;
    /**
     * @ORM\ManyToOne(targetEntity="App\CoreBundle\Entity\TopArticle")
     */
    protected $classe;

    /**
     * @ORM\ManyToOne(targetEntity="Web\FrontBundle\Entity\Country")
     */
    protected $country;

    /**
     * @ORM\ManyToOne(targetEntity="Web\FrontBundle\Entity\Categorie", cascade={"persist"})
     */
    protected $category;
    
    /**
     * @ORM\Column(type="string", length=125, name="datePublication")
     * @Assert\NotBlank()
     *
     */
    protected $create;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank()
     */
    protected $publication;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\NotBlank()
    */

    protected $etatPublication;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\NotBlank()
    */

    protected $etatBrouillons;    

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\NotBlank()
     */
    
    protected $type;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank()
     * @Assert\Length(min=2)
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
     * Set titre
     *
     * @param string $titre
     * @return Articles
     */
    public function setTitre($titre)
    {
        $this->titre = $titre;
    
        return $this;
    }

    /**
     * Get titre
     *
     * @return string 
     */
    public function getTitre()
    {
        return $this->titre;
    }

    /**
     * Set source
     *
     * @param string $source
     * @return Articles
     */
    public function setSource($source)
    {
        $this->source = $source;
    
        return $this;
    }

    /**
     * Get source
     *
     * @return string 
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Set contenu
     *
     * @param string $contenu
     * @return Articles
     */
    public function setContenu($contenu)
    {
        $this->contenu = $contenu;
    
        return $this;
    }

    /**
     * Get contenu
     *
     * @return string 
     */
    public function getContenu()
    {
        return $this->contenu;
    }

    /**
     * Set auteur
     *
     * @param string $auteur
     * @return Articles
     */
    public function setAuteur($auteur)
    {
        $this->auteur = $auteur;
    
        return $this;
    }

    /**
     * Get auteur
     *
     * @return string 
     */
    public function getAuteur()
    {
        return $this->auteur;
    }

    /**
     * Set photo
     *
     * @param string $photo
     * @return Articles
     */
    public function setPhoto($photo)
    {
        $this->photo = $photo;
    
        return $this;
    }

    /**
     * Get photo
     *
     * @return string 
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    /**
     * Set notification
     *
     * @param integer $notification
     * @return Articles
     */
    public function setNotification($notification)
    {
        $this->notification = $notification;
    
        return $this;
    }

    /**
     * Get notification
     *
     * @return integer 
     */
    public function getNotification()
    {
        return $this->notification;
    }


    /**
     * Set country
     *
     * @param \Web\FrontBundle\Entity\Country $country
     * @return Articles
     */
    public function setCountry(\Web\FrontBundle\Entity\Country $country = null)
    {
        $this->country = $country;
    
        return $this;
    }

    /**
     * Get country
     *
     * @return \Web\FrontBundle\Entity\Country
     */
    public function getCountry()
    {
        return $this->country;
    }


    /**
     * Set category
     *
     * @param \Web\FrontBundle\Entity\Categorie $category
     * @return Articles
     */
    public function setCategory(\Web\FrontBundle\Entity\Categorie $category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return \Web\FrontBundle\Entity\Categorie
     */
    public function getCategory()
    {
        return $this->category;
    }


    /**
     * Set classe
     *
     * @param \App\CoreBundle\Entity\TopArticle $classe
     * @return Articles
     */
    public function setClasse(\App\CoreBundle\Entity\TopArticle $classe = null)
    {
        $this->classe = $classe;
    
        return $this;
    }

    /**
     * Get classe
     *
     * @return \App\CoreBundle\Entity\TopArticle 
     */
    public function getClasse()
    {
        return $this->classe;
    }

    /*
     * Set create
     *
     * @param string $create
     * @return Articles
     */
    public function setCreate($create)
    {
        $this->create = $create;
    
        return $this;
    }

    /**
     * Get create
     *
     * @return string 
     */
    public function getCreate()
    {
        return $this->create;
    }


    /**
     * Set publiciation
     *
     * @param string $publication
     * @return Articles
     */
    public function setPublication($publication)
    {
        $this->publication = $publication;
    
        return $this;
    }

    /**
     * Get publication
     *
     * @return string 
     */
    public function getPublication()
    {
        return $this->publication;
    }


    /**
     * Set etatPublication
     *
     * @param integer $etatPublication
     * @return Articles
     */
    public function setEtatPublication($etatPublication)
    {
        $this->etatPublication = $etatPublication;
    
        return $this;
    }

    /**
     * Get etatPublication
     *
     * @return integer 
     */
    public function getEtatPublication()
    {
        return $this->etatPublication;
    }

     /**
     * Set etatBrouillons
     *
     * @param integer $etatBrouillon
     * @return Articles
     */
    public function setEtatBrouillon($etatBrouillon)
    {
        $this->etatBrouillon = $etatBrouillon;
    
        return $this;
    }

    /**
     * Get etatBrouillon
     *
     * @return integer 
     */
    public function getEtatBrouillon()
    {
        return $this->etatBrouillon;
    }


    /**
     * Set etatBrouillons
     *
     * @param integer $etatBrouillons
     * @return Articles
     */
    public function setEtatBrouillons($etatBrouillons)
    {
        $this->etatBrouillons = $etatBrouillons;
    
        return $this;
    }

    /**
     * Get etatBrouillons
     *
     * @return integer 
     */
    public function getEtatBrouillons()
    {
        return $this->etatBrouillons;
    }


    /**
     * Set type
     *
     * @param integer $type
     * @return Articles
     */
    public function setType($type)
    {
        $this->type = $type;
    
        return $this;
    }

    /**
     * Get type
     *
     * @return integer 
     */
    public function getType()
    {
        return $this->type;
    }
    
    /**
     * Set etat
     *
     * @param string $etat
     * @return Articles
     */
    public function setEtat($etat)
    {
        $this->etat = $etat;
    
        return $this;
    }

    /**
     * Get etat
     *
     * @return string 
     */
    public function getEtat()
    {
        return $this->etat;
    }
    
    
}