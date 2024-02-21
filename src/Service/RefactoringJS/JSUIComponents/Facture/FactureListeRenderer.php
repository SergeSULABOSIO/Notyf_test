<?php

namespace App\Service\RefactoringJS\JSUIComponents\Facture;

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
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSChamp;
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
        //Status
        $this->addChamp(
            (new JSChamp())
                ->createChoix("status", "Status")
                ->setRequired(false)
                ->setDisabled(false)
                ->setColumns(10)
                ->setChoices(FactureCrudController::TAB_STATUS_FACTURE)
                ->renderAsBadges([
                    FactureCrudController::TAB_STATUS_FACTURE[FactureCrudController::STATUS_FACTURE_SOLDEE] => 'success', //info
                    FactureCrudController::TAB_STATUS_FACTURE[FactureCrudController::STATUS_FACTURE_IMPAYEE] => 'danger', //info
                    FactureCrudController::TAB_STATUS_FACTURE[FactureCrudController::STATUS_FACTURE_ENCOURS] => 'info', //info
                ])
                ->getChamp()
        );
        // $this->addChampChoix(
        //     null,
        //     "status",
        //     "Status",
        //     false,
        //     false,
        //     10,
        //     FactureCrudController::TAB_STATUS_FACTURE,
        //     [
        //         FactureCrudController::TAB_STATUS_FACTURE[FactureCrudController::STATUS_FACTURE_SOLDEE] => 'success', //info
        //         FactureCrudController::TAB_STATUS_FACTURE[FactureCrudController::STATUS_FACTURE_IMPAYEE] => 'danger', //info
        //         FactureCrudController::TAB_STATUS_FACTURE[FactureCrudController::STATUS_FACTURE_ENCOURS] => 'info', //info
        //     ]
        // );

        //Rférence de la facture
        $this->addChamp(
            (new JSChamp())
                ->createTexte("reference", "Référence")
                ->setRequired(false)
                ->setDisabled(false)
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
        // $this->addChampTexte(
        //     null,
        //     "reference",
        //     "Référence",
        //     false,
        //     false,
        //     10,
        //     function ($value, Facture $objet) {
        //         /** @var JSCssHtmlDecoration */
        //         $formatedHtml = (new JSCssHtmlDecoration("span", $value))
        //             ->ajouterClasseCss($this->css_class_bage_ordinaire)
        //             ->outputHtml();
        //         return $formatedHtml;
        //     }
        // );

        //Type
        $this->addChamp(
            (new JSChamp())
                ->createChoix("type", "Type de facture")
                ->setRequired(false)
                ->setDisabled(false)
                ->setColumns(10)
                ->setChoices(FactureCrudController::TAB_TYPE_FACTURE)
                ->getChamp()
        );
        // $this->addChampChoix(
        //     null,
        //     "type",
        //     "Type de facture",
        //     false,
        //     false,
        //     10,
        //     FactureCrudController::TAB_TYPE_FACTURE,
        //     null
        // );

        //Elements facture
        $this->addChamp(
            (new JSChamp())
                ->createTableau("elementFactures", "Eléments facturés")
                ->setRequired(false)
                ->setDisabled(false)
                ->setColumns(10)
                ->setFormatValue(
                    function ($value, Facture $entity) {
                        return count($entity->getElementFactures()) == 0 ? "Aucun élément" : count($entity->getElementFactures()) . " élement(s).";
                    }
                )
                ->getChamp()
        );
        // $this->addChampAssociation(
        //     null,
        //     "elementFactures",
        //     "Eléments facturés",
        //     false,
        //     false,
        //     10,
        //     null,
        //     function ($value, Facture $entity) {
        //         return count($entity->getElementFactures()) == 0 ? "Aucun élément" : count($entity->getElementFactures()) . " élement(s).";
        //     }
        // );

        //Description
        $this->addChamp(
            (new JSChamp())
                ->createTexte("description", "Description")
                ->setColumns(10)
                ->setRequired(false)
                ->setDisabled(false)
                ->setFormatValue(
                    function ($value, Facture $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", substr($objet->getDescription(), 0, 50) . "..."))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );
        // $this->addChampTexte(
        //     null,
        //     "description",
        //     "Description",
        //     false,
        //     false,
        //     10,
        //     null
        // );
        //Total Du
        $this->addChamp(
            (new JSChamp())
                ->createArgent("totalDu", "Total Dû")
                ->setDisabled(false)
                ->setRequired(false)
                ->setColumns(10)
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setFormatValue(
                    function ($value, Facture $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getTotalDu())))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );
        // $this->addChampArgent(
        //     null,
        //     "totalDu",
        //     "Total Dû",
        //     false,
        //     false,
        //     10,
        //     $this->serviceMonnaie->getCodeAffichage(),
        //     function ($value, Facture $objet) {
        //         /** @var JSCssHtmlDecoration */
        //         $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getTotalDu())))
        //             ->ajouterClasseCss($this->css_class_bage_ordinaire)
        //             ->outputHtml();
        //         return $formatedHtml;
        //     }
        // );
        //Total Recu
        $this->addChamp(
            (new JSChamp())
                ->createArgent("totalRecu", "Total Reçu")
                ->setDisabled(false)
                ->setRequired(false)
                ->setColumns(10)
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setFormatValue(
                    function ($value, Facture $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getTotalRecu())))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );
        // $this->addChampArgent(
        //     null,
        //     "totalRecu",
        //     "Total Reçu",
        //     false,
        //     false,
        //     10,
        //     $this->serviceMonnaie->getCodeAffichage(),
        //     function ($value, Facture $objet) {
        //         /** @var JSCssHtmlDecoration */
        //         $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getTotalRecu())))
        //             ->ajouterClasseCss($this->css_class_bage_ordinaire)
        //             ->outputHtml();
        //         return $formatedHtml;
        //     }
        // );
        //Total Solde
        $this->addChamp(
            (new JSChamp())
                ->createArgent("totalSolde", "Solde à payer")
                ->setDisabled(true)
                ->setDisabled(true)
                ->setColumns(10)
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setFormatValue(
                    function ($value, Facture $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getTotalSolde())))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );
        // $this->addChampArgent(
        //     null,
        //     "totalSolde",
        //     "Total Solde",
        //     false,
        //     false,
        //     10,
        //     $this->serviceMonnaie->getCodeAffichage(),
        //     function ($value, Facture $objet) {
        //         /** @var JSCssHtmlDecoration */
        //         $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getTotalSolde())))
        //             ->ajouterClasseCss($this->css_class_bage_ordinaire)
        //             ->outputHtml();
        //         return $formatedHtml;
        //     }
        // );
        //Dernière modification
        $this->addChamp(
            (new JSChamp())
                ->createDate("updatedAt", "D. Modification")
                ->setRequired(false)
                ->setDisabled(false)
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
        // $this->addChampDate(
        //     null,
        //     "updatedAt",
        //     "D. Modification",
        //     false,
        //     false,
        //     10,
        //     function ($value, Facture $objet) {
        //         /** @var JSCssHtmlDecoration */
        //         $formatedHtml = (new JSCssHtmlDecoration("span", $value))
        //             ->ajouterClasseCss($this->css_class_bage_ordinaire)
        //             ->outputHtml();
        //         return $formatedHtml;
        //     }
        // );
    }

    public function batchActions(?array $champs, ?string $type = null, ?string $pageName = null, $objetInstance = null, ?Crud $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array
    {
        return $champs;
    }
}
