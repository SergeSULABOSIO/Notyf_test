<?php

namespace App\Service\RefactoringJS\Evenements;

use App\Service\RefactoringJS\Commandes\Commande;
use App\Service\RefactoringJS\Commandes\CommandeDefinirEseUserDateCreationEtModification;
use App\Service\RefactoringJS\Commandes\CommandeExecuteur;
use App\Service\ServiceDates;
use App\Service\ServiceEntreprise;

class ObservateurPisteAjout extends ObservateurAbstract implements CommandeExecuteur
{
    public function __construct(
        private ?ServiceEntreprise $serviceEntreprise,
        private ?ServiceDates $serviceDates
    ) {
        parent::__construct(Observateur::TYPE_OBSERVATEUR_AJOUT);
    }

    public function ecouter(?Evenement $evenement)
    {
        $donnees = $evenement->getDonnees();
        $donnees[Evenement::CHAMP_ENTREPRISE] = $this->serviceEntreprise->getEntreprise();
        $donnees[Evenement::CHAMP_UTILISATEUR] = $this->serviceEntreprise->getUtilisateur();
        $donnees[Evenement::CHAMP_DATE] = $this->serviceDates->aujourdhui();
        $evenement->setDonnees($donnees);
        /**
         * On définit directement l'entreprise, 
         * l'utilisateur, la date de créaton, et celle de modification
         */
        $this->executer(new CommandeDefinirEseUserDateCreationEtModification(
            $donnees[Evenement::CHAMP_NEW_VALUE],
            $this->serviceEntreprise,
            $this->serviceDates
        ));


        dd("Evenement Ajout:", $evenement);
    }

    public function executer(?Commande $commande)
    {
        if ($commande != null) {
            $commande->executer();
        }
    }
}
