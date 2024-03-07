<?php

namespace App\Service\RefactoringJS\Initialisateurs\Facture;

use App\Service\ServiceDates;
use App\Service\ServiceAvenant;
use App\Service\ServiceEntreprise;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\ElementFacture;
use App\Service\RefactoringJS\Modificateurs\Facture\AbstractModificateurFacture;

class FactureClientModif extends AbstractModificateurFacture
{
    public function __construct(
        private ServiceAvenant $serviceAvenant,
        private ServiceDates $serviceDates,
        private ServiceEntreprise $serviceEntreprise,
        private EntityManagerInterface $entityManager,
    ) {
        parent::__construct(
            $serviceAvenant,
            $serviceDates,
            $serviceEntreprise,
            $entityManager,
        );
    }

    public function OnCheckCritereIdentification(?ElementFacture $elementFacture): ?bool
    {
        return (
            $elementFacture->getCreatedAt() == null &&
            $elementFacture->getEntreprise() == null &&
            $elementFacture->getUtilisateur() == null
        );
    }
    public function OnSetNotesToInclude(?ElementFacture $elementFacture): ?ElementFacture
    {
        if ($elementFacture != Null) {
            // Frais de gestion
            $elementFacture->setIncludeFraisGestion(true);
            $elementFacture->setFraisGestionTotale($elementFacture->getTranche()->getComFraisGestion());
            // Prime d'assurance
            $elementFacture->setIncludePrime(true);
            $elementFacture->setPrimeTotale($elementFacture->getTranche()->getPrimeTotaleTranche());
        }
        return $elementFacture;
    }

    /**
     * Quand on demande le montant de l'elementFacture
     *
     * @param ElementFacture|null $elementFacture
     * @return float|null
     */
    public function OnGetMontant(?ElementFacture $elementFacture): ?float
    {
        return (
            $elementFacture->getPrimeTotale() +
            $elementFacture->getFraisGestionTotale()
        );
    }
}
