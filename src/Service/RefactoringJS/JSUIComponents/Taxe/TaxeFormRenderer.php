<?php

namespace App\Service\RefactoringJS\JSUIComponents\Tranche;

use App\Entity\Taxe;
use App\Entity\Tranche;
use App\Service\ServiceTaxes;
use App\Service\ServiceMonnaie;
use Doctrine\ORM\EntityManager;
use App\Controller\Admin\TaxeCrudController;
use App\Controller\Admin\PaiementCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use App\Controller\Admin\UtilisateurCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSPanelRenderer;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSCssHtmlDecoration;

class TaxeFormRenderer extends JSPanelRenderer
{
    public function __construct(
        private EntityManager $entityManager,
        private ServiceMonnaie $serviceMonnaie,
        private ServiceTaxes $serviceTaxes,
        string $pageName,
        $objetInstance,
        $crud,
        AdminUrlGenerator $adminUrlGenerator
    ) {
        parent::__construct(self::TYPE_FORMULAIRE, $pageName, $objetInstance, $crud, $adminUrlGenerator);
    }

    public function design()
    {
        //Nom
        $this->addChampTexte(
            null,
            "nom",
            "Intitulé",
            false,
            false,
            12,
            null
        );
        //taux IARD
        $this->addChampPourcentage(
            null,
            "tauxIARD",
            "Taux (IARD/Non-Vie)",
            false,
            false,
            12,
            null
        );
        //taux VIE
        $this->addChampPourcentage(
            null,
            "tauxVIE",
            "Taux (IARD/Non-Vie)",
            false,
            false,
            12,
            null
        );
        //Description
        $this->addChampEditeurTexte(
            null,
            "description",
            "Description",
            false,
            false,
            12,
            null
        );
        //Payable par courtier?
        $this->addChampChoix(
            null,
            "payableparcourtier",
            "Par courtier?",
            false,
            false,
            12,
            TaxeCrudController::TAB_TAXE_PAYABLE_PAR_COURTIER,
            null
        );
    }

    public function batchActions(?array $champs, ?string $type = null, ?string $pageName = null, $objetInstance = null, ?Crud $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array
    {
        return $champs;
    }
}
