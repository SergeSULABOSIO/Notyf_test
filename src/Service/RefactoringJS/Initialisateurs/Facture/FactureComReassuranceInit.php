<?php

namespace App\Service\RefactoringJS\Initialisateurs\Facture;

use DateTimeImmutable;
use App\Entity\Facture;
use App\Entity\Tranche;
use App\Entity\Assureur;
use App\Entity\Entreprise;
use App\Entity\Partenaire;
use App\Entity\Utilisateur;
use App\Service\ServiceDates;
use App\Entity\ElementFacture;
use App\Service\ServiceAvenant;
use App\Service\ServiceEntreprise;
use App\Service\ServiceCompteBancaire;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\Admin\FactureCrudController;

class FactureComReassuranceInit extends AbstractFacture
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
        return $tranche->getComReassurance();
    }
    public function getPosteSignedBy(): ?string
    {
        return "Direction financière";
    }
    public function getNomAbstract(): ?string
    {
        return "Commission de réassurance";
    }
    public function getTypeFacture(): ?string
    {
        return FactureCrudController::TYPE_FACTURE_COMMISSION_REASSURANCE;
    }
}
