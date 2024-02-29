<?php

namespace App\Service\RefactoringJS\Initialisateurs\Facture;

use App\Entity\Tranche;
use App\Service\ServiceDates;
use App\Service\ServiceAvenant;
use App\Service\ServiceEntreprise;
use App\Service\ServiceCompteBancaire;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\Admin\FactureCrudController;

class FacturePartenaireInit extends AbstractFacture
{
    public function __construct(
        private ServiceAvenant $serviceAvenant,
        private ServiceDates $serviceDates,
        private ServiceEntreprise $serviceEntreprise,
        private EntityManagerInterface $entityManager,
        private ServiceCompteBancaire $serviceCompteBancaire
    ) {
        parent::__construct(
            $serviceAvenant,
            $serviceDates,
            $serviceEntreprise,
            $entityManager,
            $serviceCompteBancaire
        );
    }
    public function getTotalDu(?Tranche $tranche): ?float
    {
        return $tranche->getRetroCommissionTotale();
    }
    public function getPosteSignedBy(): ?string
    {
        return "Direction financière";
    }
    public function getNomAbstract(): ?string
    {
        return "Note de crédit pour rétrocéssion de la commission du partenaire";
    }
    public function getDestinationFacture(): ?string
    {
        return FactureCrudController::DESTINATION_PARTENAIRE;
    }
}
