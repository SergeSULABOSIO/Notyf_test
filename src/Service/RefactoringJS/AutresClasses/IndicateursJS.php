<?php

namespace App\Service\RefactoringJS\AutresClasses;


interface IndicateursJS
{
    /**
     * Indicateurs relatifs au risque
     */
    public function getIndicaRisquePrimeReassurance();
    public function getIndicaRisquePrimeTotale();
    public function getIndicaRisquePrimeNette();
    public function getIndicaRisqueAccessoires();
    public function getIndicaRisqueTaxeRegulateur();
    public function getIndicaRisqueTaxeAssureur();
    public function getIndicaRisqueFronting();

    /**
     * Indicateurs relatifs aux revenus
     */
    public function getIndicaRevenuTotal(?int $typeRevenu);
    public function getIndicaRevenuNet(?int $typeRevenu);
    public function getIndicaRevenuPartageable(?int $typeRevenu);
    public function getIndicaRevenuTaxeAssureur(?int $typeRevenu);
    public function getIndicaRevenuTaxeCourtier(?int $typeRevenu);
    public function getIndicaRevenuReserve(?int $typeRevenu);

    /**
     * Indicateurs relatifs aux rétrocommissions
     */
    public function getIndicaPartenaireRetrocom(?int $typeRevenu);
}
