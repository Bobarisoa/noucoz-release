<?php

namespace Web\FrontBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Web\FrontBundle\Entity\Amis;
use Hip\MandrillBundle\Message;
use Hip\MandrillBundle\Dispatcher;
use Doctrine\Common\Util\Debug;
use Web\FrontBundle\Entity\Country;
use Web\FrontBundle\Entity\Articles;
use Web\FrontBundle\Entity\Invitation;
use Doctrine\ORM\Query\ResultSetMapping;


class MembreController extends Controller
{
    public function indexAction(Request $request) {

        /*
         * Affichage des articles selon pays (articles d'un pays affich�s par d�faut)
         */
        $countryId = (integer) $request->query->get('country',1);

        $em = $this->getDoctrine()->getManager();
        $countries = $em->getRepository('WebFrontBundle:Country')->findAll();
        $articles = $em->getRepository('WebFrontBundle:Articles')->findBy(array('country'=>$countryId));
        $categories = $em->getRepository('WebFrontBundle:Categorie')->findAll();

        /*
         * Affichage des articles selon la cat�gorie s�l�ctionn�e
         */
        if($request->isMethod('POST')) {
            $category = (integer) $request->request->get('category');
            // get articles in categorie where articles.category_id=categorie.id
            $em = $this->getDoctrine()->getManager();
            $articles = $em->getRepository('WebFrontBundle:Articles')->findBy(array('category' => $category));

            return $this->render('WebFrontBundle:Membre:index.html.twig', array(
                'categories' => $categories,
                'articles' => $articles,
                'countries' => $countries
            ));
        }

        /*
        $em = $this->getDoctrine()->getManager();

        // get default articles to display
        $catId = (integer) rand(1, count($categories));
        $articleDefault = $em->getRepository('WebFrontBundle:Articles')->findBy(array('category' => $catId));

        // get query to display articles in front page

        */
        return $this->render('WebFrontBundle:Membre:index.html.twig', array(
            'countries' => $countries,
            'articles' => $articles,
            'categories' => $categories
        ));
    }

    public function listeAction(Request $request) {
        $em = $this->getDoctrine()->getManager();

        $username = $request->getSession()->get('user');

        $user = $em->getRepository('AppCoreBundle:Utilisateur')->findOneBy(array('username' => $username));
        $amis = $em->getRepository('WebFrontBundle:Invitation')->findBy(array('utilisateur' => $user, 'etat' => 1));

        $members = $em->createQuery('SELECT u FROM AppCoreBundle:Utilisateur u WHERE u.username != :username AND u.username != :admin')
                ->setParameter('username', $username)
                ->setParameter('admin', 'oct15')
                ->getResult();

        $friendRequests = $em->getRepository('WebFrontBundle:Invitation')->findBy(array('utilisateur' => $user));
        $notInvitable = array();
        $notInvitable[0] = $user;
        $i = 1;
        foreach ($friendRequests as $ufr) {
            $notInvitable[$i] = $ufr->getAmi();
            $i++;
        }

        $pendingRequest = $em->getRepository('WebFrontBundle:Invitation')->findBy(array('ami' => $user, 'etat' => 0));

        $invitables = array_diff($members, $notInvitable);

        return $this->render('WebFrontBundle:Membre:liste.html.twig', array(
            'countries' => '',
            'articles' => '',
            'categories' => '',
            'amis' => $amis,
        ));
    }

     public function attenteAction(Request $request) {
        $em =  $em = $this->getDoctrine()->getManager();

        $username = $request->getSession()->get('user');

        $user = $em->getRepository('AppCoreBundle:Utilisateur')->findOneBy(array('username' => $username));
        $pendingRequest = $em->getRepository('WebFrontBundle:Invitation')->findBy(array('ami' => $user, 'etat' => 0));

        return $this->render('WebFrontBundle:Membre:attente.html.twig', array(
            'countries' => '',
            'articles' => '',
            'categories' => '',
            'pendingRequests' => $pendingRequest
        ));

    }

