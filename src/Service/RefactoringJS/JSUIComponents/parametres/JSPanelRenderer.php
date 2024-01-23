<?php

namespace App\Service\RefactoringJS\JSUIComponents\Parametres;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;


abstract class JSPanelRenderer implements JSPanel
{
    private ?array $champsPanel = [];
    private ?int $type;
    private ?string $pageName;
    private $objetInstance;
    private ?Crud $crud;
    private ?AdminUrlGenerator $adminUrlGenerator;

    public function __construct(?string $type, ?string $pageName, $objetInstance, ?Crud $crud, ?AdminUrlGenerator $adminUrlGenerator)
    {
        $this->type = $type;
        $this->pageName = $pageName;
        $this->objetInstance = $objetInstance;
        $this->crud = $crud;
        $this->adminUrlGenerator = $adminUrlGenerator;
    }

    public abstract function design();

    public function render()
    {
        //On construit le panel
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
                    // dd("Je suis ici...");
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
        $champTempo = FormField::addTab(' ' . $titre);
        if ($helpMessage != null) {
            $champTempo->setHelp($helpMessage);
        }
        if ($icone != null) {
            $champTempo->setIcon($icone);
        }
        $this->champsPanel[] = $champTempo;
    }

    public function addChampZoneTexte(?string $permission = null, ?string $attribut, ?string $titre, ?bool $required = false, ?bool $desabled = false, ?int $columns = 10)
    {
        $champTempo = TextareaField::new($attribut, $titre)
            ->renderAsHtml();
        if ($permission != null) {
            $champTempo->setPermission($permission);
        }
        if ($columns != null) {
            $champTempo->setColumns($columns);
        }
        if ($desabled != null) {
            $champTempo->setDisabled($desabled);
        }
        if ($required != null) {
            $champTempo->setRequired($required);
        }
        $this->champsPanel[] = $champTempo;
    }

    public function addChampAssociation(?string $permission = null, ?string $attribut = null, ?string $titre = null, ?bool $required = null, ?bool $desabled = null, ?int $columns = null, ?callable $formTypeOption = null)
    {
        $champTempo = AssociationField::new($attribut, $titre);
        if ($formTypeOption != null) {
            $champTempo->setFormTypeOption('query_builder', $formTypeOption);
        }
        if ($permission != null) {
            $champTempo->setPermission($permission);
        }
        if ($columns != null) {
            $champTempo->setColumns($columns);
        }
        if ($desabled != null) {
            $champTempo->setDisabled($desabled);
        }
        if ($required != null) {
            $champTempo->setRequired($required);
        }
        $this->champsPanel[] = $champTempo;
    }

    public function addChampArgent(?string $permission = null, ?string $attribut, ?string $titre, ?bool $required, ?bool $desabled, ?int $columns, ?string $currency)
    {
        $champTempo = MoneyField::new($attribut, $titre)
            ->setStoredAsCents();
        if ($currency != null) {
            $champTempo->setCurrency($currency);
        }
        if ($permission != null) {
            $champTempo->setPermission($permission);
        }
        if ($columns != null) {
            $champTempo->setColumns($columns);
        }
        if ($desabled != null) {
            $champTempo->setDisabled($desabled);
        }
        if ($required != null) {
            $champTempo->setRequired($required);
        }
        $this->champsPanel[] = $champTempo;
    }

    public function addChampChoix(?string $permission = null, ?string $attribut, ?string $titre, ?bool $required, ?bool $desabled, ?int $columns, ?array $choices, ?array $badget)
    {
        $champTempo = ChoiceField::new($attribut, $titre);
        if ($choices != null) {
            $champTempo->setChoices($choices);
        }
        if ($permission != null) {
            $champTempo->setPermission($permission);
        }
        if ($columns != null) {
            $champTempo->setColumns($columns);
        }
        if ($desabled != null) {
            $champTempo->setDisabled($desabled);
        }
        if ($required != null) {
            $champTempo->setRequired($required);
        }
        if ($badget != null) {
            $champTempo->renderAsBadges($badget);
        }
        $this->champsPanel[] = $champTempo;
    }

