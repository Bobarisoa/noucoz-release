<?php
/**
 * Created by PhpStorm.
 * User: Yoyo
 * Date: 24/09/2015
 * Time: 08:49
 */

namespace App\CoreBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use App\CoreBundle\Entity\Utilisateur;
use Web\FrontBundle\Entity\Commentaire;
use App\CoreBundle\Form\UtilisateurType;
use Symfony\Component\HttpFoundation\Response;



class ArticlesController extends Controller
{
    public function indexAction() {
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('WebFrontBundle:Articles')->findAll();

        return $this->render('AppCoreBundle:Articles:index.html.twig', array(
            'headline' => 'Articles',
            'title'	   => 'Liste des articles',
            'entities' => $entities
        ));
    }

    /**
     * Create WebFrontBundle:Articles entity
     */
    public function createAction(Request $request) {
        return $this->render('AppCoreBundle:Articles:create.html.twig');
    }

    /**
     * Edit WebFrontBundle:Articles entity
     */
    public function editAction(Request $request) {

    }

    /**
     * Delete WebFrontBundle:Articles entity
     */
    public function deleteAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('WebFrontBundle:Articles')->find($id);

        if (!$entity) {
            $this->get('session')->getFlashBag()->add(
                'danger',
                'L\'article sélectionnée n\'existe pas !'
            );

            return $this->redirect($this->generateUrl('articles'));
        }


        $em->remove($entity);
        $em->flush();

        $this->get('session')->getFlashBag()->add(
            'success',
            'L\'article intitulé <strong>' . $entity->getTitre() . '</strong> a été supprimé !'
        );

        return $this->redirect($this->generateUrl('articles'));
    }

    /**
     * ********************
     */
    public function readAction(Request $request, $id) {

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('WebFrontBundle:Articles')->find($id);
        $countries = $em->getRepository('WebFrontBundle:Country')->findAll();
        $categorie = $em->getRepository('WebFrontBundle:Categorie')->findAll();

        /**
         * Inscription
         */
        $entity  = new Utilisateur();
        $form = $this->createForm(new UtilisateurType(), $entity);
        //commentaire
        $commentaire = new Commentaire();
        $commentaire_form = $this->createFormBuilder($commentaire)
            ->add('auteur', 'text')
            ->add('datePub', 'date')
            ->add('auteur','email')
            ->add('contenu', 'textarea')
            ->add('commenter', 'submit', array('label' => 'commenter'))
            ->getForm();

        if($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {

                $factory = $this->get('security.encoder_factory');
                $encoder = $factory->getEncoder($entity);

                // Dont forget salt, common in security process
                //$entity->setSalt(md5(time()));
                //$entity->setPassword($encoder->encodePassword($entity->getPassword(), $entity->getSalt()));
                $entity->setPassword(md5($entity->getPassword()));
                $em = $this->getDoctrine()->getManager();
                $em->persist($entity);
                $em->flush();

                $this->get('session')->getFlashBag()->add(
                    'success',
                    'Le <strong>' . $entity->getFirstname() . '&nbsp;' . $entity->getLastname() . '</strong> a bien été crée !'
                );

                return $this->redirect($this->generateUrl('login'));
            }

            $commentaire_form->bind($request);
            //if($commentaire_form->isValid()) {
                $commentaire->setDatePub(new \DateTime('tomorrow'));
                $commentaire->setArticles($entities);
                $em = $this->getDoctrine()->getManager();
                $em->persist($commentaire);
                $em->flush();
            //}
        }


        return $this->render('AppCoreBundle:Articles:read.html.twig', array(
            'countries' => $countries,
            'categorie' => $categorie,
            'entities' => $entities,
            'form'	   => $form->createView(),
            'commentaire_form' => $commentaire_form->createView(),
        ));
    }

    /**
     * Translation
     */
    public function translateAction(Request $request) {

        $bingtranslator = new \BingTranslator('noucoze-dev', 'F/zGjS5yMyBr858o54uv3Xn7jCLRw6Wc1Ps8iJ/cE1w=');

        if ($request->isXmlHttpRequest()) {
            $body = $request->request->get('body');

            $fromLang = $bingtranslator->getDetectedLanguage($body);
            $toLang = $request->getLocale();

            $transString = $bingtranslator->getTranslation($fromLang, 'en', $body);

            $json = json_encode(array('translation' => $transString));

            return new Response($json);

        }
        //return new Response($bingtranslator->getToken());

    }
}