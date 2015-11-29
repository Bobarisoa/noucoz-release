<?php

namespace App\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Utilisateur
 *
 * @ORM\Table(name="utilisateur")
 * @ORM\Entity(repositoryClass="App\CoreBundle\Entity\UtilisateurRepository")
 * @UniqueEntity(fields="email", message="Cette adresse e-mail est déjà utilisée !")
 * @UniqueEntity(fields="username", message="Cet identifiant déjà utilisé !")
 */
class Utilisateur implements UserInterface
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
     * @ORM\Column(name="username", type="string", length=255)
     * @Constraints\NotNull(message="Le champ ne doit pas être vide.")
     */
    private $username;
    
    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=255, nullable=true)
     * 
     */
    private $code;
    
    /**
     * @var string
     *
     * @ORM\Column(name="lastname", type="string", length=255)
     * @Constraints\NotNull(message="Le champ ne doit pas être vide.")
     */
    private $lastname;

    /**
     * @var string
     *
     * @ORM\Column(name="firstname", type="string", length=255)
     * @Constraints\NotNull()
     */
    private $firstname;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, unique=true)
     * @Constraints\NotNull(message="Le champ ne doit pas être vide.")
     * @Constraints\Email(message="L'adresse email n'est pas valide.", checkMX=true)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=255)
     */
    private $password;
    
     /**
     * @var string
     *
     * @ORM\Column(name="position", type="string", length=255,nullable=true)
     */
    private $position;
    
     /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=255,nullable=true)
     */
    private $phone;
    
    /**
     * @var \DateTime
     * 
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;
    
     /**
     * @var boolean
     *
     * @ORM\Column(name="is_active", type="boolean" , nullable=true)
     */
    private $isActive;

    /**
     * @var string
     *
     * @ORM\Column(name="salt", type="string", length=255 , nullable=true)
     */
    private $salt;
    
    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="App\CoreBundle\Entity\Role")
     * @ORM\JoinColumn(nullable=false)
     * @Constraints\NotNull(message = "Le champ ne doit pas être vide.")
     */
    private $role;
    
     /**
     * @var string
     *
     * @ORM\Column(name="premium", type="string", length=255 , nullable=true)
     */
    private $premium;
    
    /**
     * @var string
     *
     * @ORM\Column(name="confidentialite", type="string", length=255 , nullable=true)
     */
    private $confidentialite;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="etat_amis", type="integer", nullable=true)
     */
    private $etat;

    // friendship relation
    /**
     * @ORM\OneToMany(targetEntity="Web\FrontBundle\Entity\Invitation", mappedBy="utilisateur")
     */
    protected $followers;

    /**
     * @ORM\OneToMany(targetEntity="Web\FrontBundle\Entity\Invitation", mappedBy="ami")
     */
    protected $myFriends;


    public function __construct()
    {
        $this->created = new \DateTime();
        $this->followers = new ArrayCollection();
        $this->myFriends = new ArrayCollection();
    }
    /**
     * Set username
     *
     * @param string $username
     * @return Utilisateur
     */
    public function setUsername($username)
    {
        $this->username = $username;
    
        return $this;
    }

    /**
     * Get username
     *
     * @return string 
     */
    public function getUsername()
    {
        return $this->username;
    }

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
     * Set id
     *
     * @return integer
     */
    public function setId($id)
    {
    	$this->id = $id;
    
    	return $this;
    }

    /**
     * Set lastname
     *
     * @param string $lastname
     * @return Utilisateur
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
    
        return $this;
    }

    /**
     * Get lastname
     *
     * @return string 
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set firstname
     *
     * @param string $firstname
     * @return Utilisateur
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
    
        return $this;
    }

    /**
     * Get firstname
     *
     * @return string 
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Utilisateur
     */
    public function setEmail($email)
    {
        $this->email = $email;
    
        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return Utilisateur
     */
    public function setPassword($password)
    {
        $this->password = $password;
    
        return $this;
    }

    /**
     * Get password
     *
     * @return string 
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set salt
     *
     * @param string $salt
     * @return Utilisateur
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;
    
        return $this;
    }

    /**
     * Get salt
     *
     * @return string 
     */
    public function getSalt()
    {
        return $this->salt;
    }
    
    /**
     * Set role
     *
     * @param \App\CoreBundle\Entity\Role $role
     * @return Utilisateur
     */
    public function setRole(\App\CoreBundle\Entity\Role $role)
    {
        $this->role = $role;
    
        return $this;
    }

    /**
     * Get role
     *
     * @return \App\CoreBundle\Entity\Role 
     */
    public function getRole()
    {
        return $this->role;
    }
    
    /**
     * Get roles
     *
     * @return array 
     */
    public function getRoles()
    {
    	
        return array($this->getRole()->getRole());
    }

    /**
     * Is role
     *
     * @param string $role
     * @return boolean 
     */
    public function isRole($role)
    {
        //$current_role = $this->getRole()->getRights();
        
        if ($this->getRole()->getRole() === $role) {
            return true;
        }

        return false;
    }

    
    /**
     * Erase credentials
     */
    public function eraseCredentials()
    {
    }
    
    /**
     * Magic-Method __sleep
     *
     * @return array
     */
    public function __sleep(){
        return array('id');
    }
    
    /**
     * Magic-Method __toString
     *
     * @return string
     */
    public function __toString(){
        return $this->firstname . ' ' . $this->lastname;
    }

    /**
     * Set position
     *
     * @param string $position
     * @return Utilisateur
     */
    public function setPosition($position)
    {
        $this->position = $position;
    
        return $this;
    }

    /**
     * Get position
     *
     * @return string 
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set phone
     *
     * @param string $phone
     * @return Utilisateur
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    
        return $this;
    }

    /**
     * Get phone
     *
     * @return string 
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Utilisateur
     */
    public function setCreated($created)
    {
        $this->created = $created;
    
        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime 
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     * @return Utilisateur
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
    
        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean 
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    
    public function getUserCode()
    {
    	if($this->code)
    		return strtoupper($this->code);
    	return $this->id;
    }

    /**
     * Set code
     *
     * @param string $code
     * @return Utilisateur
     */
    public function setCode($code)
    {
        $this->code = $code;
    
        return $this;
    }

    /**
     * Get code
     *
     * @return string 
     */
    public function getCode()
    {
        return $this->code;
    }


     /**
     * Set premium
     *
     * @param string $premium
     * @return Utilisateur
     */
    public function setPremium($premium)
    {
        $this->premium = $premium;
    
        return $this;
    }

    /**
     * Get premium
     *
     * @return string 
     */
    public function getPremium()
    {
        return $this->premium;
    }

    /**
     * Set confidentialite
     *
     * @param string $confidentialite
     * @return Utilisateur
     */
    public function setConfidentialite($confidentialite)
    {
        $this->confidentialite = $confidentialite;
    
        return $this;
    }

    /**
     * Get confidentialite
     *
     * @return string 
     */
    public function getConfidentialite()
    {
        return $this->confidentialite;
    }


    /**
     * Set etat
     *
     * @param integer $etat
     * @return Utilisateur
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
     * Add followers
     *
     * @param \Web\FrontBundle\Entity\Invitation $followers
     * @return Utilisateur
     */
    public function addFollower(\Web\FrontBundle\Entity\Invitation $followers)
    {
        $this->followers[] = $followers;
    
        return $this;
    }

    /**
     * Remove followers
     *
     * @param \Web\FrontBundle\Entity\Invitation $followers
     */
    public function removeFollower(\Web\FrontBundle\Entity\Invitation $followers)
    {
        $this->followers->removeElement($followers);
    }

    /**
     * Get followers
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getFollowers()
    {
        return $this->followers;
    }

    /**
     * Add myFriends
     *
     * @param \Web\FrontBundle\Entity\Invitation $myFriends
     * @return Utilisateur
     */
    public function addMyFriend(\Web\FrontBundle\Entity\Invitation $myFriends)
    {
        $this->myFriends[] = $myFriends;
    
        return $this;
    }

    /**
     * Remove myFriends
     *
     * @param \Web\FrontBundle\Entity\Invitation $myFriends
     */
    public function removeMyFriend(\Web\FrontBundle\Entity\Invitation $myFriends)
    {
        $this->myFriends->removeElement($myFriends);
    }

    /**
     * Get myFriends
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getMyFriends()
    {
        return $this->myFriends;
    }
}