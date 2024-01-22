<?php

namespace App\Service\RefactoringJS\JSUIComponents;


interface JSPanel
{
    public const TYPE_LISTE = 0;
    public const TYPE_DETAILS = 1;
    public const TYPE_FORMULAIRE = 2;

    //Fonctions de creation du panel
    public function setType(?int $type);
    public function addSection(?string $titre, ?string $icone, ?int $colonne);
    public function addOnglet(?string $titre, ?string $icone, ?string $helpMessage);
    public function addChampAssociation(?string $attribut, ?string $titre, ?bool $required, ?bool $desabled, ?int $columns);
    public function addChampChoix(?string $attribut, ?string $titre, ?bool $required, ?bool $desabled, ?int $columns, ?array $choices);
    public function addChampArgent(?string $attribut, ?string $titre, ?bool $required, ?bool $desabled, ?int $columns, ?string $currency);
    public function addChampDate(?string $attribut, ?string $titre, ?bool $required, ?bool $desabled, ?int $columns);
    
    public function getChamps():?array;
    public function reset();
    public function init();
}
