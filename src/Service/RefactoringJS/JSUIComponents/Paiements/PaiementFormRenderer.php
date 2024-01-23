<?php

namespace App\Service\RefactoringJS\JSUIComponents;

use App\Service\ServiceMonnaie;
use App\Controller\Admin\PaiementCrudController;
use App\Controller\Admin\UtilisateurCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class PaiementFormRenderer extends JSPanelRenderer
{
    public function __construct(
        private ServiceMonnaie $serviceMonnaie,
        string $pageName,
        $objetInstance,
        $crud,
        AdminUrlGenerator $adminUrlGenerator
    ) {
        parent::__construct(self::TYPE_FORMULAIRE, $pageName, $objetInstance, $crud, $adminUrlGenerator);
    }

    public function design()
    {
        $this->addOnglet(
            "Informations générales",
            "fa-solid fa-cash-register",
            "Veuillez saisir les informations relatives au paiement."
        );

        // $this->addChampDate(
        //     null,
        //     "paidAt",
        //     "Date de paiement",
        //     null,
        //     null,
        //     null
        // );
        // $this->addChampArgent(
        //     null,
        //     "montant",
        //     "Montant",
        //     null,
        //     null,
        //     null,
        //     $this->serviceMonnaie->getCodeAffichage()
        // );
        // $this->addChampZoneTexte(
        //     null,
        //     "description",
        //     "Description",
        //     null,
        //     null,
        //     null
        // );
        // $this->addChampAssociation(
        //     null,
        //     "facture",
        //     "Facture",
        //     null,
        //     null,
        //     null,
        //     null
        // );
        // $this->addChampTableau(
        //     null,
        //     "documents",
        //     "Documents",
        //     null,
        //     null,
        //     null,
        //     null,
        //     null,
        //     null,
        //     null
        // );
        // $this->addChampAssociation(
        //     null,
        //     "compteBancaire",
        //     "Comptes bancaires",
        //     null,
        //     null,
        //     null,
        //     null,
        //     null
        // );
        // $this->addChampDate(
        //     null,
        //     "createdAt",
        //     "Date de création",
        //     null,
        //     null,
        //     null
        // );
        // $this->addChampDate(
        //     null,
        //     "updatedAt",
        //     "Dernière modification",
        //     null,
        //     null,
        //     null
        // );
        // $this->addChampAssociation(
        //     UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE],
        //     "utilisateur",
        //     "Utilisateur",
        //     null,
        //     null,
        //     null,
        //     null,
        //     null
        // );
        // $this->addChampAssociation(
        //     UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE],
        //     "entreprise",
        //     "Entreprise",
        //     null,
        //     null,
        //     null,
        //     null,
        //     null
        // );
    }
}
