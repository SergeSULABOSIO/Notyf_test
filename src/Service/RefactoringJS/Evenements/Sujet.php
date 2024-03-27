<?php

namespace App\Service\RefactoringJS\Evenements;

use Doctrine\Common\Collections\ArrayCollection;

interface Sujet
{
    
    //Production du paiement
    public function ajouterObservateur(?Observateur $observateur);
    public function retirerObservateur(?Observateur $observateur);
    public function viderListeObservateurs();
    public function getListeObservateurs():?ArrayCollection;
}
