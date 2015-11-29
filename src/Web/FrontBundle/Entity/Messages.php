<?php

namespace Web\FrontBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Messages
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Messages
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
     * @ORM\Column(name="nomDestinataire", type="string", length=255)
     */
    private $nomDestinataire;

    /**
     * @var string
     *
     * @ORM\Column(name="nomRenvoi", type="string", length=255)
     */
    private $nomRenvoi;

    /**
     * @var string
     *
     * @ORM\Column(name="Messages", type="text")
     */
    private $messages;

    /**
     * @var integer
     *
     * @ORM\Column(name="Etat", type="integer")
     */
    private $etat;

    /**
     * @var string
     *
     * @ORM\Column(name="dateEnvoi", type="string", length=255)
     */
    private $dateEnvoi;


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
     * Set nomDestinataire
     *
     * @param string $nomDestinataire
     * @return Messages
     */
    public function setNomDestinataire($nomDestinataire)
    {
        $this->nomDestinataire = $nomDestinataire;
    
        return $this;
    }

    /**
     * Get nomDestinataire
     *
     * @return string 
     */
    public function getNomDestinataire()
    {
        return $this->nomDestinataire;
    }

    /**
     * Set nomRenvoi
     *
     * @param string $nomRenvoi
     * @return Messages
     */
    public function setNomRenvoi($nomRenvoi)
    {
        $this->nomRenvoi = $nomRenvoi;
    
        return $this;
    }

    /**
     * Get nomRenvoi
     *
     * @return string 
     */
    public function getNomRenvoi()
    {
        return $this->nomRenvoi;
    }

    /**
     * Set messages
     *
     * @param string $messages
     * @return Messages
     */
    public function setMessages($messages)
    {
        $this->messages = $messages;
    
        return $this;
    }

    /**
     * Get messages
     *
     * @return string 
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Set etat
     *
     * @param integer $etat
     * @return Messages
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
     * Set dateEnvoi
     *
     * @param string $dateEnvoi
     * @return Messages
     */
    public function setDateEnvoi($dateEnvoi)
    {
        $this->dateEnvoi = $dateEnvoi;
    
        return $this;
    }

    /**
     * Get dateEnvoi
     *
     * @return string 
     */
    public function getDateEnvoi()
    {
        return $this->dateEnvoi;
    }
}
