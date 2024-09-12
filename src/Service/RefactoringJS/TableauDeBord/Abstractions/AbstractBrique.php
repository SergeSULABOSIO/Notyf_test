<?php

namespace App\Service\RefactoringJS\TableauDeBord\Interfaces;

use Doctrine\Common\Collections\Collection;

abstract class AbstractBrique
{
    public function addIndicateur(InterfaceIndicateur $newIndicateur): InterfaceBrique;
    public function removeIndicateur(InterfaceIndicateur $newIndicateur): InterfaceBrique;
    public function removeAllIndicateurs(): InterfaceBrique;
    public function getIndicateurs(): Collection;
    public function getType(): int;
    public function setType(int $type): InterfaceBrique;
}
