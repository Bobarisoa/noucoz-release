<?php
/**
 * Created by PhpStorm.
 * User: Yoyo
 * Date: 24/09/2015
 * Time: 10:08
 */

namespace Web\FrontBundle\Entity;


use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="commentaire_noucoze")
 */
class Commentaire
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
    protected $auteur;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     * @Assert\Length(min=2)
     */
    protected $contenu;

    /**
     * @ORM\Column(type="datetime")
     *
     */
    protected $datePub;

    /**
     * @ORM\ManyToOne(targetEntity="Web\FrontBundle\Entity\Articles", cascade="remove")
     */
    protected $articles;

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
     * Set auteur
     *
     * @param string $auteur
     * @return Commentaire
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
     * Set contenu
     *
     * @param string $contenu
     * @return Commentaire
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
     * Set datePub
     *
     * @param $datePub
     * @return Commentaire
     */
    public function setDatePub($datePub)
    {
        $this->datePub = $datePub;
    
        return $this;
    }

    /**
     * Get datePub
     *
     *
     */
    public function getDatePub()
    {
        return $this->datePub;
    }

    /**
     * Set articles
     *
     * @param \Web\FrontBundle\Entity\Articles $articles
     * @return Commentaire
     */
    public function setArticles(\Web\FrontBundle\Entity\Articles $articles = null)
    {
        $this->articles = $articles;
    
        return $this;
    }

    /**
     * Get articles
     *
     * @return \Web\FrontBundle\Entity\Articles 
     */
    public function getArticles()
    {
        return $this->articles;
    }
}