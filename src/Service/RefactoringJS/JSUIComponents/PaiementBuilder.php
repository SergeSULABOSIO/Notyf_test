<?php

namespace App\Service\RefactoringJS\JSUIComponents;

use App\Controller\Admin\PaiementCrudController;
use App\Service\ServiceMonnaie;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class PaiementBuilder extends JSPanelBuilder
{
    public function buildListPanel(string $pageName, $objetInstance, $crud, AdminUrlGenerator $adminUrlGenerator): ?array
    {
        
        return [];
    }

    public function buildFormPanel(string $pageName, $objetInstance, $crud, AdminUrlGenerator $adminUrlGenerator): ?array
    {

        return [];
    }

    public function buildDetailsPanel(string $pageName, $objetInstance, $crud, AdminUrlGenerator $adminUrlGenerator): ?array
    {

        return [];
    }
}
