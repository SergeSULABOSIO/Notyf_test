<?php

namespace App\Service\RefactoringJS\Modificateurs\Facture;

use App\Entity\ElementFacture;
use App\Entity\Facture;
use App\Entity\Police;
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

    public abstract function OnGetMontant(?ElementFacture $elementFacture):?float;
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
                $tabResultat[] = $elementFacture;
            }
        }
        return $tabResultat;
    }

    public function getUpdatedFacture(?Facture $oldFacture): ?Facture
    {
        return $this
            ->setFacture($oldFacture)
            ->editNewElementsFacture()
            ->getFacture();
    }
}
