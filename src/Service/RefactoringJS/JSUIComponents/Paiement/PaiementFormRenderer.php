<?php

namespace App\Service\RefactoringJS\JSUIComponents\Paiement;

use App\Entity\Facture;
use App\Service\ServiceMonnaie;
use Doctrine\ORM\EntityManager;
use App\Controller\Admin\FactureCrudController;
use App\Controller\Admin\DocPieceCrudController;
use App\Controller\Admin\PaiementCrudController;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSChamp;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
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
        $this->addChamp(
            (new JSChamp())
                ->createOnglet("Informations générales")
                ->setIcon("fa-solid fa-cash-register")
                ->setHelp("Veuillez saisir les informations relatives au paiement.")
                ->getChamp()
        );

        $this->addChamp(
            (new JSChamp())
                ->createSection("Section principale")
                ->setIcon("fas fa-location-crosshairs")
                ->setColumns(10)
                ->getChamp()
        );
        
        $this->addChamp(
            (new JSChamp())
                ->createAssociation("facture", "Facture")
                ->setColumns(10)
                ->getChamp()
        );
        
        $this->addChamp(
            (new JSChamp())
                ->createChoix("type", "Tye de facture")
                ->setColumns(6)
                ->setChoices(PaiementCrudController::TAB_TYPE_PAIEMENT)
                ->getChamp()
        );
        
        $this->addChamp(
            (new JSChamp())
                ->createArgent("montant", "Montant")
                ->setColumns(2)
                ->setCurrency($this->serviceMonnaie->getCodeSaisie())
                ->getChamp()
        );
        
        $this->addChamp(
            (new JSChamp())
                ->createDate("paidAt", "Date")
                ->setRequired(true)
                ->setColumns(2)
                ->getChamp()
        );
        
        $this->addChamp(
            (new JSChamp())
                ->createEditeurTexte("description", "Description")
                ->setColumns(10)
                ->getChamp()
        );
        
        $this->addChamp(
            (new JSChamp())
                ->createSection("Références bancaires")
                ->setIcon("fa-solid fa-piggy-bank")
                ->setColumns(10)
                ->getChamp()
        );
        
        $this->addChamp(
            (new JSChamp())
                ->createAssociation("compteBancaire", "Comptes bancaires")
                ->setColumns(10)
                ->getChamp()
        );
        
        $this->addChamp(
            (new JSChamp())
                ->createSection("Pièces jointes")
                ->setColumns(10)
                ->setIcon("fa-solid fa-paperclip")
                ->getChamp()
        );
        
        $this->addChamp(
            (new JSChamp())
                ->createCollection("documents", "Documents")
                ->useEntryCrudForm(DocPieceCrudController::class)
                ->setColumns(10)
                ->getChamp()
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
