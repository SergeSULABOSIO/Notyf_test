<?php

namespace App\Service\RefactoringJS\TableauDeBord\Concrets;

use Doctrine\Common\Collections\Collection;
use App\Service\RefactoringJS\TableauDeBord\Interfaces\InterfaceBrique;
use App\Service\RefactoringJS\TableauDeBord\Interfaces\InterfaceIndicateur;

class EvenementIndicateurConcret
{
    public function __construct(private $donnee, private $message) {}

    public function getDonnee()
    {
        return $this->donnee;
    }

    public function getMessage()
    {
        return $this->message;
    }
}
