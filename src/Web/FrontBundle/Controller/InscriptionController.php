<?php

namespace Web\FrontBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Hip\MandrillBundle\Message;
use Hip\MandrillBundle\Dispatcher;
use Doctrine\Common\Util\Debug;


use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use App\CoreBundle\Entity\Utilisateur;
use App\CoreBundle\Form\UtilisateurType;

class InscriptionController extends Controller
{

    public function indexAction(Request $request)
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
	
	            return $this->redirect($this->generateUrl('inscription'));
	        }
	    }

        return $this->render('WebFrontBundle:Inscription:index.html.twig', array(
        	'headline' => 'Membres',
        	'title'	   => 'Ajouter un membre',
            'entity'   => $entity,
            'form'	   => $form->createView(),
        ));
   
    }
    

}
