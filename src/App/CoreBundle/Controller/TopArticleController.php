<?php

namespace App\CoreBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class TopArticleController extends Controller
{
    public function indexAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        $oldActifEntity = null;
        $id = $request->request->get('optradio');
        if ($id) {
            // update TopArticle entity
            $oldActifEntity = $em->getRepository('AppCoreBundle:TopArticle')->findOneBy(array('actif' => true ));
            if(!$oldActifEntity) {
                throw $this->CreateNotFoundException('Entity not found');
            }
            $oldActifEntity->setActif(false);
            $em->flush();

            $newActifEntity = $em->getRepository('AppCoreBundle:TopArticle')->find($id);
            $newActifEntity->setActif(true);
            $em->flush();
        }


        $entities = $em->getRepository('AppCoreBundle:TopArticle')->findAll();

        return $this->render('AppCoreBundle:TopArticle:index.html.twig', array(
            'headline' => 'Top news',
            'title'	   => 'Article actuellement affiché dans les blocs',
            'entities' => $entities,
        ));
    }
}