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

abstract class AbstractPaiement implements PaiementInit
{
    private ?Facture $facture;
    private ?Paiement $paiement;

    public function __construct(
        private AdminUrlGenerator $adminUrlGenerator,
        private ServiceAvenant $serviceAvenant,
        private ServiceDates $serviceDates,
        private ServiceEntreprise $serviceEntreprise,
        private EntityManagerInterface $entityManager,
        private ServiceCompteBancaire $serviceCompteBancaire
    ) {
        $this->paiement = new Paiement();
    }

    public abstract function getNomAbstract(): ?string;
    public abstract function getTypePaiement(): ?string;
    public abstract function getDestination(): ?string;

    public function buildPaiement(?Facture $facture, ?DateTimeImmutable $dateOfPayment, ?Utilisateur $utilisateur, ?float $paidAmount): ?Paiement
    {
        if ($facture == null) {
            /** @var Facture*/
            $paramIDFacture = $this->adminUrlGenerator->get(ServiceCrossCanal::CROSSED_ENTITY_FACTURE);
            if ($paramIDFacture != null) {
                $this->paiement->setFacture(
                    $this->entityManager->getRepository(Facture::class)->find($paramIDFacture)
                );
            }
        } else {
            $this->paiement->setFacture($facture);
        }
        $this->paiement->setPaidAt($dateOfPayment);
        if ($paidAmount != 0) {
            $this->paiement->setMontant($paidAmount);
        } else {
            $this->paiement->setMontant($facture->getTotalSolde());
        }
        $this->paiement->setType(PaiementCrudController::TAB_TYPE_PAIEMENT[$this->getTypePaiement()]);
        $this->paiement->setDestination($facture->getDestination());
        $this->paiement->setDescription($this->getNomAbstract() . " Facture n°" . $facture . ". Versement effectué le " . $this->serviceDates->getTexte($dateOfPayment));
        $this->paiement->setEntreprise($facture->getEntreprise());
        $this->paiement->setUtilisateur($facture->getUtilisateur());
        $this->paiement->setCreatedAt($this->serviceDates->aujourdhui());
        $this->paiement->setUpdatedAt($this->serviceDates->aujourdhui());
        return $this->paiement;
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

    public function reset()
    {
        $this->paiement = null;
    }

    public function loadSavedPaiements(?Facture $facture): ?array
    {
        // dd("Fonction non encore définie");
        $tabPaiements = [];
        if (count($facture->getPaiements()) != 0) {
            /** @var Paiement */
            foreach ($facture->getPaiements() as $paiementEnregistre) {
                // $paiementEnregistre = $this->entityManager->getRepository(Paiement::class)->find($fact->getFacture()->getId());
                if ($paiementEnregistre != null) {
                    $tabPaiements[] = $paiementEnregistre;
                }
            }
        }
        return $tabPaiements;
    }

    public function savePaiement()
    {
        if ($this->paiement != null) {
            $ancienPaiements = $this->loadSavedPaiements($this->facture);
            $testEquality = $this->areEqual($ancienPaiements, $this->paiement);
            if ($testEquality[self::PARAM_FINAL] == false) {
                //Enregistrement du paiement
                if (
                    $testEquality[self::PARAM_SAME_MONTANT] == false &&
                    $testEquality[self::PARAM_SAME_FACTURE] == true
                ) {
                    //On y ajoute la différence
                    $this->paiement->setMontant(
                        $testEquality[self::PARAM_DIFFERENCES][self::PARAM_SAME_MONTANT]
                    );
                    $this->paiement->setDescription("Ajustement (" . $this->serviceDates->getTexte($this->serviceDates->aujourdhui()) . ") - " . $this->paiement->getDescription());
                    // dd("Facture à ajouter", $this->facture);
                    $this->entityManager->persist($this->paiement);
                } else {
                    $this->entityManager->persist($this->paiement);
                }
                $this->entityManager->flush();
            }
        }
    }

    public function areEqual(?array $anciennesPaiements, ?Paiement $nouveauPaiement)
    {
        // dd("Fonction non encore définie");
        $sameMontant = false;
        $sameFacture = false;
        $final = false;
        $diff = 0;
        if (count($anciennesPaiements) != 0 && $nouveauPaiement != null) {
            $cumulMontantAnciennePaiements = 0;
            /** @var Paiement */
            foreach ($anciennesPaiements as $anciennePaiement) {
                if ($anciennePaiement->getDestination() == $nouveauPaiement->getDestination()) {
                    $cumulMontantAnciennePaiements = $cumulMontantAnciennePaiements + $nouveauPaiement->getMontant();
                    $sameFactureTempo = $anciennePaiement->getFacture() == $nouveauPaiement->getFacture();
                    if ($sameFactureTempo == true) {
                        $sameFacture = $sameFactureTempo;
                    }
                }
            }
            $sameMontant = $cumulMontantAnciennePaiements == $nouveauPaiement->getMontant();
            $final = $sameMontant == true && $sameFacture;
            $diff = ($nouveauPaiement->getMontant() - $cumulMontantAnciennePaiements);
        } else if (count($anciennesPaiements) == 0 && $nouveauPaiement != null) {
            $diff = $nouveauPaiement->getMontant();
        }
        $reponse = [
            self::PARAM_SAME_MONTANT => $sameMontant,
            self::PARAM_SAME_FACTURE => $sameFacture,
            self::PARAM_FINAL => $final,
            self::PARAM_DIFFERENCES => [
                self::PARAM_SAME_MONTANT => $diff,
            ],
        ];
        // dd("Anciennes factures: ", count($anciennesFactures), $anciennesFactures, $reponse);
        return $reponse;
    }
}
