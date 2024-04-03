<?php

namespace App\Service\RefactoringJS\Evenements;

use App\Entity\Piste;
use App\Entity\Client;
use App\Service\ServiceDates;
use App\Service\ServiceEntreprise;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\RefactoringJS\Commandes\Commande;
use App\Service\RefactoringJS\Commandes\CommandeExecuteur;
use App\Service\RefactoringJS\Commandes\CommandeDefinirEseUserDateCreationEtModification;

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

        //Draft de la commande de récupération du client
        if ($donnees[Evenement::CHAMP_DONNEE] instanceof Piste && $donnees[Evenement::CHAMP_NEW_VALUE] instanceof Client) {
            /** @var Piste */
            $piste = $donnees[Evenement::CHAMP_DONNEE];
            /** @var Client */
            $client = $donnees[Evenement::CHAMP_NEW_VALUE];

            //ici il faut actualiser la base de données
            $this->entityManager->persist($client);
            $this->entityManager->flush();

            $piste->setClient($client);
            // $client->addPiste($piste);          

            //On vide la liste des prospects
            $tabProspect = $piste->getProspect();
            foreach ($tabProspect as $pros) {
                $piste->removeProspect($pros);
            }
        }
        // dd("Evenement Ajout:", $evenement);
    }

    public function executer(?Commande $commande)
    {
        if ($commande != null) {
            $commande->executer();
        }
    }
}
