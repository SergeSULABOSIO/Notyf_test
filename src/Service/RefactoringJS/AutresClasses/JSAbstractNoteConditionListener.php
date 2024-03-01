<?php

namespace App\Service\RefactoringJS\AutresClasses;

use App\Entity\Tranche;
use PhpParser\Node\Expr\Cast\Bool_;

abstract class JSAbstractNoteConditionListener
{
    public function __construct()
    {
    }
    public function isVide(): ?Bool
    {
        return count($this->getTabTranches()) == 0;
    }
    public abstract function getCible();
    public abstract function getTabTranches();
    public function getEntityIdsAfterCanInvoiceFilter():?array{
        $tab = [];
        /** @var Tranche */
        foreach ($this->getTabTranches() as $tranche) {
            if($this->canInvoice($tranche)){
                $tab[] = $tranche->getId();
            }
        }
        return $tab;
    }

    public abstract function canInvoice(?Tranche $tranche):?Bool;
    public abstract function isSameCible($cible, ?Tranche $trancheEncours);
}
