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
use Doctrine\Common\Collections\ArrayCollection;

abstract class AbstractFacture implements FactureInit
{
    private $tranches;
    private ?Facture $facture;

    public function __construct(
        private ServiceAvenant $serviceAvenant,
        private ServiceDates $serviceDates,
        private ServiceEntreprise $serviceEntreprise,
        private EntityManagerInterface $entityManager,
        private ServiceCompteBancaire $serviceCompteBancaire
    ) {
        $this->tranches = new ArrayCollection();
        $this->facture = new Facture();
    }
    public abstract function getNomAbstract(): ?string;
    public abstract function getPosteSignedBy(): ?string;
    public abstract function getDestinationFacture(): ?string;

    public function buildFacture(?array $tranches): ?Facture
    {
        // dd($tranches);
        $this->setTranches($tranches);
        $this->setSignedBy($this->serviceEntreprise->getUtilisateur());
        $this->setPosteSignedBy($this->getPosteSignedBy());
        $this->setStatus(FactureCrudController::TAB_STATUS_FACTURE[FactureCrudController::STATUS_FACTURE_IMPAYEE]);
        $this->setDestination(FactureCrudController::TAB_DESTINATION[$this->getDestinationFacture()]);
        $this->appliquerDestination($tranches);
        $this->setDescription($this->generateDescriptionFacture());
        $this->setReference($this->generateInvoiceReference());
        $this->setEntreprise($this->tranches[0]->getEntreprise());
        $this->setUtilisateur($this->tranches[0]->getUtilisateur());
        $this->setCreatedAt($this->tranches[0]->getCreatedAt());
        $this->setUpdatedAt($this->tranches[0]->getUpdatedAt());
        $this->addElementsFacture($this->produireElementsFacture());
        $this->setComptesBancaires();
        // dd("Facture: ", $this->facture);
        return $this->facture;
    }
    private function appliquerDestination(?array $tranches)
    {
        if ($this->getDestinationFacture() == FactureCrudController::DESTINATION_ARCA) {
            $this->setAutreTiers($tranches[0]->getCotation()->getTaxeCourtier()->getOrganisation());
        } else if ($this->getDestinationFacture() == FactureCrudController::DESTINATION_DGI) {
            $this->setAutreTiers($tranches[0]->getCotation()->getTaxeAssureur()->getOrganisation());
        } else if ($this->getDestinationFacture() == FactureCrudController::DESTINATION_CLIENT) {
            $this->setAutreTiers($tranches[0]->getPolice()->getClient());
        } else if ($this->getDestinationFacture() == FactureCrudController::DESTINATION_ASSUREUR) {
            $this->setAssureur($tranches[0]->getPolice()->getAssureur());
        } else if ($this->getDestinationFacture() == FactureCrudController::DESTINATION_PARTENAIRE) {
            $this->setPartenaire($tranches[0]->getPolice()->getPartenaire());
        }
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
    public function setTranches(?array $tranches)
    {
        $this->tranches = $tranches;
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
    public function setDestination(?int $destinationFacture)
    {
        $this->facture->setDestination($destinationFacture);
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
        dd("Fonction non définie.");
        return null;
    }

    public abstract function getTotalDu(?Tranche $tranche): ?float;

    public function produireElementsFacture(): array
    {
        $totDu = 0;
        $elementsFacture = [];
        /** @var Tranche */
        // dd("Suis ici....");
        foreach ($this->tranches as $tranche) {
            $elementFacture = new ElementFacture();
            $elementFacture->setFacture($this->facture);
            $elementFacture->setEntreprise($tranche->getEntreprise());
            $elementFacture->setUtilisateur($tranche->getUtilisateur());
            $elementFacture->setCreatedAt($tranche->getCreatedAt());
            $elementFacture->setUpdatedAt($tranche->getUpdatedAt());
            /**
             * Ici il ne faut facturer que le montant non encore facturée
             * Pas le montant total dû.
             * D'oû il faut savoir combien avons-nous déjà facturé au client/tiers.
             */
            $totDu = $this->getTotalDu($tranche);
            $totInvoiced = $tranche->getTotalInvoiced_destination($this->facture->getDestination());
            $elementFacture->setMontant($totDu - $totInvoiced);

            $elementFacture->setTranche($tranche);
            $elementFacture->setTypeavenant($tranche->getPolice()->getTypeavenant());
            $elementFacture->setIdavenant($this->serviceAvenant->generateIdAvenant($tranche->getPolice()));

            if ($this->getDestinationFacture() == FactureCrudController::DESTINATION_ARCA) {
                $elementFacture->setIncludeTaxeCourtier(true);
            } else if ($this->getDestinationFacture() == FactureCrudController::DESTINATION_ASSUREUR) {
                $elementFacture->setIncludeComFronting(true);
                $elementFacture->setIncludeComLocale(true);
                $elementFacture->setIncludeComReassurance(true);
            } else if ($this->getDestinationFacture() == FactureCrudController::DESTINATION_CLIENT) {
                $elementFacture->setIncludePrime(true);
                $elementFacture->setIncludeFraisGestion(true);
            } else if ($this->getDestinationFacture() == FactureCrudController::DESTINATION_DGI) {
                $elementFacture->setIncludeTaxeAssureur(true);
            } else if ($this->getDestinationFacture() == FactureCrudController::DESTINATION_PARTENAIRE) {
                $elementFacture->setIncludeRetroCom(true);
            }


            // dd("Destination:", $this->getDestinationFacture(), $elementFacture->getIncludeTaxeAssureur(), $elementFacture);
            $elementsFacture[] = $elementFacture;
        }
        // dd($elementsFacture);
        return $elementsFacture;
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
    public function generateInvoiceReference(): ?string
    {
        return strtoupper(str_replace(" ", "", "ND/" . Date("dmYHis") . "/" . $this->serviceEntreprise->getEntreprise()->getNom() . "/" . Date("Y")));
    }

    public function generateDescriptionFacture(): string
    {
        return $this->getNomAbstract() . " - " .
            count($this->tranches) . " Tranches" .
            " : " . $this->getDestinationFacture() .
            " / Du " .
            $this->serviceDates->getTexteSimple(
                $this->tranches[0]->getPolice()->getDateeffet()
            ) .
            " au " .
            $this->serviceDates->getTexteSimple(
                $this->tranches[0]->getPolice()->getDateexpiration()
            );
    }

    public function loadSavedFactures(?array $tranche): ?array
    {
        dd("Fonction non définie pour l'instant.");
        // $tabFactures = [];
        // if (count($tranches->getElementFactures()) != 0) {
        //     // dd("Liste des elements Factures de la tranche: " . $tranche, $tranche->getElementFactures());
        //     /** @var ElementFacture */
        //     foreach ($this->setTranches()->getElementFactures() as $elementFacture) {
        //         $factureEnregistree = $this->entityManager->getRepository(Facture::class)->find($elementFacture->getFacture()->getId());
        //         if ($factureEnregistree != null) {
        //             $tabFactures[] = $factureEnregistree;
        //         }
        //     }
        // }
        return null;
    }

    public function loadSavedFacture(?Tranche $tranche): ?Facture
    {
        $tranche->getElementFactures()[0]->getFacture();
        if (count($tranche->getElementFactures())) {
        }
        return null;
    }

    public function areEqual(?array $anciennesFactures, ?Facture $nouvelleFacture)
    {
        $sameMontant = false;
        $sameClient = false;
        $samePartenaire = false;
        $sameAssureur = false;
        $sameTranche = false;
        $final = false;
        $diff = 0;
        if (count($anciennesFactures) != 0 && $nouvelleFacture != null) {
            $cumulMontantAncienneFactures = 0;
            /** @var Facture */
            foreach ($anciennesFactures as $ancienneFacture) {
                if ($ancienneFacture->getDestination() == $nouvelleFacture->getDestination()) {
                    $cumulMontantAncienneFactures = $cumulMontantAncienneFactures + $ancienneFacture->getMontantTTC();
                    // $sameMontantTempo = $ancienneFacture->getMontantTTC() == $nouvelleFacture->getMontantTTC();
                    $sameClientTempo = $ancienneFacture->getAutreTiers() == $nouvelleFacture->getAutreTiers();
                    $samePartenaireTempo = $ancienneFacture->getPartenaire() == $nouvelleFacture->getPartenaire();
                    $sameAssureurTempo = $ancienneFacture->getAssureur() == $nouvelleFacture->getAssureur();
                    $sameTrancheTempo = $ancienneFacture->getElementFactures()[0]->getTranche() == $nouvelleFacture->getElementFactures()[0]->getTranche();

                    if ($sameClientTempo == true) {
                        $sameClient = $sameClientTempo;
                    }
                    if ($samePartenaireTempo == true) {
                        $samePartenaire = $samePartenaireTempo;
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
            $final = $sameMontant == true && $sameClient == true && $sameAssureur == true && $sameTranche == true && $samePartenaire == true;
            $diff = ($nouvelleFacture->getMontantTTC() - $cumulMontantAncienneFactures);
        } else if (count($anciennesFactures) == 0 && $nouvelleFacture != null) {
            $diff = $nouvelleFacture->getMontantTTC();
        }
        $reponse = [
            self::PARAM_SAME_MONTANT => $sameMontant,
            self::PARAM_SAME_PARTENAIRE => $samePartenaire,
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
            $this->entityManager->persist($this->facture);
            $this->entityManager->flush();
        }
    }
}
