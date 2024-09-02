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
        private string $pageName,
        private $objetInstance,
        private $crud,
        private AdminUrlGenerator $adminUrlGenerator
    ) {
        parent::__construct(self::TYPE_FORMULAIRE, $pageName, $objetInstance, $crud, $adminUrlGenerator);
    }

    public function design()
    {
        $column = 12;
        if ($this->objetInstance instanceof Taxe) {
            $column = 10;
        }

        //Section - Principale
        $this->addChamp(
            (new JSChamp())
                ->createSection("Informations générales")
                ->setIcon('fa-solid fa-cash-register') //<i class="fa-sharp fa-solid fa-address-book"></i>
                ->setHelp("Veuillez saisir les informations relatives au paiement.")
                ->setColumns($column)
                ->getChamp()
        );

        //Nom
        $this->addChamp(
            (new JSChamp())
                ->createTexte("nom", "Intitulé")
                ->setColumns($column)
                ->getChamp()
        );

        //taux IARD
        $this->addChamp(
            (new JSChamp())
                ->createPourcentage("tauxIARD", "Taux (IARD/Non-Vie)")
                ->setColumns($column)
                ->getChamp()
        );

        //taux VIE
        $this->addChamp(
            (new JSChamp())
                ->createPourcentage("tauxVIE", "Taux (IARD/Non-Vie)")
                ->setColumns($column)
                ->getChamp()
        );

        //Description
        $this->addChamp(
            (new JSChamp())
                ->createEditeurTexte("description", "Description")
                ->setColumns($column)
                ->getChamp()
        );

        //Payable par courtier?
        $this->addChamp(
            (new JSChamp())
                ->createChoix("payableparcourtier", "Par courtier?")
                ->setColumns($column)
                ->setChoices(TaxeCrudController::TAB_TAXE_PAYABLE_PAR_COURTIER)
                ->getChamp()
        );
    }

    public function batchActions(?array $champs, ?string $type = null, ?string $pageName = null, $objetInstance = null, ?Crud $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array
    {
        return $champs;
    }
}
