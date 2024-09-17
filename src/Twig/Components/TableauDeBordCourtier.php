<?php
namespace App\Twig\Components;

use App\Service\RefactoringJS\Commandes\Commande;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use App\Service\RefactoringJS\Commandes\CommandeExecuteur;
use App\Twig\Components\TableauDeBord\Commandes\ComCreerTableauDeBordConcret;

#[AsTwigComponent]
class TableauDeBordCourtier
{
    public string $nom = "Tableau de bord du courtier";
    
    public function __construct() {
        
    }

    public function getTableauDeBord()
    {
        
    }
}
