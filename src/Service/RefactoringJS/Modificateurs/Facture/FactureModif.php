<?php

namespace App\Service\RefactoringJS\Modificateurs\Facture;

use App\Entity\ElementFacture;
use App\Entity\Facture;
use PhpParser\Node\Expr\Cast\Bool_;

interface FactureModif
{
    public const PARAM_FINAL = "final";
    public const PARAM_DIFFERENCES = "differences";
    public const PARAM_SAME_MONTANT = "sameMontant";
    public const PARAM_SAME_PARTENAIRE = "samePartenaire";
    public const PARAM_SAME_CLIENT = "sameClient";
    public const PARAM_SAME_ASSUREUR = "sameAssureur";
    public const PARAM_SAME_TRANCHE = "sameTranche";


    public function getFacture();
    public function setFacture(?Facture $facture);
    public function updateDescriptionFacture(?string $description);
    public function OnCheckCritereIdentification():?bool;
    public function getNewElementsFacture():array;
    public function editNewElementsFacture();
    //Production de la facture
    public function rebuildFacture():?Facture;
    public function saveFacture();
    public function reset();
}
