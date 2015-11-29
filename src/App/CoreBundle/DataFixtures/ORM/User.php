<?php 
// src/App/CoreBundle/DataFixtures/ORM/Type.php 
namespace  App\CoreBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use App\CoreBundle\Entity\Jour as EJour;
use App\CoreBundle\Entity\Utilisateur;

class User implements FixtureInterface, OrderedFixtureInterface {  
	// Dans l'argument de la méthode load, l'objet $manager est l'EntityManager  
	public function load(ObjectManager $manager)  {    
	// Liste des noms de catégorie à ajouter    
		$datas = array(
				array(
					'firstname'=>'Administrateur',
					'lastname'=>'système',
					'email'=>'hmanprod@gmail.com',
					'username'=>'master',
					'code'=>'MAS',
					'position'=>'Administrateur système',
					'password'=>'gR39XM+O6/ET4XX/GOMFEivFFOWqZxFx6jJ35wnrQV4/kLXeg7l1cG4n/IoOvlqYnDeHOysyZLkcVkw+5N1TZA==',
					'salt' => '81b44c0ab9f5775ff6012d0018fc0001',
					'role' => 'Admin',
					'is_active' => true
				),
				array(
					'firstname'=>'Superviseur',
					'lastname'=>'Test user',
					'email'=>'testuser@gmail.com',
					'username'=>'supuser',
					'code'=>'STU',
					'position'=>'Utilisateur Test',
					'password'=>'K+bKy5uo7wOt7ULqjmWhUMqVytU8Binm08iefsxoF3pONFfv9/KZrAekq8RFyscG1ElN3n/E4S9rq7SPUFg4ag==',
					'salt' => '6cc12f6be90a8ddbc88fc34c5d5de620',
					'agence' => 'Siège Immeuble Regus',
					'role' => 'Superviseur',
					'is_active' => true
				),
				array(
					'firstname'=>'Sambatra',
					'lastname'=>'RAHADRANASANDATRA',
					'email'=>'sasah@gmail.com',
					'username'=>'sambatra',
					'code'=>'SAM',
					'position'=>'Chef de ventes',
					'password'=>'K+bKy5uo7wOt7ULqjmWhUMqVytU8Binm08iefsxoF3pONFfv9/KZrAekq8RFyscG1ElN3n/E4S9rq7SPUFg4ag==',
					'salt' => '6cc12f6be90a8ddbc88fc34c5d5de620',
					'role' => 'Superviseur',
					'is_active' => true
				),
				array(
					'firstname'=>'Narindra',
					'lastname'=>'RANDRIANARIVELO',
					'email'=>'narindra.randrianari@gmail.com',
					'username'=>'narindra',
					'code'=>'NAR',
					'position'=>'Agent de ventes et d\'escale',
					'password'=>'K+bKy5uo7wOt7ULqjmWhUMqVytU8Binm08iefsxoF3pONFfv9/KZrAekq8RFyscG1ElN3n/E4S9rq7SPUFg4ag==',
					'salt' => '6cc12f6be90a8ddbc88fc34c5d5de620',
					'role' => 'Superviseur',
					'is_active' => true
				),
				array(
					'firstname'=>'Miarisoa',
					'lastname'=>'',
					'email'=>'miarisoa@gmail.com',
					'username'=>'miarisoa',
					'code'=>'MIA',
					'position'=>'Agent de ventes et d\'escale',
					'password'=>'K+bKy5uo7wOt7ULqjmWhUMqVytU8Binm08iefsxoF3pONFfv9/KZrAekq8RFyscG1ElN3n/E4S9rq7SPUFg4ag==',
					'salt' => '6cc12f6be90a8ddbc88fc34c5d5de620',
					'role' => 'Superviseur',
					'is_active' => true
				),
				array(
					'firstname'=>'Malanto',
					'lastname'=>'Nassar',
					'email'=>'myriahhelina@gmail.com',
					'username'=>'malanto',
					'code'=>'PIS',
					'position'=>'Agent de ventes',
					'password'=>'K+bKy5uo7wOt7ULqjmWhUMqVytU8Binm08iefsxoF3pONFfv9/KZrAekq8RFyscG1ElN3n/E4S9rq7SPUFg4ag==',
					'salt' => '6cc12f6be90a8ddbc88fc34c5d5de620',
					'role' => 'Superviseur',
					'is_active' => true
				),
				array(
					'firstname'=>'bot1 resa',
					'lastname'=>'madagasikara-airways.com',
					'email'=>'bot1@madagasikara-airways.com',
					'username'=>'bot1',
					'code'=>'BOT1',
					'position'=>'Bot resa',
					'password'=>'K+bKy5uo7wOt7ULqjmWhUMqVytU8Binm08iefsxoF3pONFfv9/KZrAekq8RFyscG1ElN3n/E4S9rq7SPUFg4ag==',
					'salt' => '6cc12f6be90a8ddbc88fc34c5d5de620',
					'role' => 'Freelancer',
					'is_active' => true
				),
				array(
					'firstname'=>'Agent de vente',
					'lastname'=>'externe',
					'email'=>'agent@gmail.com',
					'username'=>'agentexterne',
					'code'=>'AE1',
					'position'=>'Agent de vente externe',
					'password'=>'K+bKy5uo7wOt7ULqjmWhUMqVytU8Binm08iefsxoF3pONFfv9/KZrAekq8RFyscG1ElN3n/E4S9rq7SPUFg4ag==',
					'salt' => '6cc12f6be90a8ddbc88fc34c5d5de620',
					'role' => 'Freelancer',
					'is_active' => true
				),
				array(
					'firstname'=>'Agent',
					'lastname'=>'Escale',
					'email'=>'agentescale@gmail.com',
					'username'=>'agentescale',
					'code'=>'AES1',
					'position'=>'Agent escale',
					'password'=>'K+bKy5uo7wOt7ULqjmWhUMqVytU8Binm08iefsxoF3pONFfv9/KZrAekq8RFyscG1ElN3n/E4S9rq7SPUFg4ag==',
					'salt' => '6cc12f6be90a8ddbc88fc34c5d5de620',
					'role' => 'Freelancer',
					'is_active' => true
				)
		);

		foreach($datas as $item)    {     
			// On crée la catégorie      
			$entity = new Utilisateur();
			$entity->setFirstname($item['firstname']);
			$entity->setLastname($item['lastname']);
			$entity->setUsername($item['username']);
			$entity->setEmail($item['email']);
			$entity->setPosition($item['position']);
			$entity->setPassword($item['password']);
			$entity->setSalt($item['salt']);
			$entity->setCode($item['code']);
			$entity->setRole($manager->getRepository('AppCoreBundle:Role')->findOneBy(array('name'=>$item['role'])));
			$entity->setIsActive($item['is_active']);
      
			// On la persiste      
			$manager->persist($entity);
		}    
		// On déclenche l'enregistrement    
		$manager->flush();  
	}
	
	public function getOrder()
	{
		return 3; // the order in which fixtures will be loaded
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
