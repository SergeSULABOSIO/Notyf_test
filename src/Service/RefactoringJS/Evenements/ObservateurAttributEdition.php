<?php

namespace App\Service\RefactoringJS\Evenements;

use App\Service\ServiceDates;
use App\Service\ServiceEntreprise;
use App\Service\RefactoringJS\Commandes\Commande;
use App\Service\RefactoringJS\Evenements\Evenement;
use App\Service\RefactoringJS\Commandes\CommandeExecuteur;
use App\Service\RefactoringJS\Commandes\ComDefinirEseUserDateCreationEtModification;
use App\Service\RefactoringJS\Commandes\CommandeDefinirEseUserDateCreationEtModification;

class ObservateurAttributEdition extends ObservateurAbstract implements CommandeExecuteur
{
    public function __construct(
        private ?SuperviseurSujet $superviseurSujet,
        private ?ServiceEntreprise $serviceEntreprise,
        private ?ServiceDates $serviceDates
    ) {
        parent::__construct(Observateur::TYPE_OBSERVATEUR_EDITION);
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
         * l'utilisateur, 
         * la date de créaton, 
         * et celle de modification
         */
        // dd($evenement, "Value :" . $donnees[Evenement::CHAMP_NEW_VALUE], $donnees[Evenement::CHAMP_NEW_VALUE] instanceof Sujet);
        if ($donnees[Evenement::CHAMP_NEW_VALUE] instanceof Sujet) {
            $this->executer(new ComDefinirEseUserDateCreationEtModification(
                $evenement->getValueFormat(),
                $donnees[Evenement::CHAMP_NEW_VALUE],
                $this->serviceEntreprise,
                $this->serviceDates
            ));
        }
        // dd("Evenement Edition:", $evenement);
        //On notifie le superviseur
        if($this->superviseurSujet != null){
            $this->superviseurSujet->onAttributEdition($evenement);
        }
    }

    public function executer(?Commande $commande)
    {
        if ($commande != null) {
            $commande->executer();
        }
    }
}
