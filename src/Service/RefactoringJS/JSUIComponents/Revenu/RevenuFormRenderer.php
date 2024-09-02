<?php

namespace App\Service\RefactoringJS\JSUIComponents\Revenu;

use App\Entity\Revenu;
use App\Service\ServiceTaxes;
use App\Service\ServiceMonnaie;
use Doctrine\ORM\EntityManager;
use App\Controller\Admin\RevenuCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use App\Controller\Admin\PreferenceCrudController;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSChamp;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSPanelRenderer;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSCssHtmlDecoration;

class RevenuFormRenderer extends JSPanelRenderer
{
    public function __construct(
        private EntityManager $entityManager,
        private ServiceMonnaie $serviceMonnaie,
        private ServiceTaxes $serviceTaxes,
        private string $pageName,
        private $objetInstance,
        private $crud,
        private AdminUrlGenerator $adminUrlGenerator
    ) {
        parent::__construct(self::TYPE_FORMULAIRE, $pageName, $objetInstance, $crud, $adminUrlGenerator);
    }

    public function design()
    {
        $column = 12;
        if ($this->objetInstance instanceof Revenu) {
            $column = 10;
        }

        //Section - Principale
        $this->addChamp(
            (new JSChamp())
                ->createSection("Informations générales")
                ->setIcon('fa-solid fa-burger') //<i class="fa-solid fa-burger"></i>
                ->setHelp("Votre revenu.")
                ->setColumns($column)
                ->getChamp()
        );

        //Type
        $this->addChamp(
            (new JSChamp())
                ->createChoix("type", "Type de revenu")
                ->setColumns($column)
                ->setRequired(false)
                ->setDisabled(false)
                ->setChoices(RevenuCrudController::TAB_TYPE)
                ->getChamp()
        );
        //Partageable?
        $this->addChamp(
            (new JSChamp())
                ->createChoix("partageable", "Partageable?")
                ->setColumns($column)
                ->setRequired(false)
                ->setDisabled(false)
                ->setChoices(RevenuCrudController::TAB_PARTAGEABLE)
                ->renderAsBadges(
                    [
                        RevenuCrudController::TAB_PARTAGEABLE[RevenuCrudController::PARTAGEABLE_NON] => 'dark',
                        RevenuCrudController::TAB_PARTAGEABLE[RevenuCrudController::PARTAGEABLE_OUI] => 'success',
                    ]
                )
                ->getChamp()
        );
        //Taxable?
        $this->addChamp(
            (new JSChamp())
                ->createChoix("taxable", "Taxable?")
                ->setColumns($column)
                ->setRequired(false)
                ->setDisabled(false)
                ->setChoices(RevenuCrudController::TAB_TAXABLE)
                ->getChamp()
        );
        //Is part Tranche
        $this->addChamp(
            (new JSChamp())
                ->createBoolean("isparttranche", PreferenceCrudController::PREF_FIN_REVENU_PAR_TRANCHE)
                ->setColumns($column)
                ->setRequired(true)
                ->setDisabled(false)
                ->getChamp()
        );
        //Is part Tranche
        $this->addChamp(
            (new JSChamp())
                ->createBoolean("ispartclient", PreferenceCrudController::PREF_FIN_REVENU_PAR_CLIENT)
                ->setColumns($column)
                ->setRequired(true)
                ->setDisabled(false)
                ->getChamp()
        );
        //Base
        $this->addChamp(
            (new JSChamp())
                ->createChoix("base", "Formuale")
                ->setColumns($column)
                ->setRequired(false)
                ->setDisabled(false)
                ->setChoices(RevenuCrudController::TAB_BASE)
                ->getChamp()
        );
        //Taux
        $this->addChamp(
            (new JSChamp())
                ->createPourcentage("taux", "Taux")
                ->setRequired(false)
                ->setDisabled(false)
                ->setColumns($column)
                ->setFormatValue(
                    function ($value, Revenu $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $value))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );
        //Montant flat
        $this->addChamp(
            (new JSChamp())
                ->createArgent("montantFlat", "Montant fixe")
                ->setRequired(false)
                ->setDisabled(false)
                ->setColumns($column)
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setFormatValue(
                    function ($value, Revenu $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getMontantFlat() * 100)))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );
    }

    public function batchActions(?array $champs, ?string $type = null, ?string $pageName = null, $objetInstance = null, ?Crud $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array
    {
        return $champs;
    }
}
