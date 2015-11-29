<?php

namespace App\CoreBundle\Twig;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use App\CoreBundle\Entity\Pnr as EPnr;
use App\CoreBundle\Entity\Passager;
use App\CoreBundle\Entity\VolsPlanified;
use App\CoreBundle\Entity\Vols;
use App\CoreBundle\Entity\EcritureComptable;
use App\CoreBundle\Entity\VolsTarifaire;
use App\CoreBundle\Entity\Tarifaires;
use App\CoreBundle\Entity\PlanDeVols;
use App\CoreBundle\Entity\TypePassager;
use App\CoreBundle\Entity\Devise;

/**
 * Adds some twig syntax helper extension
 *
 */
class Pnr extends \Twig_Extension
{
	protected $em;
	
	protected $container;
	
	protected $pnr_service;
	
	public function __construct(RegistryInterface $doctrine, ContainerInterface $serviceContainer)
	{
		$this->em = $doctrine;
		
		$this->container = $serviceContainer;
		
		$this->pnr_service = $serviceContainer->get('pnr_service');
	}
	
    public function getFilters()
    {
        return array(
        	'get_min_vols_cost' => new \Twig_Filter_Method($this, 'getMinVolsCost'),
            'get_pnr_cost' => new \Twig_Filter_Method($this, 'getPnrCost'),
        	'get_pnr_payed' => new \Twig_Filter_Method($this, 'getPnrPayed'),
        	'is_passager_has_billet' => new \Twig_Filter_Method($this, 'isPassagerHasBillet'),
        	//'get_passager_pnr' => new \Twig_Filter_Method($this, 'getPassagerPnr'), This is depreceaded
        	'get_passager_poids' => new \Twig_Filter_Method($this, 'getPassagerPoids'),
        	'get_passager_boarding_pass' => new \Twig_Filter_Method($this, 'getPassagerBoardingPass'),
        	'get_passager_billet' => new \Twig_Filter_Method($this, 'getPassagerBillet'),
        	'get_passager_billet_by_vols_planified' => new \Twig_Filter_Method($this, 'getPassagerBilletByVolsPlanified'),
        	'get_vols_duration' => new \Twig_Filter_Method($this, 'getVolsDuration'),
        	'get_ttc_price' => new \Twig_Filter_Method($this, 'getTtcPrice'),
        	'get_repay_amount' => new \Twig_Filter_Method($this, 'getRepayAmount'),
        	'calcul_repay_cost' => new \Twig_Filter_Method($this, 'calculRepayCost'),
        	'get_modification_amount' => new \Twig_Filter_Method($this, 'getModificationAmount'),
        	'get_excedent_cost' => new \Twig_Filter_Method($this, 'getExcedentCost'),
        	'get_vt_cost' => new \Twig_Filter_Method($this, 'getVTCost'),
        	'get_vols_cost' => new \Twig_Filter_Method($this, 'getVolsCost'),
        	'get_vols_tarif' => new \Twig_Filter_Method($this, 'getVolsTarif'),
        	'get_devise' => new \Twig_Filter_Method($this, 'getDevise'),
        	'get_max_excedent' => new \Twig_Filter_Method($this, 'getMaxExcedent'),
        	'is_tarifaire_available' => new \Twig_Filter_Method($this, 'isTarifaireAvailable'),
        	'is_vp_available' => new \Twig_Filter_Method($this, 'isVolsPlanifiedAvailable'),
        	'is_pdv_available' => new \Twig_Filter_Method($this, 'isPlanDeVolsAvailable'),
        	'calculCurrency' => new \Twig_Filter_Method($this, 'calculCurrency'),
        	'get_vols_tarifaire_pnr' => new \Twig_Filter_Method($this, 'getVolsTarifairePnr'),
        	'get_vols_modification_cost' => new \Twig_Filter_Method($this, 'getVolsModificationCost'),
        	'get_pnr_modification_cost' => new \Twig_Filter_Method($this, 'getPnrModificationCost'),
        	'get_reajustement_tarifaire'=> new \Twig_Filter_Method($this, 'getReajustementTarifaire'),
        	'get_vols_tarifaire_cost_by_passager' => new \Twig_Filter_Method($this, 'getVolsTarifaireCostByPassager'),
        	'is_pre_reservation_expired'=> new \Twig_Filter_Method($this, 'isPreReservationExpired'),
        	'get_note_by_passager'=>new \Twig_Filter_Method($this, 'getNoteByPassager'),
            'get_time_diff'=>new \Twig_Filter_Method($this, 'getTimeDiff')
        );
    }
    public function  getTimeDiff(\DateTime $debut, \DateTime $fin)
	{
		return $debut->diff($fin);
	}
    
	public function  getMinVolsCost($list, $options)
	{
		return $this->pnr_service->getMinVolsCost($list, $options);
	}
    
    public function calculCurrency($price, Devise $devise)
    {
    	return $this->pnr_service->calculCurrency($price, $devise);
    }
    
    public function getPnrCost(EPnr $pnr)
    {
    	return $this->pnr_service->getPnrCost($pnr);
    }
    
    public function getPnrPayed(EPnr $pnr)
    {
    	return $this->pnr_service->getPnrPayed($pnr);
    }
    
