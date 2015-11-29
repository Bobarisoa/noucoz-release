<?php
namespace App\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Doctrine\Common\Util\Debug;
use Symfony\Component\Form\FormError;
use Symfony\Component\Validator\ExecutionContextInterface;

use App\CoreBundle\Entity\Devise;
use App\CoreBundle\Entity\BoardingPass;
use Symfony\Component\DependencyInjection\ContainerInterface;


class BoardingPassType extends AbstractType
{
	public $_devise;
	
	public $_maxExcedent;
	
	public $_coutExcedent;
	
	public $_container;
	
	public function __construct(ContainerInterface $container, Devise $devise, $maxExcedent, $coutExcedent)
	{
		$this->_devise = $devise;
		
		$this->_maxExcedent = $maxExcedent;
		
		$this->_coutExcedent = $coutExcedent;
		
		$this->_container = $container;
	}
	
    public function buildForm(FormBuilderInterface $builder, array $options)
    { 
        /**
         * Classe form builder
         * Fields :
         * - 
         */
    	
        $builder
	        ->add('poids', 'text', array(
	        		'label' => 'Poids du passager (Kg)',
	        		'attr'  => array(
	        				'placeholder' => 'Poids',
	        				'class'		  => 'span12',
	        		),
	        ))
            ->add('bagages', 'collection', array(
           			'label' => 'Bagages',
	        		'type' => new BagageType(),
            		'required' => false,
	           		'allow_add' => true,
	           		'allow_delete' => true,
	           		'error_bubbling' => false,
           		//'prototype' => true
           ))
           ->add('ecriture', new EncaisementCheckingExcedentType($this->_devise), array(
	           		'label' => 'Encaissement des excédents',
	           		'mapped' => false,
	           		'error_bubbling' => false,
           			'required' => false,
           			'attr'  => array(
           				'class'		  => 'ecriture-form',
           			),
           		));
            ;
           
           $maxExcedent = $this->_maxExcedent;
           $devise = $this->_devise;
           $coutExcedent = $this->_coutExcedent;
           $container = $this->_container;
           
           $bagagesValidator = function(FormEvent $event) use ($container, $maxExcedent, $coutExcedent, $devise){
           	
           	$form = $event->getForm();

           	$bagages = $form->get('bagages')->getData();

           	$poidsBagages = 0;
           	foreach($bagages as $bagage)
           		$poidsBagages += $bagage->getPoids();

           	if ($poidsBagages > $maxExcedent && $form['ecriture']['modeEncaissement']->getData()=='normal'){
           		$excedent = $poidsBagages - $maxExcedent;
           		$coutTotalNu = $excedent * $coutExcedent;
           		$coutTotalDevise =  $container->get('pnr_service')->calculCurrency($coutTotalNu, $devise);
           		
           		if($form['ecriture']['credit']->getData() != $coutTotalDevise){
           			$wordingDevise = '';
           			if($devise->getCode()!='AR')
           				$wordingDevise = ' ('.$coutTotalNu.' Ar)';
	           		$form['ecriture']['credit']->addError(new FormError(
	           			'Le poids autorisé à été dépassé. Veuillez procéder à l\'encaissement des éxcédents : '.$excedent.' Kg. Soit la somme de '.number_format($coutTotalDevise,0,',',' ').' '.$devise.$wordingDevise
	           		));
           		
	           		$container->get('session')->getFlashBag()->add(
	           				'danger',
	           				'Le poids autorisé à été dépassé. Veuillez procéder à l\'encaissement des éxcédents : '.$excedent.' Kg. Soit la somme de '.number_format($coutTotalDevise,0,',',' ').' '.$devise.$wordingDevise
	           		);
           		}else{
	           			//Validation NumeroPiece
				    	$paiementWithPieceId = array(
				    		"paiement-visa",
				    		"paiement-coupon",
				    		"paiement-mvola",
				    		"paiement-orange-money",
				    		"paiement-airtel",
				    		"paiement-cheque",
				    		//"autre"
				    	);
				    	if($form['ecriture']['modePaiement']->getData() && in_array($form['ecriture']['modePaiement']->getData()->getCode(), $paiementWithPieceId) && !$form['ecriture']['numeroPiece']->getData())
				    		$form['ecriture']['numeroPiece']->addError(new FormError("Veuillez remplir ce champ"));
				    	
				    	//Validation remarque
				    	$paiementWithRemarque = array(
				    			"autre"
				    	);
				    	if($form['ecriture']['modePaiement']->getData() && in_array($form['ecriture']['modePaiement']->getData()->getCode(), $paiementWithRemarque) && !$form['ecriture']['name']->getData())
				    		$form['ecriture']['name']->addError(new FormError("Veuillez remplir ce champ"));
           					

           			}
           		}else{
           			if($form['ecriture']['credit']->getData() && $form['ecriture']['modeEncaissement']->getData()=='normal')
           				$form['ecriture']['credit']->addError(new FormError(
           						'Encaissement non permis. Veuillez vider ce champ !'
           				));
           			if($form['ecriture']['modeEncaissement']->getData()=='libre' && !$form['ecriture']['name']->getData())
           					$form['ecriture']['name']->addError(new FormError(
           							'Veuillez remplir ce champ pour explication !'
           					));
           		}
           };
            
           // adding the validator to the FormBuilderInterface
           $builder->addEventListener(FormEvents::POST_SUBMIT, $bagagesValidator);
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
    	$resolver->setDefaults(array(
    			'data_class' => 'App\CoreBundle\Entity\BoardingPass',
    			//'csrf_protection' => false,
        ));
    }


    public function getName()
    {
        return 'app_corebundle_boardingpasstype';
    }
}
