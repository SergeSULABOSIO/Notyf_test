<?php

namespace App\Service\RefactoringJS\Commandes;

use App\Service\RefactoringJS\Commandes\Commande;
use App\Service\RefactoringJS\Evenements\Sujet;

class ComTransfererObservateursVersLesSousSujets implements Commande
{
    public function __construct(
        private ?Sujet $sujetParent,
        private ?Sujet $sujetFils
    ) {
    }

    public function executer()
    {
       
    }
}
