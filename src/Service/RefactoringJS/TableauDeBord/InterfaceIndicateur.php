<?php

namespace App\Service\RefactoringJS\TableauDeBord;

use Doctrine\Common\Collections\Collection;

interface InterfaceIndicateur
{
    //Les getters
    public function getTitre():string;
    public function getTexte():string;
    public function getDonnees():Collection;
    public function getBrique():InterfaceBrique;
    //Les setters
    public function setTitre(string $titre): InterfaceIndicateur;
    public function setDonnees(Collection $donnees): InterfaceIndicateur;
    //Autres fonctions
    public function addDonnee($donnees):InterfaceIndicateur;
    public function removeDonnee():InterfaceIndicateur;
    public function removeAllDonnees():InterfaceIndicateur;
}
