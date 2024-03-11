<?php

namespace App\Service\RefactoringJS\Initialisateurs\Paiement;

use App\Service\ServiceDates;
use App\Service\ServiceTaxes;
use App\Service\ServiceAvenant;
use App\Service\ServiceEntreprise;
use App\Service\ServiceCompteBancaire;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\Admin\FactureCrudController;
use App\Controller\Admin\PaiementCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class PaiementAssureurInit extends AbstractPaiement
{
    public function __construct(
        private AdminUrlGenerator $adminUrlGenerator,
        private ServiceAvenant $serviceAvenant,
        private ServiceDates $serviceDates,
        private ServiceTaxes $serviceTaxes,
        private ServiceEntreprise $serviceEntreprise,
        private EntityManagerInterface $entityManager,
        private ServiceCompteBancaire $serviceCompteBancaire
    ) {
        parent::__construct(
            $adminUrlGenerator,
            $serviceAvenant,
            $serviceDates,
            $serviceEntreprise,
            $entityManager,
            $serviceCompteBancaire
        );
    }

    public function getTypePaiement(): ?string
    {
        return PaiementCrudController::TYPE_PAIEMENT_ENTREE;
    }
    
    public function getDestination(): ?string
    {
        return FactureCrudController::DESTINATION_ASSUREUR;
    }

    public function getNomAbstract(): ?string
    {
        return "Reglèment des commissons ou autres revenus de courtage.";
    }
}
