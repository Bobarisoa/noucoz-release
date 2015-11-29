<?php

namespace App\CoreBundle\Services\Utilisateur;

use Doctrine\ORM\EntityManager;
use App\CoreBundle\Entity\UtilisateurHistorique;
use App\CoreBundle\Entity\Utilisateur as RegistredUtilisateur;

/**
 *
 *
 */
class Utilisateur
{

        protected $em;

        public function __construct(EntityManager $entityManager)
        {
            $this->em = $entityManager;
        }
	
	public function saveActivity(RegistredUtilisateur $utilisateur, $type, $description, $detail, $link = null)
	{
		$historique = new UtilisateurHistorique();
		$historique->setUtilisateur($utilisateur);
                $historique->setType($type);
		$historique->setDescription($description);
                $historique->setDetails($detail);
		$historique->setLink($link);
		
		$this->em->persist($historique);
		$this->em->flush();
	}
}