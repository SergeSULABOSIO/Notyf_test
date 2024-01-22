<?php

namespace App\Service\RefactoringJS\JSUIComponents;

use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;


abstract class JSPanelRenderer implements JSPanel
{
    private ?int $type;
    private ?array $champsPanel = [];

    public function __construct()
    {
        
    }

    public abstract function design();

    public function render(?string $type)
    {
        //On construit le panel
        $this->type = $type;
        $this->init();
        $this->design();
        $this->appliquerType();
    }

    public function init()
    {
        $this->champsPanel = [];
    }

    private function appliquerType()
    {
        foreach ($this->champsPanel as $champ) {
            switch ($this->type) {
                case self::TYPE_LISTE:
                    $champ->onlyOnIndex();
                    break;
                case self::TYPE_DETAILS:
                    $champ->onlyOnDetail();
                    break;
                case self::TYPE_FORMULAIRE:
                    $champ->onlyOnForms();
                    break;
                default:
                    dd("Cette fonction n'est pas encore définie.");
                    break;
            }
        }
    }

    public function addSection(?string $titre, ?string $icone, ?int $colonne)
    {
        $this->champsPanel[] = FormField::addPanel($titre)
            ->setIcon($icone)
            ->setColumns($colonne);
    }

    public function addOnglet(?string $titre, ?string $icone, ?string $helpMessage)
    {
        $this->champsPanel[] = FormField::addTab(' ' . $titre)
            ->setIcon($icone)
            ->setHelp($helpMessage);
    }

    public function addChampAssociation(?string $permission = null, ?string $attribut, ?string $titre, ?bool $required, ?bool $desabled, ?int $columns, ?callable $formTypeOption)
    {
        $this->champsPanel[] = AssociationField::new($attribut, $titre)
            ->setPermission($permission)
            ->setColumns($columns)
            ->setRequired($required)
            ->setFormTypeOption('query_builder', $formTypeOption);
    }

    public function addChampArgent(?string $permission = null, ?string $attribut, ?string $titre, ?bool $required, ?bool $desabled, ?int $columns, ?string $currency)
    {
        $this->champsPanel[] = MoneyField::new($attribut, $titre)
            ->setPermission($permission)
            ->setCurrency($currency)
            ->setStoredAsCents()
            ->setRequired($required)
            ->setColumns($columns);
    }

    public function addChampChoix(?string $permission = null, ?string $attribut, ?string $titre, ?bool $required, ?bool $desabled, ?int $columns, ?array $choices)
    {
        $this->champsPanel[] = ChoiceField::new($attribut, $titre)
            ->setPermission($permission)
            ->setChoices($choices)
            ->setColumns($columns)
            ->setRequired($required)
            ->setDisabled($desabled);
    }

    public function addChampDate(?string $permission = null, ?string $attribut, ?string $titre, ?bool $required, ?bool $desabled, ?int $columns)
    {
        $this->champsPanel[] = DateTimeField::new($attribut, $titre)
            ->setPermission($permission)
            ->setColumns($columns)
            ->setDisabled($desabled)
            ->setRequired($required);
    }

    public function addChampTexte(?string $permission = null, ?string $attribut, ?string $titre, ?bool $required, ?bool $desabled, ?int $columns)
    {
        $this->champsPanel[] = TextField::new($attribut, $titre)
            ->setPermission($permission)
            ->setColumns($columns)
            ->setDisabled($desabled)
            ->setRequired($required);
    }

    public function addChampNombre(?string $permission = null, ?string $attribut, ?string $titre, ?bool $required, ?bool $desabled, ?int $columns, ?callable $formatValue)
    {
        $this->champsPanel[] = NumberField::new($attribut, $titre)
            ->setPermission($permission)
            ->setColumns($columns)
            ->setDisabled($desabled)
            ->setRequired($required)
            ->formatValue($formatValue);
    }

    public function addChampTableau(?string $permission = null, ?string $attribut, ?string $titre, ?bool $required = false, ?bool $desabled = false, ?int $columns = 10, ?string $templatePath)
    {
        $this->champsPanel[] = ArrayField::new($attribut, $titre)
            ->setPermission($permission)
            ->setTemplatePath($templatePath)
            ->setColumns($columns)
            ->setDisabled($desabled)
            ->setRequired($required);
    }

    public function addChampCollection(?string $permission = null, ?string $attribut, ?string $titre, ?bool $required = false, ?bool $desabled = false, ?int $columns = 10, ?string $helpMessage = "Une Collection d'objets.", ?string $crudControllerFqcn, ?bool $allowAdd = true, ?bool $allowDelete = true)
    {
        $this->champsPanel[] = CollectionField::new($attribut, $titre)
            ->setPermission($permission)
            ->setHelp($helpMessage)
            ->useEntryCrudForm($crudControllerFqcn)
            ->allowAdd($allowAdd)
            ->allowDelete($allowDelete)
            ->setEntryIsComplex()
            ->setRequired($required)
            ->setColumns($columns);
    }

    public function reset()
    {
        $this->champsPanel = [];
    }

    public function getChamps(): ?array
    {
        return $this->champsPanel;
    }
}
