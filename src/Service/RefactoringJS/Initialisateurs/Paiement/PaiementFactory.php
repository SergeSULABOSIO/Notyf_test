<?php

namespace App\Service\RefactoringJS\Initialisateurs\Paiement;

use DateTimeImmutable;
use App\Entity\Facture;
use App\Entity\DocPiece;
use App\Entity\Paiement;
use App\Entity\Entreprise;
use App\Entity\Utilisateur;
use App\Service\ServiceDates;
use App\Entity\CompteBancaire;
use App\Service\ServiceAvenant;
use App\Service\ServiceCrossCanal;
use App\Service\ServiceEntreprise;
use App\Service\ServiceCompteBancaire;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\Admin\PaiementCrudController;
use App\Service\ServiceTaxes;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class PaiementFactory
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
        
    }

    public function createPaiementARCA():PaiementARCAInit{
        return new PaiementARCAInit(
            $this->adminUrlGenerator,
            $this->serviceAvenant,
            $this->serviceDates,
            $this->serviceTaxes,
            $this->serviceEntreprise,
            $this->entityManager,
            $this->serviceCompteBancaire
        );
    }

    public function createPaiementDGI():PaiementDGIInit{
        return new PaiementDGIInit(
            $this->adminUrlGenerator,
            $this->serviceAvenant,
            $this->serviceDates,
            $this->serviceTaxes,
            $this->serviceEntreprise,
            $this->entityManager,
            $this->serviceCompteBancaire
        );
    }

    public function createPaiementClient():PaiementClientInit{
        return new PaiementClientInit(
            $this->adminUrlGenerator,
            $this->serviceAvenant,
            $this->serviceDates,
            $this->serviceTaxes,
            $this->serviceEntreprise,
            $this->entityManager,
            $this->serviceCompteBancaire
        );
    }

    public function createPaiementPartenaire():PaiementPartenaireInit{
        return new PaiementPartenaireInit(
            $this->adminUrlGenerator,
            $this->serviceAvenant,
            $this->serviceDates,
            $this->serviceTaxes,
            $this->serviceEntreprise,
            $this->entityManager,
            $this->serviceCompteBancaire
        );
    }

    public function createPaiementAssureur():PaiementAssureurInit{
        return new PaiementAssureurInit(
            $this->adminUrlGenerator,
            $this->serviceAvenant,
            $this->serviceDates,
            $this->serviceTaxes,
            $this->serviceEntreprise,
            $this->entityManager,
            $this->serviceCompteBancaire
        );
    }
}
