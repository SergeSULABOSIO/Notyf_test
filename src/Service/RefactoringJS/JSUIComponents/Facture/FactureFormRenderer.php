<?php

namespace App\Service\RefactoringJS\JSUIComponents\Facture;

use App\Entity\Facture;
use App\Service\ServiceTaxes;
use App\Service\ServiceMonnaie;
use Doctrine\ORM\EntityManager;
use App\Controller\Admin\FactureCrudController;
use App\Controller\Admin\DocPieceCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use App\Controller\Admin\ElementFactureCrudController;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSChamp;
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
        $this->addChamp(
            (new JSChamp())
                ->createOnglet(" Informations générales")
                ->setIcon("fas fa-handshake")
                ->setHelp("Les articles de la facture.")
                ->getChamp()
        );

        //Section - Principale
        $this->addChamp(
            (new JSChamp())
                ->createSection("Section principale")
                ->setIcon("fas fa-location-crosshairs")
                ->getChamp()
        );

        //Assureur
        $this->addChamp(
            (new JSChamp())
                ->createAssociation("assureur", "Assureur")
                ->setColumns(5)
                ->getChamp()
        );

        //Partenaire
        $this->addChamp(
            (new JSChamp())
                ->createAssociation("partenaire", "Partenaire")
                ->setColumns(5)
                ->getChamp()
        );

        //Autres tiers
        $this->addChamp(
            (new JSChamp())
                ->createTexte("autreTiers", "Tiers Concerné")
                ->setColumns(5)
                ->getChamp()
        );

        //Type
        $this->addChamp(
            (new JSChamp())
                ->createChoix("type", "Type de facture")
                ->setColumns(10)
                ->setChoices(FactureCrudController::TAB_TYPE_FACTURE)
                ->getChamp()
        );

        //Rférence de la facture
        $this->addChamp(
            (new JSChamp())
                ->createTexte("reference", "Référence")
                ->setColumns(10)
                ->setFormatValue(
                    function ($value, Facture $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $value))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );
        Ici
        // Ici, voir en bas, c'est à partir de là qu'il faut continuer en se basant sur le modèle d'en haut.
        //Description
        $this->addChampEditeurTexte(
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
            3,
            function ($value, Facture $objet) {
                /** @var JSCssHtmlDecoration */
                $formatedHtml = (new JSCssHtmlDecoration("span", $value))
                    ->ajouterClasseCss($this->css_class_bage_ordinaire)
                    ->outputHtml();
                return $formatedHtml;
            }
        );
        $this->addChampTexte(
            null,
            "posteSignedBy",
            "Fonction",
            true,
            false,
            3,
            null
        );
        //Onglet Article
        $this->addOnglet(" Articles facturés", "fas fa-handshake", "Les articles de la facture.");
        //Montant TTC
        $this->addChampArgent(
            null,
            "totalSolde",
            "Solde à payer",
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
            false,
            false
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
        /**
         * L'objectif de ce traitement de masse c'est de pouvoir ne pas afficher certains champs
         * du formulaire en fonction du type de facture que l'on est en train
         * de payer.
         * Le comportement du formulaire doit varier en fonction du type de facture que l'on paie.
         */
        // dd($adminUrlGenerator);
        if ($adminUrlGenerator->get("donnees") != null) {
            if (FactureCrudController::TYPE_FACTURE_RETROCOMMISSIONS == $adminUrlGenerator->get("donnees")["type"]) {
                $this->addChampToDeactivate("type", 3);
                $this->addChampToDeactivate("reference", 4);
                $this->addChampToDeactivate("partenaire", 3);
                $this->addChampToRemove("compteBancaires");
                $this->addChampToRemove("autreTiers");
                $this->addChampToRemove("assureur");
            } else if (FactureCrudController::TYPE_FACTURE_PRIME == $adminUrlGenerator->get("donnees")["type"]) {
                $this->addChampToDeactivate("type", 2);
                $this->addChampToDeactivate("reference", 3);
                $this->addChampToDeactivate("assureur", 3);
                $this->addChampToDeactivate("autreTiers", 2);
                $this->addChampToRemove("compteBancaires");
                $this->addChampToRemove("autreTiers");
                $this->addChampToRemove("partenaire");
            } else if (FactureCrudController::TYPE_FACTURE_COMMISSION_LOCALE == $adminUrlGenerator->get("donnees")["type"]) {
                $this->addChampToDeactivate("type", 2);
                $this->addChampToDeactivate("reference", 3);
                $this->addChampToDeactivate("autreTiers", 2);
                $this->addChampToDeactivate("assureur", 3);
                $this->addChampToRemove("compteBancaires");
                $this->addChampToRemove("partenaire");
            } else if (FactureCrudController::TYPE_FACTURE_COMMISSION_REASSURANCE == $adminUrlGenerator->get("donnees")["type"]) {
                $this->addChampToDeactivate("type", 3);
                $this->addChampToDeactivate("reference", 3);
                $this->addChampToDeactivate("autreTiers", 2);
                $this->addChampToDeactivate("assureur", 2);
                $this->addChampToRemove("compteBancaires");
                $this->addChampToRemove("partenaire");
            } else if (FactureCrudController::TYPE_FACTURE_COMMISSION_FRONTING == $adminUrlGenerator->get("donnees")["type"]) {
                $this->addChampToDeactivate("type", 3);
                $this->addChampToDeactivate("reference", 3);
                $this->addChampToDeactivate("autreTiers", 2);
                $this->addChampToDeactivate("assureur", 2);
                $this->addChampToRemove("compteBancaires");
                $this->addChampToRemove("partenaire");
            } else if (FactureCrudController::TYPE_FACTURE_FRAIS_DE_GESTION == $adminUrlGenerator->get("donnees")["type"]) {
                $this->addChampToDeactivate("type", 3);
                $this->addChampToDeactivate("reference", 3);
                $this->addChampToDeactivate("autreTiers", 2);
                $this->addChampToDeactivate("assureur", 2);
                $this->addChampToRemove("compteBancaires");
                $this->addChampToRemove("partenaire");
            } else if (FactureCrudController::TYPE_FACTURE_NOTE_DE_PERCEPTION_ARCA == $adminUrlGenerator->get("donnees")["type"]) {
                $this->addChampToDeactivate("type", 3);
                $this->addChampToDeactivate("reference", 3);
                $this->addChampToDeactivate("autreTiers", 2);
                $this->addChampToDeactivate("assureur", 2);
                $this->addChampToRemove("compteBancaires");
                $this->addChampToRemove("partenaire");
            } else if (FactureCrudController::TYPE_FACTURE_NOTE_DE_PERCEPTION_TVA == $adminUrlGenerator->get("donnees")["type"]) {
                $this->addChampToDeactivate("type", 3);
                $this->addChampToDeactivate("reference", 3);
                $this->addChampToDeactivate("autreTiers", 2);
                $this->addChampToDeactivate("assureur", 2);
                $this->addChampToRemove("compteBancaires");
                $this->addChampToRemove("partenaire");
            }
        }
        return $champs;
    }
}
