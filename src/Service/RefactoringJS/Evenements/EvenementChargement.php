<?php
namespace App\Service\RefactoringJS\Evenements;


class EvenementChargement extends EvenementConcret
{
    public function __construct()
    {
        parent::__construct(Evenement::TYPE_CHARGEMENT);
    }
}
