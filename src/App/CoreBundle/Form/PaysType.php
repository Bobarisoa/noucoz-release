<?php
/**
 * Created by PhpStorm.
 * User: Yoyo
 * Date: 22/09/2015
 * Time: 14:20
 */

namespace App\CoreBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PaysType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options){
        /**
         * Pays form builder
         * Fields:
         * - Nom
         * - Code
         */
        $builder
            ->add('name', 'text', array(
                'label' => 'Nom du pays',
                'attr'  => array(
                    'placeholder'  => 'Nom du pays',
                    'class'        => 'span12',
                    'data-handler' => 'change',
                ),
            ))
            ->add('code', 'text', array(
                'label' => 'Code pays',
                'attr'  => array(
                    'placeholder' => 'Ex: FR, US,...',
                    'class'       => 'span12',
                ),
            ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Web\FrontBundle\Entity\Country'
        ));
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'app_corebundle_paystype';
    }
}