<?php

namespace App\Service\RefactoringJS\JSUIComponents\Paiement;

use App\Entity\Facture;
use App\Service\ServiceMonnaie;
use Doctrine\ORM\EntityManager;
use App\Controller\Admin\FactureCrudController;
use App\Controller\Admin\DocPieceCrudController;
use App\Controller\Admin\PaiementCrudController;
use App\Entity\Paiement;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSChamp;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSPanelRenderer;

class PaiementFormRenderer extends JSPanelRenderer
{
    public function __construct(
        private EntityManager $entityManager,
        private ServiceMonnaie $serviceMonnaie,
        private string $pageName,
        private $objetInstance,
        private $crud,
        private AdminUrlGenerator $adminUrlGenerator
    ) {
        parent::__construct(self::TYPE_FORMULAIRE, $pageName, $objetInstance, $crud, $adminUrlGenerator);
        $this->adminUrlGenerator = $adminUrlGenerator;
    }

    public function design()
    {
        $column = 12;
        if ($this->objetInstance instanceof Paiement) {
            $column = 10;
        }

        //Section - Principale
        $this->addChamp(
            (new JSChamp())
                ->createSection("Informations générales")
                ->setIcon('fa-solid fa-cash-register') //<i class="fa-sharp fa-solid fa-address-book"></i>
                ->setHelp("Veuillez saisir les informations relatives au paiement.")
                ->setColumns($column)
                ->getChamp()
        );

        $this->addChamp(
            (new JSChamp())
                ->createSection("Section principale")
                ->setIcon("fas fa-location-crosshairs")
                ->setColumns($column)
                ->getChamp()
        );

        $this->addChamp(
            (new JSChamp())
                ->createAssociation("facture", "Facture")
                ->setColumns($column)
                ->getChamp()
        );

        $this->addChamp(
            (new JSChamp())
                ->createChoix("destination", "Destination")
                ->setColumns($column)
                ->setChoices(FactureCrudController::TAB_DESTINATION)
                ->getChamp()
        );

        $this->addChamp(
            (new JSChamp())
                ->createChoix("type", "Type")
                ->setColumns($column)
                ->setChoices(PaiementCrudController::TAB_TYPE_PAIEMENT)
                ->getChamp()
        );

        $this->addChamp(
            (new JSChamp())
                ->createArgent("montant", "Montant")
                ->setColumns($column)
                ->setCurrency($this->serviceMonnaie->getCodeSaisie())
                ->getChamp()
        );

        $this->addChamp(
            (new JSChamp())
                ->createDate("paidAt", "Date")
                ->setRequired(true)
                ->setColumns($column)
                ->getChamp()
        );

        $this->addChamp(
            (new JSChamp())
                ->createEditeurTexte("description", "Description")
                ->setColumns($column)
                ->getChamp()
        );

        $this->addChamp(
            (new JSChamp())
                ->createSection("Références bancaires")
                ->setIcon("fa-solid fa-piggy-bank")
                ->setColumns($column)
                ->getChamp()
        );

        $this->addChamp(
            (new JSChamp())
                ->createAssociation("compteBancaire", "Comptes bancaires")
                ->setColumns($column)
                ->getChamp()
        );

        $this->addChamp(
            (new JSChamp())
                ->createSection("Pièces jointes")
                ->setColumns($column)
                ->setIcon("fa-solid fa-paperclip")
                ->getChamp()
        );

        $this->addChamp(
            (new JSChamp())
                ->createCollection("documents", "Documents")
                ->useEntryCrudForm(DocPieceCrudController::class)
                ->setColumns($column)
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
            $this->addChampToDeactivate("destination");
            $this->addChampToDeactivate("type");
        } else {
            $oFacture = $objetInstance->getFacture();
        }

        if (
            $oFacture->getDestination() == FactureCrudController::TAB_DESTINATION[FactureCrudController::DESTINATION_PARTENAIRE] ||
            $oFacture->getDestination() == FactureCrudController::TAB_DESTINATION[FactureCrudController::DESTINATION_ARCA] ||
            $oFacture->getDestination() == FactureCrudController::TAB_DESTINATION[FactureCrudController::DESTINATION_DGI]
        ) {
            $this->addChampToRemove("compteBancaire");
            $this->addChampToRemove("Références bancaires");
        }

        return $champs;
    }
}
