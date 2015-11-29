<?php

namespace App\CoreBundle\Services\Vols;

use Doctrine\ORM\EntityManager;
use App\CoreBundle\Entity\VolsNotMapped;
use App\CoreBundle\Entity\Vols as EVols;
use App\CoreBundle\Entity\Pnr;
use App\CoreBundle\Entity\PlanDeVols;
use App\CoreBundle\Entity\VolsPlanified;


/**
 * CSV Parser tool
 *
 */
class Vols
{
	/**
     *
     * @var EntityManager 
     */
    protected $em;
	
	public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }
	/**
	 * Find all Vols list
	 * @return multitype:\App\CoreBundle\Entity\VolsNotMapped
	 */
	public function findAll($options = null)
	{
		$volsByDate = array();
		$daylist = array('Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche');
		$pdvs = $this->em->getRepository('AppCoreBundle:PlanDeVols')->findByOptions($options);
		$volsID = 1; 

		foreach($pdvs as $pdv){
			$vols = $pdv->getVols();
			$days = $pdv->getDaysArray();
			foreach($vols as $singleVols){
				$period = array();
				$begin = clone $pdv->getStartAt();
				$end = clone $pdv->getEndAt();
				$end = $end->modify( '+1 day' );
				
				while($begin < $end) {
					$period[] = \DateTime::createFromFormat('d/m/Y H:i:s', $begin->format('d/m/Y H:i:s'));
					$begin->modify('+1 day');
				}
				foreach($period as $daytime){
					$dayOfTheWeek = $this->DayToNum($daytime->format("D"));
					if(in_array($daylist[$dayOfTheWeek],$days)){
						$pnr_list = $this->getAllCurrentPnr($singleVols, $daytime->format('Y-m-d'));
						$NMVols = new VolsNotMapped($singleVols);
						$NMVols->setId($volsID); $volsID++;
						$NMVols->setDate(new \DateTime($daytime->format('Y-m-d')));
						if($this->getPassagersCount($pnr_list)>$pdv->getAvion()->getNbSeating()){
							$NMVols->setNbPassagers($pdv->getAvion()->getNbSeating());
							$NMVols->setNbWaitingList($this->getPassagersCount($pnr_list) - $pdv->getAvion()->getNbSeating());
						}
						$NMVols->setNbPassagers($this->getPassagersCount($pnr_list));
						$NMVols->setNbPnr(count($pnr_list));
						$volsByDate[] = $NMVols;
					}
				}
				
			}
		}
		return $volsByDate;
	}
	
	/**
	 * 
	 * @param EVols $vols
	 * @param \Datetime $date
	 * @return \App\CoreBundle\Entity\Pnr
	 */
	public function getAllCurrentPnr(EVols $vols, $date)
	{
		return $this->em->getRepository('AppCoreBundle:Pnr')->findByOptions(array('vols'=>$vols,'owAt'=>$date));
	}
	
	public function getPassagersCount($pnr_list)
	{
		$count=0;
		foreach($pnr_list as $pnr){
			$count += count($pnr->getPassagers());
		}
		return $count;
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
	
	/**
	 * 
	 * @param PlanDeVols $entity
	 */
	public function syncVolsPlanified(PlanDeVols $entity)
	{
		$em = $this->em;
		$daylist = array('Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche');
		$days = $entity->getDaysArray();
	
		//Calcul period
    	$period = array();

    	foreach($entity->getIntervalleValidite() as $intervalle){
	    	$begin = clone $intervalle->getBeginDate();
	    	$end = clone $intervalle->getEndDate();
	    	$end = $end->modify( '+1 day' );
	    	
	    	while($begin < $end) {
	    		$period[] = \DateTime::createFromFormat('d/m/Y H:i:s', $begin->format('d/m/Y H:i:s'));
	    		$begin->modify('+1 day');
	    	}
    	
	    	//echo"<pre>";
		    //var_dump($period);
		    //exit();
			foreach($entity->getVols() as $singleVols){ 
				//Delete all unsed VP
				$volsPlanifiedListToUpdate = $em->getRepository('AppCoreBundle:VolsPlanified')->findByOptions(array('numeroVols'=>$singleVols->getNumeroVols()));
				foreach($volsPlanifiedListToUpdate as $vp){
					//TODO check if below to the period
					
					//Check if vp is deletable
					$pnr_list = $this->em->getRepository('AppCoreBundle:Pnr')->findByOptions(array('Vols'=>$vp->getVols()));
					if(!count($pnr_list)){
						$em->remove($vp);
						$em->flush();
					}
				}
	
				foreach($period as $daytime){
	    			$dayOfTheWeek = $this->DayToNum($daytime->format("D"));
	    			if(in_array($daylist[$dayOfTheWeek],$days)){
	    				$flightAt = $daytime->setTime(
	    							$singleVols->getEscaleDepart()->getAeroportAt()->format('H'), 
	    							$singleVols->getEscaleDepart()->getAeroportAt()->format('i'),
	    							0);
	    				$volsPlanifiedList = $em->getRepository('AppCoreBundle:VolsPlanified')->findByOptions(array('numeroVols'=>$singleVols->getNumeroVols(),'date'=>$flightAt));
	    				if(!count($volsPlanifiedList)){
	    					$volsPlanified = new VolsPlanified();
	    					$volsPlanified->setFlightAt($flightAt);
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
	}
}