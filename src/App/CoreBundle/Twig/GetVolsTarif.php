<?php

namespace App\CoreBundle\Twig;

use Symfony\Bridge\Doctrine\RegistryInterface;
use App\CoreBundle\Entity\Vols;
use App\CoreBundle\Entity\Tarif;
use App\CoreBundle\DataFixtures\ORM\Tarifaire;
use App\CoreBundle\Entity\Tarifaires;

/**
 * Adds some twig syntax helper extension
 *
 */
class GetVolsTarif extends \Twig_Extension
{
	protected $doctrine;
	
	public function __construct(RegistryInterface $doctrine)
	{
		$this->doctrine = $doctrine;
	}
	
    public function getFilters()
    {
        return array(
            
        );
    }

    
    
    public function getName()
    {
        return 'volstarif_extension';
    }
}