<?php

namespace Web\FrontBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Web\AdminBundle\Entity\Slider;
use Web\AdminBundle\Form\SliderType;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PageController extends Controller
{
    public function readAction(Request $request, $page)
    {
        $em = $this->getDoctrine()->getManager();
        $page = $em->getRepository('WebAdminBundle:Page')->findOneBySeoTitle($page);
        
        if(!$page)
           {
            //throw new NotFoundHttpException();
             //throw new \Exception("waaaaaaaaa",404);
            throw $this->createNotFoundException();
            //return $this->redirect($this->generateUrl('page_not_found'));
           }
           // 
            
        $langLocale = $this->get('request')->getLocale();
        $srcLang = $page->getLang();
        
        
            
        if( strtoupper($langLocale) == $srcLang )
        {
            return $this->render('WebFrontBundle:Page:index.html.twig',  array(
        	'page' => $page
            ) );
        }
        else
        {
            $Id = $page->getId();
            $findpagetrans = $em->getRepository('WebAdminBundle:Page')->findOneBySrcLang($Id);
            if(!$findpagetrans){
                    return $this->render('WebFrontBundle:Page:index.html.twig',  array(
                	'page' => $page
                    ) );
            }
            $pageredirect = $em->getRepository('WebAdminBundle:Page')->findOneBySeoTitle($findpagetrans->getSeoTitle());
             
            return $this->render('WebFrontBundle:Page:index.html.twig',  array(
                'page' => $pageredirect
            ) );
        }
            
    }
}
