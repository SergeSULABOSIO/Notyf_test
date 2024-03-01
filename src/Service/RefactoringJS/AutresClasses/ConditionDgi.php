<?php

namespace App\Service\RefactoringJS\AutresClasses;

use App\Entity\Partenaire;
use App\Entity\Tranche;

class ConditionDgi extends JSAbstractNoteConditionListener
{
    private ?array $tabTranches = null;

    public function __construct(?array $tabTranches)
    {
        $this->tabTranches = $tabTranches;
    }

    public function canInvoice(?Tranche $tranche): ?bool
    {
        return (
            $tranche->canInvoiceDGI()
        );
    }

    public function isSameCible($cible, ?Tranche $trancheEncours)
    {
        /** @var Partenaire */
        $objCible = $cible;
        return ($objCible === $trancheEncours->getTaxe(false)->getOrganisation());
    }

    public function getCible()
    {
        return $this->getTabTranches()[0]->getTaxe(false)->getOrganisation();
    }

    /**
     * Get the value of tabTranches
     */
    public function getTabTranches()
    {
        return $this->tabTranches;
    }
}
