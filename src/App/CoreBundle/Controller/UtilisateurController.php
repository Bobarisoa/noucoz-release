<?php

namespace App\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use App\CoreBundle\Entity\Utilisateur;
use App\CoreBundle\Form\UtilisateurType;

/**
 * Utilisateur controller.
 *
 */
class UtilisateurController extends Controller implements KillTheBootInterface
{
	/**
     * Lists all Classe entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('AppCoreBundle:Utilisateur')->findAll();

        return $this->render('AppCoreBundle:Utilisateur:index.html.twig', array(
        	'headline'	=> 'Membres',
        	'title' 	=> 'Liste des membres',
            'entities'  => $entities,
        ));
    }
    
    /**
     * Creates a new Utilisateur entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity  = new Utilisateur();
        $form = $this->createForm(new UtilisateurType(), $entity);
       
        if($request->getMethod() == 'POST') {
	        $form->bind($request);
                
	        if ($form->isValid()) {
                    
	        	$factory = $this->get('security.encoder_factory');
	        	$encoder = $factory->getEncoder($entity);
	        	
            // Dont forget salt, common in security process
	        	$entity->setSalt(md5(time()));
	        	$entity->setPassword($encoder->encodePassword($entity->getPassword(), $entity->getSalt()));
	        	
	            $em = $this->getDoctrine()->getManager();
	            $em->persist($entity);
	            $em->flush();
	            
	            $this->get('session')->getFlashBag()->add(
					'success',
					'Le <strong>' . $entity->getFirstname() . '&nbsp;' . $entity->getLastname() . '</strong> a bien été crée !'
				);
	
	            return $this->redirect($this->generateUrl('utilisateur'));
	        }
	    }

        return $this->render('AppCoreBundle:Utilisateur:new.html.twig', array(
        	'headline' => 'Membres',
        	'title'	   => 'Ajouter un membre',
            'entity'   => $entity,
            'form'	   => $form->createView(),
        ));
    }
    
    /**
     * Edits an Utilisateur entity.
     *
     */
    public function editAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AppCoreBundle:Utilisateur')->find($id);
        
        if (!$entity) {
            $this->get('session')->getFlashBag()->add(
				'danger',
				'Le membre sélectionné n\'existe pas !'
			);
	        
	        return $this->redirect($this->generateUrl('utilisateur'));
        }
        
        $editForm = $this->createForm(new UtilisateurType(), $entity);
        
        if ($request->getMethod() == 'PUT') {
	        $editForm->bind($request);
	        
	        if ($editForm->isValid()) {
	        	$factory = $this->get('security.encoder_factory');
	        	$encoder = $factory->getEncoder($entity);
	        	
            // Update salt and password
	        	$entity->setSalt(md5(time()));
	        	$entity->setPassword($encoder->encodePassword($entity->getPassword(), $entity->getSalt()));
	        	
	            $em->flush();
	            
				$this->get('session')->getFlashBag()->add(
					'success',
					'Le membre <strong>' . $entity->getFirstname() . ' ' . $entity->getLastname() . '</strong> a été mis à jour !'
				);
	
	            return $this->redirect($this->generateUrl('utilisateur'));
	        }
        }

