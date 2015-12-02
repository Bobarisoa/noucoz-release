<?php

namespace App\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Request;

use App\CoreBundle\Entity\Utilisateur;
use App\CoreBundle\Form\UtilisateurType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\DisabledException;

class SecuriteController extends Controller implements KillTheBootInterface
{
    public function loginAction(Request $request)
    {
        // Redirection to dashboard if iser is authenticated
        if ($this->container->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY')){
        	
        	if(!$this->container->get('security.context')->getToken()->getUser()->getIsActive())
        		throw new DisabledException();
        	
            return $this->redirect($this->generateUrl('core_dashboard'));
        }
        
        $session = $request->getSession();
        
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        }
        
//        return $this->render('AppCoreBundle:Securite:login.html.twig', array(
//            'last_username' => $session->get(SecurityContext::LAST_USERNAME),
//            'error'         => $error,
//        ));
        return $this->render('WebFrontBundle:HomePage:index.html.twig', array(
            'last_username' => $session->get(SecurityContext::LAST_USERNAME),
            'error'         => $error,
        ));
    }
  
    // Forget form management
    public function forgetAction(Request $request)
    {
        // Prevent perfom logic below if user is authenticated, redirect to dashboard
        if ($this->container->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY')){
        	
        	if(!$this->container->get('security.context')->getToken()->getUser()->getIsActive())
        		throw new DisabledException();
        	
            return $this->redirect($this->generateUrl('core_dashboard'));
        }
        
        $entity     = new Utilisateur();
        $form       = $this->createForm(new UtilisateurType(), $entity);
        $violations = array();
        
        if ($request->getMethod() == 'POST') {
            $form->submit($request);
            $violations = $this->get('validator')->validateProperty($entity, 'email');

            if (!count($violations)) {
                $em = $this->getDoctrine()->getManager();

                $user = $em->getRepository('AppCoreBundle:Utilisateur')->findOneByEmail($form->getData()->getEmail());
                
                if ($user) {
                    $factory  = $this->get('security.encoder_factory');
                    $encoder  = $factory->getEncoder($entity);
                    $password = substr(md5(uniqid(rand(), true)), 0, 8);
                    
                    $mailer   = $this->container->getParameter('mailer');
                    
                    $user->setSalt(md5(time()));
                    $user->setPassword($encoder->encodePassword($password, $user->getSalt()));
                    
                    $em->flush();
                    
                  // Send a email with new password
                    /*$message = \Swift_Message::newInstance()
                        ->setSubject('Renouvellement de mot de passe')
                        ->setFrom($mailer['address'], $mailer['name'])
                        ->setTo($user->getEmail())
                        ->setBody($this->renderView('AppCoreBundle:Email:forget.html.twig', array(
                            'user'     => $user,
                            'password' => $password,
                        )))
                    ;
                    
                    $this->get('mailer')->send($message);
                    */
                    $strHeader1  = "MIME-Version: 1.0\n";
                    $strHeader1 .= "From: ".$mailer['address']." \n";
                    $strHeader1 .= "Content-Type: text/html; charset=utf-8\n";
                    $strHeader1 .= "X-Mailer: PHP/".phpversion()."\n";
                    	mail(
                    			$user->getEmail(),
                    			'Renouvellement de mot de passe',
                    			$this->renderView('AppCoreBundle:Email:forget.html.twig', array(
		                            'user'     => $user,
		                            'password' => $password,
		                        )),
                    			$strHeader1
                    	);
                    
                    $violations = array(
                        'status'  => 'success',
                        'message' => 'Un nouveau mot de passe vous a été envoyé !',
                    );
                } else {
                    $violations = array(
                        'message' => 'Aucun utilisateur ne correspond à cette adresse !',
                    );
                }
            }
        }

        return $this->render('AppCoreBundle:Securite:forget.html.twig', array(
            'errors' => $violations,
            'form'   => $form->createView(),
        ));
    }
    
    public function changePasswordAction(Request $request, $username){
    	/*$password = substr(md5(uniqid(rand(), true)), 0, 8);

    	$em = $this->getDoctrine()->getManager();
    	$entity     = new Utilisateur();
    	$factory  = $this->get('security.encoder_factory');
    	$encoder  = $factory->getEncoder($entity);

        $user = $em->getRepository('AppCoreBundle:Utilisateur')->findOneByUsername($username);
        if(!$user)
        		throw new \Exception("The given Username is missing at the database");
    	$user->setSalt(md5(time()));
    	$user->setPassword($encoder->encodePassword($password, $user->getSalt()));
    	
    	$em->flush();
    	
    	return $this->render('AppCoreBundle:Securite:change-password.html.twig', array(
    			'password' => $password,
    			'username'   => $username	,
    	));*/
    	
    	return new Response('Rien à voir');
    }
}
