<?php 
namespace App\CoreBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use App\CoreBundle\Entity\PlanDeVols as EPlanDeVols;
use App\CoreBundle\Entity\VolsPlanified;
use App\CoreBundle\Entity\Vols;
use Doctrine\ORM\EntityManager;

class PlanDeVols
{
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
    	$em = $args->getEntityManager();
    	if ($entity instanceof EPlanDeVols)
    		$this->syncVolsPlanified($entity, $em);
    }
    
    public function postUpdate(LifecycleEventArgs $args)
    {
    	$entity = $args->getEntity();
    	$em = $args->getEntityManager();
    	if ($entity instanceof EPlanDeVols)
    		$this->syncVolsPlanified($entity, $em);
    }
    
    /**
     * 
     * @param unknown $entity
     * @param EntityManager $em
     */
    public function syncVolsPlanified(EPlanDeVols $entity, EntityManager $em)
    	{
    	
    		$daylist = array('Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche');
    		$days = $entity->getDaysArray();
    		
    		//Calcul period
    		$begin = $entity->getStartAt();
    		$end = $entity->getEndAt();
    		$end = $end->modify( '+1 day' );
    		$period = array();
    		while($begin < $end) {
    			$period[] = \DateTime::createFromFormat('d/m/Y H:i:s', $begin->format('d/m/Y H:i:s'));
    			$begin->modify('+1 day');
    		}
    		
    		foreach($entity->getVols() as $singleVols){
    			
    			//$singleVols = new Vols();
    			
    			
    			
    			
    			//Delete all unsed VP
    			$volsPlanifiedListToUpdate = $em->getRepository('AppCoreBundle:VolsPlanified')->findByOptions(array('numeroVols'=>$singleVols->getNumeroVols()));
    			foreach($volsPlanifiedListToUpdate as $vp){
    				//TODO check if below to the period
		    			$em->remove($vp);
		    			$em->flush();
    			}

    			
    			
    			
    			foreach($period as $daytime){
    				$dayOfTheWeek = $this->DayToNum($daytime->format("D"));
    				if(in_array($daylist[$dayOfTheWeek],$days)){
    					$flightAt = $daytime->setTime(
    							$singleVols->getEscaleDepart()->getAeroportAt()->format('H'), 
    							$singleVols->getEscaleDepart()->getAeroportAt()->format('i'),
    							0);
    					//var_dump($daytime->format("D, d/m"),$singleVols->getNumeroVols());
    					$volsPlanifiedList = $em->getRepository('AppCoreBundle:VolsPlanified')->findByOptions(array('numeroVols'=>$singleVols->getNumeroVols(),'date'=>$flightAt));
    					if(!count($volsPlanifiedList)){
	    					$volsPlanified = new VolsPlanified();
	    					$volsPlanified->setFlightAt($flightAt );
	    					$volsPlanified->setStatus(
	    							$em->getRepository('AppCoreBundle:StatutVols')
	    							->findOneBy(array('code'=>'vols-pret')));
	    					$singleVols->addVolsPlanified($volsPlanified);
	    					$em->persist($singleVols);
	    					$em->flush();
    					}
    				}
    			}
    	}
    }
    
	public function DayToNum($Day)
    {
    	if ($Day=="Mon" || $Day=="Lun") return 0;
    	elseif ($Day=="Tue" || $Day=="Mar") return 1;
    	elseif ($Day=="Wed" || $Day=="Mer") return 2;
    	elseif ($Day=="Thu" || $Day=="Jeu") return 3;
    	elseif ($Day=="Fri" || $Day=="Ven") return 4;
    	elseif ($Day=="Sat" || $Day=="Sam") return 5;
    	elseif($Day=="Sun" || $Day=="Dim") return 6;
    }
}