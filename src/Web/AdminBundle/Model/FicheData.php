<?php
namespace Web\AdminBundle\Model;

use Web\AdminBundle\Entity\FicheDePaie;

class FicheData
{
	
		public $ent_name ;
		public $ent_adresse ;
		public $ent_siret ;
		public $ent_urssaf ;
		public $ent_naf ;
		public $ent_conv ;
		public $ent_effectif ;
		public $sal_nom ;
		public $sal_adresse ;
		public $sal_secu ;
		public $sal_contrat ;
		public $sal_date_entree ;
		public $sal_statut ;
		public $sal_emploi ;
		public $sal_classification ;
		public $sal_coeff ;
		public $sal_matricule ;
		public $periode_du ;
		public $periode_au ;
		public $periode_date_paiement ;
		public $periode_month ;
		public $paie_salaire_brut ;
		public $paie_salaire_net ;
		public $paie_nb_heure ;
		public $paie_tx_horaire ;
		public $paie_hsupp_125 ;
		public $paie_hsupp_150 ;
		public $paie_prime ;
		public $paie_tx_transport ;
		public $paie_tx_at ;
		public $conge_paye_jrs ;
		public $conge_amaladie_jrs ;
		public $conge_sanssolde_jrs ;
		public $conge_acquis ;
		public $conge_pris ;
		public $conge_solde ;
		public $conge_cumul_heures ;
		public $conge_cumul_hsup ;
		public $conge_cumul_ta ;
		public $conge_cumul_brut ;
		public $conge_cumul_impossable ;
		public $remb_frais_transport ;
		public $remb_frais_autre ;
		public $remb_avant_nature ;
		public $smic_heure;
		public $smic_mois;
		public $plafond_ss;
		public $nb_jours;
		public $salaire_ref;
		public $tb_chomage;
		public $tb_agric;
		public $tc_agric;
		public $valeur_t;
		public $conge_paye_n_moins_1;
		public $conge_mois;
		public $urssaf_assurance_maladie_salar;
		public $urssaf_assurance_maladie_patron;
		public $urssaf_contribution_solidarité_patron;
		public $urssaf_av_plafonnee_salar;
		public $urssaf_av_plafonnee_patron;
		public $urssaf_av_deplafonnee_salar;
		public $urssaf_av_deplafonnee_patron;
		public $urssaf_accident_travail_patron;
		public $urssaf_transport_patron;
		public $urssaf_alloc_famill_patron;
		public $urssaf_alloc_famill_complement_patron;
		public $urssaf_ossope_patron;
		public $urssaf_fnal_patron;
		public $urssaf_reduc_fillon_patron;
		public $urssaf_chomage_trancheA_base;
		public $urssaf_chomage_trancheA_salar;
		public $urssaf_chomage_trancheA_patron;
		public $urssaf_chomage_trancheB_base;
		public $urssaf_chomage_trancheB_salar;
		public $urssaf_chomage_trancheB_patron;
		public $urssaf_chomage_trancheAGS_patron;
		public $cretraite_complement_arrco_tranche1_salar;
		public $cretraite_complement_arrco_tranche1_patron;
		public $cretraite_complement_agff_tranche1_salar;
		public $cretraite_complement_agff_tranche1_patron;
		public $cretraite_complement_agff_tranche2_salar;
		public $cretraite_complement_agff_tranche2_patron;
		public $prevoyance_csg_deduc_salar;
		public $prevoyance_forfait_social_patron;
		public $taxediv_apprentissage_patron;
		public $taxediv_contrib_apprenstissage_patron;
		public $taxediv_participation_entre_mini_10_patron;
		public $cadre_retraite_compl_agric_trancheB_salar;
		public $cadre_retraite_compl_agric_trancheB_patron;
		public $cadre_retraite_compl_agric_trancheC_salar;
		public $cadre_retraite_compl_agric_trancheC_patron;
		public $cet_salar;
		public $cet_patron;
		public $apec_ta_salar;
		public $apec_ta_patron;
		public $apec_tb_salar;
		public $apec_tb_patron;
		public $cretraite_complement_arrco_tranche2_salar;
		public $cretraite_complement_arrco_tranche2_patron;
		public $prevoyance_taux_ta_salar;
		public $prevoyance_taux_ta_patron;
		public $prevoyance_ta_salar;
		public $prevoyance_ta_patron;
		public $prevoyance_taux_tb_salar;
		public $prevoyance_taux_tb_patron;
		public $prevoyance_tb_salar;
		public $prevoyance_tb_patron;
		public $prevoyance_taux_tc_salar;
		public $prevoyance_taux_tc_patron;
		public $prevoyance_tc_salar;
		public $prevoyance_tc_patron;
		public $prevoyance_taux_brut;
		public $prevoyance_brut	;
		public $recap_net_impossable;
		public $recap_urssaf_csg_non_deduc;
		public $recap_urssaf_cdrs;
		public $recap_heure_periode;
		public $recap_cumul_heure;
		public $recap_cumul_heure_sup;
		public $recap_cumul_bases;
		public $recap_cumul_brut;
		public $recap_total_cotis_pat;
		public $recap_total_retenue;
		public $recap_cout_globale;
		public $recap_net_a_payer;
		public $somme_montant_salar;
		public $somme_montant_patron;


