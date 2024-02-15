<?php

namespace App\Service\RefactoringJS\Initialisateurs\Paiement;

use App\Service\ServiceDates;
use App\Service\ServiceAvenant;
use App\Service\ServiceEntreprise;
use App\Service\ServiceCompteBancaire;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\Admin\PaiementCrudController;
use App\Service\ServiceTaxes;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class PaiementTaxeAssureurInit extends AbstractPaiement
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
        return PaiementCrudController::TYPE_PAIEMENT_SORTIE;
    }

    public function getNomAbstract(): ?string
    {
        return "Reglèment de ".$this->serviceTaxes->getNomTaxeAssureur()." (dû à " . $this->serviceTaxes->getTaxe(false)->getOrganisation() . ").";
    }
}
