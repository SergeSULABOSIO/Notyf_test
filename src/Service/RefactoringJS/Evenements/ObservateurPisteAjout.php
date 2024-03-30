<?php

namespace App\Service\RefactoringJS\Evenements;

use App\Service\ServiceDates;
use App\Service\ServiceEntreprise;

class ObservateurPisteAjout extends ObservateurAbstract
{
    public function __construct(
        private ?ServiceEntreprise $serviceEntreprise,
        private ?ServiceDates $serviceDates
    ) {
        parent::__construct(Observateur::TYPE_OBSERVATEUR_AJOUT);
    }

    public function ecouter(?Evenement $evenement)
    {
        $donnees = $evenement->getDonnees();
        $donnees[Evenement::CHAMP_ENTREPRISE] = $this->serviceEntreprise->getEntreprise();
        $donnees[Evenement::CHAMP_UTILISATEUR] = $this->serviceEntreprise->getUtilisateur();
        $donnees[Evenement::CHAMP_DATE] = $this->serviceDates->aujourdhui();
        $evenement->setDonnees($donnees);
        
        
        
        
        
        
        
        
        
        dd("Evenement Ajout:", $evenement);
    }
}
