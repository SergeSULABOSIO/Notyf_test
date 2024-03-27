<?php

namespace App\Service\RefactoringJS\Evenements;

abstract class ObservateurAbstract implements Observateur
{
    private ?int $type;

    public function __construct(?int $typeObservateur)
    {
        $this->type = $typeObservateur;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(?int $typeObservateur)
    {
        $this->type = $typeObservateur;
    }

    public abstract function ecouter(?Evenement $evenement);
}
