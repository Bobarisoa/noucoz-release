<?php

namespace App\CoreBundle\Services\Settings;

use Doctrine\ORM\EntityManager;

/**
 * CSV Parser tool
 *
 */
class Settings
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
    
    public function __get($key)
    {
    	return $this->getSetting($key);
    }
    
    public function getSetting($key)
    {
    	return $this->em->getRepository('AppCoreBundle:Settings')->findOneBy(array('metaKey'=>$key));
    }
    
    public function getMetaValue($key)
    {
    	$setting = $this->getSetting($key);
    	if($setting)
    		return $setting->getMetaValue();
    	return null;
    }
}