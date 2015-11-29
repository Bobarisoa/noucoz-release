<?php 
// src/App/CoreBundle/DataFixtures/ORM/Type.php 
namespace  App\CoreBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use App\CoreBundle\Entity\Role as ERole;



class Role implements FixtureInterface, OrderedFixtureInterface {
	
	// Dans l'argument de la méthode load, l'objet $manager est l'EntityManager  
	public function load(ObjectManager $manager)  {    
	// Liste des noms de role à ajouter    
		$datas = array(
					array(
							'name'=>'Admin',
							'role'=>"ROLE_ADMIN",
							'rights'=>'a:1:{i:0;s:10:"ROLE_ADMIN";}'
					),
					array(
							'name'=>'Superviseur',
							'role'=>"ROLE_SUPERVISOR",
							'rights'=>'a:1:{i:0;s:11:"ROLE_SUPERVISOR";}'
					),
					
					array(
							'name'=>'Freelancer',
							'role'=>"ROLE_WEBMASTER",
							'rights'=>'a:1:{i:0;s:19:"ROLE_WEBMASTER";}'
					)
		);

		foreach($datas as $item)    {
                       
			// On crée le role      
			$entity = new ERole();
			$entity->setName($item['name']);
			$entity->setRole($item['role']);
			//$entity->setRights($item['rights']);

			// On la persiste      
			$manager->persist($entity);
		}
                $manager->flush(); 
	}
	
	public function getOrder()
	{
		return 1; // the order in which fixtures will be loaded
	}
	
	/**
     * Slugify a text
     *
     * @param $text
     *
     * @return string
     */
    public function slugify($text)
    {
        // replace non letter or digits by -
        $text = preg_replace('~[^\\pL\d]+~u', '-', $text);

        // trim
        $text = trim($text, '-');

        // transliterate
        if (function_exists('iconv')) {
            $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        }

        // lowercase
        $text = strtolower($text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        return $text;
    }
}
