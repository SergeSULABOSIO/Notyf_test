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
use App\Service\RefactoringJS\Commandes\Piste\CommandePisteAjouterNouveauContact;
use App\Service\RefactoringJS\Commandes\Piste\CommandePisteAjouterNouveauCotation;
use App\Service\RefactoringJS\Commandes\Piste\CommandePisteAjouterNouvelleTache;
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
        dd($evenement, "Value :" . $donnees[Evenement::CHAMP_NEW_VALUE], $donnees[Evenement::CHAMP_NEW_VALUE] instanceof Sujet);
        if ($donnees[Evenement::CHAMP_NEW_VALUE] instanceof Sujet) {
            $this->executer(new CommandeDefinirEseUserDateCreationEtModification(
                $evenement->getValueFormat(),
                $donnees[Evenement::CHAMP_NEW_VALUE],
                $this->serviceEntreprise,
                $this->serviceDates
            ));
        }


        /**
         * Commande d'ajout d'éventuel nouveau client
         */
        $this->executer(new CommandePisteAjouterNouveauClient(
            $this->entityManager,
            $evenement
        ));
        /**
         * Commande d'ajout d'éventuels contacts
         */
        $this->executer(new CommandePisteAjouterNouveauContact(
            $this->entityManager,
            $evenement
        ));
        /**
         * Commande d'ajout d'éventuels Actions / Tâches
         */
        $this->executer(new CommandePisteAjouterNouvelleTache(
            $this->entityManager,
            $evenement
        ));
        /**
         * Commande d'ajout d'éventuels Cotation
         */
        $this->executer(new CommandePisteAjouterNouveauCotation(
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
