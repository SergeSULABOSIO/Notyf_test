<?php
namespace App\Service\RefactoringJS\Evenements;


class EvenementAjout extends EvenementConcret
{
    public function __construct()
    {
        parent::__construct(Evenement::TYPE_AJOUT);
    }
}
