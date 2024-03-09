<?php

namespace App\Service\RefactoringJS\Modificateurs\Facture;

use App\Entity\ElementFacture;
use App\Entity\Facture;

interface FactureModif
{
    public function getFacture():?Facture;
    public function setFacture(?Facture $facture):self;
    public function updateDescriptionFacture(?string $description);
    public function OnGetMontant(?ElementFacture $elementFacture):?float;
    public function OnCheckCritereIdentification(?ElementFacture $elementFacture):?bool;
    public function getNewElementsFacture():array;
    public function editNewElementsFacture():self;
    public function applyChoiceOfNotesIncluded():self;
    public function isSameDestination($existingTabElementsFacture, ?ElementFacture $elementFacture):?bool;
    //Production de la facture
    public function getUpdatedFacture(?Facture $oldFacture):?Facture;
}
