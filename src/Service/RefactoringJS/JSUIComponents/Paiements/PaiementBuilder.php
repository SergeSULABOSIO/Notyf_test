<?php

namespace App\Service\RefactoringJS\JSUIComponents\Paiements;

use App\Service\ServiceMonnaie;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Service\RefactoringJS\JSUIComponents\Parametres\JSPanelBuilder;
use App\Service\RefactoringJS\JSUIComponents\Paiements\PaiementListeRenderer;
use Doctrine\ORM\EntityManager;

class PaiementBuilder extends JSPanelBuilder
{

    public function __construct(
        private EntityManager $entityManager,
        private ServiceMonnaie $serviceMonnaie
    ) {
        
    }

    public function buildListPanel(string $pageName, $objetInstance, $crud, AdminUrlGenerator $adminUrlGenerator): ?array
    {
        return (new PaiementListeRenderer(
            $this->entityManager,
            $this->serviceMonnaie,
            $pageName,
            $objetInstance,
            $crud,
            $adminUrlGenerator
        ))->getChamps();
    }

    public function buildFormPanel(string $pageName, $objetInstance, $crud, AdminUrlGenerator $adminUrlGenerator): ?array
    {
        return (new PaiementFormRenderer(
            $this->entityManager,
            $this->serviceMonnaie,
            $pageName,
            $objetInstance,
            $crud,
            $adminUrlGenerator
        ))->getChamps();
    }

    public function buildDetailsPanel(string $pageName, $objetInstance, $crud, AdminUrlGenerator $adminUrlGenerator): ?array
    {
        return (new PaiementDetailsRenderer(
            $this->entityManager,
            $this->serviceMonnaie,
            $pageName,
            $objetInstance,
            $crud,
            $adminUrlGenerator
        ))->getChamps();
    }
}
