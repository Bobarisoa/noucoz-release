<?php

namespace App\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /**
         * Classe form builder
         * Fields :
         * 
         */
        $builder
            ->add('name', 'text', array(
            	'label' => 'Nom',
            	'attr'  => array(
            		'placeholder' => 'Nom',
            		'class'		  => 'span12',
            	),
            ))
            ->add('title', 'text', array(
            		'label' => 'Titre',
            		'attr'  => array(
            				'placeholder' => 'Titre',
            				'class'		  => 'span12',
            		),
            ))
           
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\CoreBundle\Entity\Article'
        ));
    }

    public function getName()
    {
        return 'app_corebundle_articletype';
    }
}
