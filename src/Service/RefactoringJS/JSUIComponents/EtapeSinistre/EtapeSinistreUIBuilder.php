<?php

namespace App\Service\RefactoringJS\JSUIComponents\EtapeSinistre;

use App\Service\ServiceTaxes;
use App\Service\ServiceMonnaie;
use Doctrine\ORM\EntityManager;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSPanelBuilder;
use App\Service\RefactoringJS\JSUIComponents\EtapeSinistre\EtapeSinistreFormRenderer;
use App\Service\RefactoringJS\JSUIComponents\EtapeSinistre\EtapeSinistreListeRenderer;
use App\Service\RefactoringJS\JSUIComponents\EtapeSinistre\EtapeSinistreDetailsRenderer;

class EtapeSinistreUIBuilder extends JSPanelBuilder
{
    private ?EtapeSinistreListeRenderer $listeRendere = null;
    private ?EtapeSinistreDetailsRenderer $detailsRendere = null;
    private ?EtapeSinistreFormRenderer $formRendere = null;

    public function __construct()
    {
    }

    public function buildListPanel(?EntityManager $entityManager = null, ?ServiceMonnaie $serviceMonnaie = null, ?ServiceTaxes $serviceTaxes = null, ?string $pageName = null, $objetInstance = null, $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array
    {
        $this->listeRendere = new EtapeSinistreListeRenderer(
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
        $this->formRendere = new EtapeSinistreFormRenderer(
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
        $this->detailsRendere = new EtapeSinistreDetailsRenderer(
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
