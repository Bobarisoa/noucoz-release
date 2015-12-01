<?php

namespace Web\FrontBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Hip\MandrillBundle\Message;
use Hip\MandrillBundle\Dispatcher;
use Doctrine\Common\Util\Debug;
use Doctrine\ORM\Query\ResultSetMapping;
use App\CoreBundle\Entity\Utilisateur;
use Web\FrontBundle\Entity\Country;
use Web\FrontBundle\Entity\Articles;
use App\CoreBundle\Form\UtilisateurType;


use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ArticleController extends Controller
{

   	public function topNewsAction()
    {
    	
        return new Response('OK');
    }

    public function plusLusAction()
    {
    	//return $this->render('WebFrontBundle:Article:article_plus_lus.html.twig');
    }

    /**
     *
     */
    public function rechercheArticleAction(){
        $request = $this->get('request');
        $em = $this->getDoctrine()->getManager();


        $countries = $em->getRepository('WebFrontBundle:Country')->findAll();
        $categorie = $em->getRepository('WebFrontBundle:Categorie')->findAll();

        $entity  = new Utilisateur();
        $form = $this->createForm(new UtilisateurType(), $entity);

        $key = $request->get('key_word');
        $article = $em->createQuery('SELECT a FROM WebFrontBundle:Articles a WHERE a.titre like :keyWord or a.contenu like :keyWord')
            ->setParameter('keyWord', mysql_real_escape_string('%' . $key . '%'));
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate($article,$request->query->getInt('page', 1) ,12);

        return $this->render('WebFrontBundle:HomePage:result.html.twig', array(
            'countries' => $countries,
            'headline' => 'Membres',
            'title'    => 'Ajouter un membre',
            'entity'   => $entity,
            'pagination' => $pagination,
            'categorie' => $categorie,
            'form'     => $form->createView()
        ));

    }

}
