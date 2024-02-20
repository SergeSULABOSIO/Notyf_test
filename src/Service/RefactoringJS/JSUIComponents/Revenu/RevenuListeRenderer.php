<?php

namespace App\Service\RefactoringJS\JSUIComponents\Revenu;

use App\Entity\Revenu;
use App\Service\ServiceTaxes;
use App\Service\ServiceMonnaie;
use Doctrine\ORM\EntityManager;
use App\Controller\Admin\RevenuCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSChamp;
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
        $this->addChamp(
            (new JSChamp())
                ->createBoolean("validated", "Validée")
                ->setDisabled(true)
                ->getChamp()
        );
        
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
        
        //Taxable?
        $this->addChamp(
            (new JSChamp())
                ->createChoix("taxable", "Taxable?")
                ->setColumns(12)
                ->setRequired(false)
                ->setDisabled(false)
                ->setChoices(RevenuCrudController::TAB_TAXABLE)
                ->renderAsBadges(
                    [
                        RevenuCrudController::TAB_TAXABLE[RevenuCrudController::TAXABLE_OUI] => 'danger',
                        RevenuCrudController::TAB_TAXABLE[RevenuCrudController::TAXABLE_OUI] => 'success',
                    ]
                )
                ->getChamp()
        );
        
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
        
        //Revenu Pure
        $this->addChamp(
            (new JSChamp())
                ->createArgent("revenuPure", "Revenu pure")
                ->setRequired(false)
                ->setDisabled(false)
                ->setColumns(12)
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setFormatValue(
                    function ($value, Revenu $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getRevenuPure() * 100)))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );
        
        //Taxe courtier
        $this->addChamp(
            (new JSChamp())
                ->createArgent("taxeCourtier", ucfirst($this->serviceTaxes->getNomTaxeCourtier()))
                ->setRequired(false)
                ->setDisabled(false)
                ->setColumns(12)
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setFormatValue(
                    function ($value, Revenu $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getTaxeCourtier() * 100)))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );
        
        //Revenu Net
        $this->addChamp(
            (new JSChamp())
                ->createArgent("revenuNet", "Revenu net")
                ->setRequired(false)
                ->setDisabled(false)
                ->setColumns(12)
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setFormatValue(
                    function ($value, Revenu $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getRevenuNet() * 100)))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );
        
        //Taxe assureur
        $this->addChamp(
            (new JSChamp())
                ->createArgent("taxeAssureur", ucfirst($this->serviceTaxes->getNomTaxeAssureur()))
                ->setRequired(false)
                ->setDisabled(false)
                ->setColumns(12)
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setFormatValue(
                    function ($value, Revenu $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getTaxeAssureur() * 100)))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );
        
        //Revenu totale
        $this->addChamp(
            (new JSChamp())
                ->createArgent("revenuTotale", "Revenu TTC")
                ->setRequired(false)
                ->setDisabled(false)
                ->setColumns(12)
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setFormatValue(
                    function ($value, Revenu $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getRevenuTotale() * 100)))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );
        
        //Rétrocommission
        $this->addChamp(
            (new JSChamp())
                ->createArgent("retrocommissionTotale", "Rétrocom")
                ->setRequired(false)
                ->setDisabled(false)
                ->setColumns(12)
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setFormatValue(
                    function ($value, Revenu $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getRetrocommissionTotale() * 100)))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );
        
        //Réserve
        $this->addChamp(
            (new JSChamp())
                ->createArgent("reserve", "Réserve")
                ->setRequired(false)
                ->setDisabled(false)
                ->setColumns(12)
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setFormatValue(
                    function ($value, Revenu $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getReserve() * 100)))
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
                ->createTexte("police", "Police d'assurance")
                ->setRequired(false)
                ->setDisabled(false)
                ->setColumns(12)
                ->setFormatValue(
                    function ($value, Revenu $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $objet->getPolice()->getReference()))
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
                ->setRequired(false)
                ->setDisabled(false)
                ->setColumns(12)
                ->setFormatValue(
                    function ($value, Revenu $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $objet->getClient()))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );
        
        //Produit
        $this->addChamp(
            (new JSChamp())
                ->createTexte("produit", "Couverture")
                ->setRequired(false)
                ->setDisabled(false)
                ->setColumns(12)
                ->setFormatValue(
                    function ($value, Revenu $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $objet->getProduit()))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );
        
        //Assureur
        $this->addChamp(
            (new JSChamp())
                ->createTexte("partenaire", "Partenaire")
                ->setRequired(false)
                ->setDisabled(false)
                ->setColumns(12)
                ->setFormatValue(
                    function ($value, Revenu $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $objet->getPartenaire()))
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
    }

    public function batchActions(?array $champs, ?string $type = null, ?string $pageName = null, $objetInstance = null, ?Crud $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array
    {
        return $champs;
    }
}
