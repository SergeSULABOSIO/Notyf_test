<?php
namespace App\Service\RefactoringJS\Evenements;


class EvenementSuppression extends EvenementConcret
{
    public function __construct()
    {
        parent::__construct(Evenement::TYPE_SUPPRESSION);
    }
}