     public function inviteAction(Request $request) {
        $em = $this->getDoctrine()->getManager();

        $username = $request->getSession()->get('user');

        $user = $em->getRepository('AppCoreBundle:Utilisateur')->findOneBy(array('username' => $username));
        $amis = $user->getMyFriends();

        $members = $em->createQuery('SELECT u FROM AppCoreBundle:Utilisateur u WHERE u.username != :username AND u.username != :admin')
            ->setParameter('username', $username)
            ->setParameter('admin', 'oct15')
            ->getResult();

        // select all Invitation:user where i.utilisateur=user or i.ami=user
        $notInv = $em->createQuery('SELECT i FROM WebFrontBundle:Invitation i WHERE i.utilisateur = :utilisateur OR i.ami = :ami')
            ->setParameter('utilisateur', $user)
            ->setParameter('ami', $user)
            ->getResult();


        //$friendRequests = $em->getRepository('WebFrontBundle:Invitation')->findBy(array('utilisateur' => $user));

        $notInvitable = array();
        //$notInvitable[0] = $user;
        $i = 0;
        foreach ($notInv as $ufr) {
            $notInvitable[$i] = $ufr->getAmi();
            $i++;
        }

        $j = count($notInvitable);

        foreach ($notInv as $urf) {
            $j++;
            $notInvitable[$j] = $urf->getUtilisateur();
        }


        //$pendingRequest = $em->getRepository('WebFrontBundle:Invitation')->findBy(array('ami' => $user));

        $invitables = array_diff($members, $notInvitable);

        //$invitables = array_diff($invitablesTemp, $pendingRequest);

        return $this->render('WebFrontBundle:Membre:invite.html.twig', array(
            'countries' => '',
            'articles' => '',
            'categories' => '',
            'invitables' => $invitables,
        ));
    }

     public function envoyeAction(Request $request) {

        $em = $this->getDoctrine()->getManager();

        $username = $request->getSession()->get('user');

        $user = $em->getRepository('AppCoreBundle:Utilisateur')->findOneBy(array('username' => $username));
        $demandes = $em->getRepository('WebFrontBundle:Invitation')->findBy(array('utilisateur' => $user, 'etat' => 0));

        return $this->render('WebFrontBundle:Membre:envoye.html.twig', array(
            'countries' => '',
            'articles' => '',
            'categories' => '',
            'demandes' => $demandes,
        ));
    }

    public function confirmerAction(Request $request, $id) {
        $username = $request->getSession()->get('user');

        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('AppCoreBundle:Utilisateur')->findOneBy(array('username' => $username));
        $invitation = $em->getRepository('WebFrontBundle:Invitation')->find($id);

        if (!$invitation) {
            throw $this->createNotFoundException(
                'No invitation found for id '.$id
            );
        }
        $invitation->setEtat(1);
        $em->flush();

        $secondConfirm = new Invitation();
        $secondConfirm->setUtilisateur($user);
        $secondConfirm->setAmi($invitation->getUtilisateur());
        $secondConfirm->setEtat(1);

        $em->persist($secondConfirm);
        $em->flush();

        return $this->redirect($this->generateUrl('liste'));

    }

    public function ajouterAction($id, Request $request) {
        $invitation = new Invitation();

        $em = $this->getDoctrine()->getManager();

        $username = $request->getSession()->get('user');
        $sender = $em->getRepository('AppCoreBundle:Utilisateur')->findOneBy(array('username' => $username));
        $receiver = $em->getRepository('AppCoreBundle:Utilisateur')->find($id);

        $invitation->setUtilisateur($sender);
        $invitation->setAmi($receiver);
        $invitation->setEtat(0);

        $em->persist($invitation);
        $em->flush();

        return $this->redirect($this->generateUrl('invite'));

    }

    public function poursuivreAction($id, Request $request){
            $countryId = (integer) $request->query->get('country',1);
            //$amis = new Amis();
            $em = $this->getDoctrine()->getManager();
            $conn = $this->get('database_connection');
            $update = $conn->executeUpdate("UPDATE Amis SET poursuivre =1 WHERE user='".$id."'");              
            
           
            $etat = $em->getRepository('WebFrontBundle:Amis')->findOneBy(array('User' => $id));

            
           
            $countries = $em->getRepository('WebFrontBundle:Country')->findAll();
            $articles = $em->getRepository('WebFrontBundle:Articles')->findBy(array('country'=>$countryId));
            $categories = $em->getRepository('WebFrontBundle:Categorie')->findAll();

            $membre = $em->getRepository('AppCoreBundle:Utilisateur')->findAll();

            return $this->render('WebFrontBundle:Membre:liste.html.twig', array(
                'countries' => $countries,
                'articles' => $articles,
                'categories' => $categories,
                'membre' => $membre,
                'etat' => $etat
            ));
    }

