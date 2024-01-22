<?php

namespace App\Service\RefactoringJS\JSUIComponents;

use App\Controller\Admin\PaiementCrudController;

class PaiementListeRenderer extends JSPanelRenderer
{
    public function __construct()
    {
        parent::__construct(self::TYPE_LISTE);
    }

    public function design()
    {
        $this->addOnglet(
            "Informations générales",
            "fa-solid fa-cash-register",
            "Veuillez saisir les informations relatives au paiement."
        );
        $this->addChampChoix(
            null,
            "type",
            "Type",
            true,
            true,
            10,
            PaiementCrudController::TAB_TYPE_PAIEMENT,
            [
                PaiementCrudController::TAB_TYPE_PAIEMENT[PaiementCrudController::TYPE_PAIEMENT_ENTREE] => 'success', //info
                PaiementCrudController::TAB_TYPE_PAIEMENT[PaiementCrudController::TYPE_PAIEMENT_SORTIE] => 'danger', //info
            ]
        );
    }
}
