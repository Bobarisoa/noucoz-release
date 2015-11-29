<?php

namespace App\CoreBundle\Services\Pnr;

use Doctrine\ORM\EntityManager;

use App\CoreBundle\Entity\Pnr as EPnr;
use App\CoreBundle\Entity\SiegePassager;
use App\CoreBundle\Entity\Passager;
use App\CoreBundle\Entity\VolsTarifaire;
use App\CoreBundle\Entity\Vols;
use App\CoreBundle\Entity\Billet;
use App\CoreBundle\Entity\Tarif;
use App\CoreBundle\Entity\Devise;
use App\CoreBundle\Entity\Tarifaires;
use App\CoreBundle\Entity\VolsPlanified;
use Doctrine\Common\Collections\ArrayCollection;
use App\CoreBundle\Entity\BoardingPass;
use App\CoreBundle\Entity\IntervalleValidite;
use App\CoreBundle\Entity\Periode;
use App\CoreBundle\Entity\PlanDeVols;
use App\CoreBundle\Entity\TypePassager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;


/**
 * CSV Parser tool
 *
 */
class Core
{
	/**
     *
     * @var EntityManager 
     */
    protected $em;
    
    protected $container;
	
	public function __construct(EntityManager $entityManager, ContainerInterface $serviceContainer)
    {
        $this->em = $entityManager;
        
        $this->container = $serviceContainer;
    }
    
    /**
     * @deprecated use getVolsModificationCost instead
     * Calcul cout de modification
     * 
     * @param EPnr $pnr
     * @return number
     */
    public function calculModificationCost(EPnr $pnr)
    {
    	return $this->calculCurrency($this->getModificationAmount($pnr),$pnr->getDevise());
    }
    
    /**
     * getModificationAmount
     * @param float $price
     * @return number
     */
    public function getModificationAmount(EPnr $pnr)
    {
    	$res = 0;
    	$price = (float) $pnr->getCost();
    	$penalities = $this->em->getRepository('AppCoreBundle:Penality')->findBy(array('code'=>'frais-de-modification'));
    	foreach($penalities as $penality){
    		if($penality->getPercentPrice())
    			$res += ($price * $penality->getPercentPrice())/100;
    		if($penality->getStaticPrice())
    			$res += ($penality->getStaticPrice());
    	}
    	return $res;
    }
    
    /**
     * Calcul cout de remboursement
     * @param EPnr $pnr
     * @return number
     */
    public function calculRepayCost(EPnr $pnr)
    {
    	$cost = 0;
    	
    	foreach($pnr->getVolsTarifaire() as $vt){
    		if($vt->isRemboursableAvecFrais()){
	    		$tarif = $this->getVolsTarif(
	    				$vt->getVolsPlanified()->getVols(),
	    				$vt->getVolsPlanified()->getFlightAt()
	    		);
	    		$method = 'getOwByTypePassager';
	    		if($vt->getDirection()=='RT')
	    			$method = 'getRtByTypePassager';
	    		foreach($vt->getPassagers() as $passager){
	    			$passagerCost = 0;
	    			$passagerCost += $tarif->{$method}($passager->getTypePassager(), $vt->getTarifaire(),$vt->getVolsPlanified());
	    			$passagerCost += $this->getExcedentCost($passager, $vt);
	    			$cost += $this->calculCurrency($this->getRepayAmount($this->getTtcPrice($passagerCost,$vt,$passager->getTypePassager())),$pnr->getDevise());
			    //echo "<pre>";
	    		//var_dump($this->getRepayAmount($passagerCost));
	    		}
    		}	
    	}
    	
    	if($vt->isRemboursableSansFrais() && $pnr->getEcritures()->first()){
    			$cost = $this->calculCurrency($pnr->getEcritures()->first()->getCredit(), $pnr->getDevise());
    	}
    	return $cost;
    }
    
    /**
     * getRepayAmount
     * @param float $price
     * @return number
     */
    public function getRepayAmount($price)
    {
    	
    	$res = 0;
    	$price = (float) $price;
    	$penalities = $this->em->getRepository('AppCoreBundle:Penality')->findBy(array('code'=>'frais-de-remboursement'));
    	
    	foreach($penalities as $penality){
    		
    		if($penality->getPercentPrice() > 0)
    			$res += ($price * $penality->getPercentPrice())/100;
    		if($penality->getStaticPrice()){
    			if($price > $penality->getStaticPrice())
    				$res += ($price - $penality->getStaticPrice());
    		}
    	}
    	//echo "<pre>";
    	//var_dump("$res");
    	return $res;
    }
	
    /**
     * Get cout PNR
     * @param EPnr $pnr
     * @return number
     */
	public function getPnrCost(EPnr $pnr)
    {
    	$cost = 0;
    	foreach($pnr->getVolsTarifaire() as $vt)
    		$cost += $this->getVolsTarifaireCost($vt, $pnr,array('ttc'=>true));
    	
    	return $cost;
    }
    
    /**
     * Get ecriture compta total
     * @param EPnr $pnr
     * @return number
     */
    public function getPnrPayed(EPnr $pnr)
    {
    	$cost = 0;
    	foreach($pnr->getEcritures() as $ecriture){
    
    		if($ecriture->getCredit())
    			$cost += $ecriture->getCredit();
    		if($ecriture->getDebit())
    			$cost -= $ecriture->getDebit();
    	}
    	return $cost;
    }
    
    /**
     * Get cout excedent
     * @param Passager $passager
     * @param VolsTarifaire $vt
     * @return number
     */
    public  function getExcedentCost(Passager $passager, VolsTarifaire $vt)
    {
    	$excedent = $vt->getPnr()->getOneExcedent($passager, $vt);
    	if ($excedent)
    		return $excedent->getPoids() * 3000; //TODO Store excedent price to Tarif
    	return 0;
    }
    
    /**
     * getVolsTarif
     * @param Vols $vols
     * @return Tarif
     */
    public function getVolsTarif(Vols $vols, \DateTime $departAt)
    {
    	return $this->em->getRepository('AppCoreBundle:Tarif')->findOneByOptions(array(
    			'aeroportDepart'=>$vols->getEscaleDepart()->getAeroport()->getId(),
    			'aeroportArrivee'=>$vols->getEscaleArrivee()->getAeroport()->getId(),
    			'departAt'=>  $departAt
    	));
    }
    
