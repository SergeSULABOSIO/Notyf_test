<?php
namespace App\Twig\Components;

use App\Entity\Utilisateur;
use App\Service\RefactoringJS\Commandes\Commande;
use App\Service\RefactoringJS\Commandes\CommandeExecuteur;
use App\Service\RefactoringJS\TableauDeBord\Commandes\ComCreerTableauDeBordConcret;
use App\Service\RefactoringJS\TableauDeBord\Concrets\TableauDeBordConcret;
use App\Service\ServiceEntreprise;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class TableauDeBordCourtier implements CommandeExecuteur
{
    public string $nom = "Tableau de bord du courtier";
    
    public function __construct() {

    }

    public function getTableauDeBord(): ?TableauDeBordConcret
    {
        
        /** @var TableauDeBordConcret */
        $tableau = new TableauDeBordConcret();
        $this->executer(new ComCreerTableauDeBordConcret($tableau));
        dd($tableau);
        return $tableau;
    }

    public function executer(?Commande $commande)
    {
        if ($commande != null) {
            $commande->executer();
        }
    }
}