        return $this->render('AppCoreBundle:Utilisateur:edit.html.twig', array(
        	'headline'	=> 'Membres',
        	'title'		=> 'Modifier un membre',
            'entity'	=> $entity,
            'edit_form' => $editForm->createView(),
        ));
    }
    
    /**
     * Deletes an Utilisateur entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
    	$em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppCoreBundle:Utilisateur')->find($id);

        if (!$entity) {
            $this->get('session')->getFlashBag()->add(
				'danger',
				'Le memb sélectionné n\'existe pas !'
			);
	        
	        return $this->redirect($this->generateUrl('utilisateur'));
        }
        
      /*
       * In the case the user clicks on delete button (Ajax request condition)
       * we return button for confirmation
       */
    	if ($request->isXmlHttpRequest()) {
	    	$confirm = $this->container->get('Confirm');
	    	
	    	return new JsonResponse(array(
	    		'response' => $confirm->set('Êtes-vous sûr de vouloir supprimer le membre <strong>' . $entity->getFirstname() . ' ' . $entity->getLastname() . '</strong> ?', 'Êtes-vous sûr ?', 'error', array(
	    			'link'  => $request->request->get('action'),
	    			'label' => 'Supprimer',
	    		))->render(),
	    	));
    	}
    	
      
        $em->remove($entity);
        $em->flush();

        $this->get('session')->getFlashBag()->add(
                        'success',
                        'Le membre <strong>' . $entity->getFirstname() . ' ' . $entity->getLastname() . '</strong> a été supprimé !'
                );

        return $this->redirect($this->generateUrl('utilisateur'));
	
    }
    
    public function setUsernameAction(Request $request)
    {
    	if ($request->isXmlHttpRequest()) {
	    	$transformer	= $this->container->get('stringtransformer');
	    	$options 		= json_decode($request->request->get('options'));
	    	$username 		= array();
	    	
	    	for ($i = 0; $i < count($options); $i++) {
		    	$username[$i] = $transformer->lowercase($options[$i]);
		    	$username[$i] = $transformer->pure($username[$i]);
	    	}
	    	
	    	$username[1] 	= substr($username[1], 0, 1);
			$username 		= array_reverse($username);
			
	    	return new JsonResponse(array(
	    		'value' => implode('', $username),
	    	));
	    }
	}
	
	/**
     * Imports Utilisateur entities.
     * Same method explained in CoursController.php
     * Parse CSV file
     * The CSV cours structure was defined with Christophe Widmer (please contact him for CSV examples and notices)
     * This one is a bit more complex
     * WARNING : each column has its own role, dont blend it
     *
     */
    public function importAction(Request $request) {
    	$parser		= new Csv();
    	$type		= 'text/plain';
    	$ext		= 'csv';
    	$path 		= __DIR__ . '/../../../../web/uploads/csv';
    	
    	$em 		= $this->getDoctrine()->getManager();
    	$form 		= $this->createForm(new CsvType());
	    
	    if ($request->getMethod() == 'POST') {
	    	$form->bind($request);
	    	
	    	if ($form->isValid()) {
		    	$data = $form->getData();
			    $file = $data['csv']->move($path, 'temp.csv');
			    
			    $parser->set($path, 'temp.csv');
			    
			    $rows		= $parser->parseCsv();
			    $length		= count($rows);
			    
			    foreach ($rows as $row) {
			    	$statement = array();
			    	
			    	foreach ($row as $cell) {
			    		$cell 			= trim($cell);
				    	$statement[] 	= empty($cell);
			    	}
			    	
			    	if (in_array(true, $statement)) {
				    	$length--;
			    	} else {
			    		$role = $em->getRepository('AppCoreBundle:Role')->findOneBy(array('name' => $row[4]));
			    		
			    		if (!empty($role)) {
					    	$entity = new Utilisateur();
				    		$entity->setLastname($row[0]);
				    		$entity->setFirstname($row[1]);
				    		$entity->setUsername($row[2]);
				    		$entity->setEmail($row[3]);
				    		$entity->setRole($role);
				    		$entity->setPassword($row[5]);
				    		
				    		$factory = $this->get('security.encoder_factory');
				        	$encoder = $factory->getEncoder($entity);
				        	
				        	$entity->setSalt(md5(time()));
				        	$entity->setPassword($encoder->encodePassword($entity->getPassword(), $entity->getSalt()));
				    		
				    		$validator = $this->get('validator');
				    		if (!count($validator->validate($entity))) {
					    		$em->persist($entity);
				    		} else {
					    		$length--;
				    		}
			    		} else {
				    		$length--;
			    		}
			    	}
			    }
			    
			    $em->flush();
			    
			    if (file_exists($path . '/temp.csv')) {
				    unlink($path . '/temp.csv');
			    }
			    
			    $this->get('session')->getFlashBag()->add(
					'success',
					$length . ' utilisateurs ont été ajoutés !'
				);
			    
			    return $this->redirect($this->generateUrl('utilisateur_import'));
	    	}
	    }
    	
    	return $this->render('AppCoreBundle:Utilisateur:import.html.twig', array(
        	'headline'	=> 'Utilisateurs',
        	'title'		=> 'Importer des utilisateurs',
    		'form' => $form->createView(),
    	));
    }
    
    public function listeHistoriqueAction(Request $request, $id)
    {
    	$em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppCoreBundle:Utilisateur')->find($id);

        if (!$entity) {
            $this->get('session')->getFlashBag()->add(
				'danger',
				'L\'utilisateur sélectionné n\'existe pas !'
			);
	        
	        return $this->redirect($this->generateUrl('utilisateur'));
        }
        
        $listeHistorique = $em->getRepository('AppCoreBundle:UtilisateurHistorique')->findByUtilisateur($id);
        
        $listeTypes = array();
        foreach ($listeHistorique as $val) {
            $listeTypes[$val->getType()] = $val;
           
        }
     
        return $this->render('AppCoreBundle:Utilisateur:listeHistorique.html.twig', array(
            'headline'	=> 'Utilisateurs',
            'title'		=> 'Liste historique',
            'liste' => $listeHistorique,
            'types'  => $listeTypes,
            'id' => $id
        ));
        
	
    }
    
    public function showHistoriqueAction(Request $request, $id)
    {
    	$em = $this->getDoctrine()->getManager();
    	
    	if ($request->isXmlHttpRequest()) {
    		$options = json_decode($request->request->get('options'));
    	
    		foreach($options as $property => $option) {
    			if ($option == 'tous') {
    				unset($options->$property);
    			}
    		}
    	
    		// Get aeroport by options pass from menu dropdown lists
    		$options 	= (array) $options;
    		$listeHistorique 	= $em->getRepository('AppCoreBundle:UtilisateurHistorique')->findByOptions($options);
    		return $this->render('AppCoreBundle:Utilisateur:showHistorique.html.twig', array(
                                'liste' => $listeHistorique,
                                'id' => $id,
    				'options'	=> $options
    		));
    	} else {
	    	$listeHistorique = $em->getRepository('AppCoreBundle:UtilisateurHistorique')->findByUtilisateur($id);
	    
	    	return $this->render('AppCoreBundle:Utilisateur:showHistorique.html.twig', array(
	    			'headline'	=> 'Utilisateurs',
                                'title'		=> 'Liste historique',
                                'liste' => $listeHistorique,
                                'id' => $id
	    	));
    	}
    
    	return true;
    }

    //membre premium 

    public function premiumAction(){
    	$em = $this->getDoctrine()->getManager();
    	$query = $em->createQuery('SELECT p.username, p.premium FROM AppCoreBundle:Utilisateur p');
    	$entities = $query->getResult();
    	return $this->render('AppCoreBundle:Utilisateur:premium.html.twig', array(
        	'headline'	=> 'Membres',
        	'title' 	=> 'Liste des membres',
            'entities'  => $entities,
        ));
    }

    public function editPremiumAction($user){
    	$conn = $this->get('database_connection');
        $update = $conn->executeUpdate('UPDATE `utilisateur` SET premium = "premium" WHERE username="'.$user.'"');  
    	
    	$em = $this->getDoctrine()->getManager();
    	$query = $em->createQuery('SELECT p.username, p.premium FROM AppCoreBundle:Utilisateur p');
    	$entities = $query->getResult();
    	return $this->render('AppCoreBundle:Utilisateur:premium.html.twig', array(
        	'headline'	=> 'Membres',
        	'title' 	=> 'Liste des membres',
            'entities'  => $entities,
        ));
    }
}