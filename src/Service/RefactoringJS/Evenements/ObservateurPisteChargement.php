<?php

namespace App\Service\RefactoringJS\Evenements;

class ObservateurPisteChargement extends ObservateurAbstract
{
    public function __construct()
    {
        parent::__construct(Observateur::TYPE_OBSERVATEUR_CHARGEMENT);
    }

    public function ecouter(?Evenement $evenement)
    {
        dd("Hi there! J'écoute les chargements des pistes.");
    }
}
