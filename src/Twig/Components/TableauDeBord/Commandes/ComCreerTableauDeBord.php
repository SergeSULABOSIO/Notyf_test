<?php

namespace App\Service\RefactoringJS\TableauDeBord\Commandes;

use Doctrine\Common\Collections\ArrayCollection;
use App\Service\RefactoringJS\Commandes\Commande;
use App\Service\RefactoringJS\TableauDeBord\Concrets\BriqueConcret;
use App\Service\RefactoringJS\TableauDeBord\Concrets\IndicateurConcret;
use App\Service\RefactoringJS\TableauDeBord\Concrets\TableauDeBordConcret;
use App\Service\RefactoringJS\TableauDeBord\Interfaces\InterfaceBrique;

class ComCreerTableauDeBord implements Commande
{
    public function __construct() {}

    public function executer()
    {
        //*** INDICATEUR : qui contient les informations stats qui s'affichent*/
        $indic01 = (new IndicateurConcret())
            ->setTitre("Avenants")
            ->setDonnees(new ArrayCollection([
                "Primes bruttes = 100 USD",
                "Fronting = 17.65 USD",
                "Commissions = 10 USD",
                "Retrocoms = 2 USD",
                "Nombre totale d'avenants = 152"
            ]))
            ->addDonnee("Primes de réassurance = 35 USD");

        //** BRIQUE: qui est composée d'un groupe d'indicateur */
        $brique_titre = (new BriqueConcret(InterfaceBrique::TYPE_BRIQUE_DE_TITRE))
            ->addIndicateur($indic01);

        //** TABLEAU DE BORD: qui coomposé d'un ensemble des briques */
        $tableauDeBord = (new TableauDeBordConcret())
            ->addBrique($brique_titre)
            ->build();

        dd("Tableau de bord", $tableauDeBord);
    }
}
