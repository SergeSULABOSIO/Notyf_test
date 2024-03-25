<?php

namespace App\Service\RefactoringJS\AutresClasses;

use App\Entity\Client;
use App\Entity\Police;
use App\Entity\Assureur;
use App\Entity\Cotation;
use App\Entity\Partenaire;
use App\Entity\Produit;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

interface IndicateursJS
{
    /**
     * Indicateurs relatifs au risque
     */
    public function getIndicaRisquePolice():?Police;
    public function getIndicaRisqueCotation():?Cotation;
    public function getIndicaRisqueClient():?Client;
    public function getIndicaRisque():?Produit;
    public function getIndicaRisqueAssureur():?Assureur;
    public function getIndicaRisqueContacts():?ArrayCollection;
    public function getIndicaRisqueReferencePolice():?string;
    public function getIndicaRisquePrimeReassurance():?float;
    public function getIndicaRisquePrimeTotale():?float;
    public function getIndicaRisquePrimeNette():?float;
    public function getIndicaRisqueAccessoires():?float;
    public function getIndicaRisqueTaxeRegulateur():?float;
    public function getIndicaRisqueTaxeAssureur():?float;
    public function getIndicaRisqueFronting():?float;

    /**
     * Indicateurs relatifs aux revenus
     */
    public function getIndicaRevenuNet(?int $typeRevenu, ?int $partageable = null):?float;
    public function getIndicaRevenuTaxeCourtier(?int $typeRevenu = null, ?int $partageable = null):?float;
    public function getIndicaRevenuTaxeAssureur(?int $typeRevenu = null, ?int $partageable = null):?float;
    public function getIndicaRevenuPartageable(?int $typeRevenu = null, ?int $partageable = null):?float;
    public function getIndicaRevenuTotal(?int $typeRevenu = null, ?int $partageable = null):?float;
    public function getIndicaRevenuReserve(?int $typeRevenu = null):?float;

    /**
     * Indicateurs relatifs aux rétrocommissions
     */
    public function getIndicaPartenaireRetrocom(?int $typeRevenu = null):?float;
    public function getIndicaPartenaire():?Partenaire;
}
