<?php

namespace App\Service\RefactoringJS\TableauDeBord\Concrets;

use App\Service\RefactoringJS\TableauDeBord\Interfaces\InterfaceEcouteurActions;
use App\Service\RefactoringJS\TableauDeBord\Interfaces\InterfaceIndicateur;

class EcouteurActions implements InterfaceEcouteurActions
{
    private InterfaceIndicateur $indicateur;

    public function __construct() {}

    public function onAfterUpdate(EvenementIndicateur $event)
    {
        // call_user_func($callback(), "Executée avec succès.", "100 % fait.");
        //$callback();
        dd("onAfterUpdate", $event);
    }

    public function onBeforeUpdated(EvenementIndicateur $event)
    {
        // call_user_func($callback());
        //$callback();
        dd("onBeforeUpdated", $event);
    }

    public function onUpdating(EvenementIndicateur $event)
    {
        // call_user_func($callback());
        //$callback();
        dd("onUpdating", $event);
    }

    public function onError(EvenementIndicateur $event)
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
