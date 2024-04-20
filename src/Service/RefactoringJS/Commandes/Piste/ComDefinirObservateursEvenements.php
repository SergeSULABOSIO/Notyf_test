<?php

namespace App\Service\RefactoringJS\Commandes\Piste;

use App\Entity\Piste;
use App\Service\ServiceDates;
use App\Service\ServiceEntreprise;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\RefactoringJS\Commandes\Commande;
use App\Service\RefactoringJS\Evenements\ObservateurEntiteAvantAjout;
use App\Service\RefactoringJS\Evenements\ObservateurPisteAjout;
use App\Service\RefactoringJS\Evenements\ObservateurPisteEdition;
use App\Service\RefactoringJS\Evenements\ObservateurPisteChargement;
use App\Service\RefactoringJS\Evenements\ObservateurPisteSuppression;
use App\Service\RefactoringJS\Evenements\Sujet;

class ComDefinirObservateursEvenements implements Commande
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ?ServiceEntreprise $serviceEntreprise,
        private ?ServiceDates $serviceDates,
        private ?Sujet $sujet
    ) {
        
    }

    public function executer()
    {
        if ($this->sujet != null) {
            //les observateurs des évènements sur les attributs du sujet
            $this->sujet->ajouterObservateur(new ObservateurPisteAjout($this->entityManager, $this->serviceEntreprise, $this->serviceDates));
            $this->sujet->ajouterObservateur(new ObservateurPisteChargement($this->serviceEntreprise, $this->serviceDates));
            $this->sujet->ajouterObservateur(new ObservateurPisteEdition($this->serviceEntreprise, $this->serviceDates));
            $this->sujet->ajouterObservateur(new ObservateurPisteSuppression($this->serviceEntreprise, $this->serviceDates));
            
            //les obervateurs des évènements sur le sujet lui-même
            $this->sujet->ajouterObservateur(new ObservateurEntiteAvantAjout($this->entityManager, $this->serviceEntreprise, $this->serviceDates));
            
            // dd("Piste:", $this->piste);
        }
    }
}
