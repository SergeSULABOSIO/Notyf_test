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
    public const NOTE_PERIODE = "Période";
    public const NOTE_TRANCHE = "Tranche";
    public const NOTE_TYPE = "Note";
    public const NOTE_PRIME_TTC = "Prime_TTC";
    public const NOTE_PRIME_NETTE = "Prime_HT";
    public const NOTE_PRIME_FRONTING = "Fronting";
    public const NOTE_PRIME_TVA = "Taxe_Assureur";
    public const NOTE_TAUX = "Taux";
    public const NOTE_MONTANT_NET = "Montant";
    public const NOTE_TVA = "Taxes";
    public const NOTE_MONTANT_TTC = "Total_Dû";
    public const RISQUE_PRIME_TTC = "Prime totale";
    public const REVENU_NET_PARTAGEABLE = "Revenue_net_partageable";
    public const REVENU_ASSIETTE_PARTAGEABLE = "Revenue_assiette_partageable";
    public const REVENU_TVA_PARTAGEABLE = "Revenue_tva_partageable";
    public const REVENU_ARCA_PARTAGEABLE = "Revenue_arca_partageable";
    public const REVENU_TAUX = "Revenue_taux";
    public const PARTENAIRE_PART = "Partenaire_taux";
    public const PARTENAIRE_RETRCOMMISSION = "Partenaire_retrocom";
    public const PARTENAIRE_RETRCOMMISSION_PAYEE = "Partenaire_retrocom_payee";
    public const PARTENAIRE_RETRCOMMISSION_SOLDE = "Partenaire_retrocom_solde";

    //Production du paiement
    public function executer();
}