		public $vars = array();
		
	public function process(){
		
	}
	
	function __construct(FicheDePaie $fdp, $vars = array()) {
		
		$this->vars = $vars;
		
		// Entreprise
		$this->ent_name 			= $fdp->getNomEntreprise();
		$this->ent_adresse 			= $fdp->getAdresseEntreprise();
		$this->ent_siret 			= $fdp->getNumSiret();
		$this->ent_urssaf 			= $fdp->getUrssafRegionale();
		$this->ent_naf 				= $fdp->getCodeNaf();
		$this->ent_conv 			= $fdp->getConventionCollective();
		$this->ent_effectif 		= $fdp->getEffectif();
	
		// Salarié
		$this->sal_nom 				= $fdp->getPrenomSalarie();
		$this->sal_adresse 			= $fdp->getAdresseSalarie();
		$this->sal_secu 			= $fdp->getNumSecu();
		$this->sal_contrat 			= $fdp->getTypeContrat();
		$this->sal_date_entree 		= $fdp->getDateEntree();
		$this->sal_statut 			= $fdp->getStatusSalarie();
		$this->sal_emploi 			= $fdp->getEmploi();
		$this->sal_classification 	= $fdp->getPosition();
		$this->sal_coeff 			= $fdp->getCoefficient();
		$this->sal_matricule 		= $fdp->getMatricule();
	
		// Période - /!\ LES DATES doivent avoir le format classique anglais
		$this->periode_du 			= $fdp->getPeriodeDu();
		$this->periode_au 			= $fdp->getPeriodeAu();
		$this->periode_date_paiement 	= $fdp->getDatePaiement();
		
		// Elements de paie
		$this->paie_salaire_brut 	= $fdp->getSalaireBrutMensuel();
		$this->paie_salaire_net 	= 1669.80; //TODO calcul
		$this->paie_nb_heure 		= $fdp->getNbHeures();
		$this->paie_tx_horaire 		= 14.30; //TODO calcul
		$this->paie_hsupp_125 		= $fdp->getHeuresSup125();
		$this->paie_hsupp_150 		= $fdp->getHeuresSup150();
		$this->paie_prime 			= $fdp->getPrime();
		$this->paie_tx_transport 	= $fdp->getTauxTransport();
		$this->paie_tx_at 			= $fdp->getTauxAccidentTravail();
	
		// Congés
		$this->conge_paye_jrs 		= $fdp->getCongesPayes();
		$this->conge_amaladie_jrs 	= $fdp->getArretMaladie();
		$this->conge_sanssolde_jrs 	= 0; //TODO Calcul
		$this->conge_acquis 		= $fdp->getCongesAcquis();
		$this->conge_pris 			= $fdp->getCongesPris();
		$this->conge_solde 			= 14.56; // TODO calcul
		$this->conge_cumul_heures 	= $fdp->getCumulHeures();
		$this->conge_cumul_hsup 	= $fdp->getCumulHeuresSup();
		$this->conge_cumul_ta 		= $fdp->getCumulBasesTA();
		$this->conge_cumul_brut 	= $fdp->getCumulBrut();
		$this->conge_cumul_impossable = $fdp->getCumulImpossable();
	
		// Remboursement
		$this->remb_frais_transport = $fdp->getFraisTransport();
		$this->remb_frais_autre 	= $fdp->getAutreFrais();
		$this->remb_avant_nature 	= $fdp->getAvantageNature();
		
		$this->process();
	}
	
