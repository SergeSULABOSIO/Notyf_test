<?php

namespace App\Service\Monteurs;

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
use App\Service\ServiceCompteBancaire;

class FacturePrimeBuilder implements FactureBuilder
{
    // private ?Police $police;
    private ?Tranche $tranche;
    private ?Facture $facture;

    public function __construct(
        private ServiceAvenant $serviceAvenant,
        private ServiceDates $serviceDates,
        private ServiceEntreprise $serviceEntreprise,
        private EntityManagerInterface $entityManager,
        private ServiceCompteBancaire $serviceCompteBancaire
    ) {
    }
    public function setComptesBancaires()
    {
        $this->serviceCompteBancaire->setComptes($this->facture, "");
    }
    public function setPartenaire(?Partenaire $partenaire)
    {
        $this->facture->setPartenaire($partenaire);
    }
    public function setAssureur(?Assureur $assureur)
    {
        $this->facture->setAssureur($assureur);
    }
    public function setDescription(?string $description)
    {
        $this->facture->setDescription($description);
    }
    public function setReference(?string $reference)
    {
        $this->facture->setReference($reference);
    }
    public function setTranche(?Tranche $tranche)
    {
        $this->tranche = $tranche;
    }
    public function setSignedBy(?string $signataire)
    {
        $this->facture->setSignedBy($signataire);
    }
    public function setPosteSignedBy(?string $posteSignataire)
    {
        $this->facture->setPosteSignedBy($posteSignataire);
    }
    public function setStatus(?int $status)
    {
        $this->facture->setStatus($status);
    }
    public function setAutreTiers(?string $autreTiers)
    {
        $this->facture->setAutreTiers($autreTiers);
    }
    public function setType(?int $typeFacture)
    {
        $this->facture->setType($typeFacture);
    }
    public function setEntreprise(?Entreprise $entreprise)
    {
        $this->facture->setEntreprise($entreprise);
    }
    public function setUtilisateur(?Utilisateur $utilisateur)
    {
        $this->facture->setUtilisateur($utilisateur);
    }
    public function setCreatedAt(?DateTimeImmutable $dateCreation)
    {
        $this->facture->setCreatedAt($dateCreation);
    }
    public function setUpdatedAt(?DateTimeImmutable $dateModification)
    {
        $this->facture->setUpdatedAt($dateModification);
    }

    public function produireElementFacture(): ?ElementFacture
    {
        $elementFacture = new ElementFacture();
        $elementFacture->setFacture($this->facture);
        $elementFacture->setEntreprise($this->tranche->getEntreprise());
        $elementFacture->setUtilisateur($this->tranche->getUtilisateur());
        $elementFacture->setCreatedAt($this->tranche->getCreatedAt());
        $elementFacture->setUpdatedAt($this->tranche->getUpdatedAt());
        $elementFacture->setMontant($this->tranche->getPrimeTotaleTranche());
        $elementFacture->setTranche($this->tranche);
        $elementFacture->setTypeavenant($this->tranche->getPolice()->getTypeavenant());
        $elementFacture->setIdavenant($this->serviceAvenant->generateIdAvenant($this->tranche->getPolice()));
        return $elementFacture;
    }

    public function produireElementsFacture(): array
    {
        dd("Cette fonction n'est pas définie.");
        return [];
    }
    public function setTotalDu(?float $montantDu)
    {
        $this->facture->setTotalDu($montantDu);
    }
    public function setTotalRecu(?float $montantRecu)
    {
        $this->facture->setTotalRecu($montantRecu);
    }
    public function setTotalSolde(?float $montantSolde)
    {
        $this->facture->setTotalSolde($montantSolde);
    }
    public function addElementsFacture(?array $TabElementsFactures)
    {
        foreach ($TabElementsFactures as $ef) {
            $this->facture->addElementFacture($ef);
        }
    }
    public function addElementFacture(?ElementFacture $elementFacture)
    {
        $this->facture->addElementFacture($elementFacture);
    }
    public function generateInvoiceReference(?int $indice): ?string
    {
        return strtoupper(str_replace(" ", "", "ND" . $indice . "/" . Date("dmYHis") . "/" . $this->serviceEntreprise->getEntreprise()->getNom() . "/" . Date("Y")));
    }

