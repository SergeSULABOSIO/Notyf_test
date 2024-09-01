<?php
namespace App\Service\RefactoringJS\JSUIComponents\ExpertSinistre;

use App\Service\ServiceTaxes;
use App\Service\ServiceMonnaie;
use Doctrine\ORM\EntityManager;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSPanelBuilder;
use App\Service\RefactoringJS\JSUIComponents\EtapeSinistre\EtapeSinistreFormRenderer;
use App\Service\RefactoringJS\JSUIComponents\EtapeSinistre\EtapeSinistreListeRenderer;
use App\Service\RefactoringJS\JSUIComponents\ExpertSinistre\ExpertSinistreFormRenderer;
use App\Service\RefactoringJS\JSUIComponents\EtapeSinistre\EtapeSinistreDetailsRenderer;
use App\Service\RefactoringJS\JSUIComponents\ExpertSinistre\ExpertSinistreListeRenderer;
use App\Service\RefactoringJS\JSUIComponents\ExpertSinistre\ExpertSinistreDetailsRenderer;

class ExpertSinistreUIBuilder extends JSPanelBuilder
{
    private ?ExpertSinistreListeRenderer $listeRendere = null;
    private ?ExpertSinistreDetailsRenderer $detailsRendere = null;
    private ?ExpertSinistreFormRenderer $formRendere = null;

    public function __construct()
    {
    }

    public function buildListPanel(?EntityManager $entityManager = null, ?ServiceMonnaie $serviceMonnaie = null, ?ServiceTaxes $serviceTaxes = null, ?string $pageName = null, $objetInstance = null, $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array
    {
        $this->listeRendere = new ExpertSinistreListeRenderer(
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
        $this->formRendere = new ExpertSinistreFormRenderer(
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
        $this->detailsRendere = new ExpertSinistreDetailsRenderer(
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
