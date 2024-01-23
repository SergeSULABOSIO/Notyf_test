<?php

namespace App\Service\RefactoringJS\JSUIComponents\Parametres;

use DateTimeImmutable;
use App\Entity\Facture;
use App\Entity\Tranche;
use App\Entity\Assureur;
use App\Entity\Entreprise;
use App\Entity\Partenaire;
use App\Entity\Utilisateur;
use App\Entity\ElementFacture;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

abstract class JSPanelBuilder
{
    private ?array $champs;
    private ?string $pageName;
    private $objetInstance;
    private ?Crud $crud;
    private ?AdminUrlGenerator $adminUrlGenerator;


    public function __construct()
    {
        $this->initChamps();
    }

    public abstract function buildListPanel(string $pageName, $objetInstance, $crud, AdminUrlGenerator $adminUrlGenerator): ?array;
    public abstract function buildDetailsPanel(string $pageName, $objetInstance, $crud, AdminUrlGenerator $adminUrlGenerator): ?array;
    public abstract function buildFormPanel(string $pageName, $objetInstance, $crud, AdminUrlGenerator $adminUrlGenerator): ?array;

    private function initChamps(){
        $this->champs = [];
    }

    public function render(string $pageName, $objetInstance, $crud, AdminUrlGenerator $adminUrlGenerator)
    {
        $this->pageName = $pageName;
        $this->objetInstance = $objetInstance;
        $this->crud = $crud;
        $this->adminUrlGenerator = $adminUrlGenerator;
        $this->initChamps();

        switch ($this->pageName) {
            case Crud::PAGE_INDEX:
                $this->champs = $this->buildListPanel(
                    $this->pageName,
                    $this->objetInstance,
                    $this->crud,
                    $this->adminUrlGenerator
                );
                break;
            case Crud::PAGE_DETAIL:
                $this->champs = $this->buildDetailsPanel(
                    $this->pageName,
                    $this->objetInstance,
                    $this->crud,
                    $this->adminUrlGenerator
                );
                break;
            case Crud::PAGE_EDIT:
                $this->champs = $this->buildFormPanel(
                    $this->pageName,
                    $this->objetInstance,
                    $this->crud,
                    $this->adminUrlGenerator
                );
                break;

            default:
                dd("Opération non prise en compte.");
                break;
        }
        return $this->champs;
    }
}
