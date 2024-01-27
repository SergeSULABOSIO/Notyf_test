<?php

namespace App\Service\RefactoringJS\JSUIComponents\Paiement;

use App\Service\ServiceMonnaie;
use Doctrine\ORM\EntityManager;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSPanelBuilder;

class PaiementUIBuilder extends JSPanelBuilder
{
    private ?PaiementListeRenderer $paiementListeRendere = null;
    private ?PaiementFormRenderer $paiementFormRendere = null;
    private ?PaiementDetailsRenderer $paiementDetailsRendere = null;

    public function __construct()
    {
        
    }

    public function buildListPanel(?EntityManager $entityManager = null, ?ServiceMonnaie $serviceMonnaie = null, ?string $pageName = null, $objetInstance = null, $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array
    {
        $this->paiementListeRendere = new PaiementListeRenderer(
            $entityManager,
            $serviceMonnaie,
            $pageName,
            $objetInstance,
            $crud,
            $adminUrlGenerator
        );
        return $this->paiementListeRendere->getChamps();
    }

    public function buildFormPanel(?EntityManager $entityManager = null, ?ServiceMonnaie $serviceMonnaie = null, ?string $pageName = null, $objetInstance = null, $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array
    {
        $this->paiementFormRendere = new PaiementFormRenderer(
            $entityManager,
            $serviceMonnaie,
            $pageName,
            $objetInstance,
            $crud,
            $adminUrlGenerator
        );
        return $this->paiementFormRendere->getChamps();
    }

    public function buildDetailsPanel(?EntityManager $entityManager = null, ?ServiceMonnaie $serviceMonnaie = null, ?string $pageName = null, $objetInstance = null, $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array
    {
        $this->paiementDetailsRendere = new PaiementDetailsRenderer(
            $entityManager,
            $serviceMonnaie,
            $pageName,
            $objetInstance,
            $crud,
            $adminUrlGenerator
        );
        return $this->paiementDetailsRendere->getChamps();
    }
}