    /**
     * getDevise
     * @param VolsTarifaire $volsTarifaire
     * @return Devise | NULL
     */
    public function getDevise(VolsTarifaire $volsTarifaire){
    	if($volsTarifaire->getPnr()->getDevise())
    		return $volsTarifaire->getPnr()->getDevise();
    	$tarif = $this->getVolsTarif($volsTarifaire->getVolsPlanified()->getVols(), $volsTarifaire->getVolsPlanified()->getFlightAt());
    	if($tarif)
    		return $tarif->getDevise();
    	return null;
    }
    
    /**
     * Get price TTC
     * @param float $price
     * @param VolsTarifaire | Tarifaires $price
     * @return number
     */
    public function getTtcPrice($price, $object = null, TypePassager $typePassager)
    {
    	
    	$res = 0;
    	$price = (float) $price;
    	$options = array('isActive'=>true);
    	$tarifaire = null;
    	if($object instanceof VolsTarifaire){
    		$tarifaire = $object->getTarifaire();
    		$taxes = $object->getTarifaire()->getTaxes();
    	}
    	if($object instanceof Tarifaires){
    		$options['tarifaire'] = $object->getId();
    		$tarifaire = $object;
    		$taxes = $this->em->getRepository('AppCoreBundle:Taxe')->findByOptions($options);
    	}
    	
    	
    	foreach($taxes as $taxe){
    		//var_dump($taxe->getCode());
    		if($taxe->getPercentPrice())
    			$res += ((($price * $taxe->getPercentPrice())/100)
    					*$this->getTaxeByTypePassager( $typePassager, $tarifaire))/100;
    		if($taxe->getStaticPrice())
    			$res += ($taxe->getStaticPrice()
    					*$this->getTaxeByTypePassager( $typePassager, $tarifaire))/100;
    	}
    	
    	return $res + $price;
    }
    
    public function getTaxeByTypePassager(TypePassager $typePassager, Tarifaires $tarifaire){
    	$rulesByTypePassager = $tarifaire->getTypePassagerByName($typePassager->getName());
    	return $rulesByTypePassager->getPercentTaxePrice();
    }
    
    /**
     * Ajout les sièges passagers automatiquement
     * @param EPnr $pnr
     */
    public function setSiegesPassagers(EPnr $pnr){
    	foreach($pnr->getVolsTarifaire()as $vt){
    		$vp = $vt->getVolsPlanified();
	    	foreach($vt->getPassagers() as $passager){
	    		if($passager->getTypePassager()->getCode()!='bebe'){
		    		$siegePassager = $this->em->getRepository('AppCoreBundle:SiegePassager')->findOneBy(array(
		    			'passager'=>$passager,
		    			'volsPlanified'=>$vp
		    		));
		    		if(!$siegePassager){
		    			$sieges = $this->em->getRepository('AppCoreBundle:Siege')->findUsingVols($vp->getVols());
		    			foreach($sieges as $siege){
		    				$siegePassager = $this->em->getRepository('AppCoreBundle:SiegePassager')->findOneBy(array(
		    						'siege'=>$siege,
		    						'volsPlanified'=>$vp
		    				));
		    				if(!$siegePassager){
		    					$siegePassager = new SiegePassager();
		    					$siegePassager->setPassager($passager);
		    					$siegePassager->setSiege($siege);
		    					$siegePassager->setVolsPlanified($vp);
		    					$this->em->persist($siegePassager);
		    					$this->em->flush();
		    					break;
		    				}
		    			}
		    		}
	    		}
	    	}
    	}
    }
    
    /**
     * Set Status to billet-emit if needed
     * @param EPnr $pnr
     */
    public function checkPnrStatus(EPnr $pnr){
    	//echo "<pre>";
    	//Check Billet emis
    	$status = true;
    	foreach($pnr->getPassagers() as $passager){
    		if(!$this->getPassagerBillet($passager, $pnr))
	    		$status = false;
    	}
    	//var_dump("is billet emis=>$status");
    	if($status){
    		$pnr->setStatus($this->em->getRepository('AppCoreBundle:StatutPnr')->findOneBy(array('code'=>'billets-emis')));
    		$this->em->persist($pnr);
    		$this->em->flush();
    	}
    	//Check NoShow OW
    	if(
    			$pnr->getStatus()->getOrder() > 1
    			&& $pnr->getStatus()->getOrder() < 4 
    			&& $pnr->getStatus()->getCode()!='billets-noshow-ow'){
    		if($this->isPnrNoShowOw($pnr)){
    			//var_dump("isPnrNoShowOw=>".$this->isPnrNoShowOw($pnr));
    			$pnr->setStatus(
    					$this->em->getRepository('AppCoreBundle:StatutPnr')->findOneBy(array(
    							'code'=>'billets-noshow-ow'
    					))
    			);
    			$this->em->persist($pnr);
    			$this->em->flush();
    		}
    	}
    	
    	//Check NoShow
    	if($pnr->getStatus()->getOrder() < 4 && $pnr->getStatus()->getCode()!='billets-noshow'){
    		if($this->isPnrNoShow($pnr)){
    			//var_dump("isPnrNoShow=>".$this->isPnrNoShow($pnr));
    			$pnr->setStatus(
    					$this->em->getRepository('AppCoreBundle:StatutPnr')->findOneBy(array(
    							'code'=>'billets-noshow'
    					))
    			);
    			$this->em->persist($pnr);
    			$this->em->flush();
    		}
    	}
    	
    	//Check Consommé
    	if($pnr->getStatus()->getOrder() < 4 && $pnr->getStatus()->getCode()!='billets-consomme'){
    		if($this->isBilletConsomme($pnr)){
    			//var_dump("isBilletConsomme=>".$this->isBilletConsomme($pnr));
    			$pnr->setStatus(
    					$this->em->getRepository('AppCoreBundle:StatutPnr')->findOneBy(array(
    							'code'=>'billets-consomme'
    					))
    			);
    			$this->em->persist($pnr);
    			$this->em->flush();
    		}
    	}
    	
    	//Check Expiré
    	if($pnr->getStatus()->getOrder() < 4 && $pnr->getStatus()->getCode()!='billets-expire'){
    		if($this->isPnrExpired($pnr)){
    			$pnr->setStatus(
    					$this->em->getRepository('AppCoreBundle:StatutPnr')->findOneBy(array(
    							'code'=>'billets-expire'
    					))
    			);
    			$this->em->persist($pnr);
    			$this->em->flush();
    		}
    	}
    	
    }
    
