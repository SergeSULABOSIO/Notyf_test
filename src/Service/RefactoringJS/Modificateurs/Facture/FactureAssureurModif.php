<?php

namespace App\Service\RefactoringJS\Initialisateurs\Facture;

use App\Service\ServiceDates;
use App\Service\ServiceAvenant;
use App\Service\ServiceEntreprise;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\ElementFacture;
use App\Service\RefactoringJS\Modificateurs\Facture\AbstractModificateurFacture;

class FactureAssureurModif extends AbstractModificateurFacture
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
            //Com sur Fronting
            $elementFacture->setIncludeComFronting(true);
            // $elementFacture->setCommissionFronting($elementFacture->getTranche()->getComFronting());
            //Com Locale
            $elementFacture->setIncludeComLocale(true);
            // $elementFacture->setCommissionLocale($elementFacture->getTranche()->getComLocale());
            //Com de Reassurance
            $elementFacture->setIncludeComReassurance(true);
            // $elementFacture->setCommissionReassurance($elementFacture->getTranche()->getComReassurance());
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
        $cumul_montant = 0;
        if ($elementFacture->getIncludeComReassurance() == true) {
            $cumul_montant = $cumul_montant + $elementFacture->getCommissionReassurance();
        }
        if ($elementFacture->getIncludeComFronting() == true) {
            $cumul_montant = $cumul_montant + $elementFacture->getCommissionFronting();
        }
        if ($elementFacture->getIncludeComLocale() == true) {
            $cumul_montant = $cumul_montant + $elementFacture->getCommissionLocale();
        }
        return ($cumul_montant);
    }
}
