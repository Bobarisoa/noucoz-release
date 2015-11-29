<?php

namespace Web\FrontBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Hip\MandrillBundle\Message;
use Hip\MandrillBundle\Dispatcher;
use Doctrine\Common\Util\Debug;


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

}
