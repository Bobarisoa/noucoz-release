<?php

namespace Web\FrontBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Hip\MandrillBundle\Message;
use Hip\MandrillBundle\Dispatcher;
use Doctrine\Common\Util\Debug;


use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BlogController extends Controller
{

    public function indexAction(Request $request)
    {
    	
        return $this->render('WebFrontBundle:Blog:index.html.twig');
    }

}
