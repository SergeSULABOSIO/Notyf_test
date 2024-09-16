<?php
namespace App\Twig\Components\TableauDeBord\Interfaces;


use Doctrine\Common\Collections\Collection;
use App\Twig\Components\TableauDeBord\Interfaces\InterfaceBrique;

interface InterfaceIndicateur
{
    //Les getters
    public function getTitre(): string;
    public function getTexte(): string;
    public function getDonnees(): Collection;
    public function getBrique(): InterfaceBrique;
    //Les setters
    public function setTitre(string $titre): self;
    public function setDonnees(?Collection $donnees): self;
    public function setBrique(?InterfaceBrique $brique): self;
    //Autres fonctions
    public function addDonnee($donnee): self;
    public function removeDonnee($donnee): self;
    public function removeAllDonnees(): self;
    public function toString(): string;
    public function build();
}
