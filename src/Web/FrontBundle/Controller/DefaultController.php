<?php

namespace Web\FrontBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('WebFrontBundle:Default:index.html.twig', array('name' => $name));
    }

    public function membreAction(){
    	//$user = new Utilisateur();
    	$repository = $this->getDoctrine()->getRepository('WebFrontBundle:Inscription');
    	return $this->render('WebFrontBundle:Membre:membre.html.twig');
    }
}


