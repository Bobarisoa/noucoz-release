<?php

namespace App\CoreBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Adds some twig syntax helper extension
 *
 */
class Settings extends \Twig_Extension
{
	protected $container;
	
	protected $settings_service;
	
	public function __construct(ContainerInterface $serviceContainer)
	{
		
		$this->container = $serviceContainer;
		
		$this->settings_service = $serviceContainer->get('settings_service');
	}
	
    public function getFilters()
    {
        return array(
            'get_setting' => new \Twig_Filter_Method($this, 'getSetting'),
        	'get_hle' => new \Twig_Filter_Method($this, 'getHle'),
        	'get_hde' => new \Twig_Filter_Method($this, 'getHde')
        );
    }

    public function getSetting($key,$property=null)
    {
    	$setting = $this->settings_service->getSetting($key);
    	if($property && method_exists($setting, $property))
    		return $setting->{$property}();
    	return $setting;
    }
    
    public function getHle(\Datetime $time)
    {
    	$hle = $this->getSetting('HLE');
    	$time = $time->getTimestamp() -($hle->getMetaValue()*60);
    	$newTime = new \DateTime();
    	$newTime->setTimestamp($time);
    	return $newTime;
    }
    
    public function getHde(\Datetime $time)
    {
    	$hle = $this->getSetting('HDE');
    	$time = $time->getTimestamp() -($hle->getMetaValue()*60);
    	$newTime = new \DateTime();
    	$newTime->setTimestamp($time);
    	return $newTime;
    }
    
    public function getName()
    {
        return 'settings_extension';
    }
}