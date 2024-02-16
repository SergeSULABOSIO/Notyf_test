<?php

namespace App\Service\RefactoringJS\JSUIComponents\JSUIParametres;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\PercentField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;


abstract class JSPanelRenderer implements JSPanel
{
    public ?string $twig_template_status_tranche = "admin/segment/index_tranche_status.html.twig";
    public ?string $css_class_bage_ordinaire = "badge badge-light text-bold";
    private ?array $champsPanel = [];
    private ?Collection $champsPanelToRemove = null;
    private ?Collection $champsPanelToDeactivate = null;
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
    public abstract function batchActions(?array $champs, ?string $type = null, ?string $pageName = null, $objetInstance = null, ?Crud $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array;

    public function addChamp($champ){
        $this->champsPanel[] = $champ;
    }

    public function addChampToRemove(?string $nomAttribut)
    {
        if (!$this->champsPanelToRemove->contains($nomAttribut)) {
            $this->champsPanelToRemove->add($nomAttribut);
        }
        return $this;
    }

    public function addChampToDeactivate(?string $nomAttribut, ?int $columns = null)
    {
        if (!$this->champsPanelToDeactivate->contains($nomAttribut, $columns)) {
            $this->champsPanelToDeactivate->set($nomAttribut, $columns);
        }
        // dd($this->champsPanelToDeactivate);
        return $this;
    }

    public function runBatchActions(?string $type = null, ?string $pageName = null, $objetInstance = null, ?Crud $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array
    {

        /**
         * Gestion des champs à afficher et
         * ceux à désactiver.
         */
        $champsATraiterEnMasse = $this->batchActions($this->champsPanel, $type, $pageName, $objetInstance, $crud, $adminUrlGenerator);
        $tabChampsFinaux = [];
        foreach ($champsATraiterEnMasse as $champ) {
            $propertName = null;
            if ($champ instanceof FormField) {
                $propertName = $champ->getAsDto()->getLabel();
            } else {
                $propertName = $champ->getAsDto()->getProperty();
            }
            //On cache d'autres champs
            if (!$this->champsPanelToRemove->contains($propertName)) {
                $tabChampsFinaux[] = $champ;
            }
            //On applique la désactivation des champs
            if ($this->champsPanelToDeactivate->contains($propertName)) {
                $tabChampsFinaux[] = $champ->setDisabled(true);
            }
            if (isset($this->champsPanelToDeactivate[$propertName])) {
                foreach ($this->champsPanelToDeactivate as $attribut => $columns) {
                    if ($attribut == $propertName) {
                        $champ->setDisabled(true);
                        if ($columns != null) {
                            $champ->setColumns($columns);
                        }
                    }
                    $tabChampsFinaux[] = $champ;
                }
            }
        }
        // dd($tabChampsFinaux);
        return $tabChampsFinaux;
    }

    public function render()
    {
        //On construit le panel
        $this->init();
        $this->design();
        $this->appliquerType();
        $this->champsPanel = $this->runBatchActions($this->type, $this->pageName, $this->objetInstance, $this->crud, $this->adminUrlGenerator);
        // dd("Tempo...", $this->champsPanel);
    }

    public function init()
    {
        $this->champsPanel = [];
        $this->champsPanelToDeactivate = new ArrayCollection();
        $this->champsPanelToRemove = new ArrayCollection();
    }

    private function appliquerType()
    {
        foreach ($this->champsPanel as $champ) {
            // dd("Je suis ici...", $this->type);
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

    public function addSection(?string $titre, ?string $icone, ?string $helpMessage, ?int $colonne)
    {
        $champTempo = FormField::addPanel($titre);
        if ($icone != null) {
            $champTempo->setIcon($icone);
        }
        if ($helpMessage != null) {
            $champTempo->setHelp($helpMessage);
        }
        if ($colonne != null) {
            $champTempo->setColumns($colonne);
        }
        $this->champsPanel[] = $champTempo;
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

    public function addChampBooleen(?string $permission = null, ?string $attribut, ?string $titre, ?bool $required = false, ?bool $desabled = false, ?bool $renderAsSwitch = false)
    {
        $champTempo = BooleanField::new($attribut, $titre);
        if ($permission != null) {
            $champTempo->setPermission($permission);
        }
        if ($renderAsSwitch != null) {
            $champTempo->renderAsSwitch($renderAsSwitch);
        }
        if ($desabled != null) {
            $champTempo->setDisabled($desabled);
        }
        if ($required != null) {
            $champTempo->setRequired($required);
        }
        $this->champsPanel[] = $champTempo;
    }

    public function addChampEditeurTexte(?string $permission = null, ?string $attribut, ?string $titre, ?bool $required = false, ?bool $desabled = false, ?int $columns = 10)
    {
        $champTempo = TextEditorField::new($attribut, $titre);
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

    public function addChampAssociation(?string $permission = null, ?string $attribut, ?string $titre, ?bool $required, ?bool $desabled, ?int $columns, ?callable $formTypeOption, ?callable $formatValue = null)
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
        if ($formatValue != null) {
            $champTempo->formatValue($formatValue);
        }
        $this->champsPanel[] = $champTempo;
    }

    public function addChampArgent(?string $permission = null, ?string $attribut, ?string $titre, ?bool $required, ?bool $desabled, ?int $columns, ?string $currency, ?callable $formatValue = null, $decimals = 2)
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
        if ($formatValue != null) {
            $champTempo->formatValue($formatValue);
        }
        if ($decimals != 2) {
            $champTempo->setNumDecimals($decimals);
        }
        $this->champsPanel[] = $champTempo;
    }

    public function addChampChoix(?string $permission = null, ?string $attribut, ?string $titre, ?bool $required, ?bool $desabled, ?int $columns, ?array $choices, ?array $badges)
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
        if ($badges != null) {
            $champTempo->renderAsBadges($badges);
        }
        $this->champsPanel[] = $champTempo;
    }

    public function addChampDate(?string $permission = null, ?string $attribut, ?string $titre, ?bool $required, ?bool $desabled, ?int $columns, ?callable $formatValue = null)
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
        if ($formatValue != null) {
            $champTempo->formatValue($formatValue);
        }
        $this->champsPanel[] = $champTempo;
    }

    public function addChampTexte(?string $permission = null, ?string $attribut, ?string $titre, ?bool $required, ?bool $desabled, ?int $columns, ?callable $formatValue = null)
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
        if ($formatValue != null) {
            $champTempo->formatValue($formatValue);
        }
        $champTempo->renderAsHtml(true);
        $this->champsPanel[] = $champTempo;
    }

    public function addChampNombre(?string $permission = null, ?string $attribut, ?string $titre, ?bool $required, ?bool $desabled, ?int $columns, ?callable $formatValue = null)
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

    public function addChampPourcentage(?string $permission = null, ?string $attribut, ?string $titre, ?bool $required, ?bool $desabled, ?int $columns, ?callable $formatValue = null)
    {
        $champTempo = PercentField::new($attribut, $titre);
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
        $champTempo->setNumDecimals(2);
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

    public function addChampCollection(?string $permission = null, ?string $attribut, ?string $titre, ?bool $required = false, ?bool $desabled = false, ?int $columns = 10, ?string $helpMessage = "Une Collection d'objets.", ?string $crudControllerFqcn, ?bool $allowAdd = true, ?bool $allowDelete = true, ?string $templatePath = null)
    {
        $champTempo = CollectionField::new($attribut, $titre)
            ->setEntryIsComplex();
        if ($permission != null) {
            $champTempo->setPermission($permission);
        }
        if ($helpMessage != null) {
            $champTempo->setHelp($helpMessage);
        }
        if ($crudControllerFqcn != null) {
            $champTempo->useEntryCrudForm($crudControllerFqcn);
        }
        if ($templatePath != null) {
            $champTempo->setTemplatePath($templatePath);
        }
        $champTempo->setColumns($columns);
        $champTempo->setDisabled($desabled);
        $champTempo->setRequired($required);
        $champTempo->allowAdd($allowAdd);
        $champTempo->allowDelete($allowDelete);
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

    /**
     * Get the value of champsPanelToRemove
     */
    public function getChampsPanelToRemove()
    {
        return $this->champsPanelToRemove;
    }

    /**
     * Get the value of champsPanelToDeactivate
     */
    public function getChampsPanelToDeactivate()
    {
        return $this->champsPanelToDeactivate;
    }
}
