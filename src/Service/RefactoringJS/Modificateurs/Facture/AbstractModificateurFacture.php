<?php

namespace App\Service\RefactoringJS\Modificateurs\Facture;

use App\Controller\Admin\FactureCrudController;
use App\Entity\ElementFacture;
use App\Entity\Facture;
use App\Entity\Police;
use App\Entity\Tranche;
use App\Service\ServiceDates;
use App\Service\ServiceAvenant;
use App\Service\ServiceEntreprise;
use App\Service\ServiceCompteBancaire;
use Doctrine\ORM\EntityManagerInterface;

abstract class AbstractModificateurFacture implements FactureModif
{
    // private $tranches;
    private ?Facture $facture;

    public function __construct(
        private ServiceAvenant $serviceAvenant,
        private ServiceDates $serviceDates,
        private ServiceEntreprise $serviceEntreprise,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public abstract function OnGetMontant(?ElementFacture $elementFacture): ?float;
    public abstract function OnCheckCritereIdentification(?ElementFacture $elementFacture): ?bool;
    public abstract function OnSetNotesToInclude(?ElementFacture $elementFacture): ?ElementFacture;

    public function editNewElementsFacture(): FactureModif
    {
        foreach ($this->getNewElementsFacture() as $newElementFacture) {
            //Actualisation des attributs clés
            $newElementFacture->setCreatedAt(new \DateTimeImmutable());
            $newElementFacture->setUpdatedAt(new \DateTimeImmutable());
            $newElementFacture->setEntreprise($this->facture->getEntreprise());
            $newElementFacture->setUtilisateur($this->serviceEntreprise->getUtilisateur());
            /** @var Police */
            $police = $newElementFacture->getTranche()->getPolice();
            if ($police != null) {
                $newElementFacture->setTypeavenant($police->getTypeavenant());
                $newElementFacture->setIdavenant($this->serviceAvenant->generateIdAvenant($police));
            }
            $newElementFacture = $this->OnSetNotesToInclude($newElementFacture);
            $newElementFacture->setMontant($this->OnGetMontant($newElementFacture));
        }
        return $this;
    }

    public function applyChoiceOfNotesIncluded(): FactureModif
    {
        $totMontantFacture = 0;
        foreach ($this->getFacture()->getElementFactures() as $elementFacture) {
            $totMontant = 0;
            /** @var Tranche */
            $tranche = $elementFacture->getTranche();

            //On ecoute la moindre modification de réassurance
            if ($elementFacture->getIncludeComReassurance() == true) {
                $mnt = $tranche->getComReassurance();
                $elementFacture->setCommissionReassurance($mnt);
                $totMontant = $totMontant + $mnt;
            } else {
                $mnt = 0;
                $elementFacture->setCommissionReassurance($mnt);
                $totMontant = $totMontant + $mnt;
            }

            //On ecoute la moindre modification de la com surfronting
            if ($elementFacture->getIncludeComFronting() == true) {
                $mnt = $tranche->getComFronting();
                $elementFacture->setCommissionFronting($mnt);
                $totMontant = $totMontant + $mnt;
            } else {
                $mnt = 0;
                $elementFacture->setCommissionFronting($mnt);
                $totMontant = $totMontant + $mnt;
            }

            //On ecoute la moindre modification de la com locale
            if ($elementFacture->getIncludeComLocale() == true) {
                $mnt = $tranche->getComLocale();
                $elementFacture->setCommissionLocale($mnt);
                $totMontant = $totMontant + $mnt;
            } else {
                $mnt = 0;
                $elementFacture->setCommissionLocale($mnt);
                $totMontant = $totMontant + $mnt;
            }

            //On ecoute la moindre modification des frais de gestion
            if ($elementFacture->getIncludeFraisGestion() == true) {
                $mnt = $tranche->getComFraisGestion();
                $elementFacture->setFraisGestionTotale($mnt);
                $totMontant = $totMontant + $mnt;
            } else {
                $mnt = 0;
                $elementFacture->setFraisGestionTotale($mnt);
                $totMontant = $totMontant + $mnt;
            }

            //On ecoute la moindre modification des rétrocommissions
            if ($elementFacture->getIncludeRetroCom() == true) {
                $mnt = $tranche->getRetroCommissionTotale();
                $elementFacture->setRetroCommissionTotale($mnt);
                $totMontant = $totMontant + $mnt;
            } else {
                $mnt = 0;
                $elementFacture->setRetroCommissionTotale($mnt);
                $totMontant = $totMontant + $mnt;
            }

            //On ecoute la moindre modification des taxes courtiers @ARCA
            if ($elementFacture->getIncludeTaxeCourtier() == true) {
                $mnt = $tranche->getTaxeCourtierTotale();
                $elementFacture->setTaxeCourtierTotale($mnt);
                $totMontant = $totMontant + $mnt;
            } else {
                $mnt = 0;
                $elementFacture->setTaxeCourtierTotale($mnt);
                $totMontant = $totMontant + $mnt;
            }

            //On ecoute la moindre modification des taxes assureurs @TVA
            if ($elementFacture->getIncludeTaxeAssureur() == true) {
                $mnt = $tranche->getTaxeAssureurTotale();
                $elementFacture->setTaxeAssureurTotale($mnt);
                $totMontant = $totMontant + $mnt;
            } else {
                $mnt = 0;
                $elementFacture->setTaxeAssureurTotale($mnt);
                $totMontant = $totMontant + $mnt;
            }

            //On ecoute la moindre modification de la prime d'assurance
            if ($elementFacture->getIncludePrime() == true) {
                $mnt = $tranche->getPrimeTotaleTranche();
                $elementFacture->setPrimeTotale($mnt);
                $totMontant = $totMontant + $mnt;
            } else {
                $mnt = 0;
                $elementFacture->setPrimeTotale($mnt);
                $totMontant = $totMontant + $mnt;
            }
            $elementFacture->setMontant(round($totMontant));
            $totMontantFacture = $totMontantFacture + $totMontant;
        }
        // dd($this->getFacture()->getTotalDu(), $this->getFacture()->getMontantTTC(), $totMontantFacture);
        return $this;
    }

    public function setFacture(?Facture $facture): FactureModif
    {
        $this->facture = $facture;
        return $this;
    }

    public function getFacture(): ?Facture
    {
        return $this->facture;
    }

    public function updateDescriptionFacture(?string $description)
    {
        $this->facture->setDescription($description);
    }

    public function getNewElementsFacture(): array
    {
        $tabResultat = [];
        foreach ($this->getFacture()->getElementFactures() as $elementFacture) {
            if ($this->OnCheckCritereIdentification($elementFacture) == true) {
                $isSameDestination = $this->isSameDestination($this->getFacture()->getElementFactures(), $elementFacture);
                if ($isSameDestination == true) {
                    $tabResultat[] = $elementFacture;
                }
            }
        }
        return $tabResultat;
    }

    public function isSameDestination($existingTabElementsFacture, ?ElementFacture $elementFacture): ?bool
    {
        /** @var ElementFacture */
        foreach ($existingTabElementsFacture as $existingEF) {
            if ($elementFacture->getId() == null) {
                /** @var Facture */
                $facture = $elementFacture->getFacture();
                if ($facture->getDestination() == FactureCrudController::DESTINATION_ARCA) {
                    if ($existingEF->getNomTaxeCourtier() != $elementFacture->getNomTaxeCourtier()) {
                        return false;
                    }
                }
                if ($facture->getDestination() == FactureCrudController::DESTINATION_DGI) {
                    if ($existingEF->getNomTaxeAssureur() != $elementFacture->getNomTaxeAssureur()) {
                        return false;
                    }
                }
                if ($facture->getDestination() == FactureCrudController::DESTINATION_ASSUREUR) {
                    if ($existingEF->getFacture()->getAssureur() != $elementFacture->getFacture()->getAssureur()) {
                        return false;
                    }
                }
                if ($facture->getDestination() == FactureCrudController::DESTINATION_CLIENT) {
                    if ($existingEF->getTranche()->getCotation()->getClient() != $elementFacture->getTranche()->getCotation()->getClient()) {
                        return false;
                    }
                }
                if ($facture->getDestination() == FactureCrudController::DESTINATION_PARTENAIRE) {
                    if ($existingEF->getTranche()->getCotation()->getPartenaire() != $elementFacture->getTranche()->getCotation()->getPartenaire()) {
                        return false;
                    }
                }
            }
        }
        // dd("J'ai trouvé le nouveau ElementFacture!!!!", $elementFacture);
        return true;
    }

    public function getUpdatedFacture(?Facture $oldFacture): ?Facture
    {
        return $this
            ->setFacture($oldFacture)
            ->editNewElementsFacture()
            ->applyChoiceOfNotesIncluded()
            ->getFacture();
    }
}
