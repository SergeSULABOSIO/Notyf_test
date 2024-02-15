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
use App\Service\ServiceMonnaie;
use App\Service\ServiceEntreprise;
use App\Service\ServiceCompteBancaire;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\Admin\FactureCrudController;
use App\Service\ServiceTaxes;

class FactureTaxeCourtierInit extends AbstractFacture
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
        return $tranche->getTaxeCourtierTotale();
    }
    public function getPosteSignedBy(): ?string
    {
        return "Direction financière";
    }
    public function getNomAbstract(): ?string
    {
        return $this->serviceTaxes->getTaxe(true)->getNom();
    }
    public function getTypeFacture(): ?string
    {
        return FactureCrudController::TYPE_FACTURE_NOTE_DE_PERCEPTION_ARCA;
    }
}
