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
    public abstract function filtrerCanInvoice():?array;
    public abstract function isSameCible($cible, ?Tranche $trancheEncours);
}
