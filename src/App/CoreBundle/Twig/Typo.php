<?php

namespace App\CoreBundle\Twig;

/**
 * Adds some twig syntax helper extension
 *
 */
class Typo extends \Twig_Extension
{
	public $translation = array(
		'Buying ticket' => 'Achat Billet',
		'Flight Modification' => 'Modification Billet',
		'Ticket refund' => 'Remboursement Billet'
	);
	
    public function getFilters()
    {
        return array(
            'group' => new \Twig_Filter_Method($this, 'groupBy'),
            'period' => new \Twig_Filter_Method($this, 'period'),
        	'unit' => new \Twig_Filter_Method($this, 'unit'),
        	'abreviation' => new \Twig_Filter_Method($this, 'abreviation'),
        	'translate' => new \Twig_Filter_Method($this, 'translate'), 
        );
    }
    
    public function unit($data, $unit, $na = '-'){
    	if($data > 0)
    		return $data.' '.$unit;
    	return $na;
    }
    
    public function translate($string, $lang = 'en'){
    	return (array_search($string, $this->translation))?array_search($string, $this->translation):$string;
    }
    
    public function abreviation($data, $lenght = 2){
    	return substr($data, 0, $lenght);
    }

    public function groupBy($array, $filter)
    {
    	$group = array();
    	
        switch ($filter) {
	        case 'date':
		        foreach ($array as $key => $value) {
				    $group[$value->getDate()->format('Y-m-d')][] = $value;
		        }
		        
		        break;
        }
        return $group;
    }

    public function period($array, $period)
    {
    	$group = array();
    	$dates = array();
    	
    	foreach ($period as $day) {
	    	$dates[] = $day;
    	}
    	
	    foreach ($array as $key => $value) {
		    if (in_array($value->getDate(), $dates)) {
			    $group[] = $value;
		    }
	    }
		        
        return $group;
    }
    
    public function getName()
    {
        return 'typo_extension';
    }
}