<?php

namespace App\Service\RefactoringJS\JSUIComponents\Tranche;

use App\Entity\Tranche;
use App\Service\ServiceTaxes;
use App\Service\ServiceMonnaie;
use Doctrine\ORM\EntityManager;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSChamp;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSPanelRenderer;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSCssHtmlDecoration;

class TrancheListeRenderer extends JSPanelRenderer
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
        //Nom
        $this->addChamp(
            (new JSChamp())
                ->createTexte("nom", "Intitulé")
                ->setColumns(10)
                ->setFormatValue(
                    function ($value, Tranche $tranche) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $value))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );

        //Validee?
        $this->addChamp(
            (new JSChamp())
                ->createBoolean("validated", "Validée")
                ->setDisabled(true)
                ->getChamp()
        );

        //Période
        $this->addChamp(
            (new JSChamp())
                ->createTexte("periodeValidite", "Durée")
                ->setColumns(10)
                ->setFormatValue(
                    function ($value, Tranche $tranche) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $value))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );

        //Taux
        $this->addChamp(
            (new JSChamp())
                ->createPourcentage("taux", "Portion")
                ->setColumns(10)
                ->setFormatValue(
                    function ($value, Tranche $tranche) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $value))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );

        //Prime Annuelle
        $this->addChamp(
            (new JSChamp())
                ->createCollection("premiumInvoiceDetails", "Prime Totale")
                ->setColumns(10)
                ->setTemplatePath($this->twig_template_status_tranche)
                ->allowDelete(false)
                ->allowAdd(false)
                ->getChamp()
        );
        
        //Commission de réassurance
        $this->addChamp(
            (new JSChamp())
                ->createCollection("comReassuranceInvoiceDetails", "Com. Réa.")
                ->setColumns(10)
                ->setTemplatePath($this->twig_template_status_tranche)
                ->allowDelete(false)
                ->allowAdd(false)
                ->getChamp()
        );
        
        //Commission locale
        $this->addChamp(
            (new JSChamp())
                ->createCollection("comLocaleInvoiceDetails", "Com. Locale")
                ->setColumns(10)
                ->setTemplatePath($this->twig_template_status_tranche)
                ->allowDelete(false)
                ->allowAdd(false)
                ->getChamp()
        );
        
        //Commission sur Fronting
        $this->addChamp(
            (new JSChamp())
                ->createCollection("comFrontingInvoiceDetails", "Com. Fronting")
                ->setColumns(10)
                ->setTemplatePath($this->twig_template_status_tranche)
                ->allowDelete(false)
                ->allowAdd(false)
                ->getChamp()
        );
        
        //Frais de gestion
        $this->addChamp(
            (new JSChamp())
                ->createCollection("fraisGestionInvoiceDetails", "Frais de Gest.")
                ->setColumns(10)
                ->setTemplatePath($this->twig_template_status_tranche)
                ->allowDelete(false)
                ->allowAdd(false)
                ->getChamp()
        );
        
        //Revenu total
        $this->addChamp(
            (new JSChamp())
                ->createArgent("revenuTotal", "Revenu Total")
                ->setColumns(10)
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setFormatValue(
                    function ($value, Tranche $tranche) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($tranche->getRevenuTotal())))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );
        
        //Retrocommission
        $this->addChamp(
            (new JSChamp())
                ->createCollection("retrocomInvoiceDetails", "Rétro-Com.")
                ->setColumns(10)
                ->setTemplatePath($this->twig_template_status_tranche)
                ->allowDelete(false)
                ->allowAdd(false)
                ->getChamp()
        );
        
        //Taxe courtier
        $this->addChamp(
            (new JSChamp())
                ->createCollection("taxCourtierInvoiceDetails", "Frais " . ucfirst($this->serviceTaxes->getNomTaxeCourtier()))
                ->setColumns(10)
                ->setTemplatePath($this->twig_template_status_tranche)
                ->allowDelete(false)
                ->allowAdd(false)
                ->getChamp()
        );
        
        //Taxe assureur
        $this->addChamp(
            (new JSChamp())
                ->createCollection("taxAssureurInvoiceDetails", "Taxe " . ucfirst($this->serviceTaxes->getNomTaxeAssureur()))
                ->setColumns(10)
                ->setTemplatePath($this->twig_template_status_tranche)
                ->allowDelete(false)
                ->allowAdd(false)
                ->getChamp()
        );
        
        //Réserve
        $this->addChamp(
            (new JSChamp())
                ->createArgent("reserve", "Réserve")
                ->setColumns(10)
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setFormatValue(
                    function ($value, Tranche $tranche) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($tranche->getRevenuTotal())))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );
        
        //Police
        $this->addChamp(
            (new JSChamp())
                ->createTexte("police", "Réf. de la police")
                ->setColumns(10)
                ->setFormatValue(
                    function ($value, Tranche $tranche) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $tranche->getPolice()->getReference()))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );
        
        //Client
        $this->addChamp(
            (new JSChamp())
                ->createTexte("client", "Assuré(e)")
                ->setColumns(10)
                ->setFormatValue(
                    function ($value, Tranche $tranche) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $tranche->getClient()->getNom()))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );
        
        //Client
        $this->addChamp(
            (new JSChamp())
                ->createTexte("produit", "Couverture")
                ->setColumns(10)
                ->setFormatValue(
                    function ($value, Tranche $tranche) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $tranche->getProduit()->getCode()))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );
        
        //Partenaire
        $this->addChamp(
            (new JSChamp())
                ->createTexte("partenaire", "Partenaire")
                ->setColumns(10)
                ->setFormatValue(
                    function ($value, Tranche $tranche) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $tranche->getPartenaire()->getNom()))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );
        
        //Dernière modification
        $this->addChamp(
            (new JSChamp())
            ->createDate("updatedAt", "D. Modification")
            ->setColumns(10)
            ->setFormatValue(
                function ($value, Tranche $tranche) {
                    /** @var JSCssHtmlDecoration */
                    $formatedHtml = (new JSCssHtmlDecoration("span", $value))
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
