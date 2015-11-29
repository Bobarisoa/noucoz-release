<?php

namespace App\CoreBundle\Twig;

use Symfony\Bridge\Doctrine\RegistryInterface;
use App\CoreBundle\Entity\Siege as ESiege;
use App\CoreBundle\Entity\Cabine;
use App\CoreBundle\Entity\VolsPlanified;
use App\CoreBundle\Entity\Passager;

/**
 * Adds some twig syntax helper extension
 *
 */
class Siege extends \Twig_Extension
{
	protected $doctrine;
	
	public function __construct(RegistryInterface $doctrine)
	{
		$this->doctrine = $doctrine;
	}
	
    public function getFilters()
    {
        return array(
            'get_sieges_by_cabine' => new \Twig_Filter_Method($this, 'getSiegesByCabine'),
        	'get_sieges_group_by_column' => new \Twig_Filter_Method($this, 'getSiegesGroupByColumn'),
        	'has_passager' => new \Twig_Filter_Method($this, 'hasPassager'),
        	'get_siege' => new \Twig_Filter_Method($this, 'getSiege'),
        	'is_passager_seat' => new \Twig_Filter_Method($this, 'isPassagerSeat'),
        );
    }

    public function getSiegesByCabine($sieges, Cabine $cabine)
    {
    	$res = array();
        foreach($sieges as $siege){
        	if($siege->getCabine() == $cabine)
        		$res[] = $siege;
        }
        return $res;
    }
    
    public function getSiege(Passager $passager, VolsPlanified $vp)
    {
    	$siegePassager = $this->doctrine->getRepository('AppCoreBundle:SiegePassager')->findOneBy(array('passager'=>$passager,'volsPlanified'=>$vp));
    	if($siegePassager)
    		return $siegePassager->getSiege();
    	return null;
    }
    
    public function isPassagerSeat(ESiege $siege, Passager $passager, VolsPlanified $vp)
    {
    	$siegePassager = $this->doctrine->getRepository('AppCoreBundle:SiegePassager')->findOneBy(array('passager'=>$passager,'volsPlanified'=>$vp,'siege'=>$siege));
    	if($siegePassager)
    		return true;
    	return false;
    }
    
    public function getSiegesGroupByColumn($sieges)
    {
    	$res = array();
    		foreach($sieges as $siege){
    			$res[substr($siege->getNumeroSiege(), 0,1)][] = $siege;
    		}
    		
    	return $res;
    }
    
    public function hasPassager(ESiege $siege, VolsPlanified $vp)
    {
    	$sieges = $this->doctrine->getRepository('AppCoreBundle:SiegePassager')->findBy(array('siege'=>$siege,'volsPlanified'=>$vp));
    	if(sizeof($sieges))
    		return true;
    	return false;
    }
    
    public function getName()
    {
        return 'siege_extension';
    }
}