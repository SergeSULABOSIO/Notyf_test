<?php

namespace App\Service\RefactoringJS\Evenements;

use App\Service\ServiceDates;
use App\Service\ServiceEntreprise;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Service\RefactoringJS\Commandes\Commande;
use App\Service\RefactoringJS\Commandes\CommandeExecuteur;

class SuperviseurPiste implements CommandeExecuteur, Superviseur
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
        // dd("onAttributAjout", $e);
        if (!$this->historiqueEvenements->contains($e)) {
            $this->historiqueEvenements->add($e);
        }
    }


    public function onAttributChargement(?Evenement $e)
    {
        // dd("onAttributChargement", $e);
        if (!$this->historiqueEvenements->contains($e)) {
            $this->historiqueEvenements->add($e);
        }
    }

    public function onAttributEdition(?Evenement $e)
    {
        // dd("onAttributEdition", $e);
        if (!$this->historiqueEvenements->contains($e)) {
            $this->historiqueEvenements->add($e);
        }
    }

    public function onAttributSuppression(?Evenement $e)
    {
        // dd("onAttributSuppression", $e);
        if (!$this->historiqueEvenements->contains($e)) {
            $this->historiqueEvenements->add($e);
        }
    }

    public function onEntiteAvantAjout(?Evenement $e)
    {
        // dd("onEntiteAvantAjout", $e);
        if (!$this->historiqueEvenements->contains($e)) {
            $this->historiqueEvenements->add($e);
        }
    }

    public function onEntiteAvantEdition(?Evenement $e)
    {
        // dd("onEntiteAvantEdition", $e);
        if (!$this->historiqueEvenements->contains($e)) {
            $this->historiqueEvenements->add($e);
        }
    }

    public function onEntiteAvantSuppression(?Evenement $e)
    {
        // dd("onEntiteAvantSuppression", $e);
        if (!$this->historiqueEvenements->contains($e)) {
            $this->historiqueEvenements->add($e);
        }
    }

    public function onEntiteApresAjout(?Evenement $e)
    {
        // dd("onEntiteApresAjout", $e);
        if (!$this->historiqueEvenements->contains($e)) {
            $this->historiqueEvenements->add($e);
        }
        dd("Historique d'évènements:", $this->historiqueEvenements);

    }

    public function onEntiteApresChargement(?Evenement $e)
    {
        // dd("onEntiteApresChargement", $e);
        if (!$this->historiqueEvenements->contains($e)) {
            $this->historiqueEvenements->add($e);
        }
        dd("Historique d'évènements:", $this->historiqueEvenements);
    }

    public function onEntiteApresEdition(?Evenement $e)
    {
        // dd("onEntiteApresEdition", $e);
        if (!$this->historiqueEvenements->contains($e)) {
            $this->historiqueEvenements->add($e);
        }
        dd("Historique d'évènements:", $this->historiqueEvenements);
    }

    public function onEntiteApresSuppression(?Evenement $e)
    {
        // dd("onEntiteApresSuppression", $e);
        if (!$this->historiqueEvenements->contains($e)) {
            $this->historiqueEvenements->add($e);
        }
        dd("Historique d'évènements:", $this->historiqueEvenements);
    }

    public function executer(?Commande $commande)
    {
        if ($commande != null) {
            $commande->executer();
        }
    }
}
