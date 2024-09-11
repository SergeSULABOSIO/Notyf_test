<?php

namespace App\Service\RefactoringJS\TableauDeBord\Interfaces;

use Doctrine\Common\Collections\Collection;

interface InterfaceBrique
{
    public function addIndicateur(InterfaceIndicateur $newIndicateur): InterfaceBrique;
    public function removeIndicateur(InterfaceIndicateur $newIndicateur): InterfaceBrique;
    public function removeAllIndicateurs(): InterfaceBrique;
    public function getIndicateurs(): Collection;
}
