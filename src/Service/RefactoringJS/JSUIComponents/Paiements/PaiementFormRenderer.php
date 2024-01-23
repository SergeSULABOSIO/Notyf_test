<?php

namespace App\Service\RefactoringJS\JSUIComponents\Paiements;

use App\Controller\Admin\DocPieceCrudController;
use App\Service\ServiceMonnaie;
use App\Controller\Admin\PaiementCrudController;
use App\Controller\Admin\UtilisateurCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Service\RefactoringJS\JSUIComponents\Parametres\JSPanelRenderer;

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
        $this->addSection(
            "Section principale",
            "fas fa-location-crosshairs",
            10
        );
        $this->addChampAssociation(
            null,
            "facture",
            "Facture",
            false,
            true,
            10,
            null
        );
        $this->addChampChoix(
            null,
            "type",
            "Tye de facture",
            null,
            true,
            6,
            PaiementCrudController::TAB_TYPE_PAIEMENT,
            null
        );
        $this->addChampArgent(
            null,
            "montant",
            "Montant",
            null,
            null,
            2,
            $this->serviceMonnaie->getCodeSaisie()
        );
        $this->addChampDate(
            null,
            "paidAt",
            "Date",
            true,
            null,
            2
        );
        $this->addChampEditeurTexte(
            null,
            "description",
            "Description",
            false,
            null,
            10
        );
        $this->addSection(
            "Références bancaires",
            "fa-solid fa-piggy-bank",
            10
        );
        $this->addChampAssociation(
            null,
            "compteBancaire",
            "Comptes bancaires",
            false,
            null,
            10,
            null
        );
        $this->addSection(
            "Pièces jointes",
            "fa-solid fa-paperclip",
            10
        );
        $this->addChampCollection(
            null,
            "documents",
            "Documents",
            false,
            null,
            10,
            null,
            DocPieceCrudController::class,
            true,
            true
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
