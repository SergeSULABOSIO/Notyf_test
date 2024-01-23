<?php

namespace App\Service\RefactoringJS\JSUIComponents\Paiements;

use App\Service\ServiceMonnaie;
use App\Controller\Admin\PaiementCrudController;
use App\Controller\Admin\UtilisateurCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Service\RefactoringJS\JSUIComponents\Parametres\JSPanelRenderer;

class PaiementDetailsRenderer extends JSPanelRenderer
{
    public function __construct(
        private ServiceMonnaie $serviceMonnaie,
        string $pageName,
        $objetInstance,
        $crud,
        AdminUrlGenerator $adminUrlGenerator
    ) {
        parent::__construct(self::TYPE_DETAILS, $pageName, $objetInstance, $crud, $adminUrlGenerator);
    }

    public function design()
    {
        $this->addChampDate(
            null,
            "paidAt",
            "Date de paiement",
            null,
            null,
            null
        );
        $this->addChampArgent(
            null,
            "montant",
            "Montant",
            null,
            null,
            null,
            $this->serviceMonnaie->getCodeAffichage()
        );
        $this->addChampZoneTexte(
            null,
            "description",
            "Description",
            null,
            null,
            null
        );
        $this->addChampAssociation(
            null,
            "facture",
            "Facture",
            null,
            null,
            null,
            null
        );
        $this->addChampTableau(
            null,
            "documents",
            "Documents",
            null,
            null,
            null,
            null,
            null,
            null,
            null
        );
        $this->addChampAssociation(
            null,
            "compteBancaire",
            "Comptes bancaires",
            null,
            null,
            null,
            null,
            null
        );
        $this->addChampDate(
            null,
            "createdAt",
            "Date de création",
            null,
            null,
            null
        );
        $this->addChampDate(
            null,
            "updatedAt",
            "Dernière modification",
            null,
            null,
            null
        );
        $this->addChampAssociation(
            UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE],
            "utilisateur",
            "Utilisateur",
            null,
            null,
            null,
            null,
            null
        );
        $this->addChampAssociation(
            UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE],
            "entreprise",
            "Entreprise",
            null,
            null,
            null,
            null,
            null
        );
    }
}
