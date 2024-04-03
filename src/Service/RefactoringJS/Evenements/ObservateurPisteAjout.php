<?php

namespace App\Service\RefactoringJS\Evenements;

use App\Controller\Admin\ClientCrudController;
use App\Entity\Piste;
use App\Entity\Client;
use App\Service\ServiceDates;
use App\Service\ServiceEntreprise;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\RefactoringJS\Commandes\Commande;
use App\Service\RefactoringJS\Commandes\CommandeExecuteur;
use App\Service\RefactoringJS\Commandes\CommandeDefinirEseUserDateCreationEtModification;
use App\Service\RefactoringJS\Commandes\Piste\CommandePisteAjouterNouveauClient;
use DateTimeImmutable;

class ObservateurPisteAjout extends ObservateurAbstract implements CommandeExecuteur
{
    public function __construct(
        private EntityManagerInterface $entityManager,
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
         * Définition de l'entreprise, l'utilisateur et les dates
         */
        $this->executer(new CommandeDefinirEseUserDateCreationEtModification(
            $evenement->getValueFormat(),
            $donnees[Evenement::CHAMP_NEW_VALUE],
            $this->serviceEntreprise,
            $this->serviceDates
        ));
        /**
         * Commande d'ajout d'éventuel nouveau client
         */
        $this->executer(new CommandePisteAjouterNouveauClient(
            $this->entityManager,
            $evenement
        ));
        // dd("Evenement Ajout:", $evenement);
    }

    public function executer(?Commande $commande)
    {
        if ($commande != null) {
            $commande->executer();
        }
    }
}
