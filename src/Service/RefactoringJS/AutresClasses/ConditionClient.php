<?php

namespace App\Service\RefactoringJS\AutresClasses;

use App\Entity\Client;
use App\Entity\Tranche;

class ConditionClient extends JSAbstractNoteConditionListener
{
    private ?array $tabTranches = null;

    public function __construct(?array $tabTranches)
    {
        $this->tabTranches = $tabTranches;
    }

    public function canInvoice(?Tranche $tranche): ?bool
    {
        return (
            $tranche->canInvoiceClient()
        );
    }

    public function isSameCible($cible, ?Tranche $trancheEncours)
    {
        /** @var Client */
        $objCible = $cible;
        return ($objCible === $trancheEncours->getPolice()->getClient());
    }

    public function getCible()
    {
        return $this->getTabTranches()[0]->getPolice()->getClient();
    }

    /**
     * Get the value of tabTranches
     */
    public function getTabTranches()
    {
        return $this->tabTranches;
    }
}
