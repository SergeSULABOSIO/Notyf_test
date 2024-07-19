<?php

namespace App\Service\RefactoringJS\Evenements;

use App\Service\ServiceDates;
use App\Service\ServiceEntreprise;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Service\RefactoringJS\Commandes\Commande;
use App\Service\RefactoringJS\Commandes\CommandeExecuteur;
use App\Service\RefactoringJS\Commandes\Piste\ComPisteAjouterNouveauClient;
use App\Service\RefactoringJS\Commandes\Piste\ComPisteAjouterNouvelleTache;
use App\Service\RefactoringJS\Commandes\Piste\ComPisteAjouterNouveauContact;
use App\Service\RefactoringJS\Commandes\Piste\ComPisteAjouterNouveauCotation;

class SuperviseurSujet implements CommandeExecuteur, Superviseur
{

    private Collection $historiqueEvenements;

    public function __construct(
        private ?EntityManagerInterface $entityManager,
        private ?ServiceEntreprise $serviceEntreprise,
        private ?ServiceDates $serviceDates
    ) {
        $this->historiqueEvenements = new ArrayCollection();
    }

    public function onAttributAjout(?Evenement $e)
    {
        /**
         * Commande d'ajout d'éventuel nouveau client
         */
        $this->executer(new ComPisteAjouterNouveauClient(
            $this->entityManager,
            $e
        ));
        /**
         * Commande d'ajout d'éventuels contacts
         */
        $this->executer(new ComPisteAjouterNouveauContact(
            $this->entityManager,
            $e
        ));
        /**
         * Commande d'ajout d'éventuels Actions / Tâches
         */
        $this->executer(new ComPisteAjouterNouvelleTache(
            $this->entityManager,
            $e
        ));
        /**
         * Commande d'ajout d'éventuels Cotation
         */
        $this->executer(new ComPisteAjouterNouveauCotation(
            $this->entityManager,
            $e
        ));
        // dd("Evenement Ajout:", $evenement);
        
        //On peu exécuter d'autres instructions ici
        $this->updateHistoriqueEvenement("onAttributAjout", $e);
    }
    
    
    public function onAttributChargement(?Evenement $e)
    {
        //On peu exécuter d'autres instructions ici
        $this->updateHistoriqueEvenement("onAttributChargement", $e);
    }

    public function onAttributEdition(?Evenement $e)
    {
        //On peu exécuter d'autres instructions ici
        $this->updateHistoriqueEvenement("onAttributEdition", $e);
    }

    public function onAttributSuppression(?Evenement $e)
    {
        //On peu exécuter d'autres instructions ici
        $this->updateHistoriqueEvenement("onAttributSuppression", $e);
    }

    public function onEntiteAvantAjout(?Evenement $e)
    {
        //On peu exécuter d'autres instructions ici
        $this->updateHistoriqueEvenement("onEntiteAvantAjout", $e);
    }

    public function onEntiteAvantEdition(?Evenement $e)
    {
        //On peu exécuter d'autres instructions ici
        $this->updateHistoriqueEvenement("onEntiteAvantEdition", $e);
    }

    public function onEntiteAvantSuppression(?Evenement $e)
    {
        //On peu exécuter d'autres instructions ici
        $this->updateHistoriqueEvenement("onEntiteAvantSuppression", $e);
    }

    public function onEntiteApresAjout(?Evenement $e)
    {
        //On peu exécuter d'autres instructions ici
        $this->updateHistoriqueEvenement("onEntiteApresAjout", $e);
    }

    public function onEntiteApresChargement(?Evenement $e)
    {
        //On peu exécuter d'autres instructions ici
        $this->updateHistoriqueEvenement("onEntiteApresChargement", $e);
    }

    public function onEntiteApresEdition(?Evenement $e)
    {
        //On peu exécuter d'autres instructions ici
        $this->updateHistoriqueEvenement("onEntiteApresEdition", $e);
    }

    public function onEntiteApresSuppression(?Evenement $e)
    {
        //On peu exécuter d'autres instructions ici
        $this->updateHistoriqueEvenement("onEntiteApresSuppression", $e);
    }


    private function updateHistoriqueEvenement($message, ?Evenement $e)
    {
        if (!$this->historiqueEvenements->contains($e)) {
            $this->historiqueEvenements->add($e);
        }
        dd("Historique d'évènements: " . $message, $this->historiqueEvenements);
    }



    
    public function executer(?Commande $commande)
    {
        if ($commande != null) {
            $commande->executer();
        }
    }
}
