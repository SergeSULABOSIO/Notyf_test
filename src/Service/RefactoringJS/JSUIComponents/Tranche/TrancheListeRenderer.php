<?php

namespace App\Service\RefactoringJS\JSUIComponents\Paiement;

use App\Entity\Tranche;
use App\Service\ServiceMonnaie;
use Doctrine\ORM\EntityManager;
use App\Controller\Admin\PaiementCrudController;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSCssHtmlDecoration;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSPanelRenderer;

class TrancheListeRenderer extends JSPanelRenderer
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
        //Nom
        $this->addChampTexte(
            null,
            "nom",
            "Intitulé",
            false,
            false,
            10,
            function ($value, Tranche $tranche) {
                /** @var JSCssHtmlDecoration */
                $formatedHtml = (new JSCssHtmlDecoration("span", $value))
                    ->ajouterClasseCss("badge badge-light text-bold")
                    ->outputHtml();
                return $formatedHtml;
            }
        );
        //Validee?
        $this->addChampBooleen(
            null,
            "validated",
            "Validée",
            false,
            false,
            false
        );
        //Période
        $this->addChampTexte(
            null,
            "periodeValidite",
            "Durée",
            false,
            false,
            10,
            function ($value, Tranche $tranche) {
                /** @var JSCssHtmlDecoration */
                $formatedHtml = (new JSCssHtmlDecoration("span", $value))
                    ->ajouterClasseCss("badge badge-light text-bold")
                    ->outputHtml();
                return $formatedHtml;
            }
        );
        //Taux
        $this->addChampPourcentage(
            null,
            "taux",
            "Portion",
            false,
            false,
            10,
            function ($value, Tranche $tranche) {
                /** @var JSCssHtmlDecoration */
                $formatedHtml = (new JSCssHtmlDecoration("span", $value))
                    ->ajouterClasseCss("badge badge-light text-bold")
                    ->outputHtml();
                return $formatedHtml;
            }
        );
        //Prime Annuelle
        $this->addChampTableau(
            null,
            "premiumInvoiceDetails",
            "Prime totale",
            false,
            false,
            10,
            "admin/segment/index_tranche_status.html.twig"
        );
        Ici



        /*************** */
        $this->addChampChoix(
            null,
            "type",
            "Type",
            true,
            true,
            10,
            PaiementCrudController::TAB_TYPE_PAIEMENT,
            [
                PaiementCrudController::TAB_TYPE_PAIEMENT[PaiementCrudController::TYPE_PAIEMENT_ENTREE] => 'success', //info
                PaiementCrudController::TAB_TYPE_PAIEMENT[PaiementCrudController::TYPE_PAIEMENT_SORTIE] => 'danger', //info
            ]
        );
        $this->addChampArgent(
            null,
            "montant",
            "Montant",
            true,
            false,
            10,
            $this->serviceMonnaie->getCodeAffichage()
        );
        $this->addChampDate(
            null,
            "paidAt",
            "Date de paiement",
            false,
            false,
            10
        );
        $this->addChampAssociation(
            null,
            "facture",
            "Facture",
            false,
            false,
            10,
            null
        );
        $this->addChampAssociation(
            null,
            "utilisateur",
            "Utilisateur",
            false,
            false,
            10,
            null
        );
        $this->addChampAssociation(
            null,
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
