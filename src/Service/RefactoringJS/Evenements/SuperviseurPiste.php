<?php

namespace App\Service\RefactoringJS\Evenements;

use App\Service\ServiceDates;
use App\Service\ServiceEntreprise;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\RefactoringJS\Commandes\Commande;
use App\Service\RefactoringJS\Commandes\CommandeExecuteur;

class SuperviseurPiste implements CommandeExecuteur, Superviseur
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ?ServiceEntreprise $serviceEntreprise,
        private ?ServiceDates $serviceDates
    ) {
    }

    public function onAttributAjout(?Evenement $e)
    {
        dd("onAttributAjout");
    }


    public function onAttributChargement(?Evenement $e)
    {
        dd("onAttributChargement");
    }

    public function onAttributEdition(?Evenement $e)
    {
        dd("onAttributEdition");
    }

    public function onAttributSuppression(?Evenement $e)
    {
        dd("onAttributSuppression");
    }

    public function onEntiteAvantAjout(?Evenement $e)
    {
        dd("onEntiteAvantAjout");
    }

    public function onEntiteAvantEdition(?Evenement $e)
    {
        dd("onEntiteAvantEdition");
    }

    public function onEntiteAvantSuppression(?Evenement $e)
    {
        dd("onEntiteAvantSuppression");
    }

    public function onEntiteApresAjout(?Evenement $e)
    {
        dd("onEntiteApresAjout");
    }

    public function onEntiteApresChargement(?Evenement $e)
    {
        dd("onEntiteApresChargement");
    }

    public function onEntiteApresEdition(?Evenement $e)
    {
        dd("onEntiteApresEdition");
    }

    public function onEntiteApresSuppression(?Evenement $e)
    {
        dd("onEntiteApresSuppression");
    }

    public function executer(?Commande $commande)
    {
        if ($commande != null) {
            $commande->executer();
        }
    }
}
