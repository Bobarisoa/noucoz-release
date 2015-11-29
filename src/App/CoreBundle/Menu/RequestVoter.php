<?php

namespace App\CoreBundle\Menu;

use Knp\Menu\ItemInterface;
use Knp\Menu\Matcher\Voter\VoterInterface;

use Symfony\Component\DependencyInjection\ContainerInterface;

class RequestVoter implements VoterInterface
{

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function matchItem(ItemInterface $item)
    {
    	$route = substr($this->container->get('request')->getRequestUri(), 0, strlen($item->getUri()));
    	$method = substr(strrchr($this->container->get('request')->getRequestUri(), '/'), 1);

    	if (
    			(
    					$route == $item->getUri() 
    					&& ($method == 'modifier' || $method == 'importer' || $method == 'ajouter')
				) 
    			/*|| 
    			(
    					$route == $this->container->get('router')->generate('periode') 
    					&& $route == $item->getUri()
				) 
    			|| 
    			(
    					$route == $this->container->get('router')->generate('communication') 
    					&& $route == $item->getUri()
				)*/
			) {
    		return true;
    	}
    	
        return null;
    }

}