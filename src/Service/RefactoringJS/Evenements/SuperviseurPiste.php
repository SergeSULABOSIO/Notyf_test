<?php

namespace App\Service\RefactoringJS\Evenements;

use App\Service\ServiceDates;
use App\Service\ServiceEntreprise;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\RefactoringJS\Commandes\Commande;
use App\Service\RefactoringJS\Commandes\CommandeExecuteur;
use App\Service\RefactoringJS\Commandes\Piste\ComPisteAjouterNouveauClient;
use App\Service\RefactoringJS\Commandes\Piste\ComPisteAjouterNouvelleTache;
use App\Service\RefactoringJS\Commandes\Piste\ComPisteAjouterNouveauContact;
use App\Service\RefactoringJS\Commandes\Piste\ComPisteAjouterNouveauCotation;
use App\Service\RefactoringJS\Commandes\ComDefinirEseUserDateCreationEtModification;

class ObservateurAttributAjout implements CommandeExecuteur, Superviseur
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ?ServiceEntreprise $serviceEntreprise,
        private ?ServiceDates $serviceDates
    ) {

    }

    public function onAttributAjout(?Evenement $e)
    {
        
    }


    public function onAttributChargement(?Evenement $e)
    {
        
    }

    public function onAttributEdition(?Evenement $e)
    {
        
    }

    public function onAttributSuppression(?Evenement $e)
    {
        
    }

    public function onEntiteAvantAjout(?Evenement $e)
    {
        
    }

    public function onEntiteAvantEdition(?Evenement $e)
    {
        
    }

    public function onEntiteAvantSuppression(?Evenement $e)
    {
        
    }

    public function onEntiteApresAjout(?Evenement $e)
    {
        
    }

    public function onEntiteApresChargement(?Evenement $e)
    {
        
    }

    public function onEntiteApresEdition(?Evenement $e)
    {
        
    }

    public function onEntiteApresSuppression(?Evenement $e)
    {
        
    }

    public function executer(?Commande $commande)
    {
        if ($commande != null) {
            $commande->executer();
        }
    }
}
