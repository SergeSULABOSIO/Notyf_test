<?php

namespace App\Service\RefactoringJS\Evenements;

use App\Service\ServiceDates;
use App\Service\ServiceEntreprise;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\RefactoringJS\Commandes\Commande;
use App\Service\RefactoringJS\Commandes\CommandeExecuteur;
use App\Service\RefactoringJS\Commandes\ComDefinirEseUserDateCreationEtModification;

class ObservateurEntiteAvantAjout extends ObservateurAbstract implements CommandeExecuteur
{
    public function __construct(
        private ?SuperviseurSujet $superviseurSujet,
        private EntityManagerInterface $entityManager,
        private ?ServiceEntreprise $serviceEntreprise,
        private ?ServiceDates $serviceDates
    ) {
        parent::__construct(Observateur::TYPE_OBSERVATEUR_AJOUT);
    }

    public function ecouter(?Evenement $evenement)
    {
        $donnees = $evenement->getDonnees();
        /**
         * Définition de l'entreprise, l'utilisateur et les dates
         */
        $donnees[Evenement::CHAMP_ENTREPRISE] = $this->serviceEntreprise->getEntreprise();
        $donnees[Evenement::CHAMP_UTILISATEUR] = $this->serviceEntreprise->getUtilisateur();
        $donnees[Evenement::CHAMP_DATE] = $this->serviceDates->aujourdhui();
        $evenement->setDonnees($donnees);

        // dd($evenement, "Value :" . $donnees[Evenement::CHAMP_NEW_VALUE], $donnees[Evenement::CHAMP_NEW_VALUE] instanceof Sujet);
        if ($evenement->getType() == Evenement::TYPE_ENTITE_AVANT_ENREGISTREMENT) {
            $this->executer(new ComDefinirEseUserDateCreationEtModification(
                $evenement->getValueFormat(),
                $donnees[Evenement::CHAMP_NEW_VALUE],
                $this->serviceEntreprise,
                $this->serviceDates
            ));
        }
        // dd("Evenement Avant Ajout de l'entité", $evenement);
        
        //On notifie le superviseur
        if($this->superviseurSujet != null){
            $this->superviseurSujet->onEntiteAvantAjout($evenement);
        }
    }

    public function executer(?Commande $commande)
    {
        if ($commande != null) {
            $commande->executer();
        }
    }
}
