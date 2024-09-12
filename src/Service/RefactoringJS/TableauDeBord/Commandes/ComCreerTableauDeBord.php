<?php

namespace App\Service\RefactoringJS\TableauDeBord\Commandes;

use Doctrine\Common\Collections\ArrayCollection;
use App\Service\RefactoringJS\Commandes\Commande;
use App\Service\RefactoringJS\TableauDeBord\Concrets\Brique;
use App\Service\RefactoringJS\TableauDeBord\Concrets\Indicateur;
use App\Service\RefactoringJS\TableauDeBord\Concrets\TableauDeBord;
use App\Service\RefactoringJS\TableauDeBord\Concrets\EcouteurActions;
use App\Service\RefactoringJS\TableauDeBord\Interfaces\InterfaceBrique;

class ComCreerTableauDeBord implements Commande
{
    public function __construct() {}

    public function executer()
    {
        //*** INDICATEUR */
        $indic01 = new Indicateur();
        $indic01->setTitre("Polices");
        $indic01->setDonnees(new ArrayCollection([
            "Primes bruttes = 100 USD",
            "Fronting = 17.65 USD",
            "Commissions = 10 USD",
            "Retrocoms = 2 USD",
            "Nombre totale d'avenants = 152"
        ]));
        $indic01->addDonnee("Primes de réassurance = 35 USD");

        //** ECOUTEUR */
        $ecouteur = new EcouteurActions();
        $indic01->setEcouteurActions($ecouteur);

        //** BRIQUE */
        $brique_titre = new Brique(InterfaceBrique::TYPE_BRIQUE_DE_TITRE);
        $brique_titre->addIndicateur($indic01);

        //** TABLEAU DE BORD */
        $tableauDeBord = (new TableauDeBord())
            ->addBrique($brique_titre)
            ->build();

        dd("Tableau de bord", $tableauDeBord);
    }
}
