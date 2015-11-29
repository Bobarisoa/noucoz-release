<?php

namespace Web\FrontBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Hip\MandrillBundle\Message;
use Hip\MandrillBundle\Dispatcher;
use Doctrine\Common\Util\Debug;
use Web\FrontBundle\Entity\Country;
use Web\FrontBundle\Entity\Articles;


use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Doctrine\ORM\Query\ResultSetMapping;
use App\CoreBundle\Entity\Utilisateur;
use App\CoreBundle\Form\UtilisateurType;

class HomePageController extends Controller
{
    public function checkDomainsValidity(Request $request){

        $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
        //var_dump($baseurl);
        switch(true){
            case ($baseurl == 'http://agente3w.com'):
                return 'core_dashboard';
                break;
        }

        return NULL;
    }


    public function indexAction(Request $request)
    {

        // parameters to template

        $countryId = (integer) $request->query->get('country',1);

        $em = $this->getDoctrine()->getManager();

        $classeArticle = $em->getRepository('AppCoreBundle:TopArticle')->findBy(array('actif' => 1));

        /**
         *   systeme de pagination
         */

        $em    = $this->get('doctrine.orm.entity_manager');
        $dql   = "SELECT a FROM WebFrontBundle:Articles a";
        $query = $em->createQuery($dql);

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate($query,$request->query->getInt('page', 1) ,12);

        //$query = $em->createQuery('SELECT  p FROM WebFrontBundle:Articles p' )
        //          ->setMaxResults(12)
        //          ->setFirstResult(1);
        //$articles = $query->getResult();
        $countries = $em->getRepository('WebFrontBundle:Country')->findAll();
        $categorie = $em->getRepository('WebFrontBundle:Categorie')->findAll();

        /**
         * Inscription
         */
        $entity  = new Utilisateur();
        $form = $this->createForm(new UtilisateurType(), $entity);

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
        }

        $pays = $request->get('p');

        if($pays){
            $em = $this->getDoctrine()->getManager();
            $rsm = new ResultSetMapping();
            $sql = "SELECT id FROM country_noucoze where name = '".$pays."'";
            $rsm = new \Doctrine\ORM\Query\ResultSetMapping;
            $rsm->addScalarResult('id', 'id');
            $result = $em
                ->createNativeQuery($sql, $rsm)
                ->getScalarResult();

            $country = $result[0]['id'];
            //$country = (integer) $request->query->get('country',1);
            //$em    = $this->get('doctrine.orm.entity_manager');
            $dql   = "SELECT a FROM WebFrontBundle:Articles a WHERE a.country ='".$country."'";
            //$dql   = "SELECT a FROM WebFrontBundle:Articles a";
            $query = $em->createQuery($dql);
            $paginator  = $this->get('knp_paginator');
            $pagination = $paginator->paginate($query,$request->query->getInt('page', 1) ,12);
            return $this->render('WebFrontBundle:HomePage:index.html.twig', array(
                'countries' => $countries,
                'headline' => 'Membres',
                'title'    => 'Ajouter un membre',
                'entity'   => $entity,
                'pagination' => $pagination,
                'categorie' => $categorie,
                'form'     => $form->createView(),
            ));

        }

        $countries = $em->getRepository('WebFrontBundle:Country')->findAll();
        $flux_repo = $em->getRepository('AppCoreBundle:FluxRss')->findAll();
        $data = array();
        $results = array();

        foreach($flux_repo as $flux)
        {
            $xml = simplexml_load_file($flux->getLinkFlux());
        }

