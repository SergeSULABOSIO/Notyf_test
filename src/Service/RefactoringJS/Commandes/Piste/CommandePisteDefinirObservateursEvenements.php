<?php

namespace App\Service\RefactoringJS\Commandes\Piste;

use App\Entity\Piste;
use App\Service\ServiceDates;
use App\Service\ServiceEntreprise;
use App\Service\RefactoringJS\Commandes\Commande;
use App\Service\RefactoringJS\Evenements\ObservateurPisteAjout;
use App\Service\RefactoringJS\Evenements\ObservateurPisteEdition;
use App\Service\RefactoringJS\Evenements\ObservateurPisteChargement;
use App\Service\RefactoringJS\Evenements\ObservateurPisteSuppression;

class CommandePisteDefinirObservateursEvenements implements Commande
{
    public function __construct(
        private ?ServiceEntreprise $serviceEntreprise,
        private ?ServiceDates $serviceDates,
        private ?Piste $piste
    ) {
    }

    public function executer()
    {
        if($this->piste != null){
            $this->piste->ajouterObservateur(new ObservateurPisteAjout($this->serviceEntreprise, $this->serviceDates));
            $this->piste->ajouterObservateur(new ObservateurPisteChargement($this->serviceEntreprise, $this->serviceDates));
            $this->piste->ajouterObservateur(new ObservateurPisteEdition($this->serviceEntreprise, $this->serviceDates));
            $this->piste->ajouterObservateur(new ObservateurPisteSuppression($this->serviceEntreprise, $this->serviceDates));
        }
    }
}
