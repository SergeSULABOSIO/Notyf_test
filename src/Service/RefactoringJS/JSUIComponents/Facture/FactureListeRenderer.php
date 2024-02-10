<?php

namespace App\Service\RefactoringJS\JSUIComponents\Tranche;

use App\Entity\Revenu;
use App\Entity\Facture;
use App\Entity\Tranche;
use App\Service\ServiceTaxes;
use App\Service\ServiceMonnaie;
use Doctrine\ORM\EntityManager;
use App\Controller\Admin\RevenuCrudController;
use App\Controller\Admin\FactureCrudController;
use App\Controller\Admin\PaiementCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use App\Controller\Admin\UtilisateurCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSPanelRenderer;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSCssHtmlDecoration;

class FactureListeRenderer extends JSPanelRenderer
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
        dd("Ici");
        //Status
        $this->addChampChoix(
            null,
            "status",
            "Status",
            false,
            false,
            10,
            FactureCrudController::TAB_STATUS_FACTURE,
            [
                FactureCrudController::TAB_STATUS_FACTURE[FactureCrudController::STATUS_FACTURE_SOLDEE] => 'success', //info
                FactureCrudController::TAB_STATUS_FACTURE[FactureCrudController::STATUS_FACTURE_IMPAYEE] => 'danger', //info
                FactureCrudController::TAB_STATUS_FACTURE[FactureCrudController::STATUS_FACTURE_ENCOURS] => 'info', //info
            ]
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
        //Elements facture
        $this->addChampNombre(
            null,
            "elementFactures",
            "Eléments facturés",
            false,
            false,
            10,
            function ($value, Facture $entity) {
                return count($entity->getElementFactures()) == 0 ? "Aucun élément" : count($entity->getElementFactures()) . " élement(s).";
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
        //Total Du
        $this->addChampArgent(
            null,
            "totalDu",
            "Total Dû",
            false,
            false,
            10,
            $this->serviceMonnaie->getCodeAffichage(),
            function ($value, Facture $objet) {
                /** @var JSCssHtmlDecoration */
                $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getTotalDu())))
                    ->ajouterClasseCss($this->css_class_bage_ordinaire)
                    ->outputHtml();
                return $formatedHtml;
            }
        );
        //Total Recu
        $this->addChampArgent(
            null,
            "totalRecu",
            "Total Reçu",
            false,
            false,
            10,
            $this->serviceMonnaie->getCodeAffichage(),
            function ($value, Facture $objet) {
                /** @var JSCssHtmlDecoration */
                $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getTotalRecu())))
                    ->ajouterClasseCss($this->css_class_bage_ordinaire)
                    ->outputHtml();
                return $formatedHtml;
            }
        );
        //Total Solde
        $this->addChampArgent(
            null,
            "totalSolde",
            "Total Solde",
            false,
            false,
            10,
            $this->serviceMonnaie->getCodeAffichage(),
            function ($value, Facture $objet) {
                /** @var JSCssHtmlDecoration */
                $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getTotalSolde())))
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
            function ($value, Facture $objet) {
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
