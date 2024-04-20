<?php
namespace App\Service\RefactoringJS\Evenements;


class EvenementConcretAttributEdition extends EvenementConcret
{
    public function __construct()
    {
        parent::__construct(Evenement::TYPE_ATTRIBUT_EDITION);
    }
}
