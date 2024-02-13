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
use App\Service\RefactoringJS\Initisateurs\Facture\FactureInit;

class FactureComLocaleInit implements FactureInit
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
        $this->facture = new Facture();
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
        $elementFacture->setMontant($this->tranche->getComLocale());
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
        return "Commission locale / ordinaire - " .
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
        // dd($this->facture);
        $this->setTranche($tranche);
        $this->setSignedBy("Pour " . $tranche->getEntreprise()->getNom());
        $this->setPosteSignedBy("Direction financière");
        $this->setStatus(FactureCrudController::TAB_STATUS_FACTURE[FactureCrudController::STATUS_FACTURE_IMPAYEE]);
        $this->setType(FactureCrudController::TAB_TYPE_FACTURE[FactureCrudController::TYPE_FACTURE_COMMISSION_LOCALE]);
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
        $this->addElementFacture($elementFacture);
        $this->setComptesBancaires();
        return $this->facture;
    }

    public function loadSavedFactures(?Tranche $tranche): ?array
    {
        $tabFactures = [];
        if (count($tranche->getElementFactures()) != 0) {
            // dd("Liste des elements Factures de la tranche: " . $tranche, $tranche->getElementFactures());
            /** @var ElementFacture */
            foreach ($tranche->getElementFactures() as $elementFacture) {
                $factureEnregistree = $this->entityManager->getRepository(Facture::class)->find($elementFacture->getFacture()->getId());
                if ($factureEnregistree != null) {
                    $tabFactures[] = $factureEnregistree;
                }
            }
        }
        return $tabFactures;
    }

    public function areEqual(?array $anciennesFactures, ?Facture $nouvelleFacture)
    {
        $sameMontant = false;
        $sameClient = false;
        $sameAssureur = false;
        $sameTranche = false;
        $final = false;
        $diff = 0;
        if (count($anciennesFactures) != 0 && $nouvelleFacture != null) {
            $cumulMontantAncienneFactures = 0;
            /** @var Facture */
            foreach ($anciennesFactures as $ancienneFacture) {
                if ($ancienneFacture->getType() == $nouvelleFacture->getType()) {
                    $cumulMontantAncienneFactures = $cumulMontantAncienneFactures + $ancienneFacture->getMontantTTC();
                    // $sameMontantTempo = $ancienneFacture->getMontantTTC() == $nouvelleFacture->getMontantTTC();
                    $sameClientTempo = $ancienneFacture->getAutreTiers() == $nouvelleFacture->getAutreTiers();
                    $sameAssureurTempo = $ancienneFacture->getAssureur() == $nouvelleFacture->getAssureur();
                    $sameTrancheTempo = $ancienneFacture->getElementFactures()[0]->getTranche() == $nouvelleFacture->getElementFactures()[0]->getTranche();

                    if ($sameClientTempo == true) {
                        $sameClient = $sameClientTempo;
                    }
                    if ($sameAssureurTempo == true) {
                        $sameAssureur = $sameAssureurTempo;
                    }
                    if ($sameTrancheTempo == true) {
                        $sameTranche = $sameTrancheTempo;
                    }
                }
            }
            $sameMontant = $cumulMontantAncienneFactures == $nouvelleFacture->getMontantTTC();
            $final = $sameMontant == true && $sameClient == true && $sameAssureur == true && $sameTranche == true;
            $diff = ($nouvelleFacture->getMontantTTC() - $cumulMontantAncienneFactures);
        } else if (count($anciennesFactures) == 0 && $nouvelleFacture != null) {
            $diff = $nouvelleFacture->getMontantTTC();
        }
        $reponse = [
            self::PARAM_SAME_MONTANT => $sameMontant,
            self::PARAM_SAME_CLIENT => $sameClient,
            self::PARAM_SAME_ASSUREUR => $sameAssureur,
            self::PARAM_SAME_TRANCHE => $sameTranche,
            self::PARAM_FINAL => $final,
            self::PARAM_DIFFERENCES => [
                self::PARAM_SAME_MONTANT => $diff,
            ],
        ];
        // dd("Anciennes factures: ", count($anciennesFactures), $anciennesFactures, $reponse);
        return $reponse;
    }

    public function reset()
    {
        $this->facture = null;
    }

    public function saveFacture()
    {
        // dd("Cette fonction n'est pas encore définie.");
        if ($this->facture != null) {
            $ancienneFacture = $this->loadSavedFactures($this->tranche);
            $testEquality = $this->areEqual($ancienneFacture, $this->facture);
            // dd("Ici", $testEquality);
            if ($testEquality[self::PARAM_FINAL] == false) {
                //Enregistrement de la facture
                if (
                    $testEquality[self::PARAM_SAME_MONTANT] == false &&
                    $testEquality[self::PARAM_SAME_CLIENT] == true &&
                    $testEquality[self::PARAM_SAME_ASSUREUR] == true &&
                    $testEquality[self::PARAM_SAME_TRANCHE] == true
                ) {
                    //On y ajoute la différence
                    /** @var ElementFacture  */
                    $elementFacture = $this->facture->getElementFactures()[0];
                    $elementFacture
                        ->setMontant(
                            $testEquality[self::PARAM_DIFFERENCES][self::PARAM_SAME_MONTANT]
                        );
                    $this->facture->setDescription("Ajustement (" . $this->serviceDates->getTexte($this->serviceDates->aujourdhui()) . ") - " . $this->facture->getDescription());
                    // dd("Facture à ajouter", $this->facture);
                    $this->entityManager->persist($this->facture);
                } else {
                    $this->entityManager->persist($this->facture);
                }
                $this->entityManager->flush();
            }
        }
    }
}
