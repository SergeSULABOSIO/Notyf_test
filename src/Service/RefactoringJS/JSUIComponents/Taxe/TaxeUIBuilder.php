<?php

namespace App\Service\RefactoringJS\JSUIComponents\Paiement;

use App\Service\ServiceTaxes;
use App\Service\ServiceMonnaie;
use Doctrine\ORM\EntityManager;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Service\RefactoringJS\JSUIComponents\Tranche\TaxeFormRenderer;
use App\Service\RefactoringJS\JSUIComponents\Tranche\TaxeListeRenderer;
use App\Service\RefactoringJS\JSUIComponents\Tranche\TaxeDetailsRenderer;
use App\Service\RefactoringJS\JSUIComponents\Tranche\TrancheFormRenderer;
use App\Service\RefactoringJS\JSUIComponents\Tranche\TrancheListeRenderer;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSPanelBuilder;
use App\Service\RefactoringJS\JSUIComponents\Tranche\TrancheDetailsRenderer;

class TaxeUIBuilder extends JSPanelBuilder
{
    private ?TaxeListeRenderer $listeRendere = null;
    private ?TaxeDetailsRenderer $detailsRendere = null;
    private ?TaxeFormRenderer $formRendere = null;

    public function __construct()
    {
        
    }

    public function buildListPanel(?EntityManager $entityManager = null, ?ServiceMonnaie $serviceMonnaie = null, ?ServiceTaxes $serviceTaxes = null, ?string $pageName = null, $objetInstance = null, $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array
    {
        $this->listeRendere = new TaxeListeRenderer(
            $entityManager,
            $serviceMonnaie,
            $serviceTaxes,
            $pageName,
            $objetInstance,
            $crud,
            $adminUrlGenerator
        );
        return $this->listeRendere->getChamps();
    }

    public function buildFormPanel(?EntityManager $entityManager = null, ?ServiceMonnaie $serviceMonnaie = null, ?ServiceTaxes $serviceTaxes = null, ?string $pageName = null, $objetInstance = null, $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array
    {
        $this->formRendere = new TaxeFormRenderer(
            $entityManager,
            $serviceMonnaie,
            $serviceTaxes,
            $pageName,
            $objetInstance,
            $crud,
            $adminUrlGenerator
        );
        return $this->formRendere->getChamps();
    }

    public function buildDetailsPanel(?EntityManager $entityManager = null, ?ServiceMonnaie $serviceMonnaie = null, ?ServiceTaxes $serviceTaxes = null, ?string $pageName = null, $objetInstance = null, $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array
    {
        $this->detailsRendere = new TaxeDetailsRenderer(
            $entityManager,
            $serviceMonnaie,
            $serviceTaxes,
            $pageName,
            $objetInstance,
            $crud,
            $adminUrlGenerator
        );
        return $this->detailsRendere->getChamps();
    }
}
