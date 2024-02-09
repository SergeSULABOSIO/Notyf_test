<?php

namespace App\Service\RefactoringJS\JSUIComponents\Tranche;

use App\Entity\Tranche;
use App\Service\ServiceTaxes;
use App\Service\ServiceMonnaie;
use Doctrine\ORM\EntityManager;
use App\Controller\Admin\RevenuCrudController;
use App\Controller\Admin\PaiementCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use App\Controller\Admin\UtilisateurCrudController;
use App\Entity\Monnaie;
use App\Entity\Revenu;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSPanelRenderer;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSCssHtmlDecoration;

class RevenuDetailsRenderer extends JSPanelRenderer
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
        parent::__construct(self::TYPE_DETAILS, $pageName, $objetInstance, $crud, $adminUrlGenerator);
    }

    public function design()
    {
        //Validee?
        $this->addChampBooleen(
            null,
            "validated",
            "Validée",
            false,
            false,
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
        //Assureur
        $this->addChampTexte(
            null,
            "assureur",
            "Assureur",
            false,
            false,
            10,
            function ($value, Revenu $objet) {
                /** @var JSCssHtmlDecoration */
                $formatedHtml = (new JSCssHtmlDecoration("span", $objet->getAssureur()))
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
        //Date effet
        $this->addChampDate(
            null,
            "dateEffet",
            "Date d'effet",
            false,
            false,
            10,
            function ($value, Tranche $tranche) {
                /** @var JSCssHtmlDecoration */
                $formatedHtml = (new JSCssHtmlDecoration("span", $value))
                    ->ajouterClasseCss($this->css_class_bage_ordinaire)
                    ->outputHtml();
                return $formatedHtml;
            }
        );
        //Date expiration
        $this->addChampDate(
            null,
            "dateExpiration",
            "Echéance",
            false,
            false,
            10,
            function ($value, Tranche $tranche) {
                /** @var JSCssHtmlDecoration */
                $formatedHtml = (new JSCssHtmlDecoration("span", $value))
                    ->ajouterClasseCss($this->css_class_bage_ordinaire)
                    ->outputHtml();
                return $formatedHtml;
            }
        );
        //Date d'opération
        $this->addChampDate(
            null,
            "dateOperation",
            "Date d'opération",
            false,
            false,
            10,
            function ($value, Tranche $tranche) {
                /** @var JSCssHtmlDecoration */
                $formatedHtml = (new JSCssHtmlDecoration("span", $value))
                    ->ajouterClasseCss($this->css_class_bage_ordinaire)
                    ->outputHtml();
                return $formatedHtml;
            }
        );
        //Date d'émition
        $this->addChampDate(
            null,
            "dateEmition",
            "Date d'émition",
            false,
            false,
            10,
            function ($value, Tranche $tranche) {
                /** @var JSCssHtmlDecoration */
                $formatedHtml = (new JSCssHtmlDecoration("span", $value))
                    ->ajouterClasseCss($this->css_class_bage_ordinaire)
                    ->outputHtml();
                return $formatedHtml;
            }
        );
        //Cotation
        $this->addChampAssociation(
            null,
            "cotation",
            "Cotation",
            false,
            false,
            10,
            null,
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
            "Partageable?",
            false,
            false,
            10,
            RevenuCrudController::TAB_TAXABLE,
            null
        );
        //Taxable?
        $this->addChampChoix(
            null,
            "base",
            "Formuale",
            false,
            false,
            10,
            RevenuCrudController::TAB_BASE,
            null
        );
        //Taux
        $this->addChampPourcentage(
            null,
            "taux",
            "Taux",
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
                $formatedHtml = (new JSCssHtmlDecoration("span", $objet->getRevenuPure() * 100))
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
                $formatedHtml = (new JSCssHtmlDecoration("span", $objet->getRevenuNet() * 100))
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
                $formatedHtml = (new JSCssHtmlDecoration("span", $objet->getTaxeAssureur() * 100))
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
                $formatedHtml = (new JSCssHtmlDecoration("span", $objet->getRevenuTotale() * 100))
                    ->ajouterClasseCss($this->css_class_bage_ordinaire)
                    ->outputHtml();
                return $formatedHtml;
            }
        );
        //Utilisateur
        $this->addChampAssociation(
            UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE],
            "utilisateur",
            "Utilisateur",
            false,
            false,
            10,
            null,
            null
        );
        //Date de création
        $this->addChampDate(
            null,
            "createdAt",
            "D. création",
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
        //Entreprise
        $this->addChampAssociation(
            null,
            "entreprise",
            "Entreprise",
            false,
            false,
            10,
            null,
            null
        );
    }

    public function batchActions(?array $champs, ?string $type = null, ?string $pageName = null, $objetInstance = null, ?Crud $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array
    {
        return $champs;
    }
}
