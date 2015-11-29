<?php
/**
 * Created by PhpStorm.
 * User: Yoyo
 * Date: 24/09/2015
 * Time: 15:23
 */

namespace App\CoreBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class CommentaireController extends Controller
{
    public function indexAction() {
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('WebFrontBundle:Commentaire')->findAll();

        return $this->render('AppCoreBundle:Commentaire:index.html.twig', array(
            'headline' => 'Commentaire',
            'title'	   => 'Liste des commentaires',
            'entities' => $entities
        ));
    }

    /**
     * Delete WebFrontBundle:Commentaire entity
     */
    public function deleteAction(Request $request, $id) {

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('WebFrontBundle:Commentaire')->find($id);

        if (!$entity) {
            $this->get('session')->getFlashBag()->add(
                'danger',
                'Le commentaire sélectionné n\'existe pas !'
            );

            return $this->redirect($this->generateUrl('commentaire'));
        }


        $em->remove($entity);
        $em->flush();

        $this->get('session')->getFlashBag()->add(
            'success',
            'Le commentaire de <strong>' . $entity->getAuteur() . '</strong> a été supprimé !'
        );

        return $this->redirect($this->generateUrl('commentaire'));
    }
}