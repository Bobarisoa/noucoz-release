<?php

namespace Web\FrontBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Hip\MandrillBundle\Message;
use Hip\MandrillBundle\Dispatcher;
use Doctrine\Common\Util\Debug;


use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ContactController extends Controller
{
//	public function checkDomainsValidity(Request $request){
//		
//		$baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
//		//var_dump($baseurl);
//		switch(true){
//			case ($baseurl == 'http://agente3w.com'):
//				return 'core_dashboard';	
//			break;
//		}
//		
//		return NULL;
//	}
	
    public function indexAction(Request $request)
    {
    	
        return $this->render('WebFrontBundle:Contact:index.html.twig');
    }
//    
//    
//    public function pageNotFoundAction()
//    {
//        //throw new NotFoundHttpException();
//        throw $this->createNotFoundException();
//    }
}
