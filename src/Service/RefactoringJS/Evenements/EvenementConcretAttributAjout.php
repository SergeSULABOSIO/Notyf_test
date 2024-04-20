<?php
namespace App\Service\RefactoringJS\Evenements;


class EvenementConcretAttributAjout extends EvenementConcret
{
    public function __construct()
    {
        parent::__construct(Evenement::TYPE_ATTRIBUT_AJOUT);
    }
}
