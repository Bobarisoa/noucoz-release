<?php
/**
 * Created by PhpStorm.
 * User: Yoyo
 * Date: 30/09/2015
 * Time: 15:22
 */

namespace Web\FrontBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class CategorieController extends Controller
{
    public function countAction()
    {
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('WebFrontBundle:Categorie')->findAll();

        $serializer = $this->container->get('serializer');
        $entityJson = $serializer->serialize($entities, 'json');

        $response = new Response();
        $response->setContent($entityJson);

        return $response;
    }
}