<?php

namespace App\Service\RefactoringJS\JSUIComponents\ElementFacture;

use App\Entity\Facture;
use App\Entity\Tranche;
use App\Service\ServiceTaxes;
use App\Entity\ElementFacture;
use App\Service\ServiceMonnaie;
use Doctrine\ORM\EntityManager;
use App\Controller\Admin\FactureCrudController;
use App\Controller\Admin\DocPieceCrudController;
use App\Controller\Admin\PaiementCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use App\Controller\Admin\UtilisateurCrudController;
use App\Controller\Admin\ElementFactureCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSPanelRenderer;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSCssHtmlDecoration;

class ElementFactureFormRenderer extends JSPanelRenderer
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
        //Montant
        $this->addChampArgent(
            null,
            "montant",
            "Montant à payer",
            false,
            false,
            12,
            $this->serviceMonnaie->getCodeAffichage(),
            function ($value, ElementFacture $objet) {
                /** @var JSCssHtmlDecoration */
                $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getMontant())))
                    ->ajouterClasseCss($this->css_class_bage_ordinaire)
                    ->outputHtml();
                return $formatedHtml;
            }
        );
        //Prime totale
        $this->addChampArgent(
            null,
            "primeTotale",
            "Prime d'assurance",
            true,
            false,
            12,
            $this->serviceMonnaie->getCodeAffichage(),
            function ($value, ElementFacture $objet) {
                /** @var JSCssHtmlDecoration */
                $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getPrimeTotale())))
                    ->ajouterClasseCss($this->css_class_bage_ordinaire)
                    ->outputHtml();
                return $formatedHtml;
            }
        );
        //Commission totale
        $this->addChampArgent(
            null,
            "commissionTotale",
            "Commission",
            true,
            false,
            12,
            $this->serviceMonnaie->getCodeAffichage(),
            function ($value, ElementFacture $objet) {
                /** @var JSCssHtmlDecoration */
                $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getCommissionTotale())))
                    ->ajouterClasseCss($this->css_class_bage_ordinaire)
                    ->outputHtml();
                return $formatedHtml;
            }
        );
        //Frais de gestion
        $this->addChampArgent(
            null,
            "fraisGestionTotale",
            "Frais de gestion",
            true,
            false,
            12,
            $this->serviceMonnaie->getCodeAffichage(),
            function ($value, ElementFacture $objet) {
                /** @var JSCssHtmlDecoration */
                $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getFraisGestionTotale())))
                    ->ajouterClasseCss($this->css_class_bage_ordinaire)
                    ->outputHtml();
                return $formatedHtml;
            }
        );
        //Revenu Total
        $this->addChampArgent(
            null,
            "revenuTotal",
            "Revenu Total",
            true,
            false,
            12,
            $this->serviceMonnaie->getCodeAffichage(),
            function ($value, ElementFacture $objet) {
                /** @var JSCssHtmlDecoration */
                $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getRevenuTotal())))
                    ->ajouterClasseCss($this->css_class_bage_ordinaire)
                    ->outputHtml();
                return $formatedHtml;
            }
        );
        //Rétrocommission Total
        $this->addChampArgent(
            null,
            "retroCommissionTotale",
            "Rétro-commission",
            true,
            false,
            12,
            $this->serviceMonnaie->getCodeAffichage(),
            function ($value, ElementFacture $objet) {
                /** @var JSCssHtmlDecoration */
                $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getRetroCommissionTotale())))
                    ->ajouterClasseCss($this->css_class_bage_ordinaire)
                    ->outputHtml();
                return $formatedHtml;
            }
        );
        //Taxe courtier
        $this->addChampArgent(
            null,
            "taxeCourtierTotale",
            "Frais " . ucfirst("" . $this->serviceTaxes->getTaxe(true)->getNom()),
            true,
            false,
            12,
            $this->serviceMonnaie->getCodeAffichage(),
            function ($value, ElementFacture $objet) {
                /** @var JSCssHtmlDecoration */
                $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getTaxeCourtierTotale())))
                    ->ajouterClasseCss($this->css_class_bage_ordinaire)
                    ->outputHtml();
                return $formatedHtml;
            }
        );
        //Taxe assureur
        $this->addChampArgent(
            null,
            "taxeAssureurTotale",
            ucfirst("" . $this->serviceTaxes->getTaxe(false)->getNom()),
            true,
            false,
            12,
            $this->serviceMonnaie->getCodeAffichage(),
            function ($value, ElementFacture $objet) {
                /** @var JSCssHtmlDecoration */
                $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getTaxeAssureurTotale())))
                    ->ajouterClasseCss($this->css_class_bage_ordinaire)
                    ->outputHtml();
                return $formatedHtml;
            }
        );
        //Tranche
        $this->addChampAssociation(
            null,
            "tranche",
            "Tranche",
            true,
            false,
            12,
            null,
            null,
            null
        );
    }

    public function batchActions(?array $champs, ?string $type = null, ?string $pageName = null, $objetInstance = null, ?Crud $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array
    {
        /**
         * L'objectif de ce traitement de masse c'est de pouvoir ne pas afficher certains champs
         * du formulaire en fonction du type de facture que l'on est en train
         * de payer.
         * Le comportement du formulaire doit varier en fonction du type de facture que l'on paie.
         */
        // dd($adminUrlGenerator);
        if ($adminUrlGenerator->get("donnees") != null) {
            if (FactureCrudController::TYPE_FACTURE_RETROCOMMISSIONS == $adminUrlGenerator->get("donnees")["type"]) {
                $this->addChampToRemove("taxeAssureurTotale");
                $this->addChampToRemove("taxeCourtierTotale");
                $this->addChampToRemove("revenuTotal");
                $this->addChampToRemove("fraisGestionTotale");
                $this->addChampToRemove("commissionTotale");
                $this->addChampToRemove("primeTotale");
                // $this->addChampToDeactivate("type", 3);
                // $this->addChampToDeactivate("reference", 4);
                // $this->addChampToDeactivate("partenaire", 3);
                // $this->addChampToRemove("compteBancaires");
                // $this->addChampToRemove("autreTiers");
            }else if (FactureCrudController::TYPE_FACTURE_PRIME == $adminUrlGenerator->get("donnees")["type"]) {
                // $this->addChampToDeactivate("type", 2);
                // $this->addChampToDeactivate("reference", 3);
                // $this->addChampToDeactivate("assureur", 3);
                // $this->addChampToDeactivate("autreTiers", 2);
                // $this->addChampToRemove("compteBancaires");
                // $this->addChampToRemove("autreTiers");
                // $this->addChampToRemove("partenaire");
            }else if (FactureCrudController::TYPE_FACTURE_COMMISSION_LOCALE == $adminUrlGenerator->get("donnees")["type"]) {
                // $this->addChampToDeactivate("type", 2);
                // $this->addChampToDeactivate("reference", 3);
                // $this->addChampToDeactivate("autreTiers", 2);
                // $this->addChampToDeactivate("assureur", 3);
                // $this->addChampToRemove("compteBancaires");
                // $this->addChampToRemove("partenaire");
            }else if (FactureCrudController::TYPE_FACTURE_COMMISSION_REASSURANCE == $adminUrlGenerator->get("donnees")["type"]) {
                // $this->addChampToDeactivate("type", 3);
                // $this->addChampToDeactivate("reference", 3);
                // $this->addChampToDeactivate("autreTiers", 2);
                // $this->addChampToDeactivate("assureur", 2);
                // $this->addChampToRemove("compteBancaires");
                // $this->addChampToRemove("partenaire");
            }else if (FactureCrudController::TYPE_FACTURE_COMMISSION_FRONTING == $adminUrlGenerator->get("donnees")["type"]) {
                // $this->addChampToDeactivate("type", 3);
                // $this->addChampToDeactivate("reference", 3);
                // $this->addChampToDeactivate("autreTiers", 2);
                // $this->addChampToDeactivate("assureur", 2);
                // $this->addChampToRemove("compteBancaires");
                // $this->addChampToRemove("partenaire");
            }else if (FactureCrudController::TYPE_FACTURE_FRAIS_DE_GESTION == $adminUrlGenerator->get("donnees")["type"]) {
                // $this->addChampToDeactivate("type", 3);
                // $this->addChampToDeactivate("reference", 3);
                // $this->addChampToDeactivate("autreTiers", 2);
                // $this->addChampToDeactivate("assureur", 2);
                // $this->addChampToRemove("compteBancaires");
                // $this->addChampToRemove("partenaire");
            }else if (FactureCrudController::TYPE_FACTURE_NOTE_DE_PERCEPTION_ARCA == $adminUrlGenerator->get("donnees")["type"]) {
                // $this->addChampToDeactivate("type", 3);
                // $this->addChampToDeactivate("reference", 3);
                // $this->addChampToDeactivate("autreTiers", 2);
                // $this->addChampToDeactivate("assureur", 2);
                // $this->addChampToRemove("compteBancaires");
                // $this->addChampToRemove("partenaire");
            }else if (FactureCrudController::TYPE_FACTURE_NOTE_DE_PERCEPTION_TVA == $adminUrlGenerator->get("donnees")["type"]) {
                // $this->addChampToDeactivate("type", 3);
                // $this->addChampToDeactivate("reference", 3);
                // $this->addChampToDeactivate("autreTiers", 2);
                // $this->addChampToDeactivate("assureur", 2);
                // $this->addChampToRemove("compteBancaires");
                // $this->addChampToRemove("partenaire");
            }
        }
        return $champs;
    }
}
