<?php
namespace App\CoreBundle\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\Matcher\Matcher;
use Knp\Menu\Matcher\Voter\UriVoter;
use Knp\Menu\MenuFactory;
use Knp\Menu\Renderer\ListRenderer;

use Symfony\Component\DependencyInjection\ContainerAware;

/**
 * Define navigation menu structure
 * according to current user rights
 *
 */
class Navigation extends ContainerAware
{
    public function mainMenu(FactoryInterface $factory, array $options)
    {
    	//$requestStack = $this->container->get('request_stack');
    	//$request = $requestStack->getCurrentRequest();
    	
    	$user = $this->container->get('security.context')->getToken()->getUser();
    	$user_role = null;
    	if(method_exists($user, 'getRole'))
    		$user_role = $user->getRole();
    	
        $menu = $factory->createItem('Navigation')
        				->setChildrenAttributes(array('class' => 'navigation widget'));
        
        /*
         * Tableau de bord
         */
        $menu->addChild('Tableau de bord', array('route' => 'core_dashboard'))
        	 ->setAttribute('icon', 'icon-dashboard');
        	
        $menu->addChild('Suivie des articles')
        ->setUri('#')
        ->setAttribute('icon', 'icon-tasks')
        ->setLinkAttribute('class', 'expand');

        $menu['Suivie des articles']->addChild('Liste des articles', array('route' => 'articles'));
        $menu['Suivie des articles']->addChild('Liste des commentaires', array('route' => 'commentaire'));
	 
        $menu->addChild('Administration')
        ->setUri('#')
        ->setAttribute('icon', 'icon-group')
        ->setLinkAttribute('class', 'expand');

        $menu['Administration']->addChild('Gérer les membres', array('route' => 'utilisateur'));
        $menu['Administration']->addChild('Membres premium', array('route' => 'utilisateur_premium'));
        $menu['Administration']->addChild('Gestion des flux RSS', array('route' => 'flux_rss'));
         $menu['Administration']->addChild('Gestion des articles', array('route' => 'article_flux_rss'));
        $menu['Administration']->addChild('Gérer les pays', array('route' => 'pays'));
        $menu['Administration']->addChild('Gestion des blocs affichés', array('route' => 'bloc'));
		$menu['Administration']->addChild('Gestion des sondages', array('route' => 'sondage'));			
            
            

        return $menu;
    }
}