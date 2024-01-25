<?php

namespace App\Service\RefactoringJS\JSUIComponents\Parametres;

use App\Service\ServiceMonnaie;
use Doctrine\ORM\EntityManager;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

abstract class JSPanelBuilder
{
    private ?EntityManager $entityManager;
    private ?ServiceMonnaie $serviceMonnaie; 
    private ?array $champs;
    private ?string $pageName;
    private $objetInstance;
    private ?Crud $crud;
    private ?AdminUrlGenerator $adminUrlGenerator;


    public function __construct()
    {
        $this->initChamps();
    }

    public static abstract function buildListPanel(?EntityManager $entityManager = null, ?ServiceMonnaie $serviceMonnaie = null, string $pageName = null, $objetInstance = null, $crud = null, AdminUrlGenerator $adminUrlGenerator = null): ?array;
    public static abstract function buildDetailsPanel(?EntityManager $entityManager = null, ?ServiceMonnaie $serviceMonnaie = null, string $pageName = null, $objetInstance = null, $crud = null, AdminUrlGenerator $adminUrlGenerator = null): ?array;
    public static abstract function buildFormPanel(?EntityManager $entityManager = null, ?ServiceMonnaie $serviceMonnaie = null, string $pageName = null, $objetInstance = null, $crud = null, AdminUrlGenerator $adminUrlGenerator = null): ?array;

    private function initChamps()
    {
        $this->champs = [];
    }

    public function render(?EntityManager $entityManager = null, ?ServiceMonnaie $serviceMonnaie = null, string $pageName, $objetInstance, $crud, AdminUrlGenerator $adminUrlGenerator)
    {
        $this->entityManager = $entityManager;
        $this->serviceMonnaie = $serviceMonnaie;
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
                    $this->pageName,
                    $this->objetInstance,
                    $this->crud,
                    $this->adminUrlGenerator
                );
                break;
        }
        return $this->champs;
    }
}
