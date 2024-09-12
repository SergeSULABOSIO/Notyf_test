<?php

namespace App\Service\RefactoringJS\TableauDeBord\Concrets;

use Doctrine\Common\Collections\Collection;
use App\Service\RefactoringJS\TableauDeBord\Interfaces\InterfaceBrique;
use App\Service\RefactoringJS\TableauDeBord\Interfaces\InterfaceIndicateur;

class Brique implements InterfaceBrique
{
    private int $type;
    private Collection $indicateurs;

    public function __construct(int $type)
    {
        $this->type = $type;
        $this->indicateurs = new Collection();
    }

    public function addIndicateur(InterfaceIndicateur $newIndicateur): InterfaceBrique
    {
        if ($this->indicateurs->contains($newIndicateur) == false) {
            $this->indicateurs->add($newIndicateur);
        }
        return $this;
    }

    public function removeIndicateur(InterfaceIndicateur $newIndicateur): InterfaceBrique
    {
        $this->indicateurs->removeElement($newIndicateur);
        return $this;
    }

    public function removeAllIndicateurs(): InterfaceBrique
    {
        $this->indicateurs = new Collection();
        return $this;
    }
    public function getIndicateurs(): Collection
    {
        return $this->indicateurs;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type): InterfaceBrique
    {
        
        $this->type = $type;
        return $this;
    }
}