    public function isPnrNoShowOw(EPnr $pnr){
		$noshowOw = false;
    	foreach($pnr->getVolsTarifaire() as $key=>$vt){
    		$vp = $vt->getVolsPlanified();
    		if($vp->getStatus()->getOrder()>5){
    			foreach($vt->getPassagers() as $passager){
    				$bp = $this->getPassagerBoardingPass($passager, $vp);
    				if($key==0 &&  (!$bp || ($bp &&  !$bp->getIsOnboard())))
    					$noshowOw = true;
    				if($noshowOw && $key && (!$bp || ($bp &&  !$bp->getIsOnboard())))
    					$noshowOw = false;
    			}
    		}
    	}
    	 
    		return $noshowOw;
    }
    
    public function isPnrNoShow(EPnr $pnr){
    	$noshow = array();
    	foreach($pnr->getVolsTarifaire() as $vt){
    		$vp = $vt->getVolsPlanified();
    		if($vp->getStatus()->getOrder()>5){
	    		foreach($vt->getPassagers() as $passager){
	    			$bp = $this->getPassagerBoardingPass($passager, $vp);
	    			if(!$bp || ($bp &&  !$bp->getIsOnboard()))
	    				$noshow[] = true;
	    		}
    		}
    	}
    	
    	if(array_sum($noshow) == count($pnr->getVolsTarifaire()))
    		return true;
    	else
    		return false;
    }
    
    public function isBilletConsomme(EPnr $pnr){
    	foreach($pnr->getVolsTarifaire() as $vt){
    		$vp = $vt->getVolsPlanified();
    		if($vp->getStatus()->getCode() != 'vols-debarque')
    			return false;
    		foreach($vt->getPassagers() as $passager){
    			$bp = $this->getPassagerBoardingPass($passager, $vp);
    			if(!$bp || ($bp &&  !$bp->getIsOnboard()))
    				return false;
    		}
    	}
    	
    	return true;
    }
    
    public function isPnrExpired(EPnr $pnr)
    {
    	foreach($pnr->getVolsTarifaire() as $vt){
    		if($vt->getTarifaire()){
    			$maxStay = $vt->getTarifaire()->getConditionUtilisation()->getMaxStay()*24*3600;
    			if(($pnr->getCreatedAt()->getTimestamp()+$maxStay)>= time())
    				return false;
    		}
    	}
    	return true;
    }
    
    public function updatePnrCost(EPnr $pnr)
    {
    	$pnr->setCost(
    			$this->getPnrCost($pnr)
    	);
    	$this->em->persist($pnr);
    	$this->em->flush();
    }
    
    public function updatePnrExcedent(EPnr $pnr)
    {
    	foreach($pnr->getExcedents() as $excedent){
    		if(!$pnr->getPassagers()->contains($excedent->getPassager())){
    			$pnr->removeExcedent($excedent);
    			$this->em->persist($pnr);
    			$this->em->flush();
    		}
    	}
    }
    
    public function updatePnr(EPnr $pnr)
    {
    	//Check Status
    	//$this->checkPnrStatus($pnr);
    	
    	//Update Cost
    	$this->updatePnrCost($pnr);
    	
    	//Update Excedent list
    	$this->updatePnrExcedent($pnr);
    }
    
    /**
     * getPassagerBillet
     * @param Passager $passager
     * @param EPnr $pnr
     * @return Billet|NULL
     */
	public function getPassagerBillet(Passager $passager, EPnr $pnr)
    {
    	$billet = $this->em->getRepository('AppCoreBundle:Billet')->findOneBy(array(
    			'passager'=>$passager,
    			'pnr'=>$pnr
    	));
    	//var_dump($billet->getId());
    	/*if(!$billet){
    		$billet = new Billet();
    		$billet->setPnr($pnr);
    		$billet->setPassager($passager);
    		$this->em->persist($billet);
    		$this->em->flush();
    	}*/
    	return $billet;
    }
    
    /**
     * 
     * @param Passager $passager
     * @param VolsPlanified $vols
     * @return BoardingPass|NULL
     */
    public function getPassagerBoardingPass(Passager $passager, VolsPlanified $vols)
    {
    	return $passager->getBoardingPassByVolsPlanified($vols);
    }
    
    /**
     * 
     * @param Passager $passager
     * @param VolsPlanified $vols
     * @return integer
     */
    public function getPassagerPoids(Passager $passager, VolsPlanified $vols)
    {
    	$bp = $this->getPassagerBoardingPass($passager, $vols);
    	if($bp)
    		return $bp->getPoids();
    	return null;
    }
    
    /**
     * getPassagerBilletByVolsPlanified
     * @param Passager $passager
     * @param VolsPlanified $vols
     * @return Billet|NULL
     */
    public function getPassagerBilletByVolsPlanified(Passager $passager, VolsPlanified $vols)
    {
    	$pnr = $passager->getPnrByVolsPlanified($vols);
    	if($pnr)
    		return $this->getPassagerBillet($passager, $pnr);
    	return null;
    }
    
    /**
     * isPassagerHasBillet
     * @param Passager $passager
     * @param VolsPlanified $vols
     * @param EPNR $pnr
     * @return boolean
     */
    public function isPassagerHasBillet(Passager $passager, VolsPlanified $vols = null, EPNR $pnr = null)
    {
    	if($pnr === null){
    		$pnr_list = $this->em->getRepository('AppCoreBundle:Pnr')->findByOptions(array(
    				'Passagers'=>$passager,
    				'volsPlanified'=>$vols
    		));
    
    		if(!count($pnr_list))
    			return false;
    
    		$pnr = $pnr_list[0];
    	}
    
    	$billet = $this->em->getRepository('AppCoreBundle:Billet')->findOneBy(array(
    			'passager'=>$passager,
    			'pnr'=>$pnr
    	));
    	 
    	if($billet)
    		return true;
    	return false;
    }
    
    /**
     * Get Pnr by passager / volsPlanified
     * @param Passager $passager
     * @param VolsPlanified $vols
     * @return EPnr|NULL
     */
    public function getPassagerPnr(Passager $passager, VolsPlanified $vols)
    {
    	$pnr_list = $this->em->getRepository('AppCoreBundle:Pnr')->findByOptions(array(
    			'Passagers'=>$passager,
    			'volsPlanified'=>$vols
    	));
    	if(count($pnr_list))
    		return $pnr_list[0];
    	return null;
    }
    
