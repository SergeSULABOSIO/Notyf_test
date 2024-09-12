<?php

namespace App\Service\RefactoringJS\TableauDeBord\Concrets;

use Doctrine\Common\Collections\Collection;
use App\Service\RefactoringJS\TableauDeBord\Interfaces\InterfaceBrique;
use App\Service\RefactoringJS\TableauDeBord\Interfaces\InterfaceEcouteurActions;
use App\Service\RefactoringJS\TableauDeBord\Interfaces\InterfaceIndicateur;
use Doctrine\Common\Collections\ArrayCollection;

class Indicateur implements InterfaceIndicateur
{
    private string $titre;
    private Collection $donnees;
    private InterfaceBrique $brique;
    private InterfaceEcouteurActions $ecouteur;

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


    public function setDonnees(Collection $donnees): InterfaceIndicateur
    {
        $this->donnees = $donnees;
        return $this;
    }

    public function setEcouteurActions(InterfaceEcouteurActions $ecouteur): InterfaceIndicateur
    {
        $this->ecouteur = $ecouteur;
        return $this;
    }

    //Autres fonctions
    public function addDonnee($donnee): InterfaceIndicateur
    {
        if ($this->donnees->contains($donnee) == false) {
            $this->donnees->add($donnee);
        }
        return $this;
    }

    public function removeDonnee($donnee): InterfaceIndicateur
    {
        if ($this->donnees->contains($donnee) == true) {
            $this->donnees->removeElement($donnee);
        }
        return $this;
    }

    public function removeAllDonnees(): InterfaceIndicateur
    {
        $this->donnees = new ArrayCollection();
        return $this;
    }


    public function toString(): string
    {
        return $this->titre . " - Indicateur";
    }
}
