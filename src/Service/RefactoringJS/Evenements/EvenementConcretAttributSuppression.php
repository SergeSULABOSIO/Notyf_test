<?php
namespace App\Service\RefactoringJS\Evenements;


class EvenementConcretAttributSuppression extends EvenementConcret
{
    public function __construct()
    {
        parent::__construct(Evenement::TYPE_ATTRIBUT_SUPPRESSION);
    }
}
