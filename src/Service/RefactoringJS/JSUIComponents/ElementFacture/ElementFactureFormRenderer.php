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
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSChamp;
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
        //Questions - Prime
        $this->addChamp(
            (new JSChamp())
                ->createBoolean("includePrime", "Inclure la prime d'assurance")
                // ->setHelp("Vous pouvez modifier ce montant au besoin.")
                ->setColumns(12)
                ->getChamp()
        );
        $this->addChamp(
            (new JSChamp())
                ->createArgent("primeTotale", "Prime d'assurance")
                ->setHelp("Vous pouvez modifier ce montant au besoin.")
                ->setRequired(true)
                ->setColumns(12)
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setFormatValue(
                    function ($value, ElementFacture $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getPrimeTotale())))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );
        
        // //Montant
        // $this->addChamp(
        //     (new JSChamp())
        //         ->createArgent("montant", "Montant à payer")
        //         ->setColumns(12)
        //         ->setCurrency($this->serviceMonnaie->getCodeAffichage())
        //         ->setFormatValue(
        //             function ($value, ElementFacture $objet) {
        //                 /** @var JSCssHtmlDecoration */
        //                 $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getMontant())))
        //                     ->ajouterClasseCss($this->css_class_bage_ordinaire)
        //                     ->outputHtml();
        //                 return $formatedHtml;
        //             }
        //         )
        //         ->getChamp()
        // );

        // //Prime totale
        // $this->addChamp(
        //     (new JSChamp())
        //         ->createArgent("primeTotale", "Prime d'assurance")
        //         ->setRequired(true)
        //         ->setColumns(12)
        //         ->setCurrency($this->serviceMonnaie->getCodeAffichage())
        //         ->setFormatValue(
        //             function ($value, ElementFacture $objet) {
        //                 /** @var JSCssHtmlDecoration */
        //                 $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getPrimeTotale())))
        //                     ->ajouterClasseCss($this->css_class_bage_ordinaire)
        //                     ->outputHtml();
        //                 return $formatedHtml;
        //             }
        //         )
        //         ->getChamp()
        // );

        // //Commission locale
        // $this->addChamp(
        //     (new JSChamp())
        //         ->createArgent("commissionLocale", "Commission locale")
        //         ->setRequired(true)
        //         ->setColumns(12)
        //         ->setCurrency($this->serviceMonnaie->getCodeAffichage())
        //         ->setFormatValue(
        //             function ($value, ElementFacture $objet) {
        //                 /** @var JSCssHtmlDecoration */
        //                 $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getCommissionLocale())))
        //                     ->ajouterClasseCss($this->css_class_bage_ordinaire)
        //                     ->outputHtml();
        //                 return $formatedHtml;
        //             }
        //         )
        //         ->getChamp()
        // );

        // //Commission de réassurance
        // $this->addChamp(
        //     (new JSChamp())
        //         ->createArgent("commissionReassurance", "Commission de réa.")
        //         ->setRequired(true)
        //         ->setColumns(12)
        //         ->setCurrency($this->serviceMonnaie->getCodeAffichage())
        //         ->setFormatValue(
        //             function ($value, ElementFacture $objet) {
        //                 /** @var JSCssHtmlDecoration */
        //                 $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getCommissionReassurance())))
        //                     ->ajouterClasseCss($this->css_class_bage_ordinaire)
        //                     ->outputHtml();
        //                 return $formatedHtml;
        //             }
        //         )
        //         ->getChamp()
        // );

        // //Commission de céssion / fronting
        // $this->addChamp(
        //     (new JSChamp())
        //         ->createArgent("commissionFronting", "Commission sur fronting.")
        //         ->setRequired(true)
        //         ->setColumns(12)
        //         ->setCurrency($this->serviceMonnaie->getCodeAffichage())
        //         ->setFormatValue(
        //             function ($value, ElementFacture $objet) {
        //                 /** @var JSCssHtmlDecoration */
        //                 $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getCommissionFronting())))
        //                     ->ajouterClasseCss($this->css_class_bage_ordinaire)
        //                     ->outputHtml();
        //                 return $formatedHtml;
        //             }
        //         )
        //         ->getChamp()
        // );

        // //Commission totale
        // $this->addChamp(
        //     (new JSChamp())
        //         ->createArgent("commissionTotale", "Commission")
        //         ->setRequired(true)
        //         ->setColumns(12)
        //         ->setCurrency($this->serviceMonnaie->getCodeAffichage())
        //         ->setFormatValue(
        //             function ($value, ElementFacture $objet) {
        //                 /** @var JSCssHtmlDecoration */
        //                 $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getCommissionTotale())))
        //                     ->ajouterClasseCss($this->css_class_bage_ordinaire)
        //                     ->outputHtml();
        //                 return $formatedHtml;
        //             }
        //         )
        //         ->getChamp()
        // );

        // //Frais de gestion
        // $this->addChamp(
        //     (new JSChamp())
        //         ->createArgent("fraisGestionTotale", "Frais de gestion")
        //         ->setRequired(true)
        //         ->setColumns(12)
        //         ->setCurrency($this->serviceMonnaie->getCodeAffichage())
        //         ->setFormatValue(
        //             function ($value, ElementFacture $objet) {
        //                 /** @var JSCssHtmlDecoration */
        //                 $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getFraisGestionTotale())))
        //                     ->ajouterClasseCss($this->css_class_bage_ordinaire)
        //                     ->outputHtml();
        //                 return $formatedHtml;
        //             }
        //         )
        //         ->getChamp()
        // );

        // //Revenu Total
        // $this->addChamp(
        //     (new JSChamp())
        //         ->createArgent("revenuTotal", "Revenu Total")
        //         ->setRequired(true)
        //         ->setColumns(12)
        //         ->setCurrency($this->serviceMonnaie->getCodeAffichage())
        //         ->setFormatValue(
        //             function ($value, ElementFacture $objet) {
        //                 /** @var JSCssHtmlDecoration */
        //                 $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getRevenuTotal())))
        //                     ->ajouterClasseCss($this->css_class_bage_ordinaire)
        //                     ->outputHtml();
        //                 return $formatedHtml;
        //             }
        //         )
        //         ->getChamp()
        // );

        // //Rétrocommission Total
        // $this->addChamp(
        //     (new JSChamp())
        //         ->createArgent("retroCommissionTotale", "Rétro-commission")
        //         ->setRequired(true)
        //         ->setColumns(12)
        //         ->setCurrency($this->serviceMonnaie->getCodeAffichage())
        //         ->setFormatValue(
        //             function ($value, ElementFacture $objet) {
        //                 /** @var JSCssHtmlDecoration */
        //                 $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getRetroCommissionTotale())))
        //                     ->ajouterClasseCss($this->css_class_bage_ordinaire)
        //                     ->outputHtml();
        //                 return $formatedHtml;
        //             }
        //         )
        //         ->getChamp()
        // );

        // //Taxe courtier
        // $this->addChamp(
        //     (new JSChamp())
        //         ->createArgent("taxeCourtierTotale", "Frais " . ucfirst("" . $this->serviceTaxes->getTaxe(true)->getNom()))
        //         ->setRequired(true)
        //         ->setColumns(12)
        //         ->setCurrency($this->serviceMonnaie->getCodeAffichage())
        //         ->setFormatValue(
        //             function ($value, ElementFacture $objet) {
        //                 /** @var JSCssHtmlDecoration */
        //                 $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getTaxeCourtierTotale())))
        //                     ->ajouterClasseCss($this->css_class_bage_ordinaire)
        //                     ->outputHtml();
        //                 return $formatedHtml;
        //             }
        //         )
        //         ->getChamp()
        // );

        // //Taxe Assureur
        // $this->addChamp(
        //     (new JSChamp())
        //         ->createArgent("taxeAssureurTotale", ucfirst("" . $this->serviceTaxes->getTaxe(false)->getNom()))
        //         ->setRequired(true)
        //         ->setColumns(12)
        //         ->setCurrency($this->serviceMonnaie->getCodeAffichage())
        //         ->setFormatValue(
        //             function ($value, ElementFacture $objet) {
        //                 /** @var JSCssHtmlDecoration */
        //                 $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getTaxeAssureurTotale())))
        //                     ->ajouterClasseCss($this->css_class_bage_ordinaire)
        //                     ->outputHtml();
        //                 return $formatedHtml;
        //             }
        //         )
        //         ->getChamp()
        // );

        //Tranche
        $this->addChamp(
            (new JSChamp())
                ->createAssociation("tranche", "Tranche")
                ->setRequired(true)
                ->setColumns(12)
                ->getChamp()
        );
        // dd("Ici");
    }

    public function batchActions(?array $champs, ?string $type = null, ?string $pageName = null, $objetInstance = null, ?Crud $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array
    {
        /**
         * L'objectif de ce traitement de masse c'est de pouvoir ne pas afficher certains champs
         * du formulaire en fonction du type de facture que l'on est en train
         * de payer.
         * Le comportement du formulaire doit varier en fonction du type de facture que l'on paie.
         */

        // dd($type);
        $destination = null;
        // dd($adminUrlGenerator->get("donnees"));
        if ($adminUrlGenerator->get("donnees") != null) {
            $destination = $adminUrlGenerator->get("donnees")["destination"];
        } else if ($objetInstance != null) {
            if ($objetInstance instanceof Facture) {
                foreach (FactureCrudController::TAB_DESTINATION as $nomDestination => $codeDestination) {
                    /** @var Facture  */
                    if ($codeDestination == $objetInstance->getDestination()) {
                        // dd($codeType, $nomType, $objetInstance->getType());
                        $destination = $nomDestination;
                    }
                }
                // dd($objetInstance);
            }
        }
        if (FactureCrudController::DESTINATION_DGI == $destination || FactureCrudController::DESTINATION_ARCA == $destination) {
            // $this->addChampToRemove("includePrime");
            // $this->addChampToRemove("includeComLocale");
            // $this->addChampToRemove("includeComFronting");
            // $this->addChampToRemove("includeComReassurance");
            // $this->addChampToRemove("includeFraisGestion");
            // $this->addChampToRemove("includeRetroCom");
            // $this->addChampToRemove("includeTaxeCourtier");
            // $this->addChampToRemove("includeTaxeAssureur");
            $this->addChampToDeactivate("tranche", 12);
        } else if (FactureCrudController::DESTINATION_ASSUREUR == $destination) {
            // $this->addChampToDeactivate("destination", 4);
            // $this->addChampToDeactivate("assureur", 4);
            // $this->addChampToDeactivate("reference", 4);
            // $this->addChampToDeactivate("compteBancaires", 4);
            // $this->addChampToRemove("type");
            // $this->addChampToRemove("autreTiers");
            // $this->addChampToRemove("partenaire");
        } else if (FactureCrudController::DESTINATION_CLIENT == $destination) {
            // $this->addChampToDeactivate("destination", 4);
            // $this->addChampToDeactivate("autreTiers", 4);
            // $this->addChampToDeactivate("reference", 4);
            // $this->addChampToDeactivate("compteBancaires", 4);
            // $this->addChampToRemove("type");
            // $this->addChampToRemove("partenaire");
            // $this->addChampToRemove("assureur");
        } else if (FactureCrudController::DESTINATION_PARTENAIRE == $destination) {
            // $this->addChampToDeactivate("destination", 4);
            // $this->addChampToDeactivate("partenaire", 4);
            // $this->addChampToDeactivate("reference", 4);
            // $this->addChampToRemove("compteBancaires");
            // $this->addChampToRemove("type");
            // $this->addChampToRemove("assureur");
            // $this->addChampToRemove("autreTiers");
        }
        // dd("Ici", $type);
        // if (FactureCrudController::TYPE_FACTURE_RETROCOMMISSIONS == $type) {
        //     $this->addChampToRemove("primeTotale");
        //     $this->addChampToRemove("fraisGestionTotale");
        //     $this->addChampToRemove("commissionFronting");
        //     $this->addChampToRemove("commissionLocale");
        //     $this->addChampToRemove("commissionReassurance");
        //     $this->addChampToDeactivate("retroCommissionTotale");
        //     $this->addChampToRemove("commissionTotale");
        //     $this->addChampToRemove("revenuTotal");
        //     $this->addChampToRemove("taxeAssureurTotale");
        //     $this->addChampToRemove("taxeCourtierTotale");
        //     $this->addChampToRemove("tranche");
        // } else if (FactureCrudController::TYPE_FACTURE_PRIME == $type) {
        //     $this->addChampToRemove("commissionFronting");
        //     $this->addChampToRemove("commissionLocale");
        //     $this->addChampToRemove("commissionReassurance");
        //     $this->addChampToRemove("fraisGestionTotale");
        //     $this->addChampToRemove("retroCommissionTotale");
        //     $this->addChampToRemove("commissionTotale");
        //     $this->addChampToRemove("revenuTotal");
        //     $this->addChampToRemove("taxeAssureurTotale");
        //     $this->addChampToRemove("taxeCourtierTotale");
        //     $this->addChampToRemove("tranche");
        //     $this->addChampToDeactivate("primeTotale");
        // } else if (FactureCrudController::TYPE_FACTURE_COMMISSION_LOCALE == $type) {
        //     $this->addChampToRemove("primeTotale");
        //     $this->addChampToRemove("fraisGestionTotale");
        //     $this->addChampToRemove("commissionFronting");
        //     $this->addChampToDeactivate("commissionLocale");
        //     $this->addChampToRemove("commissionReassurance");
        //     $this->addChampToRemove("retroCommissionTotale");
        //     $this->addChampToRemove("commissionTotale");
        //     $this->addChampToRemove("revenuTotal");
        //     $this->addChampToRemove("taxeAssureurTotale");
        //     $this->addChampToRemove("taxeCourtierTotale");
        //     $this->addChampToRemove("tranche");
        // } else if (FactureCrudController::TYPE_FACTURE_COMMISSION_REASSURANCE == $type) {
        //     $this->addChampToRemove("primeTotale");
        //     $this->addChampToRemove("fraisGestionTotale");
        //     $this->addChampToRemove("commissionFronting");
        //     $this->addChampToRemove("commissionLocale");
        //     $this->addChampToDeactivate("commissionReassurance");
        //     $this->addChampToRemove("retroCommissionTotale");
        //     $this->addChampToRemove("commissionTotale");
        //     $this->addChampToRemove("revenuTotal");
        //     $this->addChampToRemove("taxeAssureurTotale");
        //     $this->addChampToRemove("taxeCourtierTotale");
        //     $this->addChampToRemove("tranche");
        // } else if (FactureCrudController::TYPE_FACTURE_COMMISSION_FRONTING == $type) {
        //     $this->addChampToRemove("primeTotale");
        //     $this->addChampToRemove("commissionLocale");
        //     $this->addChampToRemove("commissionReassurance");
        //     $this->addChampToRemove("fraisGestionTotale");
        //     $this->addChampToRemove("retroCommissionTotale");
        //     $this->addChampToRemove("commissionTotale");
        //     $this->addChampToRemove("revenuTotal");
        //     $this->addChampToRemove("taxeAssureurTotale");
        //     $this->addChampToRemove("taxeCourtierTotale");
        //     $this->addChampToRemove("tranche");
        //     $this->addChampToDeactivate("commissionFronting");
        // } else if (FactureCrudController::TYPE_FACTURE_FRAIS_DE_GESTION == $type) {
        //     $this->addChampToRemove("primeTotale");
        //     $this->addChampToDeactivate("fraisGestionTotale");
        //     $this->addChampToRemove("commissionFronting");
        //     $this->addChampToRemove("commissionLocale");
        //     $this->addChampToRemove("commissionReassurance");
        //     $this->addChampToRemove("retroCommissionTotale");
        //     $this->addChampToRemove("commissionTotale");
        //     $this->addChampToRemove("revenuTotal");
        //     $this->addChampToRemove("taxeAssureurTotale");
        //     $this->addChampToRemove("taxeCourtierTotale");
        //     $this->addChampToRemove("tranche");
        // } else if (FactureCrudController::TYPE_FACTURE_NOTE_DE_PERCEPTION_ARCA == $type) {
        //     $this->addChampToRemove("primeTotale");
        //     $this->addChampToRemove("fraisGestionTotale");
        //     $this->addChampToRemove("commissionFronting");
        //     $this->addChampToRemove("commissionLocale");
        //     $this->addChampToRemove("commissionReassurance");
        //     $this->addChampToRemove("retroCommissionTotale");
        //     $this->addChampToRemove("commissionTotale");
        //     $this->addChampToRemove("revenuTotal");
        //     $this->addChampToRemove("taxeAssureurTotale");
        //     $this->addChampToDeactivate("taxeCourtierTotale");
        //     $this->addChampToRemove("tranche");
        // } else if (FactureCrudController::TYPE_FACTURE_NOTE_DE_PERCEPTION_TVA == $type) {
        //     $this->addChampToRemove("primeTotale");
        //     $this->addChampToRemove("fraisGestionTotale");
        //     $this->addChampToRemove("commissionFronting");
        //     $this->addChampToRemove("commissionLocale");
        //     $this->addChampToRemove("commissionReassurance");
        //     $this->addChampToRemove("retroCommissionTotale");
        //     $this->addChampToRemove("commissionTotale");
        //     $this->addChampToRemove("revenuTotal");
        //     $this->addChampToDeactivate("taxeAssureurTotale");
        //     $this->addChampToRemove("taxeCourtierTotale");
        //     $this->addChampToRemove("tranche");
        // }
        return $champs;
    }
}
