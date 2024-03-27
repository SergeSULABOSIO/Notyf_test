<?php
namespace App\Service\RefactoringJS\Evenements;


class EvenementEdition extends EvenementConcret
{
    public function __construct()
    {
        parent::__construct(Evenement::TYPE_EDITION);
    }
}
