<?php

namespace App\Service\RefactoringJS\JSUIComponents\ElementFacture;

use App\Entity\Facture;
use App\Service\ServiceTaxes;
use App\Entity\ElementFacture;
use App\Service\ServiceMonnaie;
use Doctrine\ORM\EntityManager;
use App\Controller\Admin\FactureCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
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
        // dd("Objet Instance:", $this->getObjetInstance());
        $this->addChamp(
            (new JSChamp()) //<i class="fa-solid fa-tag"></i>
                ->createSection("Montant total")
                ->setIcon("fa-solid fa-circle-dollar-to-slot")
                ->getChamp()
        );
        //Montant
        $this->addChamp(
            (new JSChamp()) //<i class="fa-solid fa-circle-dollar-to-slot"></i>
                ->createArgent("montant", "Montant total à payer")
                ->setColumns(12)
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setFormatValue(
                    function ($value, ElementFacture $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getMontant())))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );


        $this->addChamp(
            (new JSChamp()) //<i class="fa-brands fa-stack-overflow"></i>
                ->createSection("Détails sur le motant total")
                ->setIcon("fa-brands fa-stack-overflow")
                ->getChamp()
        );
        //Questions - Prime
        $this->addChamp(
            (new JSChamp())
                ->createBoolean("includePrime", "Inclure la prime d'assurance")
                ->setColumns(12)
                ->getChamp()
        );
        $this->addChamp(
            (new JSChamp())
                ->createArgent("primeTotale", "Prime d'assurance")
                ->setHelp("Vous pouvez modifier ce montant au besoin.")
                ->setRequired(true)
                ->setDisabled(true)
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
        

        //Question Com locale
        $this->addChamp(
            (new JSChamp())
                ->createBoolean("includeComLocale", "Inclure la commission locale")
                ->setColumns(12)
                ->getChamp()
        );
        $this->addChamp(
            (new JSChamp())
                ->createArgent("commissionLocale", "Commission locale")
                ->setRequired(true)
                ->setDisabled(true)
                ->setColumns(12)
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setFormatValue(
                    function ($value, ElementFacture $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getCommissionLocale())))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );
        

        //Question Com fronting
        $this->addChamp(
            (new JSChamp())
                ->createBoolean("includeComFronting", "Inclure la commission fronting / de céssion")
                ->setColumns(12)
                ->getChamp()
        );
        $this->addChamp(
            (new JSChamp())
                ->createArgent("commissionFronting", "Commission sur fronting.")
                ->setRequired(true)
                ->setDisabled(true)
                ->setColumns(12)
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setFormatValue(
                    function ($value, ElementFacture $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getCommissionFronting())))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );
        

        //Question Com réassurance
        $this->addChamp(
            (new JSChamp())
                ->createBoolean("includeComReassurance", "Inclure la commission de réassurance")
                ->setColumns(12)
                ->getChamp()
        );
        $this->addChamp(
            (new JSChamp())
                ->createArgent("commissionReassurance", "Commission de réassurance")
                ->setRequired(true)
                ->setDisabled(true)
                ->setColumns(12)
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setFormatValue(
                    function ($value, ElementFacture $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getCommissionReassurance())))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );
        

        //Question Frais de gestion
        $this->addChamp(
            (new JSChamp())
                ->createBoolean("includeFraisGestion", "Inclure Frais de gestion")
                ->setColumns(12)
                ->getChamp()
        );
        $this->addChamp(
            (new JSChamp())
                ->createArgent("fraisGestionTotale", "Frais de gestion")
                ->setRequired(true)
                ->setDisabled(true)
                ->setColumns(12)
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setFormatValue(
                    function ($value, ElementFacture $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getFraisGestionTotale())))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );
        

        //Question Rétro commission
        $this->addChamp(
            (new JSChamp())
                ->createBoolean("includeRetroCom", "Inclure la rétro-commission")
                ->setColumns(12)
                ->getChamp()
        );
        $this->addChamp(
            (new JSChamp())
                ->createArgent("retroCommissionTotale", "Rétro-commission")
                ->setRequired(true)
                ->setDisabled(true)
                ->setColumns(12)
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setFormatValue(
                    function ($value, ElementFacture $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getRetroCommissionTotale())))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );
        

        //Question Rétro commission
        $this->addChamp(
            (new JSChamp())
                ->createBoolean("includeTaxeCourtier", "Inclure le Frais " . ucfirst("" . $this->serviceTaxes->getTaxe(true)->getNom()))
                ->setColumns(12)
                ->getChamp()
        );
        $this->addChamp(
            (new JSChamp())
                ->createArgent("taxeCourtierTotale", "Frais " . ucfirst("" . $this->serviceTaxes->getTaxe(true)->getNom()))
                ->setRequired(true)
                ->setDisabled(true)
                ->setColumns(12)
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setFormatValue(
                    function ($value, ElementFacture $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getTaxeCourtierTotale())))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );
        

        //Question Rétro commission
        $this->addChamp(
            (new JSChamp())
                ->createBoolean("includeTaxeAssureur", "Inclure la " . ucfirst("" . $this->serviceTaxes->getTaxe(false)->getNom()))
                ->setColumns(12)
                ->getChamp()
        );
        $this->addChamp(
            (new JSChamp())
                ->createArgent("taxeAssureurTotale", ucfirst("" . $this->serviceTaxes->getTaxe(false)->getNom()))
                ->setRequired(true)
                ->setColumns(12)
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setFormatValue(
                    function ($value, ElementFacture $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getTaxeAssureurTotale())))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );
        

        //Dernière section
        $this->addChamp(
            (new JSChamp())
                ->createSection("Connexion à la tranche concernée.")
                ->setIcon("fa-solid fa-layer-group")
                ->getChamp()
        );
        //Tranche
        $this->addChamp(
            (new JSChamp()) //fa-solid fa-layer-group
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
        if (FactureCrudController::DESTINATION_DGI == $destination) {
            $this->addChampToRemove("includePrime");
            $this->addChampToRemove("primeTotale");

            $this->addChampToRemove("includeComLocale");
            $this->addChampToRemove("commissionLocale");

            $this->addChampToRemove("includeComFronting");
            $this->addChampToRemove("commissionFronting");

            $this->addChampToRemove("includeComReassurance");
            $this->addChampToRemove("commissionReassurance");

            $this->addChampToRemove("includeFraisGestion");
            $this->addChampToRemove("fraisGestionTotale");

            $this->addChampToRemove("includeRetroCom");
            $this->addChampToRemove("retroCommissionTotale");

            $this->addChampToRemove("includeTaxeCourtier");
            $this->addChampToRemove("taxeCourtierTotale");

            $this->addChampToDeactivate("montant", 12);
            // $this->addChampToDeactivate("tranche", 12);
        } else if (FactureCrudController::DESTINATION_ARCA == $destination) {
            $this->addChampToRemove("includePrime");
            $this->addChampToRemove("primeTotale");

            $this->addChampToRemove("includeComLocale");
            $this->addChampToRemove("commissionLocale");

            $this->addChampToRemove("includeComFronting");
            $this->addChampToRemove("commissionFronting");

            $this->addChampToRemove("includeComReassurance");
            $this->addChampToRemove("commissionReassurance");

            $this->addChampToRemove("includeFraisGestion");
            $this->addChampToRemove("fraisGestionTotale");

            $this->addChampToRemove("includeRetroCom");
            $this->addChampToRemove("retroCommissionTotale");

            $this->addChampToRemove("includeTaxeAssureur");
            $this->addChampToRemove("taxeAssureurTotale");

            $this->addChampToDeactivate("montant", 12);
            // $this->addChampToDeactivate("tranche", 12);
        } else if (FactureCrudController::DESTINATION_ASSUREUR == $destination) {
            $this->addChampToRemove("includePrime");
            $this->addChampToRemove("primeTotale");

            $this->addChampToRemove("includeFraisGestion");
            $this->addChampToRemove("fraisGestionTotale");

            $this->addChampToRemove("includeRetroCom");
            $this->addChampToRemove("retroCommissionTotale");

            $this->addChampToRemove("includeTaxeCourtier");
            $this->addChampToRemove("taxeCourtierTotale");

            $this->addChampToRemove("includeTaxeAssureur");
            $this->addChampToRemove("taxeAssureurTotale");

            $this->addChampToDeactivate("montant", 12);
            // $this->addChampToDeactivate("tranche", 12);
        } else if (FactureCrudController::DESTINATION_CLIENT == $destination) {
            
            $this->addChampToRemove("includeComLocale");
            $this->addChampToRemove("commissionLocale");

            $this->addChampToRemove("includeComFronting");
            $this->addChampToRemove("commissionFronting");

            $this->addChampToRemove("includeComReassurance");
            $this->addChampToRemove("commissionReassurance");

            $this->addChampToRemove("includeRetroCom");
            $this->addChampToRemove("retroCommissionTotale");

            $this->addChampToRemove("includeTaxeCourtier");
            $this->addChampToRemove("taxeCourtierTotale");

            $this->addChampToRemove("includeTaxeAssureur");
            $this->addChampToRemove("taxeAssureurTotale");

            $this->addChampToDeactivate("montant", 12);
            // $this->addChampToDeactivate("tranche", 12);
        } else if (FactureCrudController::DESTINATION_PARTENAIRE == $destination) {
            $this->addChampToRemove("includePrime");
            $this->addChampToRemove("primeTotale");

            $this->addChampToRemove("includeComLocale");
            $this->addChampToRemove("commissionLocale");

            $this->addChampToRemove("includeComFronting");
            $this->addChampToRemove("commissionFronting");

            $this->addChampToRemove("includeComReassurance");
            $this->addChampToRemove("commissionReassurance");

            $this->addChampToRemove("includeFraisGestion");
            $this->addChampToRemove("fraisGestionTotale");

            $this->addChampToRemove("includeTaxeCourtier");
            $this->addChampToRemove("taxeCourtierTotale");

            $this->addChampToRemove("includeTaxeAssureur");
            $this->addChampToRemove("taxeAssureurTotale");

            $this->addChampToDeactivate("montant", 12);
            // $this->addChampToDeactivate("tranche", 12);
        }
        return $champs;
    }
}
