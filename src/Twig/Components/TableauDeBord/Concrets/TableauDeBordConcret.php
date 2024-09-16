<?php
namespace App\Twig\Components\TableauDeBord\Concrets;


use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Twig\Components\TableauDeBord\Interfaces\InterfaceBrique;
use App\Twig\Components\TableauDeBord\Interfaces\InterfaceTableauDeBord;

class TableauDeBordConcret implements InterfaceTableauDeBord
{
    private ?Collection $briques;

    public function __construct()
    {
        $this->briques = new ArrayCollection();
    }

    public function addBrique(InterfaceBrique $newBrique): self
    {
        if ($this->briques->contains($newBrique) == false) {
            $this->briques->add($newBrique);
        }
        return $this;
    }
    public function removeBrique(InterfaceBrique $brique): self
    {
        if ($this->briques->contains($brique) == true) {
            $this->briques->removeElement($brique);
        }
        return $this;
    }
    public function removeAllBriques(): self
    {
        $this->briques = new ArrayCollection();
        return $this;
    }
    public function getBriques(): Collection
    {
        return $this->briques;
    }

    public function build(): self
    {
        // dd("Construction du tableau de bord...");
        /** @var Brique */
        foreach ($this->briques as $brique) {
            $brique->build();
        }
        // dd("Tableau de bord prêt.");
        return $this;
    }
}
