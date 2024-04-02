<?php

namespace App\Service\RefactoringJS\Commandes;

use App\Entity\Piste;
use App\Service\RefactoringJS\Commandes\Commande;
use App\Service\ServiceDates;
use App\Service\ServiceEntreprise;

class CommandeDefinirEseUserDateCreationEtModification implements Commande
{
    public function __construct(
        private $objet,
        private ?ServiceEntreprise $serviceEntreprise,
        private ?ServiceDates $serviceDates
    ) {
    }

    public function executer()
    {
        if ($this->objet != null) {
            if ($this->objet->getId() == null) {
                $this->objet->setCreatedAt($this->serviceDates->aujourdhui());
                $this->objet->setUpdatedAt($this->serviceDates->aujourdhui());
            } else {
                $this->objet->setUpdatedAt($this->serviceDates->aujourdhui());
            }
            $this->objet->setUtilisateur($this->serviceEntreprise->getUtilisateur());
            $this->objet->setEntreprise($this->serviceEntreprise->getEntreprise());
        }
    }
}
