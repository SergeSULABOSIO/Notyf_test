<?php

namespace App\Service\RefactoringJS\JSUIComponents\Tranche;

use App\Entity\Facture;
use App\Entity\Tranche;
use App\Service\ServiceTaxes;
use App\Service\ServiceMonnaie;
use Doctrine\ORM\EntityManager;
use App\Controller\Admin\FactureCrudController;
use App\Controller\Admin\DocPieceCrudController;
use App\Controller\Admin\PaiementCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use App\Controller\Admin\UtilisateurCrudController;
use App\Controller\Admin\ElementFactureCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSPanelRenderer;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSCssHtmlDecoration;

class FactureFormRenderer extends JSPanelRenderer
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
        //Onglet Article
        $this->addOnglet(" Informations générales", "fas fa-handshake", "Les articles de la facture.");
        //Section - Principale
        $this->addSection(
            "Section principale",
            "fas fa-location-crosshairs",
            null,
            10
        );
        //Assureur
        $this->addChampAssociation(
            null,
            "assureur",
            "Assureur",
            false,
            false,
            5,
            null,
            null
        );
        //Partenaire
        $this->addChampAssociation(
            null,
            "partenaire",
            "Partenaire",
            false,
            false,
            5,
            null,
            null
        );
        //Autres tiers
        $this->addChampTexte(
            null,
            "autreTiers",
            "Tiers Concerné",
            false,
            false,
            5,
            null,
            null
        );
        //Pièces justificatives
        $this->addChampAssociation(
            null,
            "piece",
            "Pièces justificatives",
            false,
            false,
            10,
            null,
            null
        );
        //Type
        $this->addChampChoix(
            null,
            "type",
            "Type de facture",
            false,
            false,
            10,
            FactureCrudController::TAB_TYPE_FACTURE,
            null
        );
        //Rférence de la facture
        $this->addChampTexte(
            null,
            "reference",
            "Référence",
            false,
            false,
            10,
            function ($value, Facture $objet) {
                /** @var JSCssHtmlDecoration */
                $formatedHtml = (new JSCssHtmlDecoration("span", $value))
                    ->ajouterClasseCss($this->css_class_bage_ordinaire)
                    ->outputHtml();
                return $formatedHtml;
            }
        );
        //Description
        $this->addChampTexte(
            null,
            "description",
            "Description",
            false,
            false,
            10,
            null
        );
        //Comptes Bancaires
        $this->addChampAssociation(
            null,
            "compteBancaires",
            "Comptes bancaires",
            false,
            false,
            10,
            null,
            null
        );
        //Signed By
        $this->addChampTexte(
            null,
            "signedBy",
            "Signé par",
            true,
            false,
            5,
            function ($value, Facture $objet) {
                /** @var JSCssHtmlDecoration */
                $formatedHtml = (new JSCssHtmlDecoration("span", $value))
                    ->ajouterClasseCss($this->css_class_bage_ordinaire)
                    ->outputHtml();
                return $formatedHtml;
            }
        );
        //Onglet Article
        $this->addOnglet(" Articles facturés", "fas fa-handshake", "Les articles de la facture.");
        //Montant TTC
        $this->addChampArgent(
            null,
            "montantTTC",
            "Total à payer",
            false,
            true,
            10,
            $this->serviceMonnaie->getCodeAffichage(),
            function ($value, Facture $objet) {
                /** @var JSCssHtmlDecoration */
                $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getMontantTTC())))
                    ->ajouterClasseCss($this->css_class_bage_ordinaire)
                    ->outputHtml();
                return $formatedHtml;
            }
        );
        //Panel Articles facturées
        $this->addSection(
            "Articles facturés",
            "fa-solid fa-layer-group",
            "Elements constitutifs de la facture ou de la note de débit/crédit.",
            10
        );
        //Elements facturés
        $this->addChampCollection(
            null,
            "elementFactures",
            "Eléments facturés",
            false,
            false,
            10,
            null,
            ElementFactureCrudController::class,
            true,
            true
        );
        //Onglet Documents
        $this->addOnglet(
            "Documents ou pièces jointes",
            "fa-solid fa-paperclip",
            "Merci d'attacher vos pièces justificatives par ici."
        );
        //Documents
        $this->addChampCollection(
            null,
            "documents",
            "Documents",
            false,
            false,
            12,
            null,
            DocPieceCrudController::class,
            true,
            true
        );
    }

    public function batchActions(?array $champs, ?string $type = null, ?string $pageName = null, $objetInstance = null, ?Crud $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array
    {
        return $champs;
    }
}
