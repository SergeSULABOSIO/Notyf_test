<?php

namespace App\Service\RefactoringJS\JSUIComponents;

use App\Service\ServiceMonnaie;
use App\Controller\Admin\PaiementCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class PaiementListeRenderer extends JSPanelRenderer
{
    public function __construct(
        private ServiceMonnaie $serviceMonnaie,
        string $pageName,
        $objetInstance,
        $crud,
        AdminUrlGenerator $adminUrlGenerator
    ) {
        parent::__construct(self::TYPE_LISTE, $pageName, $objetInstance, $crud, $adminUrlGenerator);
    }

    public function design()
    {
        // $this->addOnglet(
        //     "Informations générales",
        //     "fa-solid fa-cash-register",
        //     "Veuillez saisir les informations relatives au paiement."
        // );
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
        $this->addChampArgent(
            null,
            "montant",
            "Montant",
            true,
            false,
            10,
            $this->serviceMonnaie->getCodeAffichage()
        );
        $this->addChampDate(
            null,
            "paidAt",
            "Date de paiement",
            false,
            false,
            10
        );
        $this->addChampAssociation(
            null,
            "facture",
            "Facture",
            false,
            false,
            10,
            null
        );
        $this->addChampAssociation(
            null,
            "utilisateur",
            "Utilisateur",
            false,
            false,
            10,
            null
        );
        $this->addChampAssociation(
            null,
            "entreprise",
            "Entreprise",
            false,
            false,
            10,
            null
        );
    }
}
