<?php

namespace App\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;


class DashboardController extends Controller implements KillTheBootInterface
{
    public function indexAction()
    {
    	$em = $this->getDoctrine()->getManager();
      
        // enlever la fonction   
        /* $zroleUser = $this->get('security.context')->getToken()->getUser()->getRole()->getRole();
        if( $zroleUser == "ROLE_AGENT_VENTE_EXTERNE" )
        {
                return $this->render('AppCoreBundle:Dashboard:agentExterne.html.twig',array(
            	'headline' => 'Tableaux de bord',
        		'title'	   => '',
        		'article' => $em->getRepository('WebFrontBundle:Articles')->findAll(),
        		'membres' => $em->getRepository('AppCoreBundle:Utilisateur')->findAll(),
        		'fluxrss' => $em->getRepository('AppCoreBundle:FluxRss')->findAll()
                
                 ));
        }
        */
        return $this->render('AppCoreBundle:Dashboard:index.html.twig',array(
            	'headline' => 'Tableaux de bord',
        		'title'	   => '',
        		'article' => $em->getRepository('WebFrontBundle:Articles')->findAll(),
        		'membres' => $em->getRepository('AppCoreBundle:Utilisateur')->findAll(),
        		'fluxrss' => $em->getRepository('AppCoreBundle:FluxRss')->findAll()
                
        ));
    }

    public function listeSondageAction( Request $request)
    {
       $em = $this->getDoctrine()->getManager();
       //insertion 
       $conn = $this->get('database_connection');
       $question  = $request->get('question_txt'); 
       $reponse   = $request->get('reponse_txt');
       $categorie = $request->get('categorie_txt');

       $add = $conn->executeUpdate("INSERT INTO Sondage(question,reponse,type)
       VALUES('".$question."','".$reponse."','".$categorie."')");
       $delete = $conn->executeUpdate('DELETE FROM Sondage WHERE question =""');
       $query = $em->createQuery('SELECT DISTINCT p FROM AppCoreBundle:Sondage p');
       $sondage = $query->getResult();
       $id = $request->get('id_txt');

       $delete_id = $conn->executeUpdate('DELETE FROM Sondage WHERE id = "'.$id.'"');
        return $this->render('AppCoreBundle:Sondage:listeSondage.html.twig', array(
            'headline'  => 'Sondages ',
            'title'     => 'Liste des sondages',
            'sondage'  => $sondage
        ));
    }

    public function ajoutSondageAction(){
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery('SELECT p FROM WebFrontBundle:Categorie p');
        $categories = $query->getResult();
       return $this->render('AppCoreBundle:Sondage:ajoutSondage.html.twig',
       array(
          'headline'  => 'Sondage',
          'title'  => 'Ajout sondage',
          'categorie' => $categories,
        ));
    }

    public function modifierSondageAction($id, Request $request){
        $em = $this->getDoctrine()->getManager();
        $sondages = $em->getRepository('AppCoreBundle:Sondage')
            ->findOneBy(array('id' => $id));

        $conn = $this->get('database_connection');
        $question  = $request->get('question_txt'); 
        $reponse   = $request->get('reponse_txt');
        $categorie = $request->get('categorie_txt');
        
        $update = $conn->executeUpdate('UPDATE Sondage SET question="'.$question.'", 
        reponse="'.$reponse.'", type="'.$categorie.'" WHERE id="'.$id.'"');

        return $this->render('AppCoreBundle:Sondage:editSondage.html.twig',
        array(
          'headline'  => 'Sondage',
          'title' => 'Modification sondage',
          'sondage' => $sondages,
        ));
        
    }

}