    /**
     * getVolsDuration
     * @param Vols $vols
     * @return string
     */
    public function getVolsDuration(Vols $vols)
    {
    	$diff = $vols->getEscaleDepart()->getAeroportAt()->diff($vols->getEscaleArrivee()->getAeroportAt());
    	return $diff->format('%Hh%i');
    }
    
    public function getVolsModificationCost(VolsTarifaire $vt, VolsPlanified $newVp, $newtarifaire, $options = null, $taxe = true)
    {
	    $finalCost = $this->getReajustementTarifaire($vt, $newVp, $newtarifaire, $options, $taxe);
		foreach($vt->getPassagers() as $passager){
			$cost = $this->getVolsTarifaireCostByPassager($vt, $passager);
			if($cost){
		    	$penality = $this->em->getRepository('AppCoreBundle:Penality')->findOneByCode('frais-de-modification');
		    	$bp = $passager->getBoardingPassByVolsPlanified($vt->getVolsPlanified());
		    	$vols_status_as_launch = array('vols-en-cours','arrivee-retarde','vols-complete');
		    	if(
		    		in_array($vt->getVolsPlanified()->getStatus()->getCode(), $vols_status_as_launch)
		    		&& (!$bp || ($bp && !$bp->getIsOnboard())))
		    		$penality = $this->em->getRepository('AppCoreBundle:Penality')->findOneByCode('frais-no-show');
				
		    	if(		
		    			$penality 
		    			&& (
	    					$vt->getTarifaire()->getConditionUtilisation()->getModification()<=1 
	    					|| $penality->getCode()=='frais-no-show'
		    			)){
		    		if($penality->getPercentPrice())
		    			$finalCost += ($cost * $penality->getPercentPrice())/100;
		    		if($penality->getStaticPrice())
		    			$finalCost += $penality->getStaticPrice();
		    	}
			}
    	}
    		
    	return $finalCost;
    }
    
    /**
     * // TODO Not work correctly
     * @param unknown $vt_list
     * @param string $options
     * @param string $taxe
     * @return number
     */
    public function getPnrModificationCost($vt_list, $options = null, $taxe = true)
    {
    	$finalCost = 0;
    	$isPassagerPenalityWasSet = false;
    	$penality = $this->getPnrModificationPenality($vt_list);
    	
    	foreach($vt_list as $vt){
    		
	    	$finalCost += $this->getReajustementTarifaire($vt->getOld(), $vt->getVolsPlanified(), $vt->getTarifaire(), $options, $taxe);
    		foreach($vt->getOld()->getPassagers() as $passager){
	    		if($this->getVolsTarifaireCostByPassager($vt->getOld(), $passager) && !$isPassagerPenalityWasSet){
	    			if(		
		    			$penality 
		    			&& (
	    					$vt->getOld()->getTarifaire()->getConditionUtilisation()->getModification()<=1 
	    					|| $penality->getCode()=='frais-no-show'
		    			)){
	    				if($penality->getPercentPrice())
	    					$finalCost += ($vt->getOld()->getCost() * $penality->getPercentPrice())/100;
	    				if($penality->getStaticPrice())
	    					$finalCost += $penality->getStaticPrice();
	    			}
	    		}
	    	}
	    	$isPassagerPenalityWasSet = true;
    	}
    
    	return $finalCost;
    }
    
    /**
     * TOTO Not tested
     * @param unknown $vt_list
     * @return multitype:unknown
     */
    public function getPnrModificationPenality($vt_list)
    {
    	foreach($vt_list as $vt){
		    $pnr_status_no_show= array('billets-noshow-ow','billets-noshow');
		    if(
		    		in_array($vt->getOld()->getPnr()->getStatus()->getCode(),$pnr_status_no_show)
		    		|| $vt->getOld()->getPnr()->getApplyNoshow()
		    )
		    	return $this->em->getRepository('AppCoreBundle:Penality')->findOneByCode('frais-no-show');
    	}
    	return $this->em->getRepository('AppCoreBundle:Penality')->findOneByCode('frais-de-modification');
    }
    
    public function getReajustementTarifaire(VolsTarifaire $vt, VolsPlanified $newVP, $newtarifaire, $options = null, $taxe = true)
    {
    	if($options == null){
    		$sub_options = array('adulte'=>0,'bebe'=>0,'senior'=>0,'jeune'=>0,'enfant'=>0);
    		$passagers = $vt->getPassagers();
    		if($vt->getOld())
    			$passagers = $vt->getOld()->getPassagers();
    		foreach($passagers as $passager){
    			$sub_options[$passager->getTypePassager()->getCode()] ++;
    		}
    	
    		$options = array('typePassager'=>$sub_options,'departAt'=>$vt->getVolsPlanified()->getFlightAt());
    	}
    	if(!isset($options['departAt']) && isset($options['retourAt']))
    		$options['departAt'] = $options['retourAt'];
    	
    	
    	$oldCost = $this->getVolsCost($vt->getVolsPlanified()->getVols(), $vt->getTarifaire(),array_merge($options, array('departAt'=>$vt->getVolsPlanified()->getFlightAt())), $taxe);
    	//var_dump("oldCost ".$vt->getVolsPlanified()->getId()." => ".$oldCost);
    	
    	
    	$newCost = $this->getVolsCost($newVP->getVols(), $newtarifaire, $options, $taxe);
    	//var_dump("newCost ".$newVP->getId()." => ".$newCost);
    	
    	/*var_dump($vt->getVolsPlanified()->getFlightAt()->format("dm")."->".$oldCost);
    	echo"<br>";
    	var_dump($newVP->getFlightAt()->format("dm")."->".$newCost);
    	echo"<br>";*/
    	if($oldCost < $newCost)
    		return $newCost-$oldCost;
    	return  0;
    }
    
