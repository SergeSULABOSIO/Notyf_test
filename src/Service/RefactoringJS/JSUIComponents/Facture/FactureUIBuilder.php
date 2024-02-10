<?php

namespace App\Service\RefactoringJS\JSUIComponents\Paiement;

use App\Service\ServiceTaxes;
use App\Service\ServiceMonnaie;
use Doctrine\ORM\EntityManager;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Service\RefactoringJS\JSUIComponents\Tranche\RevenuFormRenderer;
use App\Service\RefactoringJS\JSUIComponents\Tranche\FactureFormRenderer;
use App\Service\RefactoringJS\JSUIComponents\Tranche\RevenuListeRenderer;
use App\Service\RefactoringJS\JSUIComponents\Tranche\TrancheFormRenderer;
use App\Service\RefactoringJS\JSUIComponents\Tranche\FactureListeRenderer;
use App\Service\RefactoringJS\JSUIComponents\Tranche\TrancheListeRenderer;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSPanelBuilder;
use App\Service\RefactoringJS\JSUIComponents\Tranche\RevenuDetailsRenderer;
use App\Service\RefactoringJS\JSUIComponents\Tranche\FactureDetailsRenderer;
use App\Service\RefactoringJS\JSUIComponents\Tranche\TrancheDetailsRenderer;

class FactureUIBuilder extends JSPanelBuilder
{
    private ?FactureListeRenderer $listeRendere = null;
    private ?FactureDetailsRenderer $detailsRendere = null;
    private ?FactureFormRenderer $formRendere = null;

    public function __construct()
    {
        
    }

    public function buildListPanel(?EntityManager $entityManager = null, ?ServiceMonnaie $serviceMonnaie = null, ?ServiceTaxes $serviceTaxes = null, ?string $pageName = null, $objetInstance = null, $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array
    {
        $this->listeRendere = new FactureListeRenderer(
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
        $this->formRendere = new FactureFormRenderer(
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
        $this->detailsRendere = new FactureDetailsRenderer(
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
