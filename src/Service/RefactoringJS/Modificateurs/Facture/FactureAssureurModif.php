<?php

namespace App\Service\RefactoringJS\Initialisateurs\Facture;

use App\Entity\Tranche;
use App\Service\ServiceDates;
use App\Service\ServiceAvenant;
use App\Service\ServiceEntreprise;
use App\Service\ServiceCompteBancaire;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\Admin\FactureCrudController;
use App\Entity\ElementFacture;
use App\Service\RefactoringJS\Modificateurs\Facture\AbstractModificateurFacture;
use phpDocumentor\Reflection\Types\Null_;

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
            $elementFacture->setCommissionFronting($elementFacture->getTranche()->getComFronting());
            //Com Locale
            $elementFacture->setIncludeComLocale(true);
            $elementFacture->setCommissionLocale($elementFacture->getTranche()->getComLocale());
            //Com de Reassurance
            $elementFacture->setIncludeComReassurance(true);
            $elementFacture->setCommissionReassurance($elementFacture->getTranche()->getComReassurance());
        }
        return $elementFacture;
    }

    public function OnGetMontant(?ElementFacture $elementFacture): ?float
    {
        return (
            $elementFacture->getCommissionReassurance() +
            $elementFacture->getCommissionFronting() +
            $elementFacture->getCommissionLocale()
        );
    }
}
