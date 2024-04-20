<?php
namespace App\Service\RefactoringJS\Evenements;


class EvenementConcretEntiteAvantEdition extends EvenementConcret
{
    public function __construct()
    {
        parent::__construct(Evenement::TYPE_ENTITE_AVANT_EDITION);
    }
}
