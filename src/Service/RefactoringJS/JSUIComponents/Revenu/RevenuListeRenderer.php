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
use App\Controller\Admin\UtilisateurCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSPanelRenderer;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSCssHtmlDecoration;

class RevenuListeRenderer extends JSPanelRenderer
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
        parent::__construct(self::TYPE_LISTE, $pageName, $objetInstance, $crud, $adminUrlGenerator);
    }

    public function design()
    {
        //Validee?
        $this->addChampBooleen(
            null,
            "validated",
            "Validée?",
            false,
            true,
            false
        );
        //Type
        $this->addChampChoix(
            null,
            "type",
            "Type de revenu",
            false,
            false,
            10,
            RevenuCrudController::TAB_TYPE,
            null
        );
        //Partageable?
        $this->addChampChoix(
            null,
            "partageable",
            "Partageable?",
            false,
            false,
            10,
            RevenuCrudController::TAB_PARTAGEABLE,
            [
                RevenuCrudController::TAB_PARTAGEABLE[RevenuCrudController::PARTAGEABLE_NON] => 'dark',
                RevenuCrudController::TAB_PARTAGEABLE[RevenuCrudController::PARTAGEABLE_OUI] => 'success',
            ]
        );
        //Taxable?
        $this->addChampChoix(
            null,
            "taxable",
            "Taxable?",
            false,
            false,
            10,
            RevenuCrudController::TAB_TAXABLE,
            [
                RevenuCrudController::TAB_TAXABLE[RevenuCrudController::TAXABLE_OUI] => 'danger',
                RevenuCrudController::TAB_TAXABLE[RevenuCrudController::TAXABLE_OUI] => 'success',
            ]
        );
        //Base
        $this->addChampChoix(
            null,
            "base",
            "Formule de base",
            false,
            false,
            10,
            RevenuCrudController::TAB_BASE,
            null
        );
        //Revenu Pure
        $this->addChampArgent(
            null,
            "revenuPure",
            "Revenu pure",
            false,
            false,
            10,
            $this->serviceMonnaie->getCodeAffichage(),
            function ($value, Revenu $objet) {
                /** @var JSCssHtmlDecoration */
                $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getRevenuPure() * 100)))
                    ->ajouterClasseCss($this->css_class_bage_ordinaire)
                    ->outputHtml();
                return $formatedHtml;
            }
        );
        //Taxe courtier
        $this->addChampArgent(
            null,
            "taxeCourtier",
            ucfirst($this->serviceTaxes->getNomTaxeCourtier()),
            false,
            false,
            10,
            $this->serviceMonnaie->getCodeAffichage(),
            function ($value, Revenu $objet) {
                /** @var JSCssHtmlDecoration */
                $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getTaxeCourtier() * 100)))
                    ->ajouterClasseCss($this->css_class_bage_ordinaire)
                    ->outputHtml();
                return $formatedHtml;
            }
        );
        //Revenu Net
        $this->addChampArgent(
            null,
            "revenuNet",
            "Revenu net",
            false,
            false,
            10,
            $this->serviceMonnaie->getCodeAffichage(),
            function ($value, Revenu $objet) {
                /** @var JSCssHtmlDecoration */
                $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getRevenuNet() * 100)))
                    ->ajouterClasseCss($this->css_class_bage_ordinaire)
                    ->outputHtml();
                return $formatedHtml;
            }
        );
        //Taxe assureur
        $this->addChampArgent(
            null,
            "taxeAssureur",
            ucfirst($this->serviceTaxes->getNomTaxeAssureur()),
            false,
            false,
            10,
            $this->serviceMonnaie->getCodeAffichage(),
            function ($value, Revenu $objet) {
                /** @var JSCssHtmlDecoration */
                $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getTaxeAssureur() * 100)))
                    ->ajouterClasseCss($this->css_class_bage_ordinaire)
                    ->outputHtml();
                return $formatedHtml;
            }
        );
        //Revenu totale
        $this->addChampArgent(
            null,
            "revenuTotale",
            "Revenu TTC",
            false,
            false,
            10,
            $this->serviceMonnaie->getCodeAffichage(),
            function ($value, Revenu $objet) {
                /** @var JSCssHtmlDecoration */
                $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getRevenuTotale() * 100)))
                    ->ajouterClasseCss($this->css_class_bage_ordinaire)
                    ->outputHtml();
                return $formatedHtml;
            }
        );
        //Rétrocommission
        $this->addChampArgent(
            null,
            "retrocommissionTotale",
            "Rétrocom",
            false,
            false,
            10,
            $this->serviceMonnaie->getCodeAffichage(),
            function ($value, Revenu $objet) {
                /** @var JSCssHtmlDecoration */
                $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getRetrocommissionTotale() * 100)))
                    ->ajouterClasseCss($this->css_class_bage_ordinaire)
                    ->outputHtml();
                return $formatedHtml;
            }
        );
        //Réserve
        $this->addChampArgent(
            null,
            "reserve",
            "Réserve",
            false,
            false,
            10,
            $this->serviceMonnaie->getCodeAffichage(),
            function ($value, Revenu $objet) {
                /** @var JSCssHtmlDecoration */
                $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getReserve() * 100)))
                    ->ajouterClasseCss($this->css_class_bage_ordinaire)
                    ->outputHtml();
                return $formatedHtml;
            }
        );
        //Police
        $this->addChampTexte(
            null,
            "police",
            "Police d'assurance",
            false,
            false,
            10,
            function ($value, Revenu $objet) {
                /** @var JSCssHtmlDecoration */
                $formatedHtml = (new JSCssHtmlDecoration("span", $objet->getPolice()->getReference()))
                    ->ajouterClasseCss($this->css_class_bage_ordinaire)
                    ->outputHtml();
                return $formatedHtml;
            }
        );
        //Client
        $this->addChampTexte(
            null,
            "client",
            "Assuré(e)",
            false,
            false,
            10,
            function ($value, Revenu $objet) {
                /** @var JSCssHtmlDecoration */
                $formatedHtml = (new JSCssHtmlDecoration("span", $objet->getClient()))
                    ->ajouterClasseCss($this->css_class_bage_ordinaire)
                    ->outputHtml();
                return $formatedHtml;
            }
        );
        //Produit
        $this->addChampTexte(
            null,
            "produit",
            "Couverture",
            false,
            false,
            10,
            function ($value, Revenu $objet) {
                /** @var JSCssHtmlDecoration */
                $formatedHtml = (new JSCssHtmlDecoration("span", $objet->getProduit()))
                    ->ajouterClasseCss($this->css_class_bage_ordinaire)
                    ->outputHtml();
                return $formatedHtml;
            }
        );
        //Assureur
        $this->addChampTexte(
            null,
            "partenaire",
            "Partenaire",
            false,
            false,
            10,
            function ($value, Revenu $objet) {
                /** @var JSCssHtmlDecoration */
                $formatedHtml = (new JSCssHtmlDecoration("span", $objet->getPartenaire()))
                    ->ajouterClasseCss($this->css_class_bage_ordinaire)
                    ->outputHtml();
                return $formatedHtml;
            }
        );
        //Dernière modification
        $this->addChampDate(
            null,
            "updatedAt",
            "D. Modification",
            false,
            false,
            10,
            function ($value, Revenu $objet) {
                /** @var JSCssHtmlDecoration */
                $formatedHtml = (new JSCssHtmlDecoration("span", $value))
                    ->ajouterClasseCss($this->css_class_bage_ordinaire)
                    ->outputHtml();
                return $formatedHtml;
            }
        );
    }

    public function batchActions(?array $champs, ?string $type = null, ?string $pageName = null, $objetInstance = null, ?Crud $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array
    {
        return $champs;
    }
}
