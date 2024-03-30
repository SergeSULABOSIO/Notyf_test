<?php

namespace App\Service\RefactoringJS\Evenements;

class ObservateurPisteEdition extends ObservateurAbstract
{
    public function __construct()
    {
        parent::__construct(Observateur::TYPE_OBSERVATEUR_EDITION);
    }

    public function ecouter(?Evenement $evenement)
    {
        dd("Hi there! J'écoute les éditions des pistes.");
    }
}
