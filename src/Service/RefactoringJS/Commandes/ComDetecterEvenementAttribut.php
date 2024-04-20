<?php

namespace App\Service\RefactoringJS\Commandes;

use App\Entity\Piste;
use App\Service\RefactoringJS\Commandes\Commande;
use App\Service\RefactoringJS\Evenements\Evenement;
use App\Service\RefactoringJS\Evenements\EvenementConcretAjout;
use App\Service\RefactoringJS\Evenements\EvenementConcretAttributAjout;
use App\Service\RefactoringJS\Evenements\EvenementConcretAttributEdition;
use App\Service\RefactoringJS\Evenements\EvenementConcretAttributSuppression;
use App\Service\RefactoringJS\Evenements\EvenementConcretEdition;
use App\Service\RefactoringJS\Evenements\EvenementConcretSuppression;
use App\Service\RefactoringJS\Evenements\Sujet;
use DateTimeImmutable;

class ComDetecterEvenementAttribut implements Commande
{
    public function __construct(
        private ?Sujet $objetEcoute,
        private ?string $nomAttribut,
        private $oldValue,
        private $newValue,
        private ?string $formatValue
    ) {
    }

    public function executer()
    {
        //Transformation d'éventuelles dates en chaînes de caractères
        if ($this->oldValue instanceof DateTimeImmutable) {
            $this->oldValue = $this->oldValue->format("d/m/Y");
        }
        if ($this->newValue instanceof DateTimeImmutable) {
            $this->newValue = $this->newValue->format("d/m/Y");
        }

        //Création des évènements
        if ($this->oldValue == null && $this->newValue != null) {
            //AJOUT
            $eAjout = new EvenementConcretAttributAjout();
            $eAjout->setValueFormat($this->formatValue);
            $eAjout->setDonnees([
                Evenement::CHAMP_DONNEE => $this->objetEcoute,
                Evenement::CHAMP_OLD_VALUE => $this->oldValue,
                Evenement::CHAMP_NEW_VALUE => $this->newValue,
                Evenement::CHAMP_MESSAGE => "Définition de l'attribut " . $this->nomAttribut . " [" . $this->newValue . "]",
            ]);
            $this->objetEcoute->notifierLesObservateurs($eAjout);
            // dd($eAjout);
        } else if ($this->oldValue != null && $this->newValue != null && $this->oldValue != $this->newValue) {
            //EDITION
            $eEdition = new EvenementConcretAttributEdition();
            $eEdition->setValueFormat($this->formatValue);
            $eEdition->setDonnees([
                Evenement::CHAMP_DONNEE => $this->objetEcoute,
                Evenement::CHAMP_OLD_VALUE => $this->oldValue,
                Evenement::CHAMP_NEW_VALUE => $this->newValue,
                Evenement::CHAMP_MESSAGE => "Modification de l'attribut " . $this->nomAttribut . " [" . $this->oldValue . " => " . $this->newValue . "]",
            ]);
            $this->objetEcoute->notifierLesObservateurs($eEdition);
            // dd($eEdition);
        } else if (($this->oldValue != null) && $this->newValue === null || $this->newValue === "") {
            //SUPPRESSION
            $eSuppression = new EvenementConcretAttributSuppression();
            $eSuppression->setValueFormat($this->formatValue);
            $eSuppression->setDonnees([
                Evenement::CHAMP_DONNEE => $this->objetEcoute,
                Evenement::CHAMP_OLD_VALUE => $this->oldValue,
                Evenement::CHAMP_NEW_VALUE => $this->newValue,
                Evenement::CHAMP_MESSAGE => "Suppression de l'attribut " . $this->nomAttribut . " [" . $this->newValue . "]",
            ]);
            $this->objetEcoute->notifierLesObservateurs($eSuppression);
            // dd($eSuppression);
        }
    }
}
