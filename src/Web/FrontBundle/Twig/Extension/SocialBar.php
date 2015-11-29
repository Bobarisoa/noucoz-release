<?php
/**
 * Created by PhpStorm.
 * User: fehiniaina
 * Date: 12/11/2015
 * Time: 14:42
 */

namespace Web\FrontBundle\Twig\Extension;

class SocialBar extends \Twig_Extension
{
    protected $container;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct($container)
    {
        $this->container = $container;
    }

    public function getName()
    {
        return 'acme_social_bar';
    }

    public function getFunctions()
    {
        return array(
            'socialButtons' => new \Twig_Function_Method($this, 'getSocialButtons' ,array('is_safe' => array('html'))),
            'facebookButton' => new \Twig_Function_Method($this, 'getFacebookLikeButton' ,array('is_safe' => array('html'))),
        );
    }

    public function getSocialButtons($parameters = array())
    {
        // Aucun paramètre défini, on garde les valeurs par défaut
        if (!array_key_exists('facebook', $parameters)){
            $render_parameters['facebook'] = array();
            // des paramètres sont définis, on surcharge les valeurs
        }else if(is_array($parameters['facebook'])){

            // le bouton n'est pas affiché
        }else{
            $render_parameters['facebook'] = false;
        }
        $render_parameters['facebook'] = $parameters['facebook'];
        // récupère le service du helper et affiche le template
        return $this->container->get('nucoz.socialBarHelper')->socialButtons($render_parameters);
    }

    // https://developers.facebook.com/docs/reference/plugins/like/
    public function getFacebookLikeButton($parameters = array())
    {
        // valeurs par défaut. Vous pouvez les surcharger en les définissant
        $parameters = $parameters + array(
                'url' => null,
                'locale' => 'en_US',
                'send' => false,
                'width' => 300,
                'showFaces' => false,
                'layout' => 'button_count',
            );

        return $this->container->get('nucoz.socialBarHelper')->facebookButton($parameters);
    }

} 