    public function addChampDate(?string $permission = null, ?string $attribut, ?string $titre, ?bool $required, ?bool $desabled, ?int $columns)
    {
        $champTempo = DateTimeField::new($attribut, $titre);
        if ($permission != null) {
            $champTempo->setPermission($permission);
        }
        if ($columns != null) {
            $champTempo->setColumns($columns);
        }
        if ($desabled != null) {
            $champTempo->setDisabled($desabled);
        }
        if ($required != null) {
            $champTempo->setRequired($required);
        }
        $this->champsPanel[] = $champTempo;
    }

    public function addChampTexte(?string $permission = null, ?string $attribut, ?string $titre, ?bool $required, ?bool $desabled, ?int $columns)
    {
        $champTempo = TextField::new($attribut, $titre);
        if ($permission != null) {
            $champTempo->setPermission($permission);
        }
        if ($columns != null) {
            $champTempo->setColumns($columns);
        }
        if ($desabled != null) {
            $champTempo->setDisabled($desabled);
        }
        if ($required != null) {
            $champTempo->setRequired($required);
        }
        $this->champsPanel[] = $champTempo;
    }

    public function addChampNombre(?string $permission = null, ?string $attribut, ?string $titre, ?bool $required, ?bool $desabled, ?int $columns, ?callable $formatValue)
    {
        $champTempo = NumberField::new($attribut, $titre);
        if ($permission != null) {
            $champTempo->setPermission($permission);
        }
        if ($columns != null) {
            $champTempo->setColumns($columns);
        }
        if ($desabled != null) {
            $champTempo->setDisabled($desabled);
        }
        if ($required != null) {
            $champTempo->setRequired($required);
        }
        if ($formatValue != null) {
            $champTempo->formatValue($formatValue);
        }
        $this->champsPanel[] = $champTempo;
    }

    public function addChampTableau(?string $permission = null, ?string $attribut, ?string $titre, ?bool $required = false, ?bool $desabled = false, ?int $columns = null, ?string $templatePath = null)
    {
        $champTempo = ArrayField::new($attribut, $titre);
        if ($permission != null) {
            $champTempo->setPermission($permission);
        }
        if ($columns != null) {
            $champTempo->setColumns($columns);
        }
        if ($desabled != null) {
            $champTempo->setDisabled($desabled);
        }
        if ($required != null) {
            $champTempo->setRequired($required);
        }
        if ($templatePath != null) {
            $champTempo->setTemplatePath($templatePath);
        }
        $this->champsPanel[] = $champTempo;
    }

    public function addChampCollection(?string $permission = null, ?string $attribut, ?string $titre, ?bool $required = false, ?bool $desabled = false, ?int $columns = 10, ?string $helpMessage = "Une Collection d'objets.", ?string $crudControllerFqcn, ?bool $allowAdd = true, ?bool $allowDelete = true)
    {
        $champTempo = CollectionField::new($attribut, $titre)
            ->setEntryIsComplex();
        if ($permission != null) {
            $champTempo->setPermission($permission);
        }
        if ($columns != null) {
            $champTempo->setColumns($columns);
        }
        if ($desabled != null) {
            $champTempo->setDisabled($desabled);
        }
        if ($required != null) {
            $champTempo->setRequired($required);
        }
        if ($helpMessage != null) {
            $champTempo->setHelp($helpMessage);
        }
        if ($crudControllerFqcn != null) {
            $champTempo->useEntryCrudForm($crudControllerFqcn);
        }
        if ($allowAdd != null) {
            $champTempo->allowAdd($allowAdd);
        }
        if ($allowDelete != null) {
            $champTempo->allowDelete($allowDelete);
        }
        $this->champsPanel[] = $champTempo;
    }

    public function reset()
    {
        $this->champsPanel = [];
    }

    public function getChamps(): ?array
    {
        $this->render();
        return $this->champsPanel;
    }
}
