<?php

namespace App\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UtilisateurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
       /**
         * Utilisateur form builder
         * Fields :
         * - Nom
         * - Prénom
         * - Identifiant
         * - Email
         * - Role
         * - Mot de passe
         */
        $builder
            ->add('lastname', 'text', array(
                'label' => 'Nom',
                'attr'  => array(
                    'placeholder'  => 'Nom',
                    'class'        => 'span12',
                    'data-handler' => 'change',
                ),
            ))
            ->add('firstname', 'text', array(
                'label' => 'Prénom',
                'attr'  => array(
                    'placeholder'  => 'Prénom',
                    'class'        => 'span12',
                    'data-handler' => 'change',
                ),
            ))
            ->add('username', 'text', array(
                'label' => 'Identifiant/Code administratif',
                'attr'  => array(
                    'placeholder' => 'Identifiant',
                    'class'       => 'span12',
                    'data-change' => 'autocomplete',
                ),
            ))
            ->add('code', 'text', array(
            		'label' => 'Code Agent',
            		'attr'  => array(
            				'placeholder' => 'Ex: RAK',
            				'class'       => 'span12',
            		),
            ))
            ->add('isActive', 'checkbox', array('label' => 'Activé le compte', 'required' => false))
            ->add('email', 'text', array(
                'label' => 'Adresse email',
                'attr'  => array(
                    'placeholder' => 'Adresse email',
                    'class'       => 'span12',
                ),
            ))
             ->add('phone', 'text', array(
                'label' => 'Téléphone',
                'required'=>false,
                'attr'  => array(
                                'placeholder' => 'Téléphone',
                                'class'		  => 'span12',
                ),
            ))
            ->add('position', 'text', array(
                'label' => 'Poste',
                'required'=>false,
                'attr'  => array(
                                'placeholder' => 'Poste',
                                'class'		  => 'span12',
                ),
            ))
            
            ->add('role', 'entity', array(
                'label' => 'Groupe',
                'class' => 'App\CoreBundle\Entity\Role',
                'empty_value' => 'Choisissez une groupe',
                'empty_data'  => null,
            ))
            
            ->add('password', 'repeated', array(
                'type'            => 'password',
                'invalid_message' => 'Les mots de passe ne correspondent pas.',
                'required'        => (!$builder->getData()->getId() ? true : false),
                'first_options'   => array(
                    'label' => (!$builder->getData()->getId() ? 'Mot' : 'Nouveau mot') . ' de passe',
                    'attr'  => array(
                        'placeholder' => (!$builder->getData()->getId() ? 'Mot' : 'Nouveau mot') . ' de passe',
                        'class'       => 'span12',
                    ),
                ),
                'second_options' => array(
                    'label' => 'Confirmation',
                    'attr'  => array(
                        'placeholder' => 'Confirmation',
                        'class'       => 'span12',
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
