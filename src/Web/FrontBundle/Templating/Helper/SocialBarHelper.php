<?php
/**
 * Created by PhpStorm.
 * User: fehiniaina
 * Date: 12/11/2015
 * Time: 14:39
 */

namespace Web\FrontBundle\Templating\Helper;

use Symfony\Component\Templating\Helper\Helper;
use Symfony\Component\Templating\EngineInterface;

class SocialBarHelper extends Helper
{
    protected $templating;

    public function __construct(EngineInterface $templating)
    {
        $this->templating  = $templating;
    }

    public function socialButtons($parameters)
    {
        return $this->templating->render('WebFrontBundle:helper:socialButtons.html.twig', $parameters);
    }

    public function facebookButton($parameters)
    {
        return $this->templating->render('WebFrontBundle:helper:facebookButton.html.twig', $parameters);
    }

    public function getName()
    {
        return 'socialButtons';
    }
} 