<?php
namespace App\Twig\Components\TableauDeBord\Interfaces;


use Doctrine\Common\Collections\Collection;
use App\Twig\Components\TableauDeBord\Interfaces\InterfaceBrique;

interface InterfaceTableauDeBord
{
    public function addBrique(InterfaceBrique $newBrique): self;
    public function removeBrique(InterfaceBrique $brique): self;
    public function removeAllBriques(): self;
    public function getBriques(): Collection;
    public function build(): self;
}
