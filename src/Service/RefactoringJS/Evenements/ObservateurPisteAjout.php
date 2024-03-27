<?php

namespace App\Service\RefactoringJS\Evenements;

abstract class ObservateurPisteAjout extends ObservateurAbstract
{
    public function __construct()
    {
        parent::__construct(Observateur::TYPE_OBSERVATEUR_AJOUT);
    }

    public function ecouter(?Evenement $evenement)
    {
        dd("Hi there! J'écoute les ajouts des piste.");
    }
}
