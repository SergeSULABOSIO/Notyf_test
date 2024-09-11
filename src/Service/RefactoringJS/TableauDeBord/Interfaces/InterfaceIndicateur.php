<?php

namespace App\Service\RefactoringJS\TableauDeBord\Interfaces;

use Doctrine\Common\Collections\Collection;

interface InterfaceIndicateur
{
    //Les getters
    public function getTitre(): string;
    public function getTexte(): string;
    public function getDonnees(): Collection;
    public function getBrique(): InterfaceBrique;
    public function getEcouteurActions():InterfaceEcouteurActions;
    //Les setters
    public function setTitre(string $titre): InterfaceIndicateur;
    public function setDonnees(Collection $donnees): InterfaceIndicateur;
    public function setEcouteurActions(InterfaceEcouteurActions $ecouteur):InterfaceIndicateur;
    //Autres fonctions
    public function addDonnee($donnee): InterfaceIndicateur;
    public function removeDonnee($donnee): InterfaceIndicateur;
    public function removeAllDonnees(): InterfaceIndicateur;
    public function toString(): string;
}
