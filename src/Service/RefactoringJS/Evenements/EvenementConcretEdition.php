<?php
namespace App\Service\RefactoringJS\Evenements;


class EvenementConcretEdition extends EvenementConcret
{
    public function __construct()
    {
        parent::__construct(Evenement::TYPE_EDITION);
    }
}
