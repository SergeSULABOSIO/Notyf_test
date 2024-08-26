<?php

namespace App\Service\RefactoringJS\JSUIComponents\Cotation;

use App\Entity\Cotation;
use App\Service\ServiceTaxes;
use App\Service\ServiceMonnaie;
use Doctrine\ORM\EntityManager;
use App\Controller\Admin\CotationCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use App\Controller\Admin\PreferenceCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSChamp;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSPanelRenderer;

// namespace App\Service\RefactoringJS\JSUIComponents\Facture;

// use App\Entity\Revenu;
// use App\Entity\Facture;
// use App\Entity\Monnaie;
// use App\Entity\Tranche;
// use App\Service\ServiceTaxes;
// use App\Service\ServiceMonnaie;
// use Doctrine\ORM\EntityManager;
// use App\Controller\Admin\RevenuCrudController;
// use App\Controller\Admin\FactureCrudController;
// use App\Controller\Admin\PaiementCrudController;
// use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
// use App\Controller\Admin\UtilisateurCrudController;
// use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
// use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSChamp;
// use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSPanelRenderer;
// use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSCssHtmlDecoration;

class CotationDetailsRenderer extends JSPanelRenderer
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
        //Id
        $this->addChamp(
            (new JSChamp())
                ->createTexte("id", PreferenceCrudController::PREF_CRM_COTATION_ID)
                ->setColumns(10)
                ->getChamp()
        );
        //validated
        $this->addChamp(
            (new JSChamp())
                ->createTexte("validated", "Status")
                ->setColumns(10)
                ->setChoices(CotationCrudController::TAB_TYPE_RESULTAT)
                ->getChamp()
        );
        //polices
        $this->addChamp(
            (new JSChamp())
                ->createTableau("polices", "Polices")
                ->setTemplatePath('admin/segment/view_polices.html.twig')
                ->getChamp()
        );
        //Nom
        $this->addChamp(
            (new JSChamp())
                ->createTexte("nom", PreferenceCrudController::PREF_CRM_COTATION_NOM)
                ->setColumns(10)
                ->getChamp()
        );
        //Durée de la couverture
        $this->addChamp(
            (new JSChamp())
                ->createNombre("dureeCouverture", PreferenceCrudController::PREF_CRM_COTATION_DUREE)
                ->setColumns(10)
                ->setFormatValue(
                    function ($value, Cotation $entity) {
                        return $value . " mois.";
                    }
                )
                ->getChamp()
        );
        //Client
        $this->addChamp(
            (new JSChamp())
                ->createAssociation('client', "Client")
                ->setColumns(10)
                ->getChamp()
        );
        //Assureur
        $this->addChamp(
            (new JSChamp())
                ->createAssociation('assureur', "Assureur")
                ->setColumns(10)
                ->getChamp()
        );
        //Piste
        $this->addChamp(
            (new JSChamp())
                ->createAssociation('piste', "Piste")
                ->setColumns(10)
                ->getChamp()
        );
        //Partenaire
        $this->addChamp(
            (new JSChamp())
                ->createAssociation('partenaire', "Partenaire")
                ->setColumns(10)
                ->getChamp()
        );
        //Revenus
        $this->addChamp(
            (new JSChamp())
                ->createTableau('revenus', "Revenus de courtage")
                ->setTemplatePath('admin/segment/view_revenus.html.twig')
                ->getChamp()
        );
        //Chargements
        $this->addChamp(
            (new JSChamp())
                ->createTableau('chargements', "Chargement")
                ->setTemplatePath('admin/segment/view_chargements.html.twig')
                ->getChamp()
        );









        //Destination
        // $this->addChamp(
        //     (new JSChamp())
        //         ->createChoix("destination", "Destination")
        //         ->setColumns(10)
        //         ->setChoices(FactureCrudController::TAB_DESTINATION)
        //         ->getChamp()
        // );

        //Description
        // $this->addChamp(
        //     (new JSChamp())
        //         ->createTexte("description", "Description")
        //         ->setColumns(10)
        //         ->setRequired(false)
        //         ->setDisabled(false)
        //         ->setFormatValue(
        //             function ($value, Facture $objet) {
        //                 /** @var JSCssHtmlDecoration */
        //                 $formatedHtml = (new JSCssHtmlDecoration("span", $value))
        //                     ->ajouterClasseCss($this->css_class_bage_ordinaire)
        //                     ->outputHtml();
        //                 return $formatedHtml;
        //             }
        //         )
        //         ->getChamp()
        // );

        //Rférence de la facture
        // $this->addChamp(
        //     (new JSChamp())
        //         ->createTexte("reference", "Référence")
        //         ->setColumns(10)
        //         ->setFormatValue(
        //             function ($value, Facture $objet) {
        //                 /** @var JSCssHtmlDecoration */
        //                 $formatedHtml = (new JSCssHtmlDecoration("span", $value))
        //                     ->ajouterClasseCss($this->css_class_bage_ordinaire)
        //                     ->outputHtml();
        //                 return $formatedHtml;
        //             }
        //         )
        //         ->getChamp()
        // );


        //Elements facture
        // $this->addChamp(
        //     (new JSChamp())
        //         ->createTableau("elementFactures", "Eléments facturés")
        //         ->setRequired(false)
        //         ->setDisabled(false)
        //         ->setColumns(10)
        //         ->getChamp()
        // );

        //Total Du
        // $this->addChamp(
        //     (new JSChamp())
        //         ->createArgent("totalDu", "Total Dû")
        //         ->setDisabled(false)
        //         ->setRequired(false)
        //         ->setColumns(10)
        //         ->setCurrency($this->serviceMonnaie->getCodeAffichage())
        //         ->setFormatValue(
        //             function ($value, Facture $objet) {
        //                 /** @var JSCssHtmlDecoration */
        //                 $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getTotalDu())))
        //                     ->ajouterClasseCss($this->css_class_bage_ordinaire)
        //                     ->outputHtml();
        //                 return $formatedHtml;
        //             }
        //         )
        //         ->getChamp()
        // );

        //Total Recu
        // $this->addChamp(
        //     (new JSChamp())
        //         ->createArgent("totalRecu", "Total Reçu")
        //         ->setDisabled(false)
        //         ->setRequired(false)
        //         ->setColumns(10)
        //         ->setCurrency($this->serviceMonnaie->getCodeAffichage())
        //         ->setFormatValue(
        //             function ($value, Facture $objet) {
        //                 /** @var JSCssHtmlDecoration */
        //                 $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getTotalRecu())))
        //                     ->ajouterClasseCss($this->css_class_bage_ordinaire)
        //                     ->outputHtml();
        //                 return $formatedHtml;
        //             }
        //         )
        //         ->getChamp()
        // );

        //Total Solde
        // $this->addChamp(
        //     (new JSChamp())
        //         ->createArgent("totalSolde", "Solde à payer")
        //         ->setDisabled(true)
        //         ->setDisabled(true)
        //         ->setColumns(10)
        //         ->setCurrency($this->serviceMonnaie->getCodeAffichage())
        //         ->setFormatValue(
        //             function ($value, Facture $objet) {
        //                 /** @var JSCssHtmlDecoration */
        //                 $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getTotalSolde())))
        //                     ->ajouterClasseCss($this->css_class_bage_ordinaire)
        //                     ->outputHtml();
        //                 return $formatedHtml;
        //             }
        //         )
        //         ->getChamp()
        // );

        //Paiements
        // $this->addChamp(
        //     (new JSChamp())
        //         ->createTableau("paiements", "Paiements")
        //         ->setRequired(false)
        //         ->setDisabled(false)
        //         ->setColumns(10)
        //         ->getChamp()
        // );

        //Dernière modification
        // $this->addChamp(
        //     (new JSChamp())
        //         ->createDate("updatedAt", "D. Modification")
        //         ->setRequired(false)
        //         ->setDisabled(false)
        //         ->setColumns(10)
        //         ->setFormatValue(
        //             function ($value, Facture $objet) {
        //                 /** @var JSCssHtmlDecoration */
        //                 $formatedHtml = (new JSCssHtmlDecoration("span", $value))
        //                     ->ajouterClasseCss($this->css_class_bage_ordinaire)
        //                     ->outputHtml();
        //                 return $formatedHtml;
        //             }
        //         )
        //         ->getChamp()
        // );
    }

    public function batchActions(?array $champs, ?string $type = null, ?string $pageName = null, $objetInstance = null, ?Crud $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array
    {
        return $champs;
    }
}
