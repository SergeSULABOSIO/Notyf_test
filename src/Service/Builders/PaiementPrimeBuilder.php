<?php

namespace App\Service\Builders;

use App\Entity\Police;
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
use App\Service\Builders\FactureBuilder;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\Admin\FactureCrudController;
use App\Controller\Admin\PaiementCrudController;
use App\Entity\CompteBancaire;
use App\Entity\DocPiece;
use App\Entity\Paiement;
use App\Service\ServiceCompteBancaire;
use PhpParser\Node\Expr\Cast\Array_;

class PaiementPrimeBuilder implements PaiementBuilder
{
    // private ?Police $police;
    private ?Facture $facture;
    private ?Paiement $paiement;

    public function __construct(
        private ServiceAvenant $serviceAvenant,
        private ServiceDates $serviceDates,
        private ServiceEntreprise $serviceEntreprise,
        private EntityManagerInterface $entityManager,
        private ServiceCompteBancaire $serviceCompteBancaire
    ) {
        $this->paiement = new Paiement();
    }

    public function setPaidAt(?DateTimeImmutable $paidAt)
    {
        $this->paiement->setPaidAt($paidAt);
    }

    public function setMontant(?float $paidAmount)
    {
        $this->paiement->setMontant($paidAmount);
    }

    public function setDescription(?string $description)
    {
        $this->paiement->setDescription($description);
    }

    public function setFacture(?Facture $facture)
    {
        $this->paiement->setFacture($facture);
    }

    public function setEntreprise(?Entreprise $entreprise)
    {
        $this->paiement->setEntreprise($entreprise);
    }

    public function setUtilisateur(?Utilisateur $utilisateur)
    {
        $this->paiement->setUtilisateur($utilisateur);
    }

    public function setCreatedAt(?DateTimeImmutable $dateCreation)
    {
        $this->paiement->setCreatedAt($dateCreation);
    }

    public function setUpdatedAt(?DateTimeImmutable $dateModification)
    {
        $this->paiement->setUpdatedAt($dateModification);
    }

    public function setCompteBancaire(?CompteBancaire $compteBancaire)
    {
        $this->paiement->setCompteBancaire($compteBancaire);
    }

    public function addDocument(?DocPiece $document)
    {
        $this->paiement->addDocument($document);
    }

    public function setType(?int $typePaiement)
    {
        $this->paiement->setType($typePaiement);
    }

    public function setTypeFacture(?int $typeFacture)
    {
        $this->paiement->setTypeFacture($typeFacture);
    }

    public function reset()
    {
        $this->paiement = null;
    }

    public function buildPaiement(?Facture $facture, ?DateTimeImmutable $dateOfPayment, ?Utilisateur $utilisateur, ?float $paidAmount): ?Paiement
    {
        $this->paiement->setFacture($facture);
        $this->paiement->setPaidAt($dateOfPayment);
        $this->paiement->setMontant($paidAmount);
        $this->paiement->setType(PaiementCrudController::TAB_TYPE_PAIEMENT[PaiementCrudController::TYPE_PAIEMENT_ENTREE]);
        $this->paiement->setTypeFacture($facture->getType());
        $this->paiement->setDescription("Paiement de la prime. Facture n°" . $facture.". Versement effectué le " . $this->serviceDates->getTexte($dateOfPayment));
        $this->paiement->setEntreprise($facture->getEntreprise());
        $this->paiement->setUtilisateur($facture->getUtilisateur());
        $this->paiement->setCreatedAt($this->serviceDates->aujourdhui());
        $this->paiement->setUpdatedAt($this->serviceDates->aujourdhui());
        // dd("Fonction pas encore définie");
        return $this->paiement;
    }

    public function loadSavedPaiements(?Facture $facture): ?array
    {
        // dd("Fonction non encore définie");
        $tabPaiements = [];
        if (count($facture->getPaiements()) != 0) {
            /** @var Paiement */
            foreach ($facture->getPaiements() as $paiement) {
                $paiementEnregistre = $this->entityManager->getRepository(Paiement::class)->find($fact->getFacture()->getId());
                if ($factureEnregistree != null) {
                    $tabFactures[] = $factureEnregistree;
                }
            }
        }
        return $tabFactures;
    }

    public function savePaiement()
    {
        dd("Fonction non encore définie");
    }

    public function areEqual(?array $anciennesPaiements, ?Paiement $nouveauPaiement)
    {
        dd("Fonction non encore définie");
    }
}
