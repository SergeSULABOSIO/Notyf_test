<?php

namespace App\Service\RefactoringJS\TableauDeBord\Concrets;

use App\Service\RefactoringJS\TableauDeBord\Interfaces\InterfaceIndicateur;
use App\Service\RefactoringJS\TableauDeBord\Concrets\EvenementIndicateurConcret;
use App\Service\RefactoringJS\TableauDeBord\Interfaces\InterfaceEcouteurActions;

class EcouteurActionsConcret implements InterfaceEcouteurActions
{
    private InterfaceIndicateur $indicateur;

    public function __construct() {}

    public function onAfterUpdate(EvenementIndicateurConcret $event)
    {
        // call_user_func($callback(), "Executée avec succès.", "100 % fait.");
        //$callback();
        dd("onAfterUpdate", $event);
    }

    public function onBeforeUpdated(EvenementIndicateurConcret $event)
    {
        // call_user_func($callback());
        //$callback();
        dd("onBeforeUpdated", $event);
    }

    public function onUpdating(EvenementIndicateurConcret $event)
    {
        // call_user_func($callback());
        //$callback();
        dd("onUpdating", $event);
    }

    public function onError(EvenementIndicateurConcret $event)
    {
        dd("OnError", $event);
    }

    public function setIndicateur(InterfaceIndicateur $indicateur)
    {
        $this->indicateur = $indicateur;
    }

    public function getIndicateur(): InterfaceIndicateur
    {
        return $this->indicateur;
    }
}
