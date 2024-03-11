<?php

namespace App\Service\RefactoringJS\Initialisateurs\Paiement;

use DateTimeImmutable;
use App\Entity\Facture;
use App\Entity\DocPiece;
use App\Entity\Paiement;
use App\Entity\Entreprise;
use App\Entity\Utilisateur;
use App\Service\ServiceDates;
use App\Service\ServiceTaxes;
use App\Entity\CompteBancaire;
use App\Service\ServiceAvenant;
use App\Service\ServiceCrossCanal;
use App\Service\ServiceEntreprise;
use App\Service\ServiceCompteBancaire;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\Admin\FactureCrudController;
use App\Controller\Admin\PaiementCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class PaiementDGIInit extends AbstractPaiement
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
    
    public function getDestination(): ?string
    {
        return FactureCrudController::DESTINATION_DGI;
    }

    public function getNomAbstract(): ?string
    {
        $taxe = $this->serviceTaxes->getTaxe(false);
        return "Reglèment de la taxes " .$taxe->getNom() . " pour " . $taxe->getOrganisation() . ".";
    }
}
