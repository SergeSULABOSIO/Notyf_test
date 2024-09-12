<?php

namespace App\Service\RefactoringJS\TableauDeBord\Interfaces;

use Doctrine\Common\Collections\Collection;

interface InterfaceTableauDeBord
{
    public function addBrique(InterfaceBrique $newBrique): InterfaceTableauDeBord;
    public function removeBrique(InterfaceBrique $brique): InterfaceTableauDeBord;
    public function removeAllBriques(): InterfaceTableauDeBord;
    public function getBriques(): Collection;
    public function build(): InterfaceTableauDeBord;
}
