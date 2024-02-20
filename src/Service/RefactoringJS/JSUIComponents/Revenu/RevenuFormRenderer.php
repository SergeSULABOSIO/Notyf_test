<?php

namespace App\Service\RefactoringJS\JSUIComponents\Revenu;

use App\Entity\Revenu;
use App\Entity\Tranche;
use App\Service\ServiceTaxes;
use App\Service\ServiceMonnaie;
use Doctrine\ORM\EntityManager;
use App\Controller\Admin\RevenuCrudController;
use App\Controller\Admin\PaiementCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use App\Controller\Admin\PreferenceCrudController;
use App\Controller\Admin\UtilisateurCrudController;
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
        string $pageName,
        $objetInstance,
        $crud,
        AdminUrlGenerator $adminUrlGenerator
    ) {
        parent::__construct(self::TYPE_FORMULAIRE, $pageName, $objetInstance, $crud, $adminUrlGenerator);
    }

    public function design()
    {
        //Type
        $this->addChamp(
            (new JSChamp())
                ->createChoix("type", "Type de revenu")
                ->setColumns(12)
                ->setRequired(false)
                ->setDisabled(false)
                ->setChoices(RevenuCrudController::TAB_TYPE)
                ->getChamp()
        );
        // $this->addChampChoix(
        //     null,
        //     "type",
        //     "Type de revenu",
        //     false,
        //     false,
        //     12,
        //     RevenuCrudController::TAB_TYPE,
        //     null
        // );
        //Partageable?
        $this->addChamp(
            (new JSChamp())
                ->createChoix("partageable", "Partageable?")
                ->setColumns(12)
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
        // $this->addChampChoix(
        //     null,
        //     "partageable",
        //     "Partageable?",
        //     false,
        //     false,
        //     12,
        //     RevenuCrudController::TAB_PARTAGEABLE,
        //     [
        //         RevenuCrudController::TAB_PARTAGEABLE[RevenuCrudController::PARTAGEABLE_NON] => 'dark',
        //         RevenuCrudController::TAB_PARTAGEABLE[RevenuCrudController::PARTAGEABLE_OUI] => 'success',
        //     ]
        // );
        //Taxable?
        $this->addChamp(
            (new JSChamp())
                ->createChoix("taxable", "Taxable?")
                ->setColumns(12)
                ->setRequired(false)
                ->setDisabled(false)
                ->setChoices(RevenuCrudController::TAB_TAXABLE)
                ->getChamp()
        );
        // $this->addChampChoix(
        //     null,
        //     "taxable",
        //     "Taxable?",
        //     false,
        //     false,
        //     12,
        //     RevenuCrudController::TAB_TAXABLE,
        //     null
        // );
        //Is part Tranche
        $this->addChamp(
            (new JSChamp())
                ->createBoolean("isparttranche", PreferenceCrudController::PREF_FIN_REVENU_PAR_TRANCHE)
                ->setRequired(true)
                ->setDisabled(false)
                ->getChamp()
        );
        // $this->addChampBooleen(
        //     null,
        //     "isparttranche",
        //     PreferenceCrudController::PREF_FIN_REVENU_PAR_TRANCHE,
        //     true,
        //     false,
        //     true
        // );
        //Is part Tranche
        $this->addChamp(
            (new JSChamp())
                ->createBoolean("ispartclient", PreferenceCrudController::PREF_FIN_REVENU_PAR_CLIENT)
                ->setRequired(true)
                ->setDisabled(false)
                ->getChamp()
        );
        // $this->addChampBooleen(
        //     null,
        //     "ispartclient",
        //     PreferenceCrudController::PREF_FIN_REVENU_PAR_CLIENT,
        //     true,
        //     false,
        //     true
        // );
        //Base
        $this->addChamp(
            (new JSChamp())
                ->createChoix("base", "Formuale")
                ->setColumns(12)
                ->setRequired(false)
                ->setDisabled(false)
                ->setChoices(RevenuCrudController::TAB_BASE)
                ->getChamp()
        );
        // $this->addChampChoix(
        //     null,
        //     "base",
        //     "Formuale",
        //     false,
        //     false,
        //     12,
        //     RevenuCrudController::TAB_BASE,
        //     null
        // );
        //Taux
        $this->addChamp(
            (new JSChamp())
                ->createPourcentage("taux", "Taux")
                ->setRequired(false)
                ->setDisabled(false)
                ->setColumns(12)
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
        // $this->addChampPourcentage(
        //     null,
        //     "taux",
        //     "Taux",
        //     false,
        //     false,
        //     12,
        //     function ($value, Revenu $objet) {
        //         /** @var JSCssHtmlDecoration */
        //         $formatedHtml = (new JSCssHtmlDecoration("span", $value))
        //             ->ajouterClasseCss($this->css_class_bage_ordinaire)
        //             ->outputHtml();
        //         return $formatedHtml;
        //     }
        // );
        //Montant flat
        $this->addChamp(
            (new JSChamp())
                ->createArgent("montantFlat", "Montant fixe")
                ->setRequired(false)
                ->setDisabled(false)
                ->setColumns(12)
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
        // $this->addChampArgent(
        //     null,
        //     "montantFlat",
        //     "Montant fixe",
        //     false,
        //     false,
        //     12,
        //     $this->serviceMonnaie->getCodeAffichage(),
        //     function ($value, Revenu $objet) {
        //         /** @var JSCssHtmlDecoration */
        //         $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getMontantFlat() * 100)))
        //             ->ajouterClasseCss($this->css_class_bage_ordinaire)
        //             ->outputHtml();
        //         return $formatedHtml;
        //     }
        // );
    }

    public function batchActions(?array $champs, ?string $type = null, ?string $pageName = null, $objetInstance = null, ?Crud $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array
    {
        return $champs;
    }
}
