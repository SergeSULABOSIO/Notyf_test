<?php
namespace App\Twig\Components\TableauDeBord\Concrets;

use App\Twig\Components\TableauDeBord\Interfaces\InterfaceBrique;
use App\Twig\Components\TableauDeBord\Interfaces\InterfaceIndicateur;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

class BriqueConcret implements InterfaceBrique
{
    private ?int $type;
    private ?Collection $indicateurs;

    public function __construct(int $type)
    {
        $this->type = $type;
        $this->indicateurs = new ArrayCollection();
    }

    public function addIndicateur(InterfaceIndicateur $newIndicateur): InterfaceBrique
    {
        if ($this->indicateurs->contains($newIndicateur) == false) {
            $this->indicateurs->add($newIndicateur);
            $newIndicateur->setBrique($this);
        }
        return $this;
    }

    public function removeIndicateur(InterfaceIndicateur $newIndicateur): InterfaceBrique
    {
        $newIndicateur->setBrique(null);
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

    public function build()
    {
        // dd("Construction de la brique " . $this->type);
        /** @var Indicateur */
        foreach ($this->indicateurs as $indicateur) {
            $indicateur->build();
        }
        // dd("Fin de la construction de la brique " . $this->type);
    }
}
