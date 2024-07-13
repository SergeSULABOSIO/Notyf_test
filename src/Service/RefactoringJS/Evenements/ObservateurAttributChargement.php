<?php

namespace App\Service\RefactoringJS\Evenements;

use App\Service\ServiceDates;
use App\Service\ServiceEntreprise;
use App\Service\RefactoringJS\Commandes\Commande;
use App\Service\RefactoringJS\Commandes\CommandeExecuteur;
use App\Service\RefactoringJS\Commandes\ComDefinirEseUserDateCreationEtModification;
use App\Service\RefactoringJS\Commandes\CommandeDefinirEseUserDateCreationEtModification;

class ObservateurAttributChargement extends ObservateurAbstract implements CommandeExecuteur
{
    public function __construct(
        private ?SuperviseurSujet $superviseurSujet,
        private ?ServiceEntreprise $serviceEntreprise,
        private ?ServiceDates $serviceDates
    ) {
        parent::__construct(Observateur::TYPE_OBSERVATEUR_CHARGEMENT);
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
        $this->executer(new ComDefinirEseUserDateCreationEtModification(
            $evenement->getValueFormat(),
            $donnees[Evenement::CHAMP_NEW_VALUE],
            $this->serviceEntreprise,
            $this->serviceDates
        ));
        // dd("Evenement Chargement:", $evenement);

        //On notifie le superviseur
        if($this->superviseurSujet != null){
            $this->superviseurSujet->onAttributChargement($evenement);
        }
    }

    public function executer(?Commande $commande)
    {
        if ($commande != null) {
            $commande->executer();
        }
    }
}
