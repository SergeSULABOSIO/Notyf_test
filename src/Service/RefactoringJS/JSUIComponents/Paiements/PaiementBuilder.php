<?php

namespace App\Service\RefactoringJS\JSUIComponents\Paiements;

use App\Service\ServiceMonnaie;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Service\RefactoringJS\JSUIComponents\Parametres\JSPanelBuilder;
use App\Service\RefactoringJS\JSUIComponents\Paiements\PaiementListeRenderer;
use Doctrine\ORM\EntityManager;

class PaiementBuilder extends JSPanelBuilder
{

    private ?PaiementListeRenderer $paiementListeRendere = null;
    private ?PaiementFormRenderer $paiementFormRendere = null;
    private ?PaiementDetailsRenderer $paiementDetailsRendere = null;

    public function __construct()
    {
    }

    public function buildListPanel(?EntityManager $entityManager = null, ?ServiceMonnaie $serviceMonnaie = null, ?string $pageName = null, $objetInstance = null, $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array
    {
        if ($this->paiementListeRendere == null) {
            $this->paiementListeRendere = new PaiementListeRenderer(
                $entityManager,
                $serviceMonnaie,
                $pageName,
                $objetInstance,
                $crud,
                $adminUrlGenerator
            );
            dd("ici");
        }
        return $this->paiementListeRendere->getChamps();
    }

    public function buildFormPanel(?EntityManager $entityManager = null, ?ServiceMonnaie $serviceMonnaie = null, ?string $pageName = null, $objetInstance = null, $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array
    {
        if ($this->paiementFormRendere == null) {
            $this->paiementFormRendere = new PaiementFormRenderer(
                $entityManager,
                $serviceMonnaie,
                $pageName,
                $objetInstance,
                $crud,
                $adminUrlGenerator
            );
        }
        return $this->paiementFormRendere->getChamps();
    }

    public function buildDetailsPanel(?EntityManager $entityManager = null, ?ServiceMonnaie $serviceMonnaie = null, ?string $pageName = null, $objetInstance = null, $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array
    {
        if ($this->paiementDetailsRendere == null) {
            $this->paiementDetailsRendere = new PaiementDetailsRenderer(
                $entityManager,
                $serviceMonnaie,
                $pageName,
                $objetInstance,
                $crud,
                $adminUrlGenerator
            );
        }
        return $this->paiementDetailsRendere->getChamps();
    }
}
