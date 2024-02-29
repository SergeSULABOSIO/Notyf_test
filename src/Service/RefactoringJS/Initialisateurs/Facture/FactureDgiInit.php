<?php

namespace App\Service\RefactoringJS\Initialisateurs\Facture;

use App\Entity\Tranche;
use App\Service\ServiceDates;
use App\Service\ServiceAvenant;
use App\Service\ServiceEntreprise;
use App\Service\ServiceCompteBancaire;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\Admin\FactureCrudController;
use App\Service\ServiceTaxes;

class FactureDgiInit extends AbstractFacture
{
    public function __construct(
        private ServiceAvenant $serviceAvenant,
        private ServiceDates $serviceDates,
        private ServiceTaxes $serviceTaxes,
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
        return (
            $tranche->getTaxeAssureurTotale()
        );
    }
    public function getPosteSignedBy(): ?string
    {
        return "Direction financière";
    }
    public function getNomAbstract(): ?string
    {
        /** @var Taxe */
        $taxe = $this->serviceTaxes->getTaxe(false);
        return $taxe->getNom() . " pour " . $taxe->getOrganisation();
    }
    public function getDestinationFacture(): ?string
    {
        return FactureCrudController::DESTINATION_DGI;
    }
}