        for( $i=0; $i < sizeof($xml->channel->item); $i++)
        {
            $data['title'] = $xml->channel->item->title->__toString();
            $data['link']  = $xml->channel->item->link->__toString();

            $data['titre']       = $xml->channel->item[$i]->title->__toString();
            $data['description'] = $xml->channel->item[$i]->description->__toString();
            $data['pubDate']     = $xml->channel->item[$i]->pubDate->__toString();
            if (isset($xml->channel->item[$i]->enclosure))
            {
                $data['image'] = $xml->channel->item[$i]->enclosure['url']->__toString();
            }
            else
            {
                $data['image'] = "";
            }
            array_push($results, $data);
        }
        //
        return $this->render('WebFrontBundle:HomePage:index.html.twig', array(
            'countries' => $countries,
            'headline' => 'Membres',
            'title'    => 'Ajouter un membre',
            'entity'   => $entity,
            'pagination' => $pagination,
            'categorie' => $categorie,
            'form'     => $form->createView(),
            'data'     => $results,
        ));
    }

    /*
          public function paysChoixAction($pays,Request $request)
          {

          $countryId = (integer) $request->query->get('country',1);
          $em = $this->getDoctrine()->getManager();
          $countries = $em->getRepository('WebFrontBundle:Country')->findAll();
          $classeArticle = $em->getRepository('AppCoreBundle:TopArticle')->findBy(array('actif' => 1));
          //$countries_2 = $em->getRepository('WebFrontBundle:Country')->findOneBy(array('name' => $pays));
          //$country_id = $countries_2->getId();
          $em    = $this->get('doctrine.orm.entity_manager');
          //$dql   = "SELECT a FROM WebFrontBundle:Articles a  WHERE  a.country = '".$country_id."'";
          $dql   = "SELECT a FROM WebFrontBundle:Articles a ";
          $query = $em->createQuery($dql);

          // pagination
          $paginator  = $this->get('knp_paginator');
          $pagination = $paginator->paginate($query,$request->query->getInt('page', 1) ,12);

          $categorie = $em->getRepository('WebFrontBundle:Categorie')->findAll();

          //inscription
          $entity  = new Utilisateur();
          $form = $this->createForm(new UtilisateurType(), $entity);

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
          }


          return $this->render('WebFrontBundle:HomePage:pays.html.twig', array(
              'countries' => $countries,

              'headline' => 'Membres',
              'title'    => 'Ajouter un membre',
              'entity'   => $entity,
              'pagination' => $pagination,
              'categorie' => $categorie,
              'form'     => $form->createView(),
          ));

      }

  */
    public function pageNotFoundAction()
    {
        //throw new NotFoundHttpException();
        throw $this->createNotFoundException();
    }

    public function topNewsAction()
    {
        return $this->render('WebFrontBundle:HomePage:news.html.twig');
    }

    public function plusLusAction()
    {
        return $this->render('WebFrontBundle:HomePage:plus.html.twig');
    }

    public function actualiteSportifAction(Request $request){
        $em    = $this->get('doctrine.orm.entity_manager');
        $dql   = "SELECT p FROM WebFrontBundle:Articles p WHERE p.category = 1";
        $query = $em->createQuery($dql);
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate($query,$request->query->getInt('page', 1) ,12);
        return $this->render('WebFrontBundle:HomePage:actualite.html.twig'
            ,array('pagination' => $pagination));
    }
    /**
     * polling db for new articles
     */
    public function articlePollingAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery('SELECT c.id AS cat, COUNT(a.id) AS nb_article FROM Web\FrontBundle\Entity\Articles a JOIN a.category c GROUP BY c.id');
        $result = $query->getResult();
        $json = json_encode($result);

        $response = new Response();
        $response->headers->set('Content-type', 'Application/json');
        $response->setContent($json);

        return $response;
    }


    public function sondageAction(){
        //$countryId = (integer) $request->query->get('country',1);

        $em = $this->getDoctrine()->getManager();
        $countries = $em->getRepository('WebFrontBundle:Country')->findAll();
        $categorie = $em->getRepository('WebFrontBundle:Categorie')->findAll();

        //$classeArticle = $em->getRepository('AppCoreBundle:TopArticle')->findBy(array('actif' => 1));
        return $this->render('WebFrontBundle:HomePage:sondage.html.twig',array(
            'countries' => $countries,
            'categorie' => $categorie,


        ));
    }

    //recuperation actualite pour chaque categorie 
    public function actualitesCategorieAction($cat,  Request $request){

        $countryId = (integer) $request->query->get('country',1);
        $em = $this->getDoctrine()->getManager();
        $countries = $em->getRepository('WebFrontBundle:Country')->findAll();
        $classeArticle = $em->getRepository('AppCoreBundle:TopArticle')->findBy(array('actif' => 1));
        $categorie = $em->getRepository('WebFrontBundle:Categorie')->findAll();
        //$categorie_id = $request->get('categorie_txt');
        $em    = $this->get('doctrine.orm.entity_manager');

        if($cat == 'Sport')
        {
            $dql   = "SELECT a FROM WebFrontBundle:Articles a WHERE a.category = 1";
            $query = $em->createQuery($dql);
        }
        elseif ( $cat == 'Musique')
        {
            $dql   = "SELECT a FROM WebFrontBundle:Articles a WHERE a.category = 2";
            $query = $em->createQuery($dql);
        }
        elseif ($cat == 'Divertissement')
        {
            $dql   = "SELECT a FROM WebFrontBundle:Articles a WHERE a.category = 3";
            $query = $em->createQuery($dql);
        }
        elseif($cat == 'High Tech')
        {
            $dql   = "SELECT a FROM WebFrontBundle:Articles a WHERE a.category = 4";
            $query = $em->createQuery($dql);
        }
        elseif($cat == 'Santé')
        {
            $dql   = "SELECT a FROM WebFrontBundle:Articles a WHERE a.category = 5";
            $query = $em->createQuery($dql);
        }


        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate($query,$request->query->getInt('page', 1) ,12);


        $entity  = new Utilisateur();
        $form = $this->createForm(new UtilisateurType(), $entity);

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
        }

        return $this->render('WebFrontBundle:HomePage:index.html.twig', array(
            'countries' => $countries,

            'headline' => 'Membres',
            'title'    => 'Ajouter un membre',
            'entity'   => $entity,
            'pagination' => $pagination,
            'categorie' => $categorie,
            'form'     => $form->createView(),
        ));
    }

    public function MessageAction($user, Request $request){
        $countryId = (integer) $request->query->get('country',1);
        $em = $this->getDoctrine()->getManager();
        $countries = $em->getRepository('WebFrontBundle:Country')->findAll();
        $articles = $em->getRepository('WebFrontBundle:Articles')->findBy(array('country'=>$countryId));
        $categories = $em->getRepository('WebFrontBundle:Categorie')->findAll();

        $rsm = new ResultSetMapping();
        $sql = "SELECT username FROM utilisateur where username != 'oct15' AND username != '".$user."'";
        $rsm = new \Doctrine\ORM\Query\ResultSetMapping;
        $rsm->addScalarResult('username', 'username');
        $membre = $em
            ->createNativeQuery($sql, $rsm)
            ->getScalarResult();


        $amis  = $em->getRepository('WebFrontBundle:Amis')->findAll();


        if($request->getMethod() == 'POST'){
            $session = $this->getRequest()->getSession();
            $session->set('user', $user);
            $foo = $session->get($user);

            $destinataire = $request->get('destinataire');
            $renvoi  = $request->get('renvoi_txt');
            $message = $request->get('message');
            $date = date("d M Y");
            $conn = $this->get('database_connection');
            $k = $conn->executeUpdate("INSERT INTO Messages(nomDestinataire, nomRenvoi, Messages, Etat, dateEnvoi)
            VALUES('".$destinataire."', '".$renvoi."', '".$message."', 1, CURDATE())");
            $conn = $this->get('database_connection');
            $delete = $conn->executeUpdate('DELETE FROM Messages where nomDestinataire = "aucun"');
        }

        $repo = $this ->getDoctrine()
            ->getManager()
            ->getRepository('WebFrontBundle:Messages');
        $qb = $repo->createQueryBuilder('m');
        $qb->select('COUNT(m)');
        $qb->where('m.nomDestinataire =:user');
        $qb->setParameter('user', $user);
        $compter = $qb->getQuery()->getSingleScalarResult();

        return $this->render('WebFrontBundle:Membre:message.html.twig', array(
            'countries' => $countries,
            'articles' => $articles,
            'categories' => $categories,
            'membre' => $membre,
            'amis' => $amis,
            'comptage' => $compter,
        ));

    }

    public function MessageListeAction($user,Request $request){
        $countryId = (integer) $request->query->get('country',1);
        $em = $this->getDoctrine()->getManager();
        $countries = $em->getRepository('WebFrontBundle:Country')->findAll();
        $articles = $em->getRepository('WebFrontBundle:Articles')->findBy(array('country'=>$countryId));
        $categories = $em->getRepository('WebFrontBundle:Categorie')->findAll();
        $messages = $em->getRepository('WebFrontBundle:Messages')->findBy(array('nomRenvoi' => $user));

        $repo = $this ->getDoctrine()
            ->getManager()
            ->getRepository('WebFrontBundle:Messages');
        $qb = $repo->createQueryBuilder('m');
        $qb->select('COUNT(m)');
        $qb->where('m.nomDestinataire =:user');
        $qb->setParameter('user', $user);
        $compter = $qb->getQuery()->getSingleScalarResult();

        return $this->render('WebFrontBundle:Membre:liste_message.html.twig',
            array(
                'countries' => $countries,
                'articles' => $articles,
                'categories' => $categories,
                'messages' => $messages,
                'comptage' => $compter,
            ));
    }

    public function NouveauMessageAction($user, Request $request){
        $rsm = new ResultSetMapping();
        $rsm = new \Doctrine\ORM\Query\ResultSetMapping;
        $countryId = (integer) $request->query->get('country',1);
        $em = $this->getDoctrine()->getManager();
        $countries = $em->getRepository('WebFrontBundle:Country')->findAll();
        $articles = $em->getRepository('WebFrontBundle:Articles')->findBy(array('country'=>$countryId));
        $categories = $em->getRepository('WebFrontBundle:Categorie')->findAll();

        // comptage nombre des messsages 
        $repo = $this ->getDoctrine()
            ->getManager()
            ->getRepository('WebFrontBundle:Messages');
        $qb = $repo->createQueryBuilder('m');
        $qb->select('COUNT(m)');
        $qb->where('m.nomDestinataire =:user');
        $qb->setParameter('user', $user);

        $compter = $qb->getQuery()->getSingleScalarResult();

        //$messages = $em->getRepository('WebFrontBundle:Messages')->findBy(array('nomRenvoi' => $user));

        $sql = "SELECT Messages,nomRenvoi,dateEnvoi FROM Messages where nomDestinataire  = '".$user."'";

        $rsm->addScalarResult('Messages', 'Messages');
        $rsm->addScalarResult('dateEnvoi', 'dateEnvoi');
        $rsm->addScalarResult('nomRenvoi', 'nomRenvoi');
        $messages = $em
            ->createNativeQuery($sql, $rsm)
            ->getScalarResult();

        return $this->render('WebFrontBundle:Membre:nouveau_message.html.twig',
            array(
                'countries' => $countries,
                'articles' => $articles,
                'categories' => $categories,
                'messages' => $messages,
                'comptage' => $compter,
            ));
    }
}