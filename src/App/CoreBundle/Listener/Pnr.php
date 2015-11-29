<?php 
namespace App\CoreBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use App\CoreBundle\Entity\Pnr as EPnr;

use Doctrine\ORM\EntityManager;
use App\CoreBundle\Entity\Passager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use App\CoreBundle\Entity\EcritureComptable;
use Doctrine\ORM\Event\PreFlushEventArgs;
use App\CoreBundle\Entity\Excedent;
use Doctrine\ORM\Event\OnFlushEventArgs;
use App\CoreBundle\Entity\Facture;

class Pnr
{
	protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
	
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
    	$em = $args->getEntityManager();
    	if ($entity instanceof EPnr){
    		$this->checkPnrStatus($entity, $em);
    		//$this->updateCost($entity, $em);
    	}
    	if ($entity instanceof EcritureComptable){
    		if(
    					(		
    							$entity->getType()->getCode()=='remboursement-billet'
    							||
    							(
    							$entity->getType()->getCode()!="transfert"
    							&& $entity->getModeEncaissement()!="libre"
    									)
    							|| 
    							(
    							$entity->getModeEncaissement()=="libre"
    							&& ( $entity->getCredit()>0 || $entity->getDebit()>0)
    							)
    					)
    			){
	    		$facture = new Facture();
	    		$facture->setEcriture($entity);
	    		$em->persist($facture);
	    		$em->flush();
	    		
	    		$entity->setFacture($facture);
	    		$em->persist($entity);
	    		$em->flush();
    		}
    	}
    }
    
    public function postUpdate(LifecycleEventArgs $args)
    {
    	//var_dump('postUpdate');
    	//exit();
    	$entity = $args->getEntity();
    	$em = $args->getEntityManager();
    	
    	
    	
    	if ($entity instanceof EPnr){
    		$this->checkPnrStatus($entity, $em);
    		//$this->updateCost($entity, $em);
    	}
    	/*if ($entity instanceof EcritureComptable){
    		$entity->setDevise($em->getRepository('AppCoreBundle:Devise')->findOneBy(array('code'=>'AR')));
    		$em->persist($entity);
    		$em->flush();
    	}*/
    }
    
    public function onFlush(OnFlushEventArgs $args)
    {
    	
    	/*$em = $args->getEntityManager();
		$uow = $em->getUnitOfWork();
		
		var_dump('EUpdates :'.count($uow->getScheduledEntityUpdates()));
		var_dump('EDeletions :'.count($uow->getScheduledEntityDeletions()));
		var_dump('EInsertions :'.count($uow->getScheduledEntityInsertions()));
		var_dump('CUpdates :'.count($uow->getScheduledCollectionUpdates()));
		var_dump('CDeletions :'.count($uow->getScheduledCollectionDeletions()));*/
		
    }
    
    public function updateCost(EPnr $entity, EntityManager $em)
    {
    	
    	if(
    		$entity->getCost() != $this->container->get('pnr_service')->getPnrCost($entity)
		){
    		$entity->setCost(
    				$this->container->get('pnr_service')->getTtcPrice(
    						$this->container->get('pnr_service')->getPnrCost($entity)
    						)
    		);
    		$em->persist($entity);
    		$em->flush();
    	}
    }
    
    public function checkPnrStatus(EPnr $entity, EntityManager $em)
    {
    	$status = true;
    	if(!sizeof($entity->getPassagers()))
    		$status = false;
    	
    	foreach($entity->getPassagers() as $passager){
    		$billet = $this->getPassagerBillet($passager, $entity, $em);	
    		if(!$billet)
    			$status = false;
    	}

    	if($status && $entity->getStatus()->getCode() != 'billet-annule' && $entity->getStatus()->getCode() != 'rembourse'){
    		$entity->setStatus($em->getRepository('AppCoreBundle:StatutPnr')->findOneBy(array('code'=>'billets-emis')));
    		$em->persist($entity);
    		$em->flush();
    	}
    	
    		
    }
    
    public function getPassagerBillet(Passager $passager, EPnr $pnr, EntityManager $em)
    {
    	$billet = $em->getRepository('AppCoreBundle:Billet')->findOneBy(array(
    			'passager'=>$passager,
    			'pnr'=>$pnr
    	));
    	
    	if($billet)
    		return $billet;
    	
    	return null;
    }
}