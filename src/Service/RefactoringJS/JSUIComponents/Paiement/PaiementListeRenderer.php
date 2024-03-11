<?php

namespace App\Service\RefactoringJS\JSUIComponents\Paiement;

use App\Entity\Paiement;
use App\Service\ServiceMonnaie;
use Doctrine\ORM\EntityManager;
use App\Controller\Admin\FactureCrudController;
use App\Controller\Admin\PaiementCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use App\Controller\Admin\UtilisateurCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSChamp;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSPanelRenderer;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSCssHtmlDecoration;

class PaiementListeRenderer extends JSPanelRenderer
{
    public function __construct(
        private EntityManager $entityManager,
        private ServiceMonnaie $serviceMonnaie,
        string $pageName,
        $objetInstance,
        $crud,
        AdminUrlGenerator $adminUrlGenerator
    ) {
        parent::__construct(self::TYPE_LISTE, $pageName, $objetInstance, $crud, $adminUrlGenerator);
    }

    public function design()
    {
        //Type
        $this->addChamp(
            (new JSChamp())
                ->createChoix("type", "Mouvement")
                ->setRequired(true)
                ->setDisabled(true)
                ->setColumns(10)
                ->setChoices(PaiementCrudController::TAB_TYPE_PAIEMENT)
                ->renderAsBadges(
                    [
                        PaiementCrudController::TAB_TYPE_PAIEMENT[PaiementCrudController::TYPE_PAIEMENT_ENTREE] => 'success', //info
                        PaiementCrudController::TAB_TYPE_PAIEMENT[PaiementCrudController::TYPE_PAIEMENT_SORTIE] => 'danger', //info
                        PaiementCrudController::TAB_TYPE_PAIEMENT[PaiementCrudController::TYPE_PAIEMENT_AUCUN] => 'dark', //info
                    ]
                )
                ->getChamp()
        );

        //Destination
        $this->addChamp(
            (new JSChamp())
                ->createChoix("destination", "Destination")
                ->setRequired(true)
                ->setDisabled(true)
                ->setColumns(10)
                ->setChoices(FactureCrudController::TAB_DESTINATION)
                ->getChamp()
        );
        
        //Type Facture
        // $this->addChamp(
        //     (new JSChamp())
        //         ->createChoix("typeFacture", "Type de note")
        //         ->setRequired(true)
        //         ->setDisabled(true)
        //         ->setColumns(10)
        //         ->setChoices(FactureCrudController::TAB_TYPE_NOTE)
        //         ->getChamp()
        // );
        
        //Montant
        $this->addChamp(
            (new JSChamp())
                ->createArgent("montant", "Montant")
                ->setColumns(12)
                ->setRequired(true)
                ->setDisabled(false)
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setFormatValue(
                    function ($value, Paiement $paiement) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($paiement->getMontant())))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );
        
        //Paid at
        $this->addChamp(
            (new JSChamp())
                ->createDate("paidAt", "Date de paiement")
                ->setRequired(false)
                ->setDisabled(false)
                ->setColumns(10)
                ->setFormatValue(
                    function ($value, Paiement $paiement) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $value))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );
        
        //Facture
        $this->addChamp(
            (new JSChamp())
                ->createAssociation("facture", "Facture")
                ->setColumns(12)
                ->getChamp()
        );
        
        //Utilisateur
        $this->addChamp(
            (new JSChamp())
                ->createAssociation("utilisateur", "Utilisateur")
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
                ->setRequired(false)
                ->setDisabled(false)
                ->setColumns(12)
                ->getChamp()
        );
        
        //Entreprise
        $this->addChamp(
            (new JSChamp())
                ->createAssociation("entreprise", "Entreprise")
                ->setRequired(false)
                ->setDisabled(false)
                ->setColumns(12)
                ->getChamp()
        );
    }

    public function batchActions(?array $champs, ?string $type = null, ?string $pageName = null, $objetInstance = null, ?Crud $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array
    {
        return $champs;
    }
}
