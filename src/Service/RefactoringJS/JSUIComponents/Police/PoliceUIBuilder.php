<?php

namespace App\Service\RefactoringJS\JSUIComponents\Police;

use App\Service\ServiceTaxes;
use App\Service\ServiceMonnaie;
use Doctrine\ORM\EntityManager;
use App\Service\ServiceEntreprise;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Service\RefactoringJS\JSUIComponents\Police\PoliceFormRenderer;
use App\Service\RefactoringJS\JSUIComponents\Police\PoliceListeRenderer;
use App\Service\RefactoringJS\JSUIComponents\Police\PoliceDetailsRenderer;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSPanelBuilder;

class PoliceUIBuilder extends JSPanelBuilder
{
    private ?PoliceListeRenderer $listeRendere = null;
    private ?PoliceDetailsRenderer $detailsRendere = null;
    private ?PoliceFormRenderer $formRendere = null;

    public function __construct(
        private ServiceEntreprise $serviceEntreprise
    ) {
    }

    public function buildListPanel(?EntityManager $entityManager = null, ?ServiceMonnaie $serviceMonnaie = null, ?ServiceTaxes $serviceTaxes = null, ?string $pageName = null, $objetInstance = null, $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array
    {
        $this->listeRendere = new PoliceListeRenderer(
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
        $this->formRendere = new PoliceFormRenderer(
            $entityManager,
            $serviceMonnaie,
            $serviceTaxes,
            $this->serviceEntreprise,
            $pageName,
            $objetInstance,
            $crud,
            $adminUrlGenerator
        );
        return $this->formRendere->getChamps();
    }

    public function buildDetailsPanel(?EntityManager $entityManager = null, ?ServiceMonnaie $serviceMonnaie = null, ?ServiceTaxes $serviceTaxes = null, ?string $pageName = null, $objetInstance = null, $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array
    {
        $this->detailsRendere = new PoliceDetailsRenderer(
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
