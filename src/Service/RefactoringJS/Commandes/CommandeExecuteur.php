<?php

namespace App\Service\RefactoringJS\Commandes;


interface CommandeExecuteur
{
    public function executer(?Commande $commande);
}
