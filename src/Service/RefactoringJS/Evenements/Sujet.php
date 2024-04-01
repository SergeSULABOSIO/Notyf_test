<?php

namespace App\Service\RefactoringJS\Evenements;

use Doctrine\Common\Collections\ArrayCollection;

interface Sujet
{
    public function initListeObservateurs();
    public function ajouterObservateur(?Observateur $observateur);
    public function retirerObservateur(?Observateur $observateur);
    public function viderListeObservateurs();
    public function getListeObservateurs():?ArrayCollection;
    public function notifierLesObservateurs(?Evenement $evenement);
}
