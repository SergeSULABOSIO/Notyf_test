<?php
namespace App\Service\RefactoringJS\Commandes;

use App\Entity\Piste;
use App\Service\ServiceDates;
use App\Service\ServiceEntreprise;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\RefactoringJS\Evenements\Sujet;
use App\Service\RefactoringJS\Commandes\Commande;
use App\Service\RefactoringJS\Evenements\ObservateurPisteAjout;
use App\Service\RefactoringJS\Evenements\ObservateurPisteEdition;
use App\Service\RefactoringJS\Evenements\ObservateurAttributAjout;
use App\Service\RefactoringJS\Evenements\ObservateurAttributEdition;
use App\Service\RefactoringJS\Evenements\ObservateurPisteChargement;
use App\Service\RefactoringJS\Evenements\ObservateurEntiteApresAjout;
use App\Service\RefactoringJS\Evenements\ObservateurEntiteAvantAjout;
use App\Service\RefactoringJS\Evenements\ObservateurPisteSuppression;
use App\Service\RefactoringJS\Evenements\ObservateurAttributChargement;
use App\Service\RefactoringJS\Evenements\ObservateurEntiteApresEdition;
use App\Service\RefactoringJS\Evenements\ObservateurEntiteAvantEdition;
use App\Service\RefactoringJS\Evenements\ObservateurAttributSuppression;
use App\Service\RefactoringJS\Evenements\ObservateurEntiteApresChargement;
use App\Service\RefactoringJS\Evenements\ObservateurEntiteApresSuppression;
use App\Service\RefactoringJS\Evenements\ObservateurEntiteAvantSuppression;

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
            $this->sujet->ajouterObservateur(new ObservateurAttributAjout($this->entityManager, $this->serviceEntreprise, $this->serviceDates));
            $this->sujet->ajouterObservateur(new ObservateurAttributChargement($this->serviceEntreprise, $this->serviceDates));
            $this->sujet->ajouterObservateur(new ObservateurAttributEdition($this->serviceEntreprise, $this->serviceDates));
            $this->sujet->ajouterObservateur(new ObservateurAttributSuppression($this->serviceEntreprise, $this->serviceDates));
            //les obervateurs des évènements sur le sujet lui-même - AVANT
            $this->sujet->ajouterObservateur(new ObservateurEntiteAvantAjout($this->entityManager, $this->serviceEntreprise, $this->serviceDates));
            $this->sujet->ajouterObservateur(new ObservateurEntiteAvantEdition($this->entityManager, $this->serviceEntreprise, $this->serviceDates));
            $this->sujet->ajouterObservateur(new ObservateurEntiteAvantSuppression($this->entityManager, $this->serviceEntreprise, $this->serviceDates));
            //les obervateurs des évènements sur le sujet lui-même - APRES
            $this->sujet->ajouterObservateur(new ObservateurEntiteApresAjout($this->entityManager, $this->serviceEntreprise, $this->serviceDates));
            $this->sujet->ajouterObservateur(new ObservateurEntiteApresChargement($this->entityManager, $this->serviceEntreprise, $this->serviceDates));
            $this->sujet->ajouterObservateur(new ObservateurEntiteApresEdition($this->entityManager, $this->serviceEntreprise, $this->serviceDates));
            $this->sujet->ajouterObservateur(new ObservateurEntiteApresSuppression($this->entityManager, $this->serviceEntreprise, $this->serviceDates));
            
            // dd("Piste:", $this->piste);
        }
    }
}