    /**
     * Get cost by Vols
     * @param Vols $vols
     * @param Tarifaires | VolsTarifaire $tarifaire
     * @param array $options
     * @param boolean $taxe
     * @return number
     */
    public function getVolsCost(Vols $vols, $tarifaire, $options = null, $taxe = false)
    {
    	if(!isset($options['departAt']))
    		throw new \Exception("Get tarif by vols needs a datetime option 'departAt'");
    	
    	$cost = 0;
    	$departAt = $options['departAt'];
    	if(!$options['departAt'] instanceof  \DateTime)
    		$departAt = new \DateTime($options['departAt']);
    	
    	
    	$tarif = $this->getVolsTarif($vols, $departAt);

    	$direction = 'OW';
    	if(isset($options['direction']) && $options['direction']=='RT')
    		$direction = 'RT';
	
    	if($tarif){
    		if($options){
    			if(
    					isset($options["typePassager"]['senior'])
    					&& is_numeric($options["typePassager"]['senior'])
    					&& $options["typePassager"]['senior']
    			){
    				$cost += $this->getPassagerCost($options["typePassager"]['senior'], 'senior', $tarif, $tarifaire, $taxe, $departAt, $direction);
    			}
    			
    			if(
    					isset($options["typePassager"]['adulte'])
    					&& is_numeric($options["typePassager"]['adulte'])
    					&& $options["typePassager"]['adulte']
    			){
    				$cost += $this->getPassagerCost($options["typePassager"]['adulte'], 'adulte', $tarif, $tarifaire, $taxe, $departAt, $direction);
    			}
    			
    			if(
    					isset($options["typePassager"]['jeune'])
    					&& is_numeric($options["typePassager"]['jeune'])
    					&& $options["typePassager"]['jeune']
    			){
    				$cost += $this->getPassagerCost($options["typePassager"]['jeune'], 'jeune', $tarif, $tarifaire, $taxe, $departAt, $direction);
    			}
    			 
    			if(
    					isset($options["typePassager"]['enfant'])
    					&& is_numeric($options["typePassager"]['enfant'])
    					&& $options["typePassager"]['enfant']
    			){
    				$cost += $this->getPassagerCost($options["typePassager"]['enfant'], 'enfant', $tarif, $tarifaire, $taxe, $departAt, $direction);
    			}
    			 
    			if(
    					isset($options["typePassager"]['bebe'])
    					&& is_numeric($options["typePassager"]['bebe'])
    					&& $options["typePassager"]['bebe']
    			){
    				$cost += $this->getPassagerCost($options["typePassager"]['bebe'], 'bebe', $tarif, $tarifaire, $taxe, $departAt, $direction);
    			}
    		}
    	}

    	return $cost;
    }
    
    public function getPassagerCost($passagerNumber, $typePassager,Tarif $tarif, Tarifaires $tarifaire, $taxe=false, \DateTime $departAt, $direction="OW"){
    	$passagerCost = 0;
    	$typePassager = $this->em
    					->getRepository('AppCoreBundle:TypePassager')
    					->findOneBy(array('code'=>$typePassager));
    	$tarifCost = $tarif->getOwByTypePassager($typePassager, $tarifaire , $departAt);
    	
    	if($direction=='RT')
    		$tarifCost = $tarif->getRtByTypePassager($typePassager, $tarifaire , $departAt);
    	//var_dump($departAt->format("dmy")); echo "<br>";
    	//var_dump($tarifCost); echo "<br>";
    	for($i=0; $i<$passagerNumber; $i++){
    		if($taxe){
    			$passagerCost += $this->getTtcPrice(
    					$tarifCost,
    					$tarifaire,
    					$typePassager
    			);
    		}else{
    			$passagerCost += $tarifCost;
    		}
    	}
    	return $passagerCost;
    }
    
    public function getVTCost(VolsTarifaire $volsTarifaire, $options = null)
    {
    	return $this->getVolsTarifaireCost($volsTarifaire,null,$options);
    }
    
    /**
     * Get cost by VolsTarifaire
     * @param VolsTarifaire $volsTarifaire
     * @param EPNR $pnr
     * @param array $options
     * @return number | NULL
     */
    public function getVolsTarifaireCost(VolsTarifaire $volsTarifaire, EPNR $pnr = null, $options = null)
    {
    	$cost = 0;
    	
    	$tarif = $this->getVolsTarif(
    					$volsTarifaire->getVolsPlanified()->getVols(), 
    					$volsTarifaire->getVolsPlanified()->getFlightAt()
    	);
    	$tarifaire = $volsTarifaire->getTarifaire();
    	/*echo "<pre>";
    	var_dump($volsTarifaire->getVolsPlanified()->getVols()->getEscaleDepart()->getAeroport()->getName());
    	var_dump($volsTarifaire->getVolsPlanified()->getVols()->getEscaleArrivee()->getAeroport()->getName());
    	var_dump($tarif->getId());
    	exit();*/
    	$method = "getOwByTypePassager";
    	if($volsTarifaire->getDirection() == 'RT')
    		$method = "getRtByTypePassager";
    	
    	
    	if($tarif){
    		if($options){
    			
    			if(
    					isset($options["typePassager"]['senior'])
    					&& is_numeric($options["typePassager"]['senior'])
    					&& $options["typePassager"]['senior']
    			){
    				$cost += $options["typePassager"]['senior'] * $tarif->{$method}(
    						$this->em
    						->getRepository('AppCoreBundle:TypePassager')
    						->findOneBy(array('code'=>'senior')),
    						$tarifaire, 
    						$volsTarifaire->getVolsPlanified()
    				);
    			}
    			
    			if(
    					isset($options["typePassager"]['adulte'])
    					&& is_numeric($options["typePassager"]['adulte'])
    					&& $options["typePassager"]['adulte']
    			){
    				$cost += $options["typePassager"]['adulte'] * $tarif->{$method}(
    						$this->em
    						->getRepository('AppCoreBundle:TypePassager')
    						->findOneBy(array('code'=>'adulte')),
    						$tarifaire, 
    						$volsTarifaire->getVolsPlanified()
    				);
    			}
    			
    			if(
    					isset($options["typePassager"]['jeune'])
    					&& is_numeric($options["typePassager"]['jeune'])
    					&& $options["typePassager"]['jeune']
    			){
    				$cost += $options["typePassager"]['jeune'] * $tarif->{$method}(
    						$this->em
    						->getRepository('AppCoreBundle:TypePassager')
    						->findOneBy(array('code'=>'jeune')),
    						$tarifaire, 
    						$volsTarifaire->getVolsPlanified()
    				);
    			}
    
    			if(
    					isset($options["typePassager"]['enfant'])
    					&& is_numeric($options["typePassager"]['enfant'])
    					&& $options["typePassager"]['enfant']
    			){
    				$cost += $options["typePassager"]['enfant'] * $tarif->{$method}(
    						$this->em
    						->getRepository('AppCoreBundle:TypePassager')
    						->findOneBy(array('code'=>'enfant')),
    						$tarifaire, 
    						$volsTarifaire->getVolsPlanified()
    				);
    			}
    
    			if(
    					isset($options["typePassager"]['bebe'])
    					&& is_numeric($options["typePassager"]['bebe'])
    					&& $options["typePassager"]['bebe']
    			){
    				$cost += $options["typePassager"]['bebe'] * $tarif->{$method}(
    						$this->em
    						->getRepository('AppCoreBundle:TypePassager')
    						->findOneBy(array('code'=>'bebe')),
    						$tarifaire, 
    						$volsTarifaire->getVolsPlanified()
    				);
    			}
    		}
    		
    		foreach($volsTarifaire->getPassagers() as $passager){
    			$passagerCost = 0;
    			$passagerCost += $tarif->{$method}($passager->getTypePassager(), $tarifaire,$volsTarifaire->getVolsPlanified());
    			$passagerCost += $this->getExcedentCost($passager, $volsTarifaire);
    			
    			if(isset($options['ttc']) && $options['ttc'])
    				$cost += $this->getTtcPrice($passagerCost,$volsTarifaire,$passager->getTypePassager());
    			else
    				$cost += $passagerCost;
    		}
    		
    		if($volsTarifaire->getPnr()->getDevise() && $volsTarifaire->getPnr()->getDevise() != $tarif->getDevise()){
    			$cost = $this->calculCurrency($cost, $volsTarifaire->getPnr()->getDevise());
    		}    		
    	}

    	return round($cost,0);
    }
    
