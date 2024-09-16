<?php
namespace App\Twig\Components\TableauDeBord\Concrets;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Twig\Components\TableauDeBord\Interfaces\InterfaceBrique;
use App\Twig\Components\TableauDeBord\Interfaces\InterfaceIndicateur;


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

    public function setBrique(?InterfaceBrique $brique): self
    {
        $this->brique = $brique;
        return $this;
    }

    //Les setters
    public function setTitre(string $titre): self
    {
        $this->titre = $titre;
        return $this;
    }


    public function setDonnees(?Collection $donnees): self
    {
        $this->donnees = $donnees;
        return $this;
    }

    //Autres fonctions
    public function addDonnee($donnee): self
    {
        if ($this->donnees->contains($donnee) == false) {
            $this->donnees->add($donnee);
        }
        return $this;
    }

    public function removeDonnee($donnee): self
    {
        if ($this->donnees->contains($donnee) == true) {
            $this->donnees->removeElement($donnee);
        }
        return $this;
    }

    public function removeAllDonnees(): self
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
