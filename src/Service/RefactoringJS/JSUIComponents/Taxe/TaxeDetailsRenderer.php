<?php

namespace App\Service\RefactoringJS\JSUIComponents\Tranche;

use App\Entity\Taxe;
use App\Entity\Tranche;
use App\Service\ServiceTaxes;
use App\Service\ServiceMonnaie;
use Doctrine\ORM\EntityManager;
use App\Controller\Admin\TaxeCrudController;
use App\Controller\Admin\PaiementCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use App\Controller\Admin\UtilisateurCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSPanelRenderer;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSCssHtmlDecoration;

class TaxeDetailsRenderer extends JSPanelRenderer
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
            function ($value, Taxe $objet) {
                /** @var JSCssHtmlDecoration */
                $formatedHtml = (new JSCssHtmlDecoration("span", $value))
                    ->ajouterClasseCss($this->css_class_bage_ordinaire)
                    ->outputHtml();
                return $formatedHtml;
            }
        );
        //taux IARD
        $this->addChampPourcentage(
            null,
            "tauxIARD",
            "Taux (IARD/Non-Vie)",
            false,
            false,
            10,
            function ($value, Taxe $objet) {
                /** @var JSCssHtmlDecoration */
                $formatedHtml = (new JSCssHtmlDecoration("span", $value))
                    ->ajouterClasseCss($this->css_class_bage_ordinaire)
                    ->outputHtml();
                return $formatedHtml;
            }
        );
        //taux VIE
        $this->addChampPourcentage(
            null,
            "tauxVIE",
            "Taux (IARD/Non-Vie)",
            false,
            false,
            10,
            function ($value, Taxe $objet) {
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
            function ($value, Taxe $objet) {
                /** @var JSCssHtmlDecoration */
                $formatedHtml = (new JSCssHtmlDecoration("span", $value))
                    ->ajouterClasseCss($this->css_class_bage_ordinaire)
                    ->outputHtml();
                return $formatedHtml;
            }
        );
        //Organisation
        $this->addChampTexte(
            null,
            "organisation",
            "Organisation",
            false,
            false,
            10,
            function ($value, Taxe $objet) {
                /** @var JSCssHtmlDecoration */
                $formatedHtml = (new JSCssHtmlDecoration("span", $value))
                    ->ajouterClasseCss($this->css_class_bage_ordinaire)
                    ->outputHtml();
                return $formatedHtml;
            }
        );
        //Payable par
        $this->addChampChoix(
            null,
            "payableparcourtier",
            "Par courtier?",
            false,
            false,
            10,
            TaxeCrudController::TAB_TAXE_PAYABLE_PAR_COURTIER,
            null
        );
        //Utilisateur
        $this->addChampAssociation(
            UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE],
            "utilisateur",
            "Utilisateur",
            false,
            false,
            10,
            function ($value, Taxe $objet) {
                /** @var JSCssHtmlDecoration */
                $formatedHtml = (new JSCssHtmlDecoration("span", $value))
                    ->ajouterClasseCss($this->css_class_bage_ordinaire)
                    ->outputHtml();
                return $formatedHtml;
            }
        );
        //Date creation
        $this->addChampDate(
            null,
            "createdAt",
            "D. Création",
            false,
            false,
            10,
            function ($value, Taxe $objet) {
                /** @var JSCssHtmlDecoration */
                $formatedHtml = (new JSCssHtmlDecoration("span", $value))
                    ->ajouterClasseCss($this->css_class_bage_ordinaire)
                    ->outputHtml();
                return $formatedHtml;
            }
        );
        //Date modification
        $this->addChampDate(
            null,
            "updatedAt",
            "D. Modification",
            false,
            false,
            10,
            function ($value, Taxe $objet) {
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