    public function getVolsTarifaireCostByPassager(VolsTarifaire $volsTarifaire, Passager $passager, $options = null)
    {
    	$cost = 0;
    	$tarif = $this->getVolsTarif(
    			$volsTarifaire->getVolsPlanified()->getVols(),
    			$volsTarifaire->getVolsPlanified()->getFlightAt()
    	);
    	$tarifaire = $volsTarifaire->getTarifaire();
    	$method = "getOwByTypePassager";
    	if($volsTarifaire->getDirection() == 'RT')
    		$method = "getRtByTypePassager";
    	if($tarif){
    		$passagerCost = $tarif->{$method}($passager->getTypePassager(), $tarifaire,$volsTarifaire->getVolsPlanified());
    		$passagerCost += $this->getExcedentCost($passager, $volsTarifaire);
    		if(isset($options['ttc']) && $options['ttc'])
    			$cost += $this->getTtcPrice($passagerCost, $volsTarifaire, $passager->getTypePassager());
    		else
    			$cost += $passagerCost;
    
    		if($volsTarifaire->getPnr()->getDevise() && $volsTarifaire->getPnr()->getDevise() != $tarif->getDevise())
    			$cost = $this->calculCurrency($cost, $volsTarifaire->getPnr()->getDevise());
    	}
    	return $cost;
    }
    
    
    public function calculCurrency($price, Devise $devise){
    	//TODO If needed check the default currency first before make conversion
    	if($devise->getCurrencyValue())
    		return round($price / $devise->getCurrencyValue(),0);
    	return $price;
    }
    
    public function isTarifaireAvailable(Tarifaires $tarifaire, VolsPlanified $vols, $options = array())
    {
    	
    	//Check if VP is available
    	if(!$this->isVolsPlanifiedAvailable($vols,$options))
    		return false;
    	
    	$avion = $vols->getVols()->getPlanDeVols()->getAvion();
    	$restriction = $avion->getRestrictionTarifaires();
    	
    	$siegeNonReserve = 0;
    	if($restriction){
    		foreach($restriction->getTarifaires() 	as $taf)
    			$siegeNonReserve += $vols->getNbPassagersAssisByTarifaire($taf);
    		//var_dump($siegeNonReserve);
    		if($restriction->getTarifaires()->contains($tarifaire)
    			&& $siegeNonReserve >= $restriction->getNbSeating())
    		return false;
    	}
    	
    	$nombrePassagerNeeded = 0;
		if (isset ( $options ["typePassager"] )) {
			foreach ( $options ["typePassager"] as $key => $typePassager ) {
				if ($key != 'bebe')
					$nombrePassagerNeeded += $typePassager;
			}
		}
		
    	if(
    			$restriction
    			&& $restriction->getTarifaires()->contains($tarifaire)
    			&& $nombrePassagerNeeded > $restriction->getNbSeating()- $siegeNonReserve
    				)
    		return false;
    	if(!$this->isAvailableTarifaireByTimeLimit($tarifaire, $vols))
    		return false;
    	if(!$this->isAvailableTarifaireByDate($tarifaire))
    		return false;
    	if(!$this->isAvailableTarifaireByTypeAgence($tarifaire)){
	    	if($this->isAvailableTarifaireByAgence($tarifaire))
	    		return true;
	    	return false;
    	}
    	
    	return true;
    }
    
    public function isAvailableTarifaireByTimeLimit(Tarifaires $tarifaire, VolsPlanified $vols)
    {
    	$typeAgence = $this->em->getRepository("AppCoreBundle:TypeAgence")->findOneByCode($this->getSystemGroup());
    	$timeLimit = $tarifaire->findTimeLimitByTypeAgence($typeAgence);
    	
    	if(($vols->getFlightAt()->getTimestamp() - time()) >= ($timeLimit->getBeforeTime()*60))
    		return true;
    	return false;
    }
    
    public function isAvailableTarifaireByAgence(Tarifaires $tarifaire)
    {
    	if(in_array($this->getSystemId(), array_keys($tarifaire->getShowRulesByAgenceEmail())))
    		return true;
    	return false;
    }
    
    public function isAvailableTarifaireByTypeAgence(Tarifaires $tarifaire)
    {
    	if(in_array($this->getSystemGroup(), array_keys($tarifaire->getShowRulesByName())))
    		return true;
    	return false;
    }
    
    public function isAvailableTarifaireByDate(Tarifaires $tarifaire)
    {
    	//Check TarifaireByDate
    	$daylist = array('Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche');
    	$date = new \DateTime();
    	if(!count($tarifaire->getIntervalleValidite()))
    		return true;
    	 
    	foreach($tarifaire->getIntervalleValidite() as $intervale)
    	{
    		//$intervale = new IntervalleValidite();
    		if($date >= $intervale->getBeginDate() && $date <= $intervale->getEndDate()){
    			if(!count($intervale->getPeriode()))
    				return true;
    			 
    			foreach($intervale->getPeriode() as $periode)
    			{
    				$hoursMinutes = $periode->getTimeStart();
    				$hoursMinutes->setTime($date->format('H'), $date->format('i'), 0);
    				if($hoursMinutes >= $periode->getTimeStart() && $hoursMinutes <= $periode->getTimeEnd()){
    					if(!count($periode->getDays()))
    						return true;
    					foreach($periode->getDays() as $day)
    					{
    						if($daylist[$this->DayToNum($date->format("D"))] == $day->getName())
    							return true;
    					}
    				}
    			}
    		}
    	}
    	return false;
    }
    
