<?php

namespace App\Service\RefactoringJS\Evenements;

use App\Entity\Piste;
use App\Entity\Client;
use DateTimeImmutable;
use App\Service\ServiceDates;
use App\Service\ServiceEntreprise;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\Admin\ClientCrudController;
use App\Service\RefactoringJS\Commandes\Commande;
use App\Service\RefactoringJS\Commandes\CommandeExecuteur;
use App\Service\RefactoringJS\Commandes\Piste\ComPisteAjouterNouveauClient;
use App\Service\RefactoringJS\Commandes\Piste\ComPisteAjouterNouvelleTache;
use App\Service\RefactoringJS\Commandes\Piste\ComPisteAjouterNouveauContact;
use App\Service\RefactoringJS\Commandes\Piste\ComPisteAjouterNouveauCotation;
use App\Service\RefactoringJS\Commandes\Piste\CommandePisteAjouterNouveauClient;
use App\Service\RefactoringJS\Commandes\Piste\CommandePisteAjouterNouvelleTache;
use App\Service\RefactoringJS\Commandes\Piste\CommandePisteAjouterNouveauContact;
use App\Service\RefactoringJS\Commandes\Piste\CommandePisteAjouterNouveauCotation;
use App\Service\RefactoringJS\Commandes\ComDefinirEseUserDateCreationEtModification;
use App\Service\RefactoringJS\Commandes\CommandeDefinirEseUserDateCreationEtModification;

class ObservateurAttributAjout extends ObservateurAbstract implements CommandeExecuteur
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
        $donnees[Evenement::CHAMP_ENTREPRISE] = $this->serviceEntreprise->getEntreprise();
        $donnees[Evenement::CHAMP_UTILISATEUR] = $this->serviceEntreprise->getUtilisateur();
        $donnees[Evenement::CHAMP_DATE] = $this->serviceDates->aujourdhui();
        $evenement->setDonnees($donnees);


        /**
         * Définition de l'entreprise, l'utilisateur et les dates
         * 
         * Quand le nouvel attribut ajouté dans le sujet est de type Entité,
         * alors il faut rapidement défénir, pour cette nouvelle entité, l'Utilisateur,
         * l'Entreprise, la date de création ainsi que la date de modification.
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

        //On notifie le superviseur
        if($this->superviseurSujet != null){
            $this->superviseurSujet->onAttributAjout($evenement);
        }
    }

    public function executer(?Commande $commande)
    {
        if ($commande != null) {
            $commande->executer();
        }
    }
}
