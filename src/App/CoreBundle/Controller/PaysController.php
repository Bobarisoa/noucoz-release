<?php
/**
 * Created by PhpStorm.
 * User: Yoyo
 * Date: 22/09/2015
 * Time: 13:22
 */

namespace App\CoreBundle\Controller;


use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\CoreBundle\Form\PaysType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Web\FrontBundle\Entity\Country;

use ElephantIO\Client;
use ElephantIO\Engine\SocketIO\Version1X;


class PaysController extends Controller
{
    protected $client;

    function __construct() {
        $this->client = new Client(new Version1X('http://localhost:8081'));
    }

    /**
     * @param       $channel
     * @param array $data
     */
    function emit($channel, Array $data)
    {
        try {
            $this->client->initialize();
            $this->client->emit($channel, $data);
            $this->client->close();
        }
        catch (\Exception $e) {

        }
    }

    public function indexAction() {

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('WebFrontBundle:Country')->findAll();

        return $this->render('AppCoreBundle:Pays:index.html.twig', array(
            'headline' => 'Pays',
        	'title'	   => 'Liste des pays',
            'entities' => $entities
        ));
    }

    /**
     * Create a new Pays (Country) entity
     */
    public function createAction(Request $request) {
        $notification = 'A new entity has been created';

        $entity = new Country();
        $form = $this->createForm(new PaysType(), $entity);

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($entity);
                $em->flush();

                $this->get('session')->getFlashBag()->add(
                    'success',
                    'Le pays  <strong>' . $entity->getName() . '</strong> a bien &eacute;t&eacute; enregistr&eacute; !'
                );

                // emit notification on channel 'notification'
                $this->emit('php.notification', ['notification' => $notification]);

                return $this->redirect($this->generateUrl('pays'));
            }
        }

        return $this->render('AppCoreBundle:Pays:new.html.twig', array(
            'headline' => 'Pays',
            'title'	   => 'Ajouter un pays',
            'entity'   => $entity,
            'form'	   => $form->createView(),
        ));
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function showAction() {
        return $this->redirect($this->generateUrl('pays'));
    }

    /**
     * Edit pays (country) entity
     */
    public function editAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('WebFrontBundle:Country')->find($id);

        if (!$entity) {
            $this->get('session')->getFlashBag()->add(
                'danger',
                'Le pays sélectionné n\'existe pas !'
            );

            return $this->redirect($this->generateUrl('pays'));
        }

        $editForm = $this->createForm(new PaysType(), $entity);

        if($request->getMethod() == 'PUT') {
            $editForm->submit($request);

            if ($editForm->isValid()) {
                $em->flush();

                $this->get('session')->getFlashBag()->add(
                    'success',
                    'Le pays <strong>' . $entity->getName() . '</strong> a &eacute;t&eacute; mis &agrave; jour !'
                );
            }
        }

        return $this->render('AppCoreBundle:Pays:edit.html.twig', array(
            'headline'	=> 'Pays',
            'title'		=> 'Modifier un pays',
            'entity'	=> $entity,
            'form' => $editForm->createView(),
        ));
    }

    /**
     * Delete pays entity
     */
    public function deleteAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('WebFrontBundle:Country')->find($id);

        if (!$entity) {
            $this->get('session')->getFlashBag()->add(
                'danger',
                'Le pays sélectionnée n\'existe pas !'
            );

            return $this->redirect($this->generateUrl('pays'));
        }


        $em->remove($entity);
        $em->flush();

        $this->get('session')->getFlashBag()->add(
            'success',
            'Le pays <strong>' . $entity->getName() . ' - ' . $entity->getCode() . '</strong> a &eacute;t&eacute; supprim&eacute; !'
        );

        return $this->redirect($this->generateUrl('pays'));
    }
}