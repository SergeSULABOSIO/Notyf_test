<?php

namespace App\Service\RefactoringJS\JSUIComponents\JSUIParametres;

use App\Service\ServiceTaxes;
use App\Service\ServiceMonnaie;
use Doctrine\ORM\EntityManager;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

abstract class JSPanelBuilder
{
    private ?EntityManager $entityManager;
    private ?ServiceMonnaie $serviceMonnaie;
    private ?ServiceTaxes $serviceTaxe;
    private ?array $champs;
    private ?string $pageName;
    private $objetInstance;
    private ?Crud $crud;
    private ?AdminUrlGenerator $adminUrlGenerator;


    public function __construct()
    {
        $this->initChamps();
    }

    public abstract function buildListPanel(?EntityManager $entityManager = null, ?ServiceMonnaie $serviceMonnaie = null, ?ServiceTaxes $serviceTaxes = null, string $pageName = null, $objetInstance = null, $crud = null, AdminUrlGenerator $adminUrlGenerator = null): ?array;
    public abstract function buildDetailsPanel(?EntityManager $entityManager = null, ?ServiceMonnaie $serviceMonnaie = null, ?ServiceTaxes $serviceTaxes = null, string $pageName = null, $objetInstance = null, $crud = null, AdminUrlGenerator $adminUrlGenerator = null): ?array;
    public abstract function buildFormPanel(?EntityManager $entityManager = null, ?ServiceMonnaie $serviceMonnaie = null, ?ServiceTaxes $serviceTaxes = null, string $pageName = null, $objetInstance = null, $crud = null, AdminUrlGenerator $adminUrlGenerator = null): ?array;

    private function initChamps()
    {
        $this->champs = [];
    }

    public function render(?EntityManager $entityManager = null, ?ServiceMonnaie $serviceMonnaie = null, ?ServiceTaxes $serviceTaxes = null, string $pageName, $objetInstance, $crud, AdminUrlGenerator $adminUrlGenerator)
    {
        $this->entityManager = $entityManager;
        $this->serviceMonnaie = $serviceMonnaie;
        $this->serviceTaxe = $serviceTaxes;
        $this->pageName = $pageName;
        $this->objetInstance = $objetInstance;
        $this->crud = $crud;
        $this->adminUrlGenerator = $adminUrlGenerator;
        $this->initChamps();
        
        switch ($this->pageName) {
            case Crud::PAGE_INDEX:
                $this->champs = $this->buildListPanel(
                    $this->entityManager,
                    $this->serviceMonnaie,
                    $this->serviceTaxe,
                    $this->pageName,
                    $this->objetInstance,
                    $this->crud,
                    $this->adminUrlGenerator
                );
                break;
            case Crud::PAGE_DETAIL:
                $this->champs = $this->buildDetailsPanel(
                    $this->entityManager,
                    $this->serviceMonnaie,
                    $this->serviceTaxe,
                    $this->pageName,
                    $this->objetInstance,
                    $this->crud,
                    $this->adminUrlGenerator
                );
                break;
            case Crud::PAGE_EDIT || Crud::PAGE_NEW:
                $this->champs = $this->buildFormPanel(
                    $this->entityManager,
                    $this->serviceMonnaie,
                    $this->serviceTaxe,
                    $this->pageName,
                    $this->objetInstance,
                    $this->crud,
                    $this->adminUrlGenerator
                );
                // dd("Ici:", $this->pageName, $this->champs);
                break;
        }
        // dd("Ici", $this->champs);
        return $this->champs;
    }
}
