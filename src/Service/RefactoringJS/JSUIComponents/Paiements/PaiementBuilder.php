<?php

namespace App\Service\RefactoringJS\JSUIComponents\Paiements;

use App\Service\ServiceMonnaie;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Service\RefactoringJS\JSUIComponents\Parametres\JSPanelBuilder;
use App\Service\RefactoringJS\JSUIComponents\Paiements\PaiementListeRenderer;

class PaiementBuilder extends JSPanelBuilder
{

    public function __construct(
        private ServiceMonnaie $serviceMonnaie
    ) {
        
    }

    public function buildListPanel(string $pageName, $objetInstance, $crud, AdminUrlGenerator $adminUrlGenerator): ?array
    {
        return (new PaiementListeRenderer(
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
            $this->serviceMonnaie,
            $pageName,
            $objetInstance,
            $crud,
            $adminUrlGenerator
        ))->getChamps();
    }
}
