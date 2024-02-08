<?php

namespace App\Service\RefactoringJS\JSUIComponents\JSUIParametres;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;


interface JSPanel
{
    public const TYPE_LISTE = 0;
    public const TYPE_DETAILS = 1;
    public const TYPE_FORMULAIRE = 2;

    //Fonctions de creation du panel
    public function addSection(?string $titre, ?string $icone, ?int $colonne);
    public function addOnglet(?string $titre, ?string $icone, ?string $helpMessage);
    public function addChampAssociation(?string $permission = null, ?string $attribut, ?string $titre, ?bool $required, ?bool $desabled, ?int $columns, ?callable $formTypeOption);
    public function addChampChoix(?string $permission = null, ?string $attribut, ?string $titre, ?bool $required, ?bool $desabled, ?int $columns, ?array $choices, ?array $badget);
    public function addChampBooleen(?string $permission = null, ?string $attribut, ?string $titre, ?bool $required = false, ?bool $desabled = false, ?bool $renderAsSwitch = false);
    public function addChampArgent(?string $permission = null, ?string $attribut, ?string $titre, ?bool $required, ?bool $desabled, ?int $columns, ?string $currency, ?callable $formatValue = null);
    public function addChampDate(?string $permission = null, ?string $attribut, ?string $titre, ?bool $required, ?bool $desabled, ?int $columns, ?callable $formatValue = null);
    public function addChampTexte(?string $permission = null, ?string $attribut, ?string $titre, ?bool $required, ?bool $desabled, ?int $columns, ?callable $formatValue = null);
    public function addChampNombre(?string $permission = null, ?string $attribut, ?string $titre, ?bool $required, ?bool $desabled, ?int $columns, ?callable $formatValue = null);
    public function addChampPourcentage(?string $permission = null, ?string $attribut, ?string $titre, ?bool $required, ?bool $desabled, ?int $columns, ?callable $formatValue = null);
    public function addChampCollection(?string $permission = null, ?string $attribut, ?string $titre, ?bool $required = false, ?bool $desabled = false, ?int $columns = 10, ?string $helpMessage = "Une Collection d'objets.", ?string $crudControllerFqcn, ?bool $allowAdd = true, ?bool $allowDelete = true, ?string $templatePath = null);
    public function addChampTableau(?string $permission = null, ?string $attribut, ?string $titre, ?bool $required = false, ?bool $desabled = false, ?int $columns = 10);
    public function addChampZoneTexte(?string $permission = null, ?string $attribut, ?string $titre, ?bool $required = false, ?bool $desabled = false, ?int $columns = 10);
    public function addChampEditeurTexte(?string $permission = null, ?string $attribut, ?string $titre, ?bool $required = false, ?bool $desabled = false, ?int $columns = 10);
    public function init();
    public function render();
    public function getChamps():?array;
    public function runBatchActions(?string $type = null, ?string $pageName = null, $objetInstance = null, ?Crud $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null):?array;
}