    public function profilAction($user){
         $em = $this->getDoctrine()->getManager();
         $profil = $em->getRepository('AppCoreBundle:Utilisateur')
            ->findOneBy(array('username' => $user));
         $countries = $em->getRepository('WebFrontBundle:Country')->findAll();
         return $this->render('WebFrontBundle:Membre:profil.html.twig',array(
                'countries' => $countries,
                'profil' => $profil
            ));
    }

    public function checkLoginAction(){
        $em = $this->getDoctrine()->getManager();
        $profil = $em->getRepository('AppCoreBundle:Utilisateur');
        $request = $this->get('request');
        if($request->getMethod() == 'POST'){
            $username =  $request->get('_username');
            $password =  $request->get('_password');
            $pass_transf = md5($password);
            $profil = $em->getRepository('AppCoreBundle:Utilisateur')->findOneBy(array(
             'username' => $username, 'password' => $pass_transf));
            
            $rsm = new ResultSetMapping();
			$sql = "SELECT username, password FROM utilisateur where username = '".$username."'
            AND password = '".$pass_transf."'";
			$rsm = new \Doctrine\ORM\Query\ResultSetMapping;
			$rsm->addScalarResult('username', 'username');
            $rsm->addScalarResult('password', 'password');
			$result = $em
			->createNativeQuery($sql, $rsm)
			->getScalarResult();
            
            $messages = "login ou mot de passe incorrecte !";
            $info = "Veuillez retaper votre login et mot de passe pour de raison de sécurité";
            if(empty($result)){
               return $this->render('WebFrontBundle:Inscription:connexion_echec.html.twig', array('messages' => $messages)); 
            }
            else{
                return $this->render('WebFrontBundle:Inscription:connexion.html.twig',array('profil' => $profil, 'info' => $info ));  
            }
            
           
            
            
            /*if($username == $profil->getUsername()){
                echo 'OK';
            }
            else
            {
                echo 'FALSE';    
            }
            */
            
              //$pass = $profil->getPassword();
              
              //if(isset($user) AND isset($pass)){
                //return $this->render('WebFrontBundle:Inscription:connexion.html.twig',array('profil' => $profil));
             // }
              
        }
    }

    public function loginAction(){
        $em = $this->getDoctrine()->getManager();
        $request = $this->get('request');
      if($request->getMethod() == 'POST'){
             $username =  $request->get('_username');
             $password =  $request->get('_password');
             $pass_transf = md5($password);
             
             $profil = $em->getRepository('AppCoreBundle:Utilisateur')->findOneBy(array(
             'username' => $username, 'password' => $pass_transf));
             
             $rsm = new ResultSetMapping();
			 $sql = "SELECT username, password FROM utilisateur where username = '".$username."'
             AND password = '".$pass_transf."'";
			 $rsm = new \Doctrine\ORM\Query\ResultSetMapping;
			 $rsm->addScalarResult('username', 'username');
             $rsm->addScalarResult('password', 'password');
			 $result = $em
			->createNativeQuery($sql, $rsm)
			->getScalarResult();
          
             //session 
             $session = $this->getRequest()->getSession();

             if($username == 'oct15'  AND  $pass_transf == md5('alpha')){
                $session->set('user', $username);
                return $this->redirect($this->generateUrl('core_dashboard'));
             }
             
             $messages = "login ou mot de passe incorrecte !";
             if(empty($result)){
                return $this->render('WebFrontBundle:Inscription:connexion_echec.html.twig', array('messages' => $messages)); 
             }
             else
             {
                 $countryId = (integer) $request->query->get('country',1);
            
                $em = $this->getDoctrine()->getManager();
                $foo = $session->get('user');

                $countries = $em->getRepository('WebFrontBundle:Country')->findAll();
                $articles = $em->getRepository('WebFrontBundle:Articles')->findBy(array('country'=>$countryId));
                $categories = $em->getRepository('WebFrontBundle:Categorie')->findAll();
              
                $session->set('user', $username);
                $foo = $session->get('user');
                return $this->render('WebFrontBundle:Membre:index.html.twig', array(
                    'profil' => $profil,
                    'categories' => $categories,
                    'articles' => $articles,
                    'countries' => $countries
                ));
             }

        }
        

    }


	
    
    public function deconnexionAction(){
        $this->get('session')->clear();

    }

