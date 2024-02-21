<?php

namespace App\Service\RefactoringJS\JSUIComponents\ElementFacture;

use App\Service\ServiceTaxes;
use App\Entity\ElementFacture;
use App\Service\ServiceMonnaie;
use Doctrine\ORM\EntityManager;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use App\Controller\Admin\UtilisateurCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSChamp;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSPanelRenderer;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSCssHtmlDecoration;

class ElementFactureListeRenderer extends JSPanelRenderer
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
        //Police
        $this->addChamp(
            (new JSChamp())
                ->createAssociation("police", "Police")
                ->setRequired(false)
                ->setDisabled(false)
                ->setColumns(10)
                ->getChamp()
        );
        // $this->addChampAssociation(
        //     null,
        //     "police",
        //     "Police",
        //     false,
        //     false,
        //     10,
        //     null,
        //     null,
        //     null
        // );

        //Facture
        $this->addChamp(
            (new JSChamp())
                ->createAssociation("facture", "Facture")
                ->setRequired(false)
                ->setDisabled(false)
                ->setColumns(10)
                ->getChamp()
        );
        // $this->addChampAssociation(
        //     null,
        //     "facture",
        //     "Facture",
        //     false,
        //     false,
        //     10,
        //     null,
        //     null,
        //     null
        // );
        //Montant
        $this->addChamp(
            (new JSChamp())
                ->createArgent("montant", "Montant à payer")
                ->setRequired(false)
                ->setDisabled(false)
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
        // $this->addChampArgent(
        //     null,
        //     "montant",
        //     "Montant à payer",
        //     false,
        //     false,
        //     10,
        //     $this->serviceMonnaie->getCodeAffichage(),
        //     function ($value, ElementFacture $objet) {
        //         /** @var JSCssHtmlDecoration */
        //         $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getMontant())))
        //             ->ajouterClasseCss($this->css_class_bage_ordinaire)
        //             ->outputHtml();
        //         return $formatedHtml;
        //     }
        // );
        //Created At
        $this->addChamp(
            (new JSChamp())
                ->createDate("createdAt", "D. Création")
                ->setRequired(false)
                ->setDisabled(false)
                ->setColumns(10)
                ->setFormatValue(
                    function ($value, ElementFacture $objet) {
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
        //     "createdAt",
        //     "D. Création",
        //     false,
        //     false,
        //     10,
        //     function ($value, ElementFacture $objet) {
        //         /** @var JSCssHtmlDecoration */
        //         $formatedHtml = (new JSCssHtmlDecoration("span", $value))
        //             ->ajouterClasseCss($this->css_class_bage_ordinaire)
        //             ->outputHtml();
        //         return $formatedHtml;
        //     }
        // );

        //Edited At
        $this->addChamp(
            (new JSChamp())
                ->createDate("updatedAt", "D. Modification")
                ->setRequired(false)
                ->setDisabled(false)
                ->setColumns(10)
                ->setFormatValue(
                    function ($value, ElementFacture $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $value))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );
        
        //Utilisateur
        $this->addChamp(
            (new JSChamp())
                ->createAssociation("entreprise", "Entreprise")
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
                ->setRequired(false)
                ->setDisabled(false)
                ->setColumns(10)
                ->getChamp()
        );
        // $this->addChampAssociation(
        //     UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE],
        //     "utilisateur",
        //     "Utilisateur",
        //     false,
        //     false,
        //     10,
        //     null,
        //     null,
        //     null
        // );
        //Entreprise
        $this->addChamp(
            (new JSChamp())
                ->createAssociation("entreprise", "Entreprise")
                ->setRequired(false)
                ->setDisabled(false)
                ->setColumns(10)
                ->getChamp()
        );
        // $this->addChampAssociation(
        //     null,
        //     "entreprise",
        //     "Entreprise",
        //     false,
        //     false,
        //     10,
        //     null,
        //     null,
        //     null
        // );
    }

    public function batchActions(?array $champs, ?string $type = null, ?string $pageName = null, $objetInstance = null, ?Crud $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array
    {
        return $champs;
    }
}
