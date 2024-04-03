<?php

namespace App\Service\RefactoringJS\Commandes\Piste;

use App\Entity\Piste;
use App\Service\RefactoringJS\Commandes\Commande;
use App\Service\RefactoringJS\Evenements\Evenement;
use App\Service\RefactoringJS\Evenements\EvenementConcretAjout;
use App\Service\RefactoringJS\Evenements\EvenementConcretEdition;
use App\Service\RefactoringJS\Evenements\EvenementConcretSuppression;

class CommandePisteDetecterChangementAttribut implements Commande
{
    public function __construct(
        private ?Piste $piste,
        private ?string $nomAttribut,
        private $oldValue,
        private $newValue,
        private ?string $formatValue
    ) {
    }

    public function executer()
    {
        if ($this->oldValue == null && $this->newValue != null) {
            $eAjout = new EvenementConcretAjout();
            $eAjout->setValueFormat($this->formatValue);
            $eAjout->setDonnees([
                Evenement::CHAMP_DONNEE => $this->piste,
                Evenement::CHAMP_OLD_VALUE => $this->oldValue,
                Evenement::CHAMP_NEW_VALUE => $this->newValue,
                Evenement::CHAMP_MESSAGE => "Définition de l'attribut " . $this->nomAttribut . " [" . $this->newValue."]",
            ]);
            $this->piste->notifierLesObservateurs($eAjout);
        } else if ($this->oldValue != null && $this->newValue != null && $this->oldValue != $this->newValue) {
            $eEdition = new EvenementConcretEdition();
            $eEdition->setValueFormat($this->formatValue);
            $eEdition->setDonnees([
                Evenement::CHAMP_DONNEE => $this->piste,
                Evenement::CHAMP_OLD_VALUE => $this->oldValue,
                Evenement::CHAMP_NEW_VALUE => $this->newValue,
                Evenement::CHAMP_MESSAGE => "Modification de l'attribut " . $this->nomAttribut . " [" . $this->oldValue . " => " . $this->newValue ."]",
            ]);
            $this->piste->notifierLesObservateurs($eEdition);
        } else if (($this->oldValue != null) && $this->newValue === null || $this->newValue === "") {
            $eSuppression = new EvenementConcretSuppression();
            $eSuppression->setValueFormat($this->formatValue);
            $eSuppression->setDonnees([
                Evenement::CHAMP_DONNEE => $this->piste,
                Evenement::CHAMP_OLD_VALUE => $this->oldValue,
                Evenement::CHAMP_NEW_VALUE => $this->newValue,
                Evenement::CHAMP_MESSAGE => "Suppression de l'attribut " . $this->nomAttribut . " [" . $this->newValue."]",
            ]);
            $this->piste->notifierLesObservateurs($eSuppression);
        }
    }
}
