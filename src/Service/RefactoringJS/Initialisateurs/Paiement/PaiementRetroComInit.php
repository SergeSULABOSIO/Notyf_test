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
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class PaiementRetroComInit extends AbstractPaiement
{
    public function __construct(
        private AdminUrlGenerator $adminUrlGenerator,
        private ServiceAvenant $serviceAvenant,
        private ServiceDates $serviceDates,
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
        return "Reglèment de la rétrocommission dûe au parténaire intermédiaire.";
    }
}
