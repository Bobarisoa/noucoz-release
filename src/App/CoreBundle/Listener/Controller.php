<?php 
namespace App\CoreBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use App\CoreBundle\Entity\Pnr as EPnr;

use Doctrine\ORM\EntityManager;
use App\CoreBundle\Entity\Passager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use App\CoreBundle\Entity\EcritureComptable;
use Doctrine\ORM\Event\PreFlushEventArgs;
use App\CoreBundle\Entity\Excedent;
use Doctrine\ORM\Event\OnFlushEventArgs;
use App\CoreBundle\Entity\Facture;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Doctrine\Common\Util\Debug;
use App\CoreBundle\Controller\KillTheBootInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\CoreBundle\Entity\Utilisateur;

class Controller
{
	protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
	
    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();
        
        /*
         * $controller peut être une classe ou une closure. Ce n'est pas
         * courant dans Symfony2 mais ça peut arriver.
         * Si c'est une classe, elle est au format array
         */
        if (!is_array($controller)) {
        	return;
        }
        
        if ($controller[0] instanceof KillTheBootInterface) {
	        $user = $this->container->get('security.context')->getToken()->getUser();
	        
	        if($user instanceof Utilisateur && $user->getRole() == 'ROLE_AGENT_BOT'){
	        	$redirectUrl = $controller[0]->generateUrl('logout');
	        	$event->setController(function() use ($redirectUrl) {
			        return new RedirectResponse($redirectUrl);
			    });
	        }
        }
        
    }

}