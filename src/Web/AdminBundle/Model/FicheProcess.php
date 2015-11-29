<?php
namespace Web\AdminBundle\Model;

use Web\AdminBundle\Model\FicheData;


class FicheProcess extends FicheData
{
	public function process(){
		/*
		*	##############################################################################################################################
		*	VARIABLES A CALCULER
		*	##############################################################################################################################
		*/
		// Si possible avoir une table global VARIABLE avec trois champs => NOM_VAR / DATE_DEBUT / DATE_FIN -> Ca permet d'avoir les différentes valeurs du SMIC par exemple (aussi pour plafond SS, valeur_T, etc...)
		// SMIC 2015
		$this->smic_heure = $this->getVars('SMIC_HEURE');
		$this->smic_mois = $this->getVars('SMIC_MOIS');
		
		// Plafond sécu sociale 2015
		$this->plafond_ss = $this->getVars('PLAFOND_SECURITE_SOCIALE');
		
		//N	b congé par mois 2015
		$this->conge_mois = 2.08;
		
		$this->periode_month = date( 'm',strtotime( $this->periode_du->getTimestamp() ) );
		
		// Salaire de référence pour novembre 2015
		// Récupère le nombre de jours en novembre 2015
		$this->nb_jours = cal_days_in_month(CAL_GREGORIAN, $this->periode_month, 2015);
		// Calcul le salaire de réf
		$this->salaire_ref = $this->paie_salaire_brut + ( $this->paie_hsupp_125 * 1.25 ) + ( $this->paie_hsupp_150 * 1.5 ) - ( $this->conge_amaladie_jrs * ( $this->paie_salaire_brut / $this->nb_jours ) ) - ( $this->conge_sanssolde_jrs * $this->paie_tx_horaire );
		
		// Différents taux
		$this->tb_chomage = ( ( $this->salaire_ref - $this->plafond_ss ) < 0 ) ? 0 : ( $this->salaire_ref - $this->plafond_ss );
		$this->tb_agric = ( ( $this->salaire_ref - $this->plafond_ss ) < 0 ) ? 0 : ( $this->salaire_ref - $this->plafond_ss );
		$this->tc_agric = 0; // TODO - Nécessaire d'avoir les prévoyances
		
		// Valeur spécifique pour le calcul de la réduction Fillon	
		$this->valeur_t = ( $this->ent_effectif < 20 ) ? 0.2795 : 0.2835;
		
		/*
		*	##############################################################################################################################
		*	CALCULES GENERIQUES TOUTES CONVENTIONS
		*	##############################################################################################################################
		*/
		
		/*
		 * CALCULS SPECIQUES AUX CONGES 
		*/
		$this->conge_paye_n_moins_1 = $this->conge_mois ;
		if($this->nb_jours_novembre - $this->conge_pris - $this->conge_amaladie_jrs < 10)
			$this->conge_paye_n_moins_1 = 0;
		

		
		/*
		 * CALCULS SPECIQUES A URSSAF
		*/
		$this->urssaf_assurance_maladie_salar = $this->salaire_ref * ( 0.75 / 100 );
		$this->urssaf_assurance_maladie_patron = $this->salaire_ref * ( 12.8 / 100 );
		
		$this->urssaf_contribution_solidarité_patron = $this->salaire_ref * ( 0.30 / 100 );
		
		$base_av_plafonnee = ( $this->salaire_ref > $this->plafond_ss ) ? $this->plafond_ss : $this->salaire_ref ;
		$this->urssaf_av_plafonnee_salar = ( 6.85 / 100 ) * $base_av_plafonnee;
		$this->urssaf_av_plafonnee_patron = ( 8.50 / 100 ) * $base_av_plafonnee;
		
		$this->urssaf_av_deplafonnee_salar = $this->salaire_ref * ( 0.30 / 100 );
		$this->urssaf_av_deplafonnee_patron = $this->salaire_ref * ( 1.80 / 100 );
		
		$this->urssaf_accident_travail_patron = $this->salaire_ref * ( $this->paie_tx_at / 100 );
		
		//??
		$this->urssaf_transport_patron = $this->salaire_ref * ( $this->paie_tx_transport / 100 );
		
		$this->urssaf_alloc_famill_patron = $this->salaire_ref * ( 3.45 / 100 );
		
		$this->urssaf_alloc_famill_complement_patron = ( 1.80 / 100 ) * ( ( $this->salaire_ref > ( $this->smic_mois * 1.6 ) ) ? $this->salaire_ref : 0 );
		
		$this->urssaf_ossope_patron = $this->salaire_ref * ( 0.016 / 100 );
		
		$this->urssaf_fnal_patron = ( $this->ent_effectif < 20 ) ? ( ( 0.10 / 100 ) * $this->salaire_ref ) : ( ( 0.50 / 100 ) * $this->salaire_ref );
		
		$this->urssaf_reduc_fillon_patron = 0; // TODO
		
		$this->urssaf_chomage_trancheA_base = ( $this->salaire_ref > $this->plafond_ss ) ? $this->plafond_ss : $this->salaire_ref;
		$this->urssaf_chomage_trancheA_salar = ( 2.40 / 100 ) * $this->urssaf_chomage_trancheA_base;
		$this->urssaf_chomage_trancheA_patron = ( 4.00 / 100 ) * $this->urssaf_chomage_trancheA_base;
		
		$this->urssaf_chomage_trancheB_base = ( ( $this->salaire_ref - $this->plafond_ss ) > 9510 ) ? 9510 : $this->tb_chomage;
		$this->urssaf_chomage_trancheB_salar = ( 2.40 / 100 ) * $this->urssaf_chomage_trancheB_base;
		$this->urssaf_chomage_trancheB_patron = ( 4.00 / 100 ) * $this->urssaf_chomage_trancheB_base;
		
		$this->urssaf_chomage_trancheAGS_patron = ( 0.30 / 100 ) * ( $this->urssaf_chomage_trancheA_base + $this->urssaf_chomage_trancheB_base );
		
		/*
		 * CALCULS SPECIQUES A CAISSE DE RETRAITE
		*/
		$this->cretraite_complement_arrco_tranche1_salar = ( 3.10 / 100 ) * ( ( $this->salaire_ref > $this->plafond_ss ) ? $this->plafond_ss : $this->salaire_ref );
		$this->cretraite_complement_arrco_tranche1_patron = ( 4.65 / 100 ) * ( ( $this->salaire_ref > $this->plafond_ss ) ? $this->plafond_ss : $this->salaire_ref );
		
		$this->cretraite_complement_agff_tranche1_salar = ( 0.80 / 100 ) * ( ( $this->salaire_ref > $this->plafond_ss ) ? $this->plafond_ss : $this->salaire_ref );
		$this->cretraite_complement_agff_tranche1_patron = ( 1.20 / 100 ) * ( ( $this->salaire_ref > $this->plafond_ss ) ? $this->plafond_ss : $this->salaire_ref );
		
		$this->cretraite_complement_agff_tranche2_salar = ( 0.90 / 100 ) * ( ( ( $this->salaire_ref - $this->plafond_ss ) > 9510 ) ? 9510 : $this->tb_agric );
		$this->cretraite_complement_agff_tranche2_patron = ( 1.30 / 100 ) * ( ( ( $this->salaire_ref - $this->plafond_ss ) > 9510 ) ? 9510 : $this->tb_agric );
		
		
		/*
		 * CALCULS SPECIQUES A TAXES DIVERSES
		*/
		$this->taxediv_apprentissage_patron = ( 0.50 / 100 ) * $this->salaire_ref;
		
		$this->taxediv_contrib_apprenstissage_patron = ( 0.18 / 100 ) * $this->salaire_ref;
		
		// /!\ Uniquement pour les entreprises dont l'effectif est en dessous de 10 salariés
		$this->taxediv_participation_entre_mini_10_patron = ( 0.20 / 100 ) * $this->salaire_ref;
		
		// /!\ Uniquement pour les entreprises dont l'effectif est au dessus ou égal à 20 salariés
		$this->taxediv_participation_entre_mini_10_patron = 1.50 * ( $this->paie_hsupp_125 + $this->paie_hsupp_150 );
		
		/*
		*	##############################################################################################################################
		*	CALCULES UNIQUEMENT POUR LES CADRES
		*	##############################################################################################################################
		*/
		$this->cadre_retraite_compl_agric_trancheB_salar = ( 7.80 / 100 ) * ( ( ( $this->salaire_ref - $this->plafond_ss ) > 12680 ) ? 12680 : $this->tb_agric );
		$this->cadre_retraite_compl_agric_trancheB_patron = ( 12.75 / 100 ) * ( ( ( $this->salaire_ref - $this->plafond_ss ) > 12680 ) ? 12680 : $this->tb_agric );
		
		$this->cadre_retraite_compl_agric_trancheC_salar = ( 7.80 / 100 ) * ( ( ( $this->salaire_ref - $this->cretraite_complement_arrco_tranche1_salar - $this->tb_agric ) > 12680 ) ? 12680 : $this->tc_agric );
		$this->cadre_retraite_compl_agric_trancheC_patron = ( 12.75 / 100 ) * ( ( ( $this->salaire_ref - $this->cretraite_complement_arrco_tranche1_patron - $this->tb_agric ) > 12680 ) ? 12680 : 0 );
		
		$this->cet_salar = ( 0.13 / 100 ) * ( $this->cretraite_complement_agff_tranche1_salar + $this->cretraite_complement_agff_tranche2_salar );
		$this->cet_patron = ( 0.22 / 100 ) * ( $this->cretraite_complement_agff_tranche1_patron + $this->cretraite_complement_agff_tranche2_patron );
		
		$this->apec_ta_salar = ( 0.024 / 100 ) * ( ( $this->salaire_ref > $this->plafond_ss ) ? $this->plafond_ss : $this->salaire_ref );
		$this->apec_ta_patron = ( 0.036 / 100 ) * ( ( $this->salaire_ref > $this->plafond_ss ) ? $this->plafond_ss : $this->salaire_ref );
		
		$this->apec_tb_salar = ( 0.024 / 100 ) * ( ( ( $this->salaire_ref - $this->plafond_ss ) > 9510 ) ? 9150 : $this->tb_chomage );
		$this->apec_tb_patron = ( 0.036 / 100 ) * ( ( ( $this->salaire_ref - $this->plafond_ss ) > 9510) ? 9510 : $this->tb_chomage );
		
		/*
		*	##############################################################################################################################
		*	CALCULES UNIQUEMENT POUR LES NON CADRES
		*	##############################################################################################################################
		*/
		
		$this->cretraite_complement_arrco_tranche2_salar = ( 8.10 / 100 ) * ( ( ( $this->salaire_ref - $this->plafond_ss ) > 9510 ) ? 9150 : $this->tb_chomage );
		$this->cretraite_complement_arrco_tranche2_patron = ( 12.65 / 100 ) * ( ( ( $this->salaire_ref - $this->plafond_ss ) > 9510 ) ? 9150 : $this->tb_chomage );
		
		
		/*
		 *	##############################################################################################################################
		*	CALCULES UNIQUEMENT POUR SYNTEC
		*	##############################################################################################################################
		*/
		if($this->ent_conv->getSlug()=="syntec"){
			if($this->sal_statut->getSlug() =="cadre"){
				//Non Cadre
				$this->prevoyance_taux_ta_salar = ( 0.37 / 100 );
				$this->prevoyance_ta_salar = $this->prevoyance_taux_ta_salar * ( ( $this->salaire_ref > $this->plafond_ss ) ? $this->plafond_ss : $this->salaire_ref );
				$this->prevoyance_taux_ta_patron = ( 0.37 / 100 );
				$this->prevoyance_ta_patron = $this->prevoyance_taux_ta_patron * ( ( $this->salaire_ref > $this->plafond_ss ) ? $this->plafond_ss : $this->salaire_ref );
				
				$this->prevoyance_taux_tb_salar = ( 0.57 / 100 );
				$this->prevoyance_tb_salar = $this->prevoyance_taux_tb_salar * ( ( ( $this->salaire_ref - $this->plafond_ss ) > 9510 ) ? 9510 : $this->tb_chomage );
				$this->prevoyance_taux_tb_patron = ( 0.57 / 100 );
				$this->prevoyance_tb_patron = $this->prevoyance_taux_tb_patron * ( ( ( $this->salaire_ref - $this->plafond_ss ) > 9510 ) ? 9510 : $this->tb_chomage );
			}
			if($this->sal_statut->getSlug() =="non_cadre"){
				//Cadre
				$this->prevoyance_taux_ta_salar = ( 0.37 / 100 );
				$this->prevoyance_ta_salar = $this->prevoyance_taux_ta_salar * ( ( $this->salaire_ref > $this->plafond_ss ) ? $this->plafond_ss : $this->salaire_ref );
				$this->prevoyance_taux_ta_patron = ( 0.37 / 100 );
				$this->prevoyance_ta_patron = $this->prevoyance_taux_ta_patron * ( ( $this->salaire_ref > $this->plafond_ss ) ? $this->plafond_ss : $this->salaire_ref );
				
				$this->prevoyance_taux_tb_salar = ( 0.57 / 100 );
				$this->prevoyance_tb_salar = $this->prevoyance_taux_tb_salar * ( ( ( $this->salaire_ref - $this->plafond_ss ) > 12680  ) ? 12680 : $this->tb_agric );
				$this->prevoyance_taux_tb_patron = ( 0.57 / 100 );
				$this->prevoyance_tb_patron = $this->prevoyance_taux_tb_patron * ( ( ( $this->salaire_ref - $this->plafond_ss ) > 12680  ) ? 12680 : $this->tb_agric );
				
				$this->prevoyance_taux_tc_salar = ( 0.57 / 100 );
				$this->prevoyance_tc_salar = $this->prevoyance_taux_tc_salar * ( ( ( $this->salaire_ref - $this->plafond_ss ) > 12680 ) ? 12680 : $this->tc_agric );
				$this->prevoyance_taux_tc_patron = ( 0.57 / 100 );
				$this->prevoyance_tc_patron = $this->prevoyance_taux_tc_patron * ( ( ( $this->salaire_ref - $this->plafond_ss ) > 12680 ) ? 12680 : $this->tc_agric );
			}
		}
		
		/*
		 *	##############################################################################################################################
		*	CALCULES UNIQUEMENT POUR PRESTATAIRE DE SERVICE TERTIAIRE
		*	##############################################################################################################################
		*/
		if($this->ent_conv->getSlug()=="prestataires-services"){
			if($this->sal_statut->getSlug() =="cadre"){
				//Non Cadre
				$this->prevoyance_taux_ta_salar = ( 0.396 / 100 );
				$this->prevoyance_ta_salar = $this->prevoyance_taux_ta_salar * ( ( $this->salaire_ref > $this->plafond_ss ) ? $this->plafond_ss : $this->salaire_ref );
				$this->prevoyance_taux_ta_patron = ( 0.484 / 100 );
				$this->prevoyance_ta_patron = $this->prevoyance_taux_ta_patron * ( ( $this->salaire_ref > $this->plafond_ss ) ? $this->plafond_ss : $this->salaire_ref );
				
				$this->prevoyance_taux_tb_salar = ( 0.396 / 100 );
				$this->prevoyance_tb_salar = $this->prevoyance_taux_tb_salar * ( ( ( $this->salaire_ref - $this->plafond_ss ) > 9510 ) ? 9510 : $this->tb_chomage );
				$this->prevoyance_taux_tb_patron = ( 0.484 / 100 );
				$this->prevoyance_tb_patron = $this->prevoyance_taux_tb_patron * ( ( ( $this->salaire_ref - $this->plafond_ss ) > 9510 ) ? 9510 : $this->tb_chomage );
			}
			if($this->sal_statut->getSlug() =="non_cadre"){
			//Cadre
				$this->prevoyance_taux_ta_salar = 0;
				$this->prevoyance_ta_salar = 0 ;
				$this->prevoyance_taux_ta_patron = ( 1.50 / 100 );
				$this->prevoyance_ta_patron = $this->prevoyance_taux_ta_patron * ( ( $this->salaire_ref > $this->plafond_ss ) ? $this->plafond_ss : $this->salaire_ref );
				
				$this->prevoyance_taux_tb_salar = ( 0.648 / 100 );
				$this->prevoyance_tb_salar = $this->prevoyance_taux_tb_salar * ( ( ( $this->salaire_ref - $this->plafond_ss ) > 12680  ) ? 12680 : $this->tb_agric );
				$this->prevoyance_taux_tb_patron = ( 0.792 / 100 );
				$this->prevoyance_tb_patron = $this->prevoyance_taux_tb_patron * ( ( ( $this->salaire_ref - $this->plafond_ss ) > 12680  ) ? 12680 : $this->tb_agric );
				
				$this->prevoyance_taux_tc_salar = ( 0.648 / 100 );
				$this->prevoyance_tc_salar = $this->prevoyance_taux_tc_salar * ( ( ( $this->salaire_ref - $this->plafond_ss ) > 12680 ) ? 12680 : $this->tc_agric );
				$this->prevoyance_taux_tc_patron = ( 0.792 / 100 );
				$this->prevoyance_tc_patron = $this->prevoyance_taux_tc_patron * ( ( ( $this->salaire_ref - $this->plafond_ss ) > 12680 ) ? 12680 : $this->tc_agric );
			}
		}
		
		/*
		 *	##############################################################################################################################
		*	CALCULES UNIQUEMENT POUR HOTELS CAFE RESTAURANT
		*	##############################################################################################################################
		*/
		if($this->ent_conv->getSlug()=="hotels-cafes-restaurants"){
			if($this->sal_statut->getSlug() == "cadre"){
				//Non Cadre
				$this->prevoyance_taux_ta_salar = ( 0.40 / 100 );
				$this->prevoyance_ta_salar = $this->prevoyance_taux_ta_salar * ( ( $this->salaire_ref > $this->plafond_ss ) ? $this->plafond_ss : $this->salaire_ref );
				$this->prevoyance_taux_ta_patron = ( 0.40 / 100 );
				$this->prevoyance_ta_patron = $this->prevoyance_taux_ta_patron * ( ( $this->salaire_ref > $this->plafond_ss ) ? $this->plafond_ss : $this->salaire_ref );
				
				$this->prevoyance_taux_tb_salar = ( 0.40 / 100 );
				$this->prevoyance_tb_salar = $this->prevoyance_taux_tb_salar * ( ( ( $this->salaire_ref - $this->plafond_ss ) > 9510 ) ? 9510 : $this->tb_chomage );
				$this->prevoyance_taux_tb_patron = ( 0.40 / 100 );
				$this->prevoyance_tb_patron = $this->prevoyance_taux_tb_patron * ( ( ( $this->salaire_ref - $this->plafond_ss ) > 9510 ) ? 9510 : $this->tb_chomage );
			}
			if($this->sal_statut->getSlug() == "non_cadre"){
				//Cadre
				$this->prevoyance_taux_ta_salar = ( 0.40 / 100 );
				$this->prevoyance_ta_salar = $this->prevoyance_taux_ta_salar * ( ( $this->salaire_ref > $this->plafond_ss ) ? $this->plafond_ss : $this->salaire_ref );
				$this->prevoyance_taux_ta_patron = ( 0.40 / 100 );
				$this->prevoyance_ta_patron = $this->prevoyance_taux_ta_patron * ( ( $this->salaire_ref > $this->plafond_ss ) ? $this->plafond_ss : $this->salaire_ref );
				
				$this->prevoyance_taux_tb_salar = ( 0.40 / 100 );
				$this->prevoyance_tb_salar = $this->prevoyance_taux_tb_salar * ( ( ( $this->salaire_ref - $this->plafond_ss ) > 12680  ) ? 12680 : $this->tb_agric );
				$this->prevoyance_taux_tb_patron = ( 0.40 / 100 );
				$this->prevoyance_tb_patron = $this->prevoyance_taux_tb_patron * ( ( ( $this->salaire_ref - $this->plafond_ss ) > 12680  ) ? 12680 : $this->tb_agric );
				
				$this->prevoyance_taux_tc_salar = ( 0.40 / 100 );
				$this->prevoyance_tc_salar = $this->prevoyance_taux_tc_salar * ( ( ( $this->salaire_ref - $this->plafond_ss ) > 12680 ) ? 12680 : $this->tc_agric );
				$this->prevoyance_taux_tc_patron = ( 0.40 / 100 );
				$this->prevoyance_tc_patron = $this->prevoyance_taux_tc_patron * ( ( ( $this->salaire_ref - $this->plafond_ss ) > 12680 ) ? 12680 : $this->tc_agric );
			}
		}
		
		/*
		 *	##############################################################################################################################
		*	CALCULES UNIQUEMENT POUR COMMERCE DE GROS
		*	##############################################################################################################################
		*/
		if($this->ent_conv->getSlug()=="commerce-gros"){
			if($this->sal_statut->getSlug() == "cadre"){
				//Non Cadre
				$this->prevoyance_taux_ta_salar = ( 0.156 / 100 );
				$this->prevoyance_ta_salar = $this->prevoyance_taux_ta_salar * ( ( $this->salaire_ref > $this->plafond_ss ) ? $this->plafond_ss : $this->salaire_ref );
				$this->prevoyance_taux_ta_patron = ( 0.274 / 100 );
				$this->prevoyance_ta_patron = $this->prevoyance_taux_ta_patron * ( ( $this->salaire_ref > $this->plafond_ss ) ? $this->plafond_ss : $this->salaire_ref );
				
				$this->prevoyance_taux_tb_salar = ( 0.156 / 100 );
				$this->prevoyance_tb_salar = $this->prevoyance_taux_tb_salar * ( ( ( $this->salaire_ref - $this->plafond_ss ) > 9510 ) ? 9510 : $this->tb_chomage );
				$this->prevoyance_taux_tb_patron = ( 0.274 / 100 );
				$this->prevoyance_tb_patron = $this->prevoyance_taux_tb_patron * ( ( ( $this->salaire_ref - $this->plafond_ss ) > 9510 ) ? 9510 : $this->tb_chomage );
			}
			if($this->sal_statut->getSlug() == "non_cadre"){
				//Cadre
				$this->prevoyance_taux_ta_salar = ( 0.602 / 100 );
				$this->prevoyance_ta_salar = $this->prevoyance_taux_ta_salar * ( ( $this->salaire_ref > $this->plafond_ss ) ? $this->plafond_ss : $this->salaire_ref );
				$this->prevoyance_taux_ta_patron = ( 2.198 / 100 );
				$this->prevoyance_ta_patron = $this->prevoyance_taux_ta_patron * ( ( $this->salaire_ref > $this->plafond_ss ) ? $this->plafond_ss : $this->salaire_ref );
				
				$this->prevoyance_taux_tb_salar = ( 2.26 / 100 ) ;
				$this->prevoyance_tb_salar = $this->prevoyance_taux_tb_salar * ( ( ( $this->salaire_ref - $this->plafond_ss ) > 12680  ) ? 12680 : $this->tb_agric );
				$this->prevoyance_taux_tb_patron = ( 2.28 / 100 );
				$this->prevoyance_tb_patron = $this->prevoyance_taux_tb_patron * ( ( ( $this->salaire_ref - $this->plafond_ss ) > 12680  ) ? 12680 : $this->tb_agric );
				
				$this->prevoyance_taux_tc_salar = ( 2.26 / 100 ) ;
				$this->prevoyance_tc_salar = $this->prevoyance_taux_tc_salar * ( ( ( $this->salaire_ref - $this->plafond_ss ) > 12680 ) ? 12680 : $this->tc_agric );
				$this->prevoyance_taux_tc_patron = ( 2.28 / 100 );
				$this->prevoyance_tc_patron = $this->prevoyance_taux_tc_patron * ( ( ( $this->salaire_ref - $this->plafond_ss ) > 12680 ) ? 12680 : $this->tc_agric );
			}
		}
		/*
		*	##############################################################################################################################
		*	CALCULES UNIQUEMENT POUR COMMERCE DE DETAILS
		*	##############################################################################################################################
		*/
		if($this->ent_conv->getSlug()=="commerce-detail"){
			if($this->sal_statut->getSlug() == "cadre"){
				//Non Cadre
				$this->prevoyance_taux_ta_salar = ( 0.19 / 100 );
				$this->prevoyance_ta_salar = $this->prevoyance_taux_ta_salar * ( ( $this->salaire_ref > $this->plafond_ss ) ? $this->plafond_ss : $this->salaire_ref );
				$this->prevoyance_taux_ta_patron = ( 0.25 / 100 );
				$this->prevoyance_ta_patron = $this->prevoyance_taux_ta_patron * ( ( $this->salaire_ref > $this->plafond_ss ) ? $this->plafond_ss : $this->salaire_ref );
				
				$this->prevoyance_taux_tb_salar = ( 0.19 / 100 ) ;
				$this->prevoyance_tb_salar = $this->prevoyance_taux_tb_salar * ( ( ( $this->salaire_ref - $this->plafond_ss ) > 9510 ) ? 9510 : $this->tb_chomage );
				$this->prevoyance_taux_tb_patron = ( 0.25 / 100 ) ;
				$this->prevoyance_tb_patron = $this->prevoyance_tb_patron * ( ( ( $this->salaire_ref - $this->plafond_ss ) > 9510 ) ? 9510 : $this->tb_chomage );
			}
			if($this->sal_statut->getSlug() == "non_cadre"){
				//Cadre
				$this->prevoyance_taux_ta_salar = 0 ;
				$this->prevoyance_ta_salar = 0 ;
				$this->prevoyance_taux_ta_patron = 0 ;
				$this->prevoyance_ta_patron = $this->prevoyance_ta_patron * ( ( $this->salaire_ref > $this->plafond_ss ) ? $this->plafond_ss : $this->salaire_ref );
			}
		}
		/*
		*	##############################################################################################################################
		*	CALCULES UNIQUEMENT POUR LES BANQUES
		*	##############################################################################################################################
		*/
		if($this->ent_conv->getSlug()=="banques"){
			if($this->sal_statut->getSlug() == "cadre"){
				//Non Cadre
				$this->prevoyance_taux_brut = 0.75 / 100 ;
				$this->prevoyance_brut = ( $this->prevoyance_brut_ta  ) * $this->salaire_ref ;
			}
			if($this->sal_statut->getSlug() == "non_cadre"){
				//Cadre
				$this->prevoyance_taux_brut = 1.50 / 100 ;
				$this->prevoyance_brut = ( $this->prevoyance_brut_ta  ) * ( ( $this->salaire_ref > $this->plafond_ss ) ? $this->plafond_ss : $this->salaire_ref );
			}
		}
		/*
		 *	##############################################################################################################################
		*	CALCULES UNIQUEMENT POUR LES ASSURANCES
		*	##############################################################################################################################
		*/
		if($this->ent_conv->getSlug()=="assurances"){
			if($this->sal_statut->getSlug() == "cadre"){
				//Non Cadre
				$this->prevoyance_taux_brut = ( 0.75 / 100 ) ;
				$this->prevoyance_brut = $this->prevoyance_brut_ta * $this->salaire_ref ;
			}
			if($this->sal_statut->getSlug() == "non_cadre"){
				//Cadre
				$this->prevoyance_taux_brut = ( 1.50 / 100 ) ;
				$this->prevoyance_brut = $this->prevoyance_brut_ta * ( ( $this->salaire_ref > $this->plafond_ss ) ? $this->plafond_ss : $this->salaire_ref );
			}
		}
		
		/*
		 * CALCULS SPECIQUES A PREVOYANCE
		* 5.10% de Var.Salaire réf. * 98.25% + (Val.Prévoyance TA ou Val.Prévoyance Brut)
		* SI 3.Effectif < 10  ALORS 8% de (Val.Prévoyance TA + et/ou Val.Prévoyance TB + et/ou Val.Prévoyance Brut )  SINON 8% de 0
		*/
		$this->prevoyance_csg_deduc_salar = ( (5.10/100) * $this->salaire_ref ) * (98.25/100) + (($this->prevoyance_brut)?$this->prevoyance_brut:$this->prevoyance_ta_salar);
		
		$this->prevoyance_forfait_social_patron = ($this->ent_effectif < 10)?(8/100) * ($this->prevoyance_ta_patron + $this->prevoyance_tb_patron + $this->prevoyance_brut):0;
		
		/*
		 *	##############################################################################################################################
		*	TOTAL DES RETENUS
		*	##############################################################################################################################
		*/
		
		$this->somme_montant_salar = 
			//URSSAF
			$this->urssaf_assurance_maladie_salar + $this->urssaf_av_deplafonnee_salar + $this->urssaf_av_plafonnee_salar + $this->urssaf_chomage_trancheA_salar + $this->urssaf_chomage_trancheB_salar +
			//RETRAITE
			$this->cretraite_complement_agff_tranche1_salar + $this->cretraite_complement_agff_tranche2_salar + $this->cretraite_complement_arrco_tranche1_salar +
			//PREVOYANCE
			$this->prevoyance_csg_deduc_salar + $this->prevoyance_ta_salar + $this->prevoyance_tb_salar + $this->prevoyance_tc_salar
		;
			
		$this->somme_montant_patron = 
			//URSSAF
			$this->urssaf_accident_travail_patron + $this->urssaf_alloc_famill_complement_patron + $this->urssaf_alloc_famill_patron + $this->urssaf_assurance_maladie_patron + $this->urssaf_av_deplafonnee_patron + $this->urssaf_av_plafonnee_patron + $this->urssaf_chomage_trancheA_patron + $this->urssaf_chomage_trancheAGS_patron + $this->urssaf_chomage_trancheB_patron + $this->urssaf_contribution_solidarité_patron + $this->urssaf_fnal_patron + $this->urssaf_ossope_patron + $this->urssaf_reduc_fillon_patron + $this->urssaf_transport_patron +
			//RETRAITE
			$this->cretraite_complement_agff_tranche1_patron + $this->cretraite_complement_agff_tranche2_patron + $this->cretraite_complement_arrco_tranche1_patron +
			//TAXE
			$this->taxediv_apprentissage_patron + $this->taxediv_contrib_apprenstissage_patron + $this->taxediv_participation_entre_mini_10_patron +
			//PREVOYANCE
			$this->prevoyance_forfait_social_patron + $this->prevoyance_ta_patron + $this->prevoyance_tb_patron + $this->prevoyance_tc_patron
		;
				
		if($this->ent_conv->getSlug()=='cadre'){
			$this->somme_montant_salar += $this->cadre_retraite_compl_agric_trancheB_salar + $this->cadre_retraite_compl_agric_trancheC_salar + $this->cet_salar + $this->apec_ta_salar + $this->apec_tb_salar;
			$this->somme_montant_patron += $this->cadre_retraite_compl_agric_trancheB_patron + $this->cadre_retraite_compl_agric_trancheC_patron + $this->cet_patron + $this->apec_ta_patron + $this->apec_tb_patron;
		}else{
			$this->somme_montant_salar += $this->cretraite_complement_arrco_tranche2_salar;
			$this->somme_montant_patron += $this->cretraite_complement_arrco_tranche1_patron;
		}
		
		/*
			*	##############################################################################################################################
		*	CALCULES SPECIFIQUES RECAPITULATIF
		*	##############################################################################################################################
		*/

		$this->recap_net_impossable = $this->salaire_ref - $this->somme_montant_salar;
		$this->recap_urssaf_csg_non_deduc = (2.4/100) * ($this->salaire_ref * (98.25/100) + $this->prevoyance_ta_patron + $this->prevoyance_tb_patron + $this->prevoyance_brut);
		$this->recap_urssaf_cdrs = (0.5/100) * ($this->salaire_ref * (98.25/100) + $this->prevoyance_ta_patron + $this->prevoyance_tb_patron + $this->prevoyance_brut);		
		$this->recap_heure_periode = $this->paie_nb_heure + $this->paie_hsupp_125 + $this->paie_hsupp_150;
		$this->recap_cumul_heure = $this->recap_heure_periode + $this->conge_cumul_heures + $this->conge_cumul_hsup;
		$this->recap_cumul_heure_sup = $this->conge_cumul_hsup + $this->paie_hsupp_125 + $this->paie_hsupp_150;
		$this->recap_cumul_bases = $this->getWorkedMonthOfTheYear() * $base_av_plafonnee;
		$this->recap_cumul_brut = $this->salaire_ref;
		$this->recap_total_cotis_pat = $this->somme_montant_patron;
		$this->recap_total_retenue = $this->somme_montant_salar + $this->somme_montant_patron + $this->recap_urssaf_csg_non_deduc + $this->recap_urssaf_cdrs;
		$this->recap_cout_globale = $this->salaire_ref + $this->somme_montant_patron;

		$this->recap_net_a_payer = $this->recap_net_impossable - $this->recap_urssaf_csg_non_deduc - $this->recap_urssaf_cdrs + $this->remb_frais_transport  - $this->remb_avant_nature;
	}
	
	public function getWorkedMonthOfTheYear()
	{
		$month = $this->periode_au->format('m');
		if($this->sal_date_entree->getTimestamp() > mktime(0,0,0,1,1,date('y',$this->periode_au->getTimestamp())) )
			$month = $this->periode_au->format('m');
		return $month;
	}
}