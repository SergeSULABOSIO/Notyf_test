<?php

namespace App\Service\RefactoringJS\Evenements;

use App\Service\ServiceDates;
use App\Service\ServiceEntreprise;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Service\RefactoringJS\Commandes\Commande;
use App\Service\RefactoringJS\Commandes\CommandeExecuteur;
use App\Service\RefactoringJS\Commandes\Piste\ComPersisterEntite;
use App\Service\RefactoringJS\Commandes\Piste\ComPisteAjouterNouveauClient;
use App\Service\RefactoringJS\Commandes\Piste\ComPisteAjouterNouvelleTache;
use App\Service\RefactoringJS\Commandes\Piste\ComPisteAjouterNouveauContact;
use App\Service\RefactoringJS\Commandes\Piste\ComPisteAjouterNouveauCotation;
use App\Service\RefactoringJS\Commandes\Piste\ComPisteAjusterParamClient;
use App\Service\RefactoringJS\Commandes\Piste\ComPisteAjusterParamCotation;

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






    /**
     * Ici on écoute à chaque fois que l'on ajoute à ce sujet un attribut tout simplement.
     * Quel que soit le type de cet attribut.
     *
     * @param Evenement|null $e
     * @return void
     */
    public function onAttributAjout(?Evenement $e)
    {
        // dd("Evenement - Piste - Erreur lors de la création de la nouvelle piste", $e);

        // Commande de persistance d'une entité dans la base
        $this->executer(new ComPersisterEntite($this->entityManager, $e));

        // Pour les ajustement des paramètres complexes pour les PISTE
        // 1. Commande d'ajout d'éventuel nouveau client
        $this->executer(new ComPisteAjusterParamClient($this->entityManager, $e));
        
        // 2. Commande d'ajout d'éventuels Cotation
        //$this->executer(new ComPisteAjusterParamCotation($this->entityManager, $e));
        
        // dd("Event - Piste - Ajout", $e);
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
        $this->setCreateTime($e);
        $this->setUpdateTime($e);
        $this->updateHistoriqueEvenement("onEntiteAvantAjout", $e);
    }

    public function onEntiteAvantEdition(?Evenement $e)
    {
        //On peu exécuter d'autres instructions ici
        $this->setUpdateTime($e);
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






    private function setUpdateTime(?Evenement $e)
    {
        /**
         * @var Sujet $sujetEdite
         */
        $sujetEdite = $e->getDonnees()["Données"];
        $sujetEdite->setUpdatedAt(new \DateTimeImmutable("now"));
    }

    private function setCreateTime(?Evenement $e)
    {
        /**
         * @var Sujet $sujetEdite
         */
        $sujetEdite = $e->getDonnees()["Données"];
        $sujetEdite->setCreatedAt(new \DateTimeImmutable("now"));
    }


    private function updateHistoriqueEvenement($message, ?Evenement $e)
    {
        if (!$this->historiqueEvenements->contains($e)) {
            $this->historiqueEvenements->add($e);
        }
        // dd("Historique d'évènements: " . $message, $this->historiqueEvenements);
    }




    public function executer(?Commande $commande)
    {
        if ($commande != null) {
            $commande->executer();
        }
    }
}
