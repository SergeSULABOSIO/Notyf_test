<?php

namespace App\Service\RefactoringJS\JSUIComponents\Paiements;

use App\Service\ServiceMonnaie;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Service\RefactoringJS\JSUIComponents\Parametres\JSPanelBuilder;
use App\Service\RefactoringJS\JSUIComponents\Paiements\PaiementListeRenderer;
use Doctrine\ORM\EntityManager;

class PaiementBuilder extends JSPanelBuilder
{

    private static ?PaiementListeRenderer $paiementListeRendere = null;
    private static ?PaiementFormRenderer $paiementFormRendere = null;
    private static ?PaiementDetailsRenderer $paiementDetailsRendere = null;

    public function __construct(
        private EntityManager $entityManager,
        private ServiceMonnaie $serviceMonnaie
    ) {
        
    }

    public static function buildListPanel(?EntityManager $entityManager = null, ?ServiceMonnaie $serviceMonnaie = null, ?string $pageName = null, $objetInstance = null, $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array
    {
        return (new PaiementListeRenderer(
            $entityManager,
            $serviceMonnaie,
            $pageName,
            $objetInstance,
            $crud,
            $adminUrlGenerator
        ))->getChamps();
    }

    public static function buildFormPanel(?EntityManager $entityManager = null, ?ServiceMonnaie $serviceMonnaie = null, ?string $pageName = null, $objetInstance = null, $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array
    {
        return (new PaiementFormRenderer(
            $entityManager,
            $serviceMonnaie,
            $pageName,
            $objetInstance,
            $crud,
            $adminUrlGenerator
        ))->getChamps();
    }

    public static function buildDetailsPanel(?EntityManager $entityManager = null, ?ServiceMonnaie $serviceMonnaie = null, ?string $pageName = null, $objetInstance = null, $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array
    {
        return (new PaiementDetailsRenderer(
            $entityManager,
            $serviceMonnaie,
            $pageName,
            $objetInstance,
            $crud,
            $adminUrlGenerator
        ))->getChamps();
    }
}
