<?php

namespace App\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class FluxRssType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /**
         * Classe form builder
         * Fields :
         * 
         */
        $builder
            ->add('nameSite', 'text', array(
            	'label' => 'Nom site',
            	'attr'  => array(
            		'placeholder' => 'Nom site',
            		'class'		  => 'span12',
            	),
            ))
            ->add('linkFlux', 'text', array(
            		'label' => 'Lien flux',
            		'attr'  => array(
            				'placeholder' => 'Lien flux',
            				'class'		  => 'span12',
            		),
            ))
            ->add('categorySite', 'text', array(
            		'label' => 'Catégorie Site',
            		'attr' => array(
            				'placeholder' => 'Catégorie Site',
            				'class' => 'span12',
            		),
            ))
           
           ->add('pays', 'text', array(
           		'label' => 'Pays',
           		'attr' => array(
           				'placeholder' => 'Pays',
           				'class' => 'span12',
           		),
           ))
           
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\CoreBundle\Entity\FluxRss'
        ));
    }

    public function getName()
    {
        return 'app_corebundle_fluxrsstype';
    }
}
