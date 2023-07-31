<?php

namespace App\Entity;



class CalculableEntity
{
    //POLICES
    public $calc_polices_primes_nette = 0;
    public $calc_polices_fronting = 0;
    public $calc_polices_accessoire = 0;
    public $calc_polices_tva = 0;
    public $calc_polices_primes_totale = 0;

    //SINISTRE
    public $calc_sinistre_dommage_total = 0;
    public $calc_sinistre_indemnisation_total = 0;
    public $calc_sinistre_indice_SP = 0;

    //SECTION - REVENU
    public $calc_revenu_reserve = 0;
    public $calc_revenu_partageable = 0;
    public $calc_revenu_ht = 0;
    public $calc_revenu_ttc = 0;
    public $calc_revenu_ttc_encaisse = 0;
    public $calc_revenu_ttc_solde_restant_du = 0;

    //SECTION - PARTENAIRES
    public $calc_retrocom = 0;
    public $calc_retrocom_payees = 0;
    public $calc_retrocom_solde = 0;
    
    //SECTION - TAXES - COURTIER
    public $calc_taxes_courtier_tab = [];
    public $calc_taxes_courtier = 0;
    public $calc_taxes_courtier_payees = 0;
    public $calc_taxes_courtier_solde = 0;
    //SECTION - TAXES - ASSUREUR
    public $calc_taxes_assureurs_tab = [];
    public $calc_taxes_assureurs = 0;
    public $calc_taxes_assureurs_payees = 0;
    public $calc_taxes_assureurs_solde = 0;
}
