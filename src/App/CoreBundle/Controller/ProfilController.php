<?php

namespace App\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use App\CoreBundle\Entity\Utilisateur;
use App\CoreBundle\Form\ProfilType;

/**
 * Profil controller.
 *
 */
class ProfilController extends Controller implements KillTheBootInterface
{
	/**
     * Lists all Classe entities.
     *
     */
    public function editAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        
        $id = $this->get('security.context')
        		   ->getToken()
        		   ->getUser()
        		   ->getId();

        $entity = $em->getRepository('AppCoreBundle:Utilisateur')->find($id);
        
        // Only if current user is recognizable
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Utilisateur entity.');
        }
        
        $editForm = $this->createForm(new ProfilType(), $entity);
        
        if ($request->getMethod() == 'PUT') {
	        $editForm->bind($request);
	        
	        if ($editForm->isValid()) {
	        	$factory = $this->get('security.encoder_factory');
	        	$encoder = $factory->getEncoder($entity);
	        	
	        	$entity->setSalt(md5(time()));
	        	$entity->setPassword($encoder->encodePassword($entity->getPassword(), $entity->getSalt()));
	        	
	            $em->flush();
	            
				$this->get('session')->getFlashBag()->add(
					'success',
					'Votre profil a été mise à jour !'
				);
	
	            return $this->redirect($this->generateUrl('profil'));
	        }
        }

        return $this->render('AppCoreBundle:Profil:edit.html.twig', array(
        	'headline'	  => 'Profil',
        	'title'		  => 'Modifier mon mot de passe',
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
        ));
    }
}