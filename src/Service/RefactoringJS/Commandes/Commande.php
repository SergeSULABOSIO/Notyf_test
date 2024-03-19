<?php

namespace App\Service\RefactoringJS\Commandes;


interface Commande
{
    public const NOTE_NO = "No";
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

    //Production du paiement
    public function executer();
}
