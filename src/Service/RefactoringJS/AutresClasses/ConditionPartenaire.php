<?php

namespace App\Service\RefactoringJS\AutresClasses;

use App\Entity\Assureur;
use App\Entity\Client;
use App\Entity\Partenaire;
use App\Entity\Tranche;

class ConditionPartenaire extends JSAbstractNoteConditionListener
{
    private ?array $tabTranches = null;

    public function __construct(?array $tabTranches)
    {
        $this->tabTranches = $tabTranches;
    }

    public function canInvoice(?Tranche $tranche): ?bool
    {
        return (
            $tranche->canInvoicePartenaire()
        );
    }

    public function isSameCible($cible, ?Tranche $trancheEncours)
    {
        /** @var Partenaire */
        $objCible = $cible;
        return ($objCible === $trancheEncours->getPolice()->getPartenaire());
    }

    public function getCible()
    {
        return $this->getTabTranches()[0]->getPolice()->getPartenaire();
    }

    /**
     * Get the value of tabTranches
     */
    public function getTabTranches()
    {
        return $this->tabTranches;
    }
}
