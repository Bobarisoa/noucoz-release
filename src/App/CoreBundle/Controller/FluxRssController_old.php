<?php

namespace App\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use App\CoreBundle\Entity\FluxRss;
use App\CoreBundle\Form\FluxRssType;

class FluxRssController extends Controller implements KillTheBootInterface
{
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $flux_repo = $em->getRepository('AppCoreBundle:FluxRss');
        $entities   = $flux_repo->findAll();
        
        return $this->render('AppCoreBundle:FluxRss:index.html.twig', array(
            	'headline' => 'Flux RSS',
        		'title'	   => 'Liste des flux rss',
                'entities' => $entities
                                
        ));
        return true;
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
    		$entities 	= $em->getRepository('AppCoreBundle:FluxRss')->findByOptions($options);
    		
    		return $this->render('AppCoreBundle:FluxRss:show.html.twig', array(
    				'entities'	=> $entities,
    				'options'	=> $options
    		));
    	} else {
	    	$entities = $em->getRepository('AppCoreBundle:FluxRss')->findAll();
	    
	    	return $this->render('AppCoreBundle:FluxRss:show.html.twig', array(
	    			'headline'	=> 'Flux RSS',
	    			'title' 	=> 'Liste des flux rss',
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
        $entity  = new FluxRss();
        $form = $this->createForm(new FluxRssType(), $entity);
        
        if($request->getMethod() == 'POST') {
	        $form->bind($request);
	
	        if ($form->isValid()) {
	        	
	            $em = $this->getDoctrine()->getManager();
	            $em->persist($entity);
	            $em->flush();
	            
	            $this->get('session')->getFlashBag()->add(
					'success',
					'Le flux rss  <strong>' . $entity->getNameSite() . '</strong> a bien été créé !'
				);
	
	            return $this->redirect($this->generateUrl('flux_rss'));
	        }
	    }

        return $this->render('AppCoreBundle:FluxRss:new.html.twig', array(
        	'headline' => 'Flux RSS',
        	'title'	   => 'Ajouter une flux rss',
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

        $entity = $em->getRepository('AppCoreBundle:FluxRss')->find($id);
        
        if (!$entity) {
            $this->get('session')->getFlashBag()->add(
				'danger',
				'Le flux rss sélectionné n\'existe pas !'
			);
	        
	        return $this->redirect($this->generateUrl('flux_rss'));
        }
        
        
        $editForm = $this->createForm(new FluxRssType(), $entity);
        
        if ($request->getMethod() == 'PUT') {
	        $editForm->bind($request);
	        
	        if ($editForm->isValid()) {
	        
                                $em->flush();
	            
				$this->get('session')->getFlashBag()->add(
					'success',
					'Le flux rss <strong>' . $entity->getNameSite() . '</strong> a été mis à jour !'
				);
	
	            return $this->redirect($this->generateUrl('flux_rss'));
	        }
        }

        return $this->render('AppCoreBundle:FluxRss:edit.html.twig', array(
        	'headline'	=> 'Flux RSS',
        	'title'		=> 'Modifier un flux rss',
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
        $entity = $em->getRepository('AppCoreBundle:FluxRss')->find($id);

		if (!$entity) {
			$this->get('session')->getFlashBag()->add(
				'danger',
				'Le pays sélectionnée n\'existe pas !'
			);

			return $this->redirect($this->generateUrl('flux_rss'));
		}


		$em->remove($entity);
		$em->flush();

		$this->get('session')->getFlashBag()->add(
			'success',
			'Le site <strong>' . $entity->getNameSite() . '</strong> a été supprimé !'
		);

		return $this->redirect($this->generateUrl('flux_rss'));
    }

    public function showRssAction(Request $request){

        $em = $this->getDoctrine()->getManager();
        $flux_repo = $em->getRepository('AppCoreBundle:FluxRss');
        $entities  = $flux_repo->findAll();

        //$dates = date("d M Y");
        $dates = "08 Oct 2015";
        $conn = $this->get('database_connection');
        //$articles    = $conn->executeUpdate('SELECT FROM article_noucoze WHERE datePublication = "'.$dates.'"');
        $articles   = $em->getRepository('WebFrontBundle:Articles')->findBy(array('create' => $dates));
        
        

        foreach($entities as $value){
        //$path = $value->getLinkFlux();
        $xml = simplexml_load_file($value->getLinkFlux());

        foreach($xml as $value ){
            foreach($value as $item){  
                foreach($item as $url){}
                    $x = $item->pubDate;

                    $date = substr($x,5,12);
                    $date_replace = str_replace('  ', ' ', $date);

                    $contenu = $item->description;

                    $titre = $item->title;

                    $photo = $url['url'];
                    
                    $source = "http://www.lemonde.fr";
                    $conn = $this->get('database_connection');

                    $update   = $conn->executeUpdate('INSERT INTO article_noucoze (country_id,titre,source,contenu,auteur,photo,notification,category_id,classe_id,datePublication)
                    VALUES(1, "'.$titre.'","'.$source.'", "'.$contenu.'", "auteur", "'.$photo.'", 1, 1 ,1, "'.$date_replace.'")');
                    $update = $conn->executeUpdate('DELETE FROM article_noucoze WHERE titre = "" OR contenu ="" OR titre = "L\'Equipe.fr"');
                    //$update_2 = $conn->executeUpdate('DELETE FROM article_noucoze WHERE datePublication != "'.$dates.'"');     
                      
                }
            
            }
           
        }
      
        $dates = date("d M Y");
        

        return $this->render('AppCoreBundle:FluxRss:rss.html.twig', array(
                'headline' => 'Flux RSS',
                'title'    => 'Flux rss par article',
                'entities' => $entities,
                'articles'  => $articles,
                'dates' => $dates
        ));
        return true;
    }


}

