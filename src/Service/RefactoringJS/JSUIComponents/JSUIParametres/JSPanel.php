<?php

namespace App\Service\RefactoringJS\JSUIComponents\JSUIParametres;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;


interface JSPanel
{
    public const TYPE_LISTE = 0;
    public const TYPE_DETAILS = 1;
    public const TYPE_FORMULAIRE = 2;

    public function init();
    public function render();
    public function addChampToRemove(?string $nomAttribut);
    public function addChampToDeactivate(?string $nomAttribut, ?int $columns = null);
    public function getChamps():?array;
    public function runBatchActions(?string $type = null, ?string $pageName = null, $objetInstance = null, ?Crud $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null):?array;
}