    public function generateDescriptionFacture(): string
    {
        return "Prime d'assurance - " .
            $this->tranche->getNom() .
            " : Ref. police: " .
            $this->tranche->getPolice()->getReference() . " / " .
            $this->tranche->getPolice()->getProduit() . " / " .
            $this->tranche->getPolice()->getClient() . " / " .
            $this->tranche->getPolice()->getAssureur() .
            " / Du " .
            $this->serviceDates->getTexteSimple(
                $this->tranche->getPolice()->getDateeffet()
            ) .
            " au " .
            $this->serviceDates->getTexteSimple(
                $this->tranche->getPolice()->getDateexpiration()
            );
    }

    public function buildFacture(?int $indice, ?Tranche $tranche): ?Facture
    {
        $this->facture = new Facture();
        $this->setTranche($tranche);
        $this->setSignedBy("Pour " . $tranche->getPolice()->getAssureur()->getNom());
        $this->setPosteSignedBy("Direction financière");
        $this->setStatus(FactureCrudController::TAB_STATUS_FACTURE[FactureCrudController::STATUS_FACTURE_IMPAYEE]);
        $this->setType(FactureCrudController::TAB_TYPE_FACTURE[FactureCrudController::TYPE_FACTURE_PRIME]);
        $this->setAutreTiers($tranche->getPolice()->getClient()->getNom());
        $this->setPartenaire($tranche->getPolice()->getPartenaire());
        $this->setAssureur($tranche->getPolice()->getAssureur());
        $this->setDescription($this->generateDescriptionFacture());
        $this->setReference($this->generateInvoiceReference($indice));
        $this->setEntreprise($this->tranche->getEntreprise());
        $this->setUtilisateur($this->tranche->getUtilisateur());
        $this->setCreatedAt($this->tranche->getCreatedAt());
        $this->setUpdatedAt($this->tranche->getUpdatedAt());
        //Element facture / article de la facture
        $elementFacture = $this->produireElementFacture();
        $this->setTotalDu($elementFacture->getMontant());
        $this->setTotalRecu(0);
        $this->setTotalSolde(($elementFacture->getMontant() - 0));
        $this->addElementFacture($elementFacture);
        $this->setComptesBancaires();
        return $this->facture;
    }

    public function loadSavedFacture(?Tranche $tranche): ?Facture
    {
        if ($tranche->getElementFactures()[0] != null) {
            $factureEnregistrees = $this->entityManager->getRepository(Facture::class)->find($tranche->getElementFactures()[0]->getFacture()->getId());
            if ($factureEnregistrees != null) {
                return $factureEnregistrees;
            }
        }
        return null;
    }

    public function areEqual(?Facture $factureA, ?Facture $factureB)
    {
        $sameMontant = $factureA->getMontantTTC() == $factureB->getMontantTTC();
        $sameClient = $factureA->getAutreTiers() == $factureB->getAutreTiers();
        $sameAssureur = $factureA->getAssureur() == $factureB->getAssureur();
        $sameTranche = $factureA->getElementFactures()[0]->getTranche() == $factureB->getElementFactures()[0]->getTranche();

        $final = $sameMontant && $sameClient && $sameAssureur && $sameTranche;
        return [
            self::PARAM_SAME_MONTANT => $sameMontant,
            self::PARAM_SAME_CLIENT => $sameClient,
            self::PARAM_SAME_ASSUREUR => $sameAssureur,
            self::PARAM_SAME_TRANCHE => $sameTranche,
            self::PARAM_FINAL => $final,
        ];
    }

    public function saveFacture()
    {
        dd("Cette fonction n'est pas encore définie.");
        if ($this->facture != null) {
            $ancienneFacture = $this->loadSavedFacture($this->tranche);
            if ($this->areEqual($ancienneFacture, $this->facture)[self::PARAM_FINAL] == false) {
                //Enregistrement de la facture
                $this->entityManager->persist($this->facture);
                $this->entityManager->flush();
            }
        }
    }
}
