<?php

namespace App\Service\RefactoringJS\TableauDeBord\Concrets;

use App\Service\RefactoringJS\TableauDeBord\Interfaces\InterfaceEcouteurActions;
use App\Service\RefactoringJS\TableauDeBord\Interfaces\InterfaceIndicateur;

class EcouteurActions implements InterfaceEcouteurActions
{
    private InterfaceIndicateur $indicateur;

    public function __construct() {}

    public function onAfterUpdate(callable $callback)
    {
        call_user_func($callback(), "Executée avec succès.", "100 % fait.");
        //$callback();
        dd("onAfterUpdate");
    }

    public function onBeforeUpdated(callable $callback)
    {
        call_user_func($callback());
        //$callback();
        dd("onBeforeUpdated");
    }

    public function onUpdating(callable $callback)
    {
        call_user_func($callback());
        //$callback();
        dd("onUpdating");
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
