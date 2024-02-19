<?php

namespace App\Service\RefactoringJS\JSUIComponents\Taxe;

use App\Entity\Taxe;
use App\Entity\Tranche;
use App\Service\ServiceTaxes;
use App\Service\ServiceMonnaie;
use Doctrine\ORM\EntityManager;
use App\Controller\Admin\TaxeCrudController;
use App\Controller\Admin\PaiementCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use App\Controller\Admin\UtilisateurCrudController;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSChamp;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSPanelRenderer;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSCssHtmlDecoration;

class TaxeFormRenderer extends JSPanelRenderer
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
        parent::__construct(self::TYPE_FORMULAIRE, $pageName, $objetInstance, $crud, $adminUrlGenerator);
    }

    public function design()
    {
        //Nom
        $this->addChamp(
            (new JSChamp())
                ->createTexte("nom", "Intitulé")
                ->setColumns(12)
                ->getChamp()
        );

        //taux IARD
        $this->addChamp(
            (new JSChamp())
                ->createPourcentage("tauxIARD", "Taux (IARD/Non-Vie)")
                ->setColumns(12)
                ->getChamp()
        );

        //taux VIE
        $this->addChamp(
            (new JSChamp())
                ->createPourcentage("tauxVIE", "Taux (IARD/Non-Vie)")
                ->getChamp()
        );

        //Description
        $this->addChamp(
            (new JSChamp())
                ->createEditeurTexte("description", "Description")
                ->setColumns(12)
                ->getChamp()
        );
        
        //Payable par courtier?
        $this->addChamp(
            (new JSChamp())
                ->createChoix("payableparcourtier", "Par courtier?")
                ->setColumns(12)
                ->setChoices(TaxeCrudController::TAB_TAXE_PAYABLE_PAR_COURTIER)
                ->getChamp()
        );
    }

    public function batchActions(?array $champs, ?string $type = null, ?string $pageName = null, $objetInstance = null, ?Crud $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array
    {
        return $champs;
    }
}
