<?php

namespace App\Service\RefactoringJS\TableauDeBord\Concrets;

use Doctrine\Common\Collections\Collection;
use App\Service\RefactoringJS\TableauDeBord\Interfaces\InterfaceBrique;
use App\Service\RefactoringJS\TableauDeBord\Interfaces\InterfaceIndicateur;
use Doctrine\Common\Collections\ArrayCollection;

class IndicateurConcret implements InterfaceIndicateur
{
    private ?string $titre = null;
    private ?Collection $donnees = null;
    private ?InterfaceBrique $brique = null;

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

    //Les setters
    public function setTitre(string $titre): InterfaceIndicateur
    {
        $this->titre = $titre;
        return $this;
    }


    public function setDonnees(?Collection $donnees): InterfaceIndicateur
    {
        $this->donnees = $donnees;
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

    public function build()
    {
        // dd("** Construction de l'indicateur " . $this->titre);
    }
}
