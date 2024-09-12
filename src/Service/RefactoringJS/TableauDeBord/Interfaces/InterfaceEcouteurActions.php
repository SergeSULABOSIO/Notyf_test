<?php

namespace App\Service\RefactoringJS\TableauDeBord\Interfaces;

use App\Service\RefactoringJS\TableauDeBord\Concrets\EvenementIndicateur;

interface InterfaceEcouteurActions
{
    public function onAfterUpdate(EvenementIndicateur $event);
    public function onBeforeUpdated(EvenementIndicateur $event);
    public function onUpdating(EvenementIndicateur $event);
    public function onError(EvenementIndicateur $event);
    public function setIndicateur(InterfaceIndicateur $indicateur);
    public function getIndicateur():InterfaceIndicateur;
}
