<?php

namespace App\Service\RefactoringJS\Evenements;

class ObservateurPisteSuppression extends ObservateurAbstract
{
    public function __construct()
    {
        parent::__construct(Observateur::TYPE_OBSERVATEUR_SUPPRESSION);
    }

    public function ecouter(?Evenement $evenement)
    {
        dd("Hi there! J'écoute les suppressions des pistes.");
    }
}
