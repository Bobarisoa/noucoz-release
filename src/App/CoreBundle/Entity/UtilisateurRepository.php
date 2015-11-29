<?php

namespace App\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * UtilisateurRepository
 *
 * Répertoire de méthodes personnalisées
 * pour l'entité Utilisateur
 */
class UtilisateurRepository extends EntityRepository
{
    /**
     * Find all Utilisateur entities ordered by lastname
     *
     * @return array
     */
    public function findAll()
    {
        return $this->findBy(array(), array('lastname' => 'ASC'));
    }
    
    /**
     * Find all Utilisateur entities as main teacher
     *
     * @return array
     */
    public function findAllByRole($role)
    {
    	return $this->createQueryBuilder('u')
    	->where('u.role = '.$role)
    	->getQuery()
    	->getResult();
    	;
    }
    
    /**
     * Find all Utilisateur entities as main teacher
     *
     * @return array
     */
    public function findAllMainTeacher()
    {
        return $this->createQueryBuilder('u')
                    ->where('u.role = 4')
                    ->getQuery()
                    ->getResult();
        ;
    }
    
    /**
     * Find all Utilisateur entities as teacher
     *
     * @return array
     */
    public function findAllTeacher()
    {
    	return $this->createQueryBuilder('u')
    	->where('u.role = 1')
    	->getQuery()
    	->getResult();
    	;
    }
    
    /**
     * Find all Utilisateur entities as secretary
     *
     * @return array
     */
    public function findAllSecretaire()
    {
        return $this->createQueryBuilder('u')
                    ->where('u.role = 3')
                    ->getQuery()
                    ->getResult();
        ;
    }
    
    /**
     * Find all Utilisateur entities as main teacher by ids
     *
     * @param string $username
     * @return array
     */
    public function findUserByIDs($ids)
    {
    	return $this->createQueryBuilder('u')
    	->where('u.id IN (:ids)')
    	->setParameter('ids', $ids)
    	->getQuery()
    	->getOneOrNullResult()
    	;
    }
    
    /**
     * Find all Utilisateur entities as teacher by username
     *
     * @param string $username
     * @return array
     */
    public function findTeacherByUsername($username)
    {
        return $this->createQueryBuilder('u')
                    ->where('u.username = :username')
                    ->andWhere('u.role = 1 OR u.role = 2 OR u.role = 4')
                    ->setParameter('username', $username)
                    ->getQuery()
                    ->getOneOrNullResult()
        ;
    }

    /**
     * Find all Utilisateur entities as main teacher by username
     *
     * @param string $username
     * @return array
     */
    public function findMainTeacherByUsername($username)
    {
        return $this->createQueryBuilder('u')
                    ->where('u.username = :username')
                    ->andWhere('u.role = 4')
                    ->setParameter('username', $username)
                    ->getQuery()
                    ->getOneOrNullResult()
        ;
    }
    
    /**
     * Find all Utilisateur entities by eleves and role
     *
     * @param array $eleves
     * @return array
     */
    public function findAllByEleveAndRole($eleves,$role) {
    	$query = $this->createQueryBuilder('u');
    	switch ($role){
    		case 1:
    			$codes = array();
    			foreach($eleves as $eleve){
    				foreach($eleve->getCours() as $cours)
    					$codes[] = $cours->getParsedName('teacher_code');
    			}
    			$query
    				->where('u.username IN (:codes)')
			    	->setParameter('codes', $codes);
    		break;
    		case 4:
    			$query
    				->join('u.eleves_as_main_teacher', 'e', 'WITH', 'e.id IN (:eleves)')
    				->setParameter('eleves', $eleves);
    		break;
    	}
    	
    	
    	return $query
			    	->groupBy('u')
			    	->orderBy('u.lastname')
			    	->getQuery()
			    	->getResult();
    }
    
    /**
     * Find all Utilisateur entities as main teacher by eleves
     *
     * @param array $eleves
     * @return array
     */
    public function findAllMainTeachersByEleve($eleves) {
        return $this->createQueryBuilder('u')
                    ->join('u.eleves_as_main_teacher', 'e', 'WITH', 'e.id IN (:eleves)')
                    ->setParameter('eleves', $eleves)
                    ->groupBy('u')
                    ->orderBy('u.lastname')
                    ->getQuery()
                    ->getResult();
    }
    
    /**
    * Find all Utilisateur ID as main teacher by eleves
    *
    * @param array $eleves
    * @return array
    */
    public function findAllMainTeachersIDByEleve($eleves) {
    	$response = array();
    	foreach($this->findAllMainTeachersByEleve($eleves) as $teacher)
    		$response [] = $teacher->getId();
    	return $response;
    }
    
    /**
     * Find all Utilisateur entities as teacher by eleves
     *
     * @param array $eleves
     * @return array
     */
    public function findAllTeachersByEleve($eleves) {
    	return $this->createQueryBuilder('u')
    	->join('u.cours', 'c')
    	->join('c.eleves', 'e', 'WITH', 'e.id IN (:eleves)')
    	->setParameter('eleves', $eleves)
    	->groupBy('u')
    	->orderBy('u.lastname')
    	->getQuery()
    	->getResult()
    	;
    }
    
    /**
     * Find all Utilisateur ID as main teacher by eleves
     *
     * @param array $eleves
     * @return array
     */
    public function findAllTeachersIDByEleve($eleves) {
    	$response = array();
    	foreach($this->findAllTeachersByEleve($eleves) as $teacher)
    		$response [] = $teacher->getId();
    	return $response;
    }
    
     
    public function findByOptionsAgent($options, $idRoles)
    {
            //Agent(Utilisateur)=>PNR=>Ecritures=>TypeEcriture
            $query = $this->createQueryBuilder('u')
            ->leftJoin('u.pnr', 'p')
            ->leftJoin('p.ecritures', 'e')
            ->leftJoin('e.type', 't');

            if (isset($options['Agent'])) {
                    $query->andWhere('u.id = :agentid')
                    ->setParameter('agentid', $options['Agent'])
                    ;
            }
            if (isset($options['DateDu'])) {
                $query->andWhere('p.owAt >= :owat')
                    ->setParameter('owat', new \DateTime($options['DateDu']))
                    ;
            }
            if (isset($options['DateAu'])) {
                $query->andWhere('p.owAt <= :owat')
                    ->setParameter('owat', new \DateTime($options['DateAu']))
                    ;
            }

            if (isset($options['TypeEcriture'])) {
                $query->andWhere('t.id = :type')
                    ->setParameter('type', $options['TypeEcriture'])
                    ;
            }
            
            if (isset($idRoles)) {
                $query->andWhere('u.role IN (:role)')
                    ->setParameter('role', $idRoles)
                    ;
            }

            $query->orderBy('u.lastname');

            return $query->getQuery()
                         ->getResult();

    }
        
}
