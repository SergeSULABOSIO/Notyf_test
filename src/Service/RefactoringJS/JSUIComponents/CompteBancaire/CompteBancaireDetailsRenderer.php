<?php

namespace App\Service\RefactoringJS\JSUIComponents\Tranche;

use App\Entity\Tranche;
use App\Service\ServiceTaxes;
use App\Service\ServiceMonnaie;
use Doctrine\ORM\EntityManager;
use App\Controller\Admin\PaiementCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use App\Controller\Admin\UtilisateurCrudController;
use App\Entity\CompteBancaire;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSPanelRenderer;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSCssHtmlDecoration;

class CompteBancaireDetailsRenderer extends JSPanelRenderer
{
    private ?string $twig_template_status_tranche = "admin/segment/index_tranche_status.html.twig";
    private ?string $css_class_bage_ordinaire = "badge badge-light text-bold";

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
        //Intitulé du compte
        $this->addChampTexte(
            null,
            "intitule",
            "Intitulé",
            false,
            false,
            10,
            function ($value, CompteBancaire $objet) {
                /** @var JSCssHtmlDecoration */
                $formatedHtml = (new JSCssHtmlDecoration("span", $value))
                    ->ajouterClasseCss($this->css_class_bage_ordinaire)
                    ->outputHtml();
                return $formatedHtml;
            }
        );
        //Numéro du compte
        $this->addChampTexte(
            null,
            "numero",
            "Numéro du compte",
            false,
            false,
            10,
            function ($value, CompteBancaire $objet) {
                /** @var JSCssHtmlDecoration */
                $formatedHtml = (new JSCssHtmlDecoration("span", $value))
                    ->ajouterClasseCss($this->css_class_bage_ordinaire)
                    ->outputHtml();
                return $formatedHtml;
            }
        );
        //Banque
        $this->addChampTexte(
            null,
            "banque",
            "Banque",
            false,
            false,
            10,
            function ($value, CompteBancaire $objet) {
                /** @var JSCssHtmlDecoration */
                $formatedHtml = (new JSCssHtmlDecoration("span", $value))
                    ->ajouterClasseCss($this->css_class_bage_ordinaire)
                    ->outputHtml();
                return $formatedHtml;
            }
        );
        //Code Swift
        $this->addChampTexte(
            null,
            "codeSwift",
            "Code Swift",
            false,
            false,
            10,
            function ($value, CompteBancaire $objet) {
                /** @var JSCssHtmlDecoration */
                $formatedHtml = (new JSCssHtmlDecoration("span", $value))
                    ->ajouterClasseCss($this->css_class_bage_ordinaire)
                    ->outputHtml();
                return $formatedHtml;
            }
        );
        //Code monnaie
        $this->addChampTexte(
            null,
            "codeMonnaie",
            "Devise",
            false,
            false,
            10,
            function ($value, CompteBancaire $objet) {
                /** @var JSCssHtmlDecoration */
                $formatedHtml = (new JSCssHtmlDecoration("span", $value))
                    ->ajouterClasseCss($this->css_class_bage_ordinaire)
                    ->outputHtml();
                return $formatedHtml;
            }
        );
        //Date de création
        $this->addChampDate(
            null,
            "createdAt",
            "D. Création",
            false,
            false,
            10,
            function ($value, CompteBancaire $objet) {
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
            function ($value, CompteBancaire $objet) {
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
            null
        );
        //Entreprise
        $this->addChampAssociation(
            UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE],
            "entreprise",
            "Entreprise",
            false,
            false,
            10,
            null
        );
    }

    public function batchActions(?array $champs, ?string $type = null, ?string $pageName = null, $objetInstance = null, ?Crud $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array
    {
        return $champs;
    }
}
