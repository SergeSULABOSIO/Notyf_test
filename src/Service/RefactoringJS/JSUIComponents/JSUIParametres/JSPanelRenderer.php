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

    /**
     * Get the value of objetInstance
     */ 
    public function getObjetInstance()
    {
        return $this->objetInstance;
    }
}
