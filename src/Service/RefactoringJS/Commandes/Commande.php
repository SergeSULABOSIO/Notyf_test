<?php

namespace App\Service\RefactoringJS\Commandes;


interface Commande
{
    //COLONNES - ARTICLE
    public const NOTE_NO = "No";
    public const NOMBRE_ARTICLE = "No_Articles";
    public const NOTE_REFERENCE_POLICE = "Reference_Police";
    public const NOTE_AVENANT = "Avenant";
    public const NOTE_RISQUE = "Risque";
    public const NOTE_CLIENT = "Client";
    public const NOTE_PERIODE = "Période";
    public const NOTE_TRANCHE = "Tranche";
    public const NOTE_TYPE = "Note";
    public const NOTE_PRIME_TTC = "Prime_TTC";
    public const NOTE_TOTAL_A_PAYER = "Total_a_payer";
    public const NOTE_PRIME_NETTE = "Prime_HT";
    public const NOTE_PRIME_FRONTING = "Fronting";
    public const NOTE_PRIME_TVA = "Taxe_Assureur";
    public const NOTE_TAUX = "Taux";
    public const PARTENAIRE_RETROCOM_PART = "Part";
    public const NOTE_MONTANT_NET = "Montant";
    public const NOTE_TVA = "Taxes";
    public const NOTE_MONTANT_TTC = "Total_Dû";
    public const RISQUE_PRIME_TTC = "Prime totale";
    public const REVENU_NET_PARTAGEABLE = "Revenue_net_partageable";
    public const REVENU_NET = "Revenue_net";
    public const REVENU_ASSIETTE_PARTAGEABLE = "Revenue_assiette_partageable";
    public const REVENU_TVA_PARTAGEABLE = "Revenue_tva_partageable";
    public const REVENU_ARCA_PARTAGEABLE = "Revenue_arca_partageable";
    public const REVENU_TAXE_COURTIER = "Revenue_taxe_courtier";
    public const REVENU_TAXE_ASSUREUR = "Revenue_taxe_assureur";
    public const REVENU_TAXE_ASSUREUR_TAUX = "Revenue_taxe_assureur_taux";
    public const REVENU_TAXE_COURTIER_TAUX = "Revenue_taxe_courtier_taux";
    public const REVENU_TAXE_COURTIER_PAYEE = "Revenue_taxe_courtier_payee";
    public const REVENU_TAXE_ASSUREUR_PAYEE = "Revenue_taxe_assureur_payee";
    public const REVENU_TAXE_COURTIER_SOLDE = "Revenue_taxe_courtier_solde";
    public const REVENU_TAXE_ASSUREUR_SOLDE = "Revenue_taxe_assureur_solde";
    public const REVENU_TAUX = "Revenue_taux";
    public const PARTENAIRE_RETROCOMMISSION_PART = "Partenaire_taux";
    public const PARTENAIRE_RETROCOMMISSION = "Partenaire_retrocom";
    public const PARTENAIRE_RETROCOMMISSION_PAYEE = "Partenaire_retrocom_payee";
    public const PARTENAIRE_RETROCOMMISSION_SOLDE = "Partenaire_retrocom_solde";

    //Production du paiement
    public function executer();
}
