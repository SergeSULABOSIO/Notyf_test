<?php
namespace App\Service\RefactoringJS\Evenements;


class EvenementConcretEntiteApresSuppression extends EvenementConcret
{
    public function __construct()
    {
        parent::__construct(Evenement::TYPE_ENTITE_APRES_SUPPRESSION);
    }
}
