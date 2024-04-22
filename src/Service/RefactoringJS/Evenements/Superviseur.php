<?php

namespace App\Service\RefactoringJS\Evenements;

interface Superviseur
{
    //Attributs
    public function onAttributAjout(?Evenement $e);
    public function onAttributChargement(?Evenement $e);
    public function onAttributEdition(?Evenement $e);
    public function onAttributSuppression(?Evenement $e);
    //Entité - Avant
    public function onEntiteAvantAjout(?Evenement $e);
    public function onEntiteAvantEdition(?Evenement $e);
    public function onEntiteAvantSuppression(?Evenement $e);
    //Entité - Après
    public function onEntiteApresAjout(?Evenement $e);
    public function onEntiteApresChargement(?Evenement $e);
    public function onEntiteApresEdition(?Evenement $e);
    public function onEntiteApresSuppression(?Evenement $e);
}