    public function isPassagerHasBillet(Passager $passager, VolsPlanified $vols = null, EPNR $pnr = null)
    {
    	return $this->pnr_service->isPassagerHasBillet($passager, $vols, $pnr);	
    }
    
    public function getPassagerPnr(Passager $passager, VolsPlanified $vols)
    {
    	return $this->pnr_service->getPassagerPnr($passager, $vols);	
    }
    
    public function getPassagerBillet(Passager $passager, EPnr $pnr)
    {
    	return $this->pnr_service->getPassagerBillet($passager, $pnr);
    }
    
    public function getPassagerBilletByVolsPlanified(Passager $passager, VolsPlanified $vols)
    {
    	return $this->pnr_service->getPassagerBilletByVolsPlanified($passager, $vols);
    }
    
    public function getPassagerPoids(Passager $passager, VolsPlanified $vols)
    {
    	return $this->pnr_service->getPassagerPoids($passager, $vols);
    }
    
    public function getPassagerBoardingPass(Passager $passager, VolsPlanified $vols)
    {
    	return $this->pnr_service->getPassagerBoardingPass($passager, $vols);
    }
    
    public function getVolsDuration(Vols $vols)
    {    
    	return $this->pnr_service->getVolsDuration($vols);
    }
    
    public function getTtcPrice($price, VolsTarifaire $vols = null, TypePassager $passager)
    {
    	return $this->pnr_service->getTtcPrice($price, $vols, $passager);
    }
    
    public function getRepayAmount($price)
    {
    	return $this->pnr_service->getRepayAmount($price);
    }
    
    public function getModificationAmount($price, EPnr $pnr)
    {
    	return $this->pnr_service->getModificationAmount($price, $pnr);
    }
    
    public  function getExcedentCost(Passager $passager, VolsTarifaire $vt)
    {
    	return $this->pnr_service->getExcedentCost($passager, $vt);
    }
    
    public function getVolsTarif(Vols $vols, \DateTime $departAt)
    {
    	return $this->pnr_service->getVolsTarif($vols, $departAt);
    }
    
    public function getDevise(VolsTarifaire $volsTarifaire){
    	return $this->pnr_service->getDevise($volsTarifaire);
    }
    
    public function getVolsTarifaireCost(VolsTarifaire $volsTarifaire, EPNR $pnr = null, $options = null)
    {
    	return $this->pnr_service->getVolsTarifaireCost($volsTarifaire, $pnr, $options);
    }
    
    public function getVTCost(VolsTarifaire $volsTarifaire, $options = null)
    {
    	return $this->pnr_service->getVTCost($volsTarifaire, $options);
    }
    
    public function getVolsCost(Vols $vols, $vt, $options = null, $taxe = false)
    {
    	return $this->pnr_service->getVolsCost($vols, $vt, $options, $taxe);
    }
    
    public function isTarifaireAvailable(Tarifaires $tarifaire, VolsPlanified $vols, $options=array())
    {
    	return $this->pnr_service->isTarifaireAvailable($tarifaire, $vols, $options);
    }
    
    public function isVolsPlanifiedAvailable(VolsPlanified $vols, $options = array())
    {
    	return $this->pnr_service->isVolsPlanifiedAvailable($vols, $options);
    }
    
    public function isPlanDeVolsAvailable(PlanDeVols $pdv)
    {
    	return $this->pnr_service->isPlanDeVolsAvailable($pdv);
    }
    
    public function getMaxExcedent(Passager $passager, VolsPlanified $vp)
    {
    	return $this->pnr_service->getMaxExcedent($passager, $vp);
    }
    
    public function getVolsTarifairePnr(Passager $passager, VolsPlanified $vp)
    {
    	return $this->pnr_service->getVolsTarifairePnr($passager, $vp);
    }
    
    public function getVolsModificationCost(VolsTarifaire $vt, VolsPlanified $vp, $newtarifaire, $options = null, $taxe = true)
    {
    	return $this->pnr_service->getVolsModificationCost($vt, $vp, $newtarifaire, $options, $taxe);
    }
    
    public function getPnrModificationCost($vt_list, $options = null, $taxe = true)
    {
    	return $this->pnr_service->getPnrModificationCost($vt_list, $options, $taxe);
    }
    
    public function getReajustementTarifaire(VolsTarifaire $vt, VolsPlanified $newVp, $newtarifaire, $options = null, $taxe = true)
    {
    	return $this->pnr_service->getReajustementTarifaire($vt, $newVp, $newtarifaire, $options, $taxe);
    }
    
    public function getVolsTarifaireCostByPassager(VolsTarifaire $volsTarifaire, Passager $passager, $options = null)
    {
    	return $this->pnr_service->getVolsTarifaireCostByPassager($volsTarifaire, $passager, $options);
    }
    
    public function isPreReservationExpired(EPnr $pnr)
    {
    	return $this->pnr_service->isPreReservationExpired($pnr);
    }
    
    public function calculRepayCost(EPnr $pnr)
    {
    	return $this->pnr_service->calculRepayCost($pnr);
    }
    
    public function getNoteByPassager(Passager $passager,EPnr $pnr)
    {
    	return $this->pnr_service->getNoteByPassager($passager, $pnr);
    }
    
    public function getName()
    {
        return 'pnr_extension';
    }
}