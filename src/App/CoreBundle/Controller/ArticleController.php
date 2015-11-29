<?php

namespace App\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use App\CoreBundle\Entity\Article;
use App\CoreBundle\Form\ArticleType;

class ArticleController extends Controller implements KillTheBootInterface
{
    public function indexAction()
    {
        
        $em = $this->getDoctrine()->getManager();
        $article_repo = $em->getRepository('AppCoreBundle:Article');
        $entities   = $article_repo->findAll();
        
        return $this->render('AppCoreBundle:Article:index.html.twig', array(
            	'headline' => 'Articles',
        		'title'	   => 'Liste des articles',
                'entities' => $entities
                                
        ));

    }
    
    
    public function showAction(Request $request)
    {
    	$em = $this->getDoctrine()->getManager();
    	
    	if ($request->isXmlHttpRequest()) {
    		$options = json_decode($request->request->get('options'));
    	
    		foreach($options as $property => $option) {
    			if ($option == 'tous') {
    				unset($options->$property);
    			}
    		}
    	
    		// Get article by options pass from menu dropdown lists
    		$options 	= (array) $options;
    		$entities 	= $em->getRepository('AppCoreBundle:Article')->findByOptions($options);
    		
    		return $this->render('AppCoreBundle:Article:show.html.twig', array(
    				'entities'	=> $entities,
    				'options'	=> $options
    		));
    	} else {
	    	$entities = $em->getRepository('AppCoreBundle:Article')->findAll();
	    
	    	return $this->render('AppCoreBundle:Article:show.html.twig', array(
	    			'headline'	=> 'Articles',
	    			'title' 	=> 'Liste des articles',
	    			'entities'  => $entities
	    	));
    	}
    
    	return true;
    }
    
    /**
     * Creates a new Article entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity  = new Article();
        $form = $this->createForm(new ArticleType(), $entity);
        
        if($request->getMethod() == 'POST') {
	        $form->bind($request);
	
	        if ($form->isValid()) {
	        	
	            $em = $this->getDoctrine()->getManager();
	            $em->persist($entity);
	            $em->flush();
	            
	            $this->get('session')->getFlashBag()->add(
					'success',
					'L\'article  <strong>' . $entity->getName() . '</strong> a bien été créé !'
				);
	
	            return $this->redirect($this->generateUrl('article'));
	        }
	    }

        return $this->render('AppCoreBundle:Article:new.html.twig', array(
        	'headline' => 'Articles',
        	'title'	   => 'Ajouter une article',
            'entity'   => $entity,
            'form'	   => $form->createView(),
        ));
    }
    
    /**
     * Edits a Article entity.
     *
     */
    public function editAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AppCoreBundle:Article')->find($id);
        
        if (!$entity) {
            $this->get('session')->getFlashBag()->add(
				'danger',
				'L\'article sélectionné n\'existe pas !'
			);
	        
	        return $this->redirect($this->generateUrl('article'));
        }
        
        
        $editForm = $this->createForm(new ArticleType(), $entity);
        
        if ($request->getMethod() == 'PUT') {
	        $editForm->bind($request);
	        
	        if ($editForm->isValid()) {
	        
                                $em->flush();
	            
				$this->get('session')->getFlashBag()->add(
					'success',
					'L\'article <strong>' . $entity->getName() . '</strong> a été mis à jour !'
				);
	
	            return $this->redirect($this->generateUrl('article'));
	        }
        }

        return $this->render('AppCoreBundle:Article:edit.html.twig', array(
        	'headline'	=> 'Article',
        	'title'		=> 'Modifier un article',
            'entity'	=> $entity,
            'form' => $editForm->createView(),
        ));
    }
    
    /**
     * Deletes a Article entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
    	$em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppCoreBundle:Article')->find($id);

        if (!$entity) {
            $this->get('session')->getFlashBag()->add(
				'danger',
				'L\'article sélectionnée n\'existe pas !'
			);
	        
	        return $this->redirect($this->generateUrl('article'));
        }
        
    	if ($request->isXmlHttpRequest()) {
	    	$confirm = $this->container->get('Confirm');
	    	
	    	return new JsonResponse(array(
	    		'response' => $confirm->set('Êtes-vous sûr de vouloir supprimer l\'article <strong>' . $entity->getName() . '</strong> ?', 'Êtes-vous sûr ?', 'error', array(
	    			'link'  => $request->request->get('action'),
	    			'label' => 'Supprimer',
	    		))->render(),
	    	));
    	}
    	
    	if ($request->getMethod() == 'GET') {
    		
                    $em->remove($entity);
                    $em->flush();
	        	
	            $this->get('session')->getFlashBag()->add(
					'success',
					'L\'article <strong>' . $entity->getName() . '</strong> a été supprimée !'
				);
		        
		        return $this->redirect($this->generateUrl('article'));
               
        }
    }
}
