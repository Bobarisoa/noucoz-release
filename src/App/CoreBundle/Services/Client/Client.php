<?php

namespace App\CoreBundle\Services\Client;

use Doctrine\ORM\EntityManager;
use App\CoreBundle\Entity\ClientHistorique;
use App\CoreBundle\Entity\Client as RegistredClient;

/**
 * CSV Parser tool
 *
 */
class Client
{
	/**
     *
     * @var EntityManager 
     */
    protected $em;
	
	public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }
	
	public function saveActivity(RegistredClient $client, $description, $link = null)
	{
		$historique = new ClientHistorique();
		$historique->setClient($client);
		$historique->setDescription($description);
		$historique->setLink($link);
		
		$this->em->persist($historique);
		$this->em->flush();
	}
}