    public function modifierAction($user){
        $request = $this->get('request');
        if($request->getMethod() == 'POST'){
            $code =  $request->get('code');
            $lastname =  $request->get('lastname');
            $firstname =  $request->get('firstname');
            $email  =  $request->get('email');
            $phone =  $request->get('phone');
            $em = $this->getDoctrine()->getManager();
            $conn = $this->get('database_connection');
            $update = $conn->executeUpdate("UPDATE utilisateur SET firstname = '".$firstname."', lastname ='".$lastname."',
            code = '".$code."', email = '".$email."', phone = '".$phone."' WHERE username='".$user."'");
            if($update){
                $em = $this->getDoctrine()->getManager();
                $profil = $em->getRepository('AppCoreBundle:Utilisateur')->findOneBy(array('username' => $user));
                $countries = $em->getRepository('WebFrontBundle:Country')->findAll();
                return $this->render('WebFrontBundle:Membre:profil.html.twig',array(
                    'countries' => $countries,
                   'profil' => $profil
                )); 
            }
        }    
    }

    public function confidentialiteAction(Request $request){
        $countryId = (integer) $request->query->get('country',1);
        $em = $this->getDoctrine()->getManager();
        $countries = $em->getRepository('WebFrontBundle:Country')->findAll();
        $articles = $em->getRepository('WebFrontBundle:Articles')->findBy(array('country'=>$countryId));
        $categories = $em->getRepository('WebFrontBundle:Categorie')->findAll();

        $membre = $em->getRepository('AppCoreBundle:Utilisateur')->findAll();
        

        return $this->render('WebFrontBundle:Membre:confidentialite.html.twig', array(
            'countries' => $countries,
            'articles' => $articles,
            'categories' => $categories,
            'membre' => $membre
        ));
     
    }

   
    public function updateConfidentialiteAction($user ,Request $request){
        $countryId = (integer) $request->query->get('country',1);
        $em = $this->getDoctrine()->getManager();

        $conn = $this->get('database_connection');
        $update = $conn->executeUpdate("UPDATE utilisateur set confidentialite = 'active' where username = '".$user."'");
        $countries = $em->getRepository('WebFrontBundle:Country')->findAll();
        $articles = $em->getRepository('WebFrontBundle:Articles')->findBy(array('country'=>$countryId));
        $categories = $em->getRepository('WebFrontBundle:Categorie')->findAll();
        $membre = $em->getRepository('AppCoreBundle:Utilisateur')->findAll();
        return $this->render('WebFrontBundle:Membre:confidentialite.html.twig', array(
            'countries' => $countries,
            'articles' => $articles,
            'categories' => $categories,
            'membre' => $membre
        ));
    }

    public function posterAction($user, Request $request){
            $countryId = (integer) $request->query->get('country',1);
            $em = $this->getDoctrine()->getManager();
            $countries = $em->getRepository('WebFrontBundle:Country')->findAll();
            $articles = $em->getRepository('WebFrontBundle:Articles')->findBy(array('country'=>$countryId));
            $categories = $em->getRepository('WebFrontBundle:Categorie')->findAll();


            $titre = $request->get('titre_txt');
            $source = $request->get('source_txt');
            $categorie = $request->get('categorie_txt');
            $contenu = $request->get('contenu_txt');
            $auteur = $request->get('auteur_txt');
            $photo = $_FILES['photo_txt']['name'];                                                  
            foreach($request->files as $uploadedFile){
            
                $root = $_SERVER['DOCUMENT_ROOT'];
                //$file = $uploadedFile->move($root.'noucoze-dev/web/template/img/', $photo);
               $file = $uploadedFile->move($root.'/web/template/img/', $photo);
            }   
                   
           
            $session = $this->getRequest()->getSession();
            $session->set('user', $user);
            $foo = $session->get($user);
           
            $date = date('d M Y');
            $conn = $this->get('database_connection');
            $add = $conn->executeUpdate("INSERT INTO article_noucoze(classe_id,country_id,category_id,titre,source,contenu,auteur,photo,notification,datePublication,publication,type)VALUES
            (1, 1, '".$categorie."', '".$titre."', '".$source."', '".$contenu."', '".$auteur."', '".$photo."', 'notification', '".$date."', '".$user."', 2)");
            
            $liste = $em->getRepository('WebFrontBundle:Articles')->findBy(
            array('publication' => $user));
            
            if($add){
                return $this->render('WebFrontBundle:Membre:liste_article.html.twig', 
                array(
                    'countries' => $countries,
                    'articles' => $articles,
                    'liste' => $liste,
                    'categories' => $categories,
                ));
            }
    }


    public function posterArticleAction($user, Request $request)
    {
       
        $countryId = (integer) $request->query->get('country',1);
        $em = $this->getDoctrine()->getManager();
        $countries = $em->getRepository('WebFrontBundle:Country')->findAll();
        $articles = $em->getRepository('WebFrontBundle:Articles')->findBy(array('country'=>$countryId));
        $categories = $em->getRepository('WebFrontBundle:Categorie')->findAll();

        $membre = $em->getRepository('AppCoreBundle:Utilisateur')->findAll();
        $amis = $em->getRepository('WebFrontBundle:Amis')->findAll();
       
            /*$advert = new Articles();
            $formBuilder = $this->get('form.factory')->createBuilder('form', $advert);
            $formBuilder
                ->add('contenu', 'ckeditor', array (
                'label'             => 'Contenu',
                'config_name'       => 'my_custom_config',
                'config' => array(
                    'language'    => 'fr'),
                ));
            $form = $formBuilder->getForm();
            */
            $articles = new Articles();
            $formBuilder = $this->get('form.factory')->createBuilder('form', $articles);
            $formBuilder->add('photo', 'file');
            $form = $formBuilder->getForm();

            return $this->render('WebFrontBundle:HomePage:poster.html.twig', array(
            //'form' => $form->createView(),
            'countries' => $countries,
            'articles' => $articles,
            'categories' => $categories,
            'membre' => $membre,
            'amis' => $amis,
            'form' => $form->createView(),

         ));              
        
        
    }

    public function updatePublicationAction($user, $id, Request $request){
        $countryId = (integer) $request->query->get('country',1);
        $em = $this->getDoctrine()->getManager();
        $countries = $em->getRepository('WebFrontBundle:Country')->findAll();
        $articles = $em->getRepository('WebFrontBundle:Articles')->findBy(array('country'=>$countryId));
        $categories = $em->getRepository('WebFrontBundle:Categorie')->findAll();

        $conn  = $this->get('database_connection');
        $query = $conn->executeUpdate('UPDATE article_noucoze set etatPublication = 1 WHERE id = "'.$id.'"');
        $liste = $em->getRepository('WebFrontBundle:Articles')->findBy(
            array('publication' => $user));
        if($query){
           return $this->render('WebFrontBundle:Membre:liste_article.html.twig', 
                array(
                    'countries' => $countries,
                    'articles' => $articles,
                    'liste' => $liste,
                   'categories' => $categories,
                ));
        }
    }

    public function updateBrouillonAction($user, $id, Request $request){

    }
    public function publierArticleAction($user, Request $request){
        $countryId = (integer) $request->query->get('country',1);
        $em = $this->getDoctrine()->getManager();
        $countries = $em->getRepository('WebFrontBundle:Country')->findAll();
        $articles = $em->getRepository('WebFrontBundle:Articles')->findBy(array('country'=>$countryId));
        $categories = $em->getRepository('WebFrontBundle:Categorie')->findAll();
        $liste = $em->getRepository('WebFrontBundle:Articles')->findBy(
            array('publication' => $user));
        if($liste){
           return $this->render('WebFrontBundle:Membre:liste_article.html.twig', 
                array(
                    'countries' => $countries,
                    'articles' => $articles,
                    'liste' => $liste,
                   'categories' => $categories,
                ));
        }
    }
   

    public function brouillonsArticleAction($user, Request $request){
        $countryId = (integer) $request->query->get('country',1);
        $em = $this->getDoctrine()->getManager();
        $countries = $em->getRepository('WebFrontBundle:Country')->findAll();
        $articles = $em->getRepository('WebFrontBundle:Articles')->findBy(array('country'=>$countryId));
        $categories = $em->getRepository('WebFrontBundle:Categorie')->findAll();
        $conn  = $this->get('database_connection');
        $liste = $em->getRepository('WebFrontBundle:Articles')->findBy(
            array('publication' => $user));
        if($liste){
            return $this->render('WebFrontBundle:Membre:brouillons_article.html.twig', 
                array(
                    'countries' => $countries,
                    'articles' => $articles,
                    'liste' => $liste,
                   'categories' => $categories,
                ));   
        }

    }

    public function acceuilPageAction(Request $request){
        $countryId = (integer) $request->query->get('country',1);
        $em = $this->getDoctrine()->getManager();
        $countries = $em->getRepository('WebFrontBundle:Country')->findAll();
        $articles = $em->getRepository('WebFrontBundle:Articles')->findBy(array('country'=>$countryId));
        $categories = $em->getRepository('WebFrontBundle:Categorie')->findAll();

        return $this->render('WebFrontBundle:Membre:home.html.twig',
         array(   
            'categories' => $categories,
            'articles' => $articles,
            'countries' => $countries
        ));
        
    }


}