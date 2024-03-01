<?php

namespace App\Service\RefactoringJS\AutresClasses;

use App\Entity\Assureur;
use App\Entity\Client;
use App\Entity\Tranche;

class ConditionAssureur extends JSAbstractNoteConditionListener
{
    private ?array $tabTranches = null;

    public function __construct(?array $tabTranches)
    {
        $this->tabTranches = $tabTranches;
    }

    public function canInvoice(?Tranche $tranche): ?bool
    {
        return (
            $tranche->canInvoiceAssureur()
        );
    }

    public function isSameCible($cible, ?Tranche $trancheEncours)
    {
        /** @var Assureur */
        $objCible = $cible;
        return ($objCible === $trancheEncours->getPolice()->getAssureur());
    }

    public function getCible()
    {
        return $this->getTabTranches()[0]->getPolice()->getAssureur();
    }

    /**
     * Get the value of tabTranches
     */
    public function getTabTranches()
    {
        return $this->tabTranches;
    }
}