    public function isPlanDeVolsAvailable(PlanDeVols $pdv)
    {
    	if($this->isAvailablePlanDeVolsByAgence($pdv))
    		return true;
    	
    	if($this->isAvailablePlanDeVolsByAgenceEmail($pdv))
    		return true;
    
    	return false;
    }
    
    public function isAvailablePlanDeVolsByAgence(PlanDeVols $pdv)
    {
		//var_dump(array_keys($pdv->getShowRulesByName()));
    	if(in_array($this->getSystemGroup(), array_keys($pdv->getShowRulesByName())))
    		return true;
    	 
    	return false;
    }
    
    public function isAvailablePlanDeVolsByAgenceEmail(PlanDeVols $pdv)
    {
    	if(in_array($this->getSystemId(), array_keys($pdv->getShowRulesByAgenceEmail())))
    		return true;
    
    	return false;
    }
    
    public function isVolsPlanifiedAvailable(VolsPlanified $vols, $options = array())
    {
    	$avion = $vols->getVols()->getPlanDeVols()->getAvion();
    	$restrictions = $avion->getRestrictionPassager();
    	$volsTarifaires = $vols->getVolsTarifaire();
    	 
    	//echo "<pre>";
    	//$seats = 0;
    	foreach($volsTarifaires as $vt){
    			$seatsPerCabine = 0;
    			foreach($restrictions as $restriction){
    				//Count all available seats
    				//$seats += $vt->getPnr()->getNbPassagersAssis();
    				
    				//Count all available seats per cabine
    				if($vt->getPnr()->getStatus()->getOrder() <= 3 && $restriction->getCabine() == $vt->getTarifaire()->getCabine())
    					$seatsPerCabine += $vt->getNbPassagersByTypePassager($restriction->getTypePassager());
    				if($seatsPerCabine >= $restriction->getNbSieges())
    					return false;
    			}
    	}
    	
    	
    	
    	
    	$nombrePassagerNeeded = 0;
    	if(isset($options["typePassager"])){
	    	foreach($options["typePassager"] as $key=>$typePassager){
	    		if($key!='bebe')
	    			$nombrePassagerNeeded += $typePassager;
	    	}
    	}
    	if($nombrePassagerNeeded > $vols->getNbAvailableSeating())
    		return false;

    	if($vols->getNbAvailableSeating() <= 0)
    		return false;

    	return true;
    }
    
    public function getVolsPNR(Vols $vols)
    {
    	return $this->em->getRepository('AppCoreBundle:Pnr')->findByOptions(array('Vols'=>$vols));
    }
    
    public function getVolsTarifaireByVols(VolsPlanified $vols)
    {
    	return $this->em->getRepository('AppCoreBundle:VolsTarifaire')->findBy(array('volsPlanified'=>$vols));
    }
    
    public function getPassagersByVolsPlanified(VolsPlanified $vols)
    {
    	$passagers = array();
    	//$pnr_list = $this->getVolsPNR($vols->getVols());
    	$vt_list = $this->getVolsTarifaireByVols($vols);
    	foreach($vt_list as $vt)
    		$passagers = array_merge($passagers,$vt->getPassagers()->toArray());
    	
    	return $passagers;
    }
    
    public function getPnrByBoardingPass(BoardingPass $bp)
    {
    	$pnr_list =  $this->em->getRepository('AppCoreBundle:Pnr')->findByOptions(array(
    			'VolsPlanified'=>$bp->getVolsPlanified(),
    			'Passager'=>$bp->getPassager()
    	));
    	if(sizeof($pnr_list))
    		return $pnr_list[0];
    	return null;
    }
    
