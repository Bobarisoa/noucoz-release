<?php

namespace App\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ProfilType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /**
         * Profil form builder
         * Fields :
         * - Password
         */
        $builder
            ->add('password', 'repeated', array(
                'type'            => 'password',
                'invalid_message' => 'Les mots de passe ne correspondent pas.',
                'required'        => true,
                'first_options'   => array(
                    'label' => 'Nouveau mot de passe',
                    'attr'  => array(
                        'placeholder' => 'Nouveau mot de passe',
                    ),
                ),
                'second_options'  => array(
                    'label' => 'Confirmation',
                    'attr'  => array(
                        'placeholder' => 'Confirmation',
                    ),
                ),
            ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\CoreBundle\Entity\Utilisateur'
        ));
    }

    public function getName()
    {
        return 'app_corebundle_utilisateurtype';
    }
}
