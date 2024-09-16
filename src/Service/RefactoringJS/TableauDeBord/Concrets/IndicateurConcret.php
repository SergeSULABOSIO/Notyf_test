<?php

namespace App\Service\RefactoringJS\TableauDeBord\Concrets;

use Doctrine\Common\Collections\Collection;
use App\Service\RefactoringJS\TableauDeBord\Interfaces\InterfaceBrique;
use App\Service\RefactoringJS\TableauDeBord\Interfaces\InterfaceEcouteurActions;
use App\Service\RefactoringJS\TableauDeBord\Interfaces\InterfaceIndicateur;
use Doctrine\Common\Collections\ArrayCollection;

class IndicateurConcret implements InterfaceIndicateur
{
    private ?string $titre = null;
    private ?Collection $donnees = null;
    private ?InterfaceBrique $brique = null;
    private ?InterfaceEcouteurActions $ecouteur = null;

    public function __construct()
    {
        $this->donnees = new ArrayCollection();
    }

    //Les getters
    public function getTitre(): string
    {
        return $this->titre;
    }

    public function getTexte(): string
    {
        return "Je suis un indicateur du tableau de bord!";
    }

    public function getDonnees(): Collection
    {
        return $this->donnees;
    }

    public function getBrique(): InterfaceBrique
    {
        return $this->brique;
    }

    public function setBrique(?InterfaceBrique $brique): InterfaceIndicateur
    {
        $this->brique = $brique;
        return $this;
    }

    public function getEcouteurActions(): InterfaceEcouteurActions
    {
        return $this->ecouteur;
    }

    //Les setters
    public function setTitre(string $titre): InterfaceIndicateur
    {
        $this->titre = $titre;
        return $this;
    }


    public function setDonnees(?Collection $donnees): InterfaceIndicateur
    {
        //Ecouteur
        if ($this->ecouteur != null) {
            $this->ecouteur->onBeforeUpdated(new EvenementIndicateurConcret($this, "Préparation du transfert des données..."));
            $this->ecouteur->onUpdating(new EvenementIndicateurConcret($this, "Transfert..."));
        }

        $this->donnees = $donnees;

        //Ecouteur
        if ($this->ecouteur != null) {
            $this->ecouteur->onAfterUpdate(new EvenementIndicateurConcret($this, "Transfert effectué."));
        }

        return $this;
    }

    public function setEcouteurActions(?InterfaceEcouteurActions $ecouteur): InterfaceIndicateur
    {
        $this->ecouteur = $ecouteur;
        return $this;
    }

    //Autres fonctions
    public function addDonnee($donnee): InterfaceIndicateur
    {
        if ($this->donnees->contains($donnee) == false) {
            //Ecouteur
            if ($this->ecouteur != null) {
                $this->ecouteur->onBeforeUpdated(new EvenementIndicateurConcret($this, "Préparation de l'ajout de " . $donnee . "..."));
                $this->ecouteur->onUpdating(new EvenementIndicateurConcret($this, "Ajout..."));
            }

            $this->donnees->add($donnee);

            //Ecouteur
            if ($this->ecouteur != null) {
                $this->ecouteur->onAfterUpdate(new EvenementIndicateurConcret($this, "Ajout de la donnée effectué."));
            }
        } else {
            //Ecouteur erreur
            if ($this->ecouteur != null) {
                $this->ecouteur->onError(new EvenementIndicateurConcret($this, "La donnée " . $donnee . " existe déjà."));
            }
        }
        return $this;
    }

    public function removeDonnee($donnee): InterfaceIndicateur
    {
        if ($this->donnees->contains($donnee) == true) {
            //Ecouteur
            if ($this->ecouteur != null) {
                $this->ecouteur->onBeforeUpdated(new EvenementIndicateurConcret($this, "Préparation de la suppression de " . $donnee . "..."));
                $this->ecouteur->onUpdating(new EvenementIndicateurConcret($this, "Destruction..."));
            }

            $this->donnees->removeElement($donnee);

            //Ecouteur
            if ($this->ecouteur != null) {
                $this->ecouteur->onAfterUpdate(new EvenementIndicateurConcret($this, "Retrait de la donnée effectué."));
            }
        }
        return $this;
    }

    public function removeAllDonnees(): InterfaceIndicateur
    {
        //Ecouteur
        if ($this->ecouteur != null) {
            $this->ecouteur->onBeforeUpdated(new EvenementIndicateurConcret($this, "Préparation de la destruction de toutes les données..."));
            $this->ecouteur->onUpdating(new EvenementIndicateurConcret($this, "Destruction..."));
        }

        $this->donnees = new ArrayCollection();

        //Ecouteur
        if ($this->ecouteur != null) {
            $this->ecouteur->onAfterUpdate(new EvenementIndicateurConcret($this, "Destruction effectuée."));
        }
        return $this;
    }


    public function toString(): string
    {
        return $this->titre . " - Indicateur";
    }

    public function build()
    {
        // dd("** Construction de l'indicateur " . $this->titre);
    }
}