    public function getPnrByPassagerAndVolsPlanified(Passager $passager, VolsPlanified $vols)
    {
    	$pnr_list =  $this->em->getRepository('AppCoreBundle:Pnr')->findByOptions(array(
    			'VolsPlanified'=>$vols,
    			'Passager'=>$passager
    	));
    	if(sizeof($pnr_list))
    		return $pnr_list[0];
    	return null;
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
    
    public function getMaxExcedent(Passager $passager, VolsPlanified $vp)
    {
		$volsTarifaire = $this->em->getRepository('AppCoreBundle:VolsTarifaire')->findOneByOptions(array(
    			'volsPlanified'=>$vp,
    			'passager'=>$passager
    	));
		$poidsBagages = 0;
		if($volsTarifaire){
	    	$excedent = $this->em->getRepository('AppCoreBundle:Excedent')->findOneBy(array(
	    			'volsTarifaire'=>$volsTarifaire,
	    			'passager'=>$passager
	    	));
	    	
	    	$bp = $this->em->getRepository('AppCoreBundle:BoardingPass')->findOneBy(array(
	    			'volsPlanified'=>$volsTarifaire->getVolsPlanified(),
	    			'passager'=>$passager
	    	));
	    	

    		$bagageLimit = $volsTarifaire->getTarifaire()->findTarifBagageLimit($passager->getTypePassager());
    		
    		if($bagageLimit)
    			$poidsBagages = $bagageLimit->getPoids();
    		if($excedent)
    			$poidsBagages += $excedent->getPoids();
    		
    		if($bp){
	    		$poidsBagagesBp = 0;
	    		foreach($bp->getBagages() as $bagage)
	    			$poidsBagagesBp += $bagage->getPoids();
	    		
	    		if($poidsBagagesBp > $poidsBagages)
	    			return $poidsBagagesBp;
    		}
	    		
	    }
	    return $poidsBagages;
    }
    
    public function findVolsReadyForEmbarcation(){
    	$hle_object = 
    	$this->em->getRepository('AppCoreBundle:Settings')->findOneBy(array(
    			'metaKey'=>'HLE',
    	));
    	if($hle_object){
    		$hle = $hle_object->getMetaValue();
    		
    		return $this->em->getRepository('AppCoreBundle:VolsPlanified')->findVolsReadyForEmbarcation(
    				$hle
    		);
    	}
    	return null;
    }
    
	public function setEmbarquementPassagerAction($vp, $passager)
	{
			$em = $this->getDoctrine()->getManager();
			
			$passager = $em->getRepository('AppCoreBundle:Passager')->find($passager);
			if(!$passager)
				return false;
			
			$vp = $em->getRepository('AppCoreBundle:VolsPlanified')->find($vp);
			if(!$vp)
				return false;
			
			$boardingPass = $em->getRepository('AppCoreBundle:BoardingPass')->findOneBy(array(
				'passager'=>$passager,
				'volsPlanified'=>$vp
			));
			if(!$boardingPass)
				return false;
			
			if($boardingPass->getIsOnBoard())
				$boardingPass->setIsOnBoard(false);
			else
				$boardingPass->setIsOnBoard(true);
			
			$em->persist($boardingPass);
			$em->flush();
			
			return $boardingPass->getIsOnBoard();		
	}
	
	public function getVolsTarifairePnr(Passager $passager, VolsPlanified $vp)
	{
		$pnr_list = $this->em->getRepository('AppCoreBundle:Pnr')->findByOptions(array(
    			'Passagers'=>$passager,
    			'volsPlanified'=>$vp
    	));
		
    	foreach($pnr_list as $pnr){
    		foreach($pnr->getVolsTarifaire() as $vt)
    			if($vt->getVolsPlanified() == $vp)
    				return $vt;
    	}
    	
    	return null;
	}
	
	public function isPreReservationExpired(EPnr $pnr)
	{
		if($pnr->getStatus()->getCode()!='liste-d-attente')
			return false;
			
		foreach ($pnr->getVolsTarifaire() as $vt)
		{
			$typeAgence = $this->em->getRepository('AppCoreBundle:TypeAgence')->findOneByCode($this->getSystemGroup());
			$rule = $vt->getTarifaire()->findTimeLimitByTypeAgence($typeAgence);
			//echo "<pre>";
			//var_dump($rule->getAfterHours());
			
			if((time() - $pnr->getCreatedAt()->getTimestamp()) < $rule->getAfterHours()*60)
				return false;
		}
		return true;
	}
	
	public function createGroupedTicket(Request $request, EPnr $pnr, $options = array())
	{

		//Trick to prevent cache size exceeded
		//set_time_limit(0);
		while(ob_get_level()) ob_end_clean();
		ob_implicit_flush(true);
		 
		/*
		 * Construct PDF with Mpdf service
		* Layout based on HTML twig template
		*/
		$service	= $this->container->get('tfox.mpdfport');
		$pdf = $service->getMpdf(array('', 'A4', 8, 'Helvetica', 35, 10, 5, 30, 10, 10));
		$pdf->AliasNbPages('{NBPAGES}');
		$pdf->setTitle('billet-' . date('d-m-Y-H-i', time()) . '.pdf');
		//$pdf->SetHeader('Billet');
		//$pdf->SetFooter('{DATE j/m/Y}|{PAGENO}/{NBPAGES}');
		 
		/*$html 		= $this->renderView('AppCoreBundle:Pnr:billetpdf.pdf.twig', array(
		 'billets'   => $em->getRepository('AppCoreBundle:Billet')->findBy(array('pnr' => $pnr))
		));*/
		//$test = '';
		foreach ($pnr->getPassagers() as $key=>$passager ){
			$billet = $this->em->getRepository('AppCoreBundle:Billet')->findOneBy(array('pnr' => $pnr, 'passager' => $passager));
			if(!$billet){
				$billet = new Billet();
				$billet->setPnr($pnr);
				$billet->setPassager($passager);
				$this->em->persist($billet);
				$this->em->flush();
				$billet->postPersist();
				$this->em->persist($billet);
				$this->em->flush();
			}
			
			$html 		= $this->container->get('templating')->render('AppCoreBundle:Pnr:_billetByPassagerPdf_'.$pnr->getLang().'.pdf.twig', array(
					'pnr'	   => $pnr,
					'passager' => $passager,
					'billet'   => $billet,
					'taxes'	   => $this->em->getRepository('AppCoreBundle:Taxe')->findBy(array('isActive'=>true)),
			));
			//$test .= $html;
			$pdf->WriteHTML($html);
			if($key < count($pnr->getPassagers())-1)
				$pdf->AddPage();
		}
		
		$url = 'billets-pnr-'.$pnr->getNumero().'.pdf';
		 
		$link = './media/billets/' . $url;
		 
		$pdf->Output($link, 'F');
		
		//Change pnr status if needed
		$this->checkPnrStatus($pnr);
		
		if(isset($options['absolute_url']) && $options['absolute_url'])
			return  $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/media/billets/' . $url;

		return  '/media/billets/' . $url;
	}
	
	
	public function getMinVolsCost($list, $options)
	{
		$minCost = 0;
		$tarifaires = $this->em->getRepository("AppCoreBundle:Tarifaires")->findOnlineBooking();
		
		if(count($tarifaires)){
			$minCost = $this->getVolsCost($list[0]->getVols(), $tarifaires[0], $options, true);
			//var_dump($minCost);
			foreach($list as $vp){
				foreach($tarifaires as $tarifaire){
					$vpCost = $this->getVolsCost($vp->getVols(), $tarifaire, $options, true);
					if($vpCost < $minCost)
						$minCost = $vpCost;
					 
				}
			}
		}
		return $minCost;
	}
	
	public function getNoteByPassager(Passager $passager, EPnr $pnr)
	{

		$passageNote =  $this->em->getRepository("AppCoreBundle:PassagerNote")->findOneBy(array(
			'passager'=>$passager,
			'pnr'=>$pnr
		));
		
		if($passageNote)
			return $passageNote->getNotes();
		return null;
	}

    
    public function getSystemGroup()
    {
    	
    	if(is_string($this->container->get('security.context')->getToken()->getUser())){
    		$user = $this->em->getRepository("AppCoreBundle:Utilisateur")->findOneByUsername($this->container->get('security.context')->getToken()->getUser());
    		return $user->getAgences()->getTypeAgences()->getCode();
    	}
    		
    	return $this->container->get('security.context')->getToken()->getUser()->getAgences()->getTypeAgences()->getCode();
    }
    
    public function getSystemId()
    {
    	if(is_string($this->container->get('security.context')->getToken()->getUser())){
    		$user = $this->em->getRepository("AppCoreBundle:Utilisateur")->findOneByUsername($this->container->get('security.context')->getToken()->getUser());
    		return $user->getAgences()->getEmail();
    	}
    	return $this->container->get('security.context')->getToken()->getUser()->getAgences()->getEmail();
    }
}