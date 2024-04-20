<?php
namespace App\Service\RefactoringJS\Evenements;


class EvenementConcretEntiteApresEdition extends EvenementConcret
{
    public function __construct()
    {
        parent::__construct(Evenement::TYPE_ENTITE_APRES_EDITION);
    }
}
