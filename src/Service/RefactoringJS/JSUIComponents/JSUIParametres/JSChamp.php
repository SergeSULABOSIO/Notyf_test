<?php

namespace App\Service\RefactoringJS\JSUIComponents\JSUIParametres;

use Vich\UploaderBundle\Form\Type\VichFileType;
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


class JSChamp
{
    private $champ = null;
    private ?string $tittre;
    private ?string $attribut;

    public function __construct()
    {
    }

    public function reset()
    {
        $this->champ = null;
    }

    public function setIcon(?string $icon)
    {
        /** @var FormField */
        $this->champ->setIcon($icon);
        return $this;
    }

    public function setHelp(?string $messageHelp)
    {
        /** @var FormField */
        $this->champ->setHelp($messageHelp);
        return $this;
    }

    public function setColumns($columns)
    {
        /** @var FormField */
        $this->champ->setColumns($columns);
        return $this;
    }

    public function setPermission(?string $permission)
    {
        /** @var FormField */
        $this->champ->setPermission($permission);
        return $this;
    }

    public function setDisabled(?bool $disabled)
    {
        /** @var FormField */
        $this->champ->setDisabled($disabled);
        return $this;
    }

    public function setRequired(?bool $required)
    {
        /** @var FormField */
        $this->champ->setRequired($required);
        return $this;
    }

    public function renderAsSwitch(?bool $renderAsSwitch)
    {
        /** @var FormField */
        $this->champ->renderAsSwitch($renderAsSwitch);
        return $this;
    }

    public function setFormTypeOption(?callable $formTypeOption)
    {
        /** @var FormField */
        $this->champ->setFormTypeOption('query_builder', $formTypeOption);
        return $this;
    }

    public function setFormType(string $formTypeFqcn)
    {
        /** @var FormField */
        $this->champ->setFormType($formTypeFqcn);
        return $this;
    }

    public function setFormTypeOptions(string $optionName, $optionValue)
    {
        /** @var FormField */
        $this->champ->setFormTypeOption($optionName, $optionValue);
        return $this;
    }

    public function setFormatValue(?callable $formatValue)
    {
        /** @var FormField */
        $this->champ->formatValue($formatValue);
        return $this;
    }

    public function setCurrency(?string $currency)
    {
        /** @var FormField */
        $this->champ->setCurrency($currency);
        return $this;
    }

    public function setDecimals($decimals)
    {
        /** @var FormField */
        $this->champ->setNumDecimals($decimals);
        return $this;
    }

    public function setChoices(?array $choices)
    {
        /** @var FormField */
        $this->champ->setChoices($choices);
        return $this;
    }

    public function renderAsBadges(?array $badges)
    {
        /** @var FormField */
        $this->champ->renderAsBadges($badges);
        return $this;
    }

    public function setTemplatePath(?string $templatePath)
    {
        /** @var FormField */
        $this->champ->setTemplatePath($templatePath);
        return $this;
    }

    public function allowAdd(?bool $allowAdd)
    {
        /** @var FormField */
        $this->champ->allowAdd($allowAdd);
        return $this;
    }

    public function allowDelete(?bool $allowDelete)
    {
        /** @var FormField */
        $this->champ->allowDelete($allowDelete);
        return $this;
    }

    public function useEntryCrudForm(?string $crudControllerFqcn)
    {
        /** @var FormField */
        $this->champ->useEntryCrudForm($crudControllerFqcn);
        return $this;
    }

    public function createSection(?string $titre)
    {
        $this->setTittre($titre);
        $this->champ = FormField::addPanel($titre);
        return $this;
    }

    public function createOnglet(?string $titre)
    {
        $this->setTittre($titre);
        $this->champ = FormField::addTab(' ' . $titre);
        return $this;
    }

    public function createZonneTexte(?string $attribut, ?string $titre)
    {
        $this->setAttribut($attribut);
        $this->setTittre($titre);
        $this->champ = TextareaField::new($attribut, $titre)
            ->renderAsHtml();
        return $this;
    }

    public function createBoolean(?string $attribut, ?string $titre)
    {
        $this->setAttribut($attribut);
        $this->setTittre($titre);
        $this->champ = BooleanField::new($attribut, $titre);
        return $this;
    }

    public function createEditeurTexte(?string $attribut, ?string $titre)
    {
        $this->setAttribut($attribut);
        $this->setTittre($titre);
        $this->champ = TextEditorField::new($attribut, $titre);
        return $this;
    }

    public function createAssociation(?string $attribut, ?string $titre)
    {
        $this->setAttribut($attribut);
        $this->setTittre($titre);
        $this->champ = AssociationField::new($attribut, $titre);
        return $this;
    }

    public function createArgent(?string $attribut, ?string $titre, $decimals = 2)
    {
        $this->setAttribut($attribut);
        $this->setTittre($titre);
        $this->champ = MoneyField::new($attribut, $titre)
            ->setNumDecimals($decimals)
            ->setStoredAsCents();
        return $this;
    }

    public function createChoix(?string $attribut, ?string $titre)
    {
        $this->setAttribut($attribut);
        $this->setTittre($titre);
        $this->champ = ChoiceField::new($attribut, $titre);
        return $this;
    }

    public function createDate(?string $attribut, ?string $titre)
    {
        $this->setAttribut($attribut);
        $this->setTittre($titre);
        $this->champ = DateTimeField::new($attribut, $titre);
        return $this;
    }

    public function createTexte(?string $attribut, ?string $titre)
    {
        $this->setAttribut($attribut);
        $this->setTittre($titre);
        $this->champ = TextField::new($attribut, $titre)
            ->renderAsHtml();
        return $this;
    }

    public function createNombre(?string $attribut, ?string $titre, $decimals = 2)
    {
        $this->setAttribut($attribut);
        $this->setTittre($titre);
        $this->champ = NumberField::new($attribut, $titre)
            ->setNumDecimals($decimals);
        return $this;
    }

    public function createPourcentage(?string $attribut, ?string $titre, $decimals = 2)
    {
        $this->setAttribut($attribut);
        $this->setTittre($titre);
        $this->champ = PercentField::new($attribut, $titre)
            ->setNumDecimals($decimals);
        return $this;
    }

    public function createTableau(?string $attribut, ?string $titre)
    {
        $this->setAttribut($attribut);
        $this->setTittre($titre);
        $this->champ = ArrayField::new($attribut, $titre);
        return $this;
    }

    public function createCollection(?string $attribut, ?string $titre)
    {
        $this->setAttribut($attribut);
        $this->setTittre($titre);
        $this->champ = CollectionField::new($attribut, $titre)
            ->setEntryIsComplex();
        return $this;
    }

    /**
     * Get the value of tittre
     */
    public function getTittre()
    {
        return $this->tittre;
    }

    /**
     * Set the value of tittre
     *
     * @return  self
     */
    public function setTittre($tittre)
    {
        $this->tittre = $tittre;

        return $this;
    }

    /**
     * Get the value of attribut
     */
    public function getAttribut()
    {
        return $this->attribut;
    }

    /**
     * Set the value of attribut
     *
     * @return  self
     */
    public function setAttribut($attribut)
    {
        $this->attribut = $attribut;

        return $this;
    }

    /**
     * Get the value of champ
     */
    public function getChamp()
    {
        return $this->champ;
    }

    /**
     * Set the value of champ
     *
     * @return  self
     */
    public function setChamp($champ)
    {
        $this->champ = $champ;

        return $this;
    }
}
