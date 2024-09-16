<?php
namespace App\Twig\Components;

use App\Service\RefactoringJS\Commandes\Commande;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use App\Service\RefactoringJS\Commandes\CommandeExecuteur;
use App\Twig\Components\TableauDeBord\Commandes\ComCreerTableauDeBordConcret;
use App\Twig\Components\TableauDeBord\Concrets\TableauDeBordConcret;

#[AsTwigComponent]
class TableauDeBordCourtier implements CommandeExecuteur
{
    public string $nom = "Tableau de bord du courtier";
    
    public function __construct() {

    }

    public function getTableauDeBord():TableauDeBordConcret
    {
        $com = new ComCreerTableauDeBordConcret();
        $this->executer($com);
        // dd($com->getTableauDeBord());
        return $com->getTableauDeBord();
    }

    public function executer(?Commande $commande)
    {
        if ($commande != null) {
            $commande->executer();
        }
    }
}
