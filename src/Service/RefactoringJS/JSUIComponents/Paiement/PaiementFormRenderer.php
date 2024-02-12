<?php

namespace App\Service\RefactoringJS\JSUIComponents\Paiement;

use App\Entity\Facture;
use App\Service\ServiceMonnaie;
use Doctrine\ORM\EntityManager;
use App\Service\ServiceCrossCanal;
use App\Controller\Admin\FactureCrudController;
use App\Controller\Admin\DocPieceCrudController;
use App\Controller\Admin\PaiementCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSPanelRenderer;

class PaiementFormRenderer extends JSPanelRenderer
{
    private ?AdminUrlGenerator $adminUrlGenerator;

    public function __construct(
        private EntityManager $entityManager,
        private ServiceMonnaie $serviceMonnaie,
        string $pageName,
        $objetInstance,
        $crud,
        AdminUrlGenerator $adminUrlGenerator
    ) {
        parent::__construct(self::TYPE_FORMULAIRE, $pageName, $objetInstance, $crud, $adminUrlGenerator);
        $this->adminUrlGenerator = $adminUrlGenerator;
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
            null,
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
            null,
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
            null,
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
    }

    public function batchActions(?array $champs, ?string $type = null, ?string $pageName = null, $objetInstance = null, ?Crud $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array
    {
        /**
         * L'objectif de ce traitement de masse c'est de pouvoir ne pas afficher certains champs
         * du formulaire en fonction du type de facture que l'on est en train
         * de payer.
         * Le comportement du formulaire doit varier en fonction du type de facture que l'on paie.
         */
        /** @var Facture */
        $oFacture = null;
        if ($pageName == Action::NEW) {
            $oFacture = $this->entityManager
                ->getRepository(Facture::class)
                ->find(
                    $adminUrlGenerator->get("donnees")["facture"]
                );
            $this->addChampToDeactivate("facture");
        } else {
            $oFacture = $objetInstance->getFacture();
        }

        if (
            $oFacture->getType() == FactureCrudController::TAB_TYPE_FACTURE[FactureCrudController::TYPE_FACTURE_PRIME] ||
            $oFacture->getType() == FactureCrudController::TAB_TYPE_FACTURE[FactureCrudController::TYPE_FACTURE_RETROCOMMISSIONS] ||
            $oFacture->getType() == FactureCrudController::TAB_TYPE_FACTURE[FactureCrudController::TYPE_FACTURE_NOTE_DE_PERCEPTION_ARCA] ||
            $oFacture->getType() == FactureCrudController::TAB_TYPE_FACTURE[FactureCrudController::TYPE_FACTURE_NOTE_DE_PERCEPTION_TVA]
        ) {
            $this->addChampToRemove("compteBancaire");
            $this->addChampToRemove("Références bancaires");
        }

        return $champs;
    }
}
