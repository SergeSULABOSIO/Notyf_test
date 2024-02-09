<?php

namespace App\Service\RefactoringJS\JSUIComponents\Tranche;

use App\Entity\Tranche;
use App\Service\ServiceTaxes;
use App\Service\ServiceMonnaie;
use Doctrine\ORM\EntityManager;
use App\Controller\Admin\PaiementCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use App\Controller\Admin\UtilisateurCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSPanelRenderer;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSCssHtmlDecoration;

class TrancheDetailsRenderer extends JSPanelRenderer
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
        //Nom
        $this->addChampTexte(
            null,
            "nom",
            "Intitulé",
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
        //Validee?
        $this->addChampBooleen(
            null,
            "validated",
            "Validée",
            false,
            false,
            false
        );
        //Période
        $this->addChampTexte(
            null,
            "periodeValidite",
            "Durée",
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
        //Taux
        $this->addChampPourcentage(
            null,
            "taux",
            "Portion",
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
        //Prime Annuelle
        $this->addChampCollection(
            null,
            "premiumInvoiceDetails",
            "Prime Totale",
            false,
            false,
            10,
            null,
            null,
            false,
            false,
            $this->twig_template_status_tranche
        );
        //Commission de réassurance
        $this->addChampCollection(
            null,
            "comReassuranceInvoiceDetails",
            "Com. Réa.",
            false,
            false,
            10,
            null,
            null,
            false,
            false,
            $this->twig_template_status_tranche
        );
        //Commission locale
        $this->addChampCollection(
            null,
            "comLocaleInvoiceDetails",
            "Com. Locale",
            false,
            false,
            10,
            null,
            null,
            false,
            false,
            $this->twig_template_status_tranche
        );
        //Commission sur Fronting
        $this->addChampCollection(
            null,
            "comFrontingInvoiceDetails",
            "Com. Fronting",
            false,
            false,
            10,
            null,
            null,
            false,
            false,
            $this->twig_template_status_tranche
        );
        //Frais de gestion
        $this->addChampCollection(
            null,
            "fraisGestionInvoiceDetails",
            "Frais de Gest.",
            false,
            false,
            10,
            null,
            null,
            false,
            false,
            $this->twig_template_status_tranche
        );
        //Revenu total
        $this->addChampArgent(
            null,
            "revenuTotal",
            "Revenu Total",
            false,
            false,
            10,
            $this->serviceMonnaie->getCodeAffichage(),
            function ($value, Tranche $tranche) {
                /** @var JSCssHtmlDecoration */
                $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($tranche->getRevenuTotal())))
                    ->ajouterClasseCss($this->css_class_bage_ordinaire)
                    ->outputHtml();
                return $formatedHtml;
            }
        );
        //Retrocommission
        $this->addChampCollection(
            null,
            "retrocomInvoiceDetails",
            "Rétro-Com.",
            false,
            false,
            10,
            null,
            null,
            false,
            false,
            $this->twig_template_status_tranche
        );
        //Taxe courtier
        $this->addChampCollection(
            null,
            "taxCourtierInvoiceDetails",
            "Frais " . ucfirst($this->serviceTaxes->getNomTaxeCourtier()),
            false,
            false,
            10,
            null,
            null,
            false,
            false,
            $this->twig_template_status_tranche
        );
        //Taxe assureur
        $this->addChampCollection(
            null,
            "taxAssureurInvoiceDetails",
            "Taxe " . ucfirst($this->serviceTaxes->getNomTaxeAssureur()),
            false,
            false,
            10,
            null,
            null,
            false,
            false,
            $this->twig_template_status_tranche
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
            function ($value, Tranche $tranche) {
                /** @var JSCssHtmlDecoration */
                $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($tranche->getReserve())))
                    ->ajouterClasseCss($this->css_class_bage_ordinaire)
                    ->outputHtml();
                return $formatedHtml;
            }
        );
        //Police
        $this->addChampTexte(
            null,
            "police",
            "Réf. de la police",
            false,
            false,
            10,
            function ($value, Tranche $tranche) {
                /** @var JSCssHtmlDecoration */
                $formatedHtml = (new JSCssHtmlDecoration("span", $tranche->getPolice()))
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
            function ($value, Tranche $tranche) {
                /** @var JSCssHtmlDecoration */
                $formatedHtml = (new JSCssHtmlDecoration("span", $tranche->getClient()->getNom()))
                    ->ajouterClasseCss($this->css_class_bage_ordinaire)
                    ->outputHtml();
                return $formatedHtml;
            }
        );
        //Client
        $this->addChampTexte(
            null,
            "produit",
            "Couverture",
            false,
            false,
            10,
            function ($value, Tranche $tranche) {
                /** @var JSCssHtmlDecoration */
                $formatedHtml = (new JSCssHtmlDecoration("span", $tranche->getProduit()->getNom()))
                    ->ajouterClasseCss($this->css_class_bage_ordinaire)
                    ->outputHtml();
                return $formatedHtml;
            }
        );
        //Partenaire
        $this->addChampTexte(
            null,
            "partenaire",
            "Partenaire",
            false,
            false,
            10,
            function ($value, Tranche $tranche) {
                /** @var JSCssHtmlDecoration */
                $formatedHtml = (new JSCssHtmlDecoration("span", $tranche->getPartenaire()->getNom()))
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
            function ($value, Tranche $tranche) {
                /** @var JSCssHtmlDecoration */
                $formatedHtml = (new JSCssHtmlDecoration("span", $value))
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
