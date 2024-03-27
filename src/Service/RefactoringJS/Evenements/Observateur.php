<?php

namespace App\Service\RefactoringJS\Evenements;

interface Observateur
{
    public const TYPE_OBSERVATEUR_AJOUT = 0;
    public const TYPE_OBSERVATEUR_EDITION = 1;
    public const TYPE_OBSERVATEUR_SUPPRESSION = 2;
    public const TYPE_OBSERVATEUR_CHARGEMENT = 3;

    public function getType():?int;
    public function setType(?int $typeObservateur);
    public function ecouter(?Evenement $evenement);
}
