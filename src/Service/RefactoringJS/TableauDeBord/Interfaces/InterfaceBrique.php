<?php

namespace App\Service\RefactoringJS\TableauDeBord\Interfaces;

use Doctrine\Common\Collections\Collection;

interface InterfaceBrique
{
    public const TYPE_BRIQUE_DE_TITRE = 0;
    public const TYPE_BRIQUE_DE_PIED = 1;
    public const TYPE_BRIQUE_DE_GAUCHE = 2;
    public const TYPE_BRIQUE_DE_DROITE = 3;

    public function addIndicateur(InterfaceIndicateur $newIndicateur): InterfaceBrique;
    public function removeIndicateur(InterfaceIndicateur $newIndicateur): InterfaceBrique;
    public function removeAllIndicateurs(): InterfaceBrique;
    public function getIndicateurs(): Collection;
    public function getType(): int;
    public function setType(int $type): InterfaceBrique;
    public function build();
}
