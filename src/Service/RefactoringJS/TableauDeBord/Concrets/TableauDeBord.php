<?php

namespace App\Service\RefactoringJS\TableauDeBord\Concrets;

use Doctrine\Common\Collections\Collection;
use App\Service\RefactoringJS\TableauDeBord\Interfaces\InterfaceBrique;
use App\Service\RefactoringJS\TableauDeBord\Interfaces\InterfaceTableauDeBord;
use Doctrine\Common\Collections\ArrayCollection;

class TableauDeBord implements InterfaceTableauDeBord
{
    private Collection $briques;

    public function __construct()
    {
        $this->briques = new ArrayCollection();
    }

    public function addBrique(InterfaceBrique $newBrique): InterfaceTableauDeBord
    {
        return $this;
    }
    public function removeBrique(InterfaceBrique $brique): InterfaceTableauDeBord
    {
        return $this;
    }
    public function removeAllBriques(): InterfaceTableauDeBord
    {
        return $this;
    }
    public function getBriques(): Collection
    {
        return $this->briques;
    }

    public function build()
    {
        dd("Construction du tableau de bord...");
        /** @var Brique */
        foreach ($this->briques as $brique) {
            $brique->build();
        }
        dd("Tableau de bord prêt.");
    }
}
