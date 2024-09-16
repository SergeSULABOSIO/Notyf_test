<?php

namespace App\Service\RefactoringJS\TableauDeBord\Interfaces;

use App\Service\RefactoringJS\TableauDeBord\Concrets\EvenementIndicateur;
use App\Service\RefactoringJS\TableauDeBord\Concrets\EvenementIndicateurConcret;

interface InterfaceEcouteurActions
{
    public function onAfterUpdate(EvenementIndicateurConcret $event);
    public function onBeforeUpdated(EvenementIndicateurConcret $event);
    public function onUpdating(EvenementIndicateurConcret $event);
    public function onError(EvenementIndicateurConcret $event);
    public function setIndicateur(InterfaceIndicateur $indicateur);
    public function getIndicateur():InterfaceIndicateur;
}