	public function getEntName() {
		return $this->ent_name;
	}
	public function setEntName($ent_name) {
		$this->ent_name = $ent_name;
		return $this;
	}
	public function getEntAdresse() {
		return $this->ent_adresse;
	}
	public function setEntAdresse($ent_adresse) {
		$this->ent_adresse = $ent_adresse;
		return $this;
	}
	public function getEntSiret() {
		return $this->ent_siret;
	}
	public function setEntSiret($ent_siret) {
		$this->ent_siret = $ent_siret;
		return $this;
	}
	public function getEntUrssaf() {
		return $this->ent_urssaf;
	}
	public function setEntUrssaf($ent_urssaf) {
		$this->ent_urssaf = $ent_urssaf;
		return $this;
	}
	public function getEntNaf() {
		return $this->ent_naf;
	}
	public function setEntNaf($ent_naf) {
		$this->ent_naf = $ent_naf;
		return $this;
	}
	public function getEntConv() {
		return $this->ent_conv;
	}
	public function setEntConv($ent_conv) {
		$this->ent_conv = $ent_conv;
		return $this;
	}
	public function getEntEffectif() {
		return $this->ent_effectif;
	}
	public function setEntEffectif($ent_effectif) {
		$this->ent_effectif = $ent_effectif;
		return $this;
	}
	public function getSalNom() {
		return $this->sal_nom;
	}
	public function setSalNom($sal_nom) {
		$this->sal_nom = $sal_nom;
		return $this;
	}
	public function getSalAdresse() {
		return $this->sal_adresse;
	}
	public function setSalAdresse($sal_adresse) {
		$this->sal_adresse = $sal_adresse;
		return $this;
	}
	public function getSalSecu() {
		return $this->sal_secu;
	}
	public function setSalSecu($sal_secu) {
		$this->sal_secu = $sal_secu;
		return $this;
	}
	public function getSalContrat() {
		return $this->sal_contrat;
	}
	public function setSalContrat($sal_contrat) {
		$this->sal_contrat = $sal_contrat;
		return $this;
	}
	public function getSalDateEntree() {
		return $this->sal_date_entree;
	}
	public function setSalDateEntree($sal_date_entree) {
		$this->sal_date_entree = $sal_date_entree;
		return $this;
	}
	public function getSalStatut() {
		return $this->sal_statut;
	}
	public function setSalStatut($sal_statut) {
		$this->sal_statut = $sal_statut;
		return $this;
	}
	public function getSalEmploi() {
		return $this->sal_emploi;
	}
	public function setSalEmploi($sal_emploi) {
		$this->sal_emploi = $sal_emploi;
		return $this;
	}
	public function getSalClassification() {
		return $this->sal_classification;
	}
	public function setSalClassification($sal_classification) {
		$this->sal_classification = $sal_classification;
		return $this;
	}
	public function getSalCoeff() {
		return $this->sal_coeff;
	}
	public function setSalCoeff($sal_coeff) {
		$this->sal_coeff = $sal_coeff;
		return $this;
	}
	public function getSalMatricule() {
		return $this->sal_matricule;
	}
	public function setSalMatricule($sal_matricule) {
		$this->sal_matricule = $sal_matricule;
		return $this;
	}
	public function getPeriodeDu() {
		return $this->periode_du;
	}
	public function setPeriodeDu($periode_du) {
		$this->periode_du = $periode_du;
		return $this;
	}
	public function getPeriodeAu() {
		return $this->periode_au;
	}
	public function setPeriodeAu($periode_au) {
		$this->periode_au = $periode_au;
		return $this;
	}
	public function getPeriodeDatePaiement() {
		return $this->periode_date_paiement;
	}
	public function setPeriodeDatePaiement($periode_date_paiement) {
		$this->periode_date_paiement = $periode_date_paiement;
		return $this;
	}
	public function getPeriodeMonth() {
		return $this->periode_month;
	}
	public function setPeriodeMonth($periode_month) {
		$this->periode_month = $periode_month;
		return $this;
	}
	public function getPaieSalaireBrut() {
		return $this->paie_salaire_brut;
	}
	public function setPaieSalaireBrut($paie_salaire_brut) {
		$this->paie_salaire_brut = $paie_salaire_brut;
		return $this;
	}
	public function getPaieSalaireNet() {
		return $this->paie_salaire_net;
	}
	public function setPaieSalaireNet($paie_salaire_net) {
		$this->paie_salaire_net = $paie_salaire_net;
		return $this;
	}
	public function getPaieNbHeure() {
		return $this->paie_nb_heure;
	}
	public function setPaieNbHeure($paie_nb_heure) {
		$this->paie_nb_heure = $paie_nb_heure;
		return $this;
	}
	public function getPaieTxHoraire() {
		return $this->paie_tx_horaire;
	}
	public function setPaieTxHoraire($paie_tx_horaire) {
		$this->paie_tx_horaire = $paie_tx_horaire;
		return $this;
	}
	public function getPaieHsupp125() {
		return $this->paie_hsupp_125;
	}
	public function setPaieHsupp125($paie_hsupp_125) {
		$this->paie_hsupp_125 = $paie_hsupp_125;
		return $this;
	}
	public function getPaieHsupp150() {
		return $this->paie_hsupp_150;
	}
	public function setPaieHsupp150($paie_hsupp_150) {
		$this->paie_hsupp_150 = $paie_hsupp_150;
		return $this;
	}
	public function getPaiePrime() {
		return $this->paie_prime;
	}
	public function setPaiePrime($paie_prime) {
		$this->paie_prime = $paie_prime;
		return $this;
	}
	public function getPaieTxTransport() {
		return $this->paie_tx_transport;
	}
	public function setPaieTxTransport($paie_tx_transport) {
		$this->paie_tx_transport = $paie_tx_transport;
		return $this;
	}
	public function getPaieTxAt() {
		return $this->paie_tx_at;
	}
	public function setPaieTxAt($paie_tx_at) {
		$this->paie_tx_at = $paie_tx_at;
		return $this;
	}
	public function getCongePayeJrs() {
		return $this->conge_paye_jrs;
	}
	public function setCongePayeJrs($conge_paye_jrs) {
		$this->conge_paye_jrs = $conge_paye_jrs;
		return $this;
	}
	public function getCongeAmaladieJrs() {
		return $this->conge_amaladie_jrs;
	}
	public function setCongeAmaladieJrs($conge_amaladie_jrs) {
		$this->conge_amaladie_jrs = $conge_amaladie_jrs;
		return $this;
	}
	public function getCongeSanssoldeJrs() {
		return $this->conge_sanssolde_jrs;
	}
	public function setCongeSanssoldeJrs($conge_sanssolde_jrs) {
		$this->conge_sanssolde_jrs = $conge_sanssolde_jrs;
		return $this;
	}
	public function getCongeAcquis() {
		return $this->conge_acquis;
	}
	public function setCongeAcquis($conge_acquis) {
		$this->conge_acquis = $conge_acquis;
		return $this;
	}
	public function getCongePris() {
		return $this->conge_pris;
	}
	public function setCongePris($conge_pris) {
		$this->conge_pris = $conge_pris;
		return $this;
	}
	public function getCongeSolde() {
		return $this->conge_solde;
	}
	public function setCongeSolde($conge_solde) {
		$this->conge_solde = $conge_solde;
		return $this;
	}
	public function getCongeCumulHeures() {
		return $this->conge_cumul_heures;
	}
	public function setCongeCumulHeures($conge_cumul_heures) {
		$this->conge_cumul_heures = $conge_cumul_heures;
		return $this;
	}
	public function getCongeCumulHsup() {
		return $this->conge_cumul_hsup;
	}
	public function setCongeCumulHsup($conge_cumul_hsup) {
		$this->conge_cumul_hsup = $conge_cumul_hsup;
		return $this;
	}
	public function getCongeCumulTa() {
		return $this->conge_cumul_ta;
	}
	public function setCongeCumulTa($conge_cumul_ta) {
		$this->conge_cumul_ta = $conge_cumul_ta;
		return $this;
	}
	public function getCongeCumulBrut() {
		return $this->conge_cumul_brut;
	}
	public function setCongeCumulBrut($conge_cumul_brut) {
		$this->conge_cumul_brut = $conge_cumul_brut;
		return $this;
	}
	public function getCongeCumulImpossable() {
		return $this->conge_cumul_impossable;
	}
	public function setCongeCumulImpossable($conge_cumul_impossable) {
		$this->conge_cumul_impossable = $conge_cumul_impossable;
		return $this;
	}
	public function getRembFraisTransport() {
		return $this->remb_frais_transport;
	}
	public function setRembFraisTransport($remb_frais_transport) {
		$this->remb_frais_transport = $remb_frais_transport;
		return $this;
	}
	public function getRembFraisAutre() {
		return $this->remb_frais_autre;
	}
	public function setRembFraisAutre($remb_frais_autre) {
		$this->remb_frais_autre = $remb_frais_autre;
		return $this;
	}
	public function getRembAvantNature() {
		return $this->remb_avant_nature;
	}
	public function setRembAvantNature($remb_avant_nature) {
		$this->remb_avant_nature = $remb_avant_nature;
		return $this;
	}
	public function getSmicHeure() {
		return $this->smic_heure;
	}
	public function setSmicHeure($smic_heure) {
		$this->smic_heure = $smic_heure;
		return $this;
	}
	public function getSmicMois() {
		return $this->smic_mois;
	}
	public function setSmicMois($smic_mois) {
		$this->smic_mois = $smic_mois;
		return $this;
	}
	public function getPlafondSs() {
		return $this->plafond_ss;
	}
	public function setPlafondSs($plafond_ss) {
		$this->plafond_ss = $plafond_ss;
		return $this;
	}
	public function getNbJoursNovembre() {
		return $this->nb_jours_novembre;
	}
	public function setNbJoursNovembre($nb_jours_novembre) {
		$this->nb_jours_novembre = $nb_jours_novembre;
		return $this;
	}
	public function getSalaireRef() {
		return $this->salaire_ref;
	}
	public function setSalaireRef($salaire_ref) {
		$this->salaire_ref = $salaire_ref;
		return $this;
	}
	public function getTbChomage() {
		return $this->tb_chomage;
	}
	public function setTbChomage($tb_chomage) {
		$this->tb_chomage = $tb_chomage;
		return $this;
	}
	public function getTbAgric() {
		return $this->tb_agric;
	}
	public function setTbAgric($tb_agric) {
		$this->tb_agric = $tb_agric;
		return $this;
	}
	public function getTcAgric() {
		return $this->tc_agric;
	}
	public function setTcAgric($tc_agric) {
		$this->tc_agric = $tc_agric;
		return $this;
	}
	public function getValeurT() {
		return $this->valeur_t;
	}
	public function setValeurT($valeur_t) {
		$this->valeur_t = $valeur_t;
		return $this;
	}
	public function getCongeMois() {
		return $this->conge_mois;
	}
	public function setCongeMois($conge_mois) {
		$this->conge_mois = $conge_mois;
		return $this;
	}
	public function getVars($key=null) {
		if($key)
			return $this->vars[$key];
		return $this->vars;
	}
	public function setVars($vars) {
		$this->vars = $vars;
		return $this;
	}	
}