<?php

namespace App\Service\RefactoringJS\Commandes;

use App\Service\RefactoringJS\Evenements\Sujet;
use App\Service\RefactoringJS\Commandes\Commande;
use App\Service\RefactoringJS\Evenements\Evenement;
use App\Service\RefactoringJS\Evenements\EvenementConcretEntiteApresAjout;
use App\Service\RefactoringJS\Evenements\EvenementConcretEntiteAvantAjout;
use App\Service\RefactoringJS\Evenements\EvenementConcretEntiteApresEdition;
use App\Service\RefactoringJS\Evenements\EvenementConcretEntiteAvantEdition;
use App\Service\RefactoringJS\Evenements\EvenementConcretEntiteApresChargement;
use App\Service\RefactoringJS\Evenements\EvenementConcretEntiteApresSuppression;
use App\Service\RefactoringJS\Evenements\EvenementConcretEntiteAvantSuppression;

class ComDetecterEvenementEntite implements Commande
{
    public function __construct(
        private ?Sujet $objetEcoute,
        private ?int $typeEvenement
    ) {
    }

    public function executer()
    {
        //Création des évènements
        $e = null;
        $message = "Null";
        switch ($this->typeEvenement) {
            case Evenement::TYPE_ENTITE_AVANT_ENREGISTREMENT:
                $e = new EvenementConcretEntiteAvantAjout();
                $message = "Avant enregistrement de l'entité [" . $this->objetEcoute . "]";
                break;
            case Evenement::TYPE_ENTITE_AVANT_EDITION:
                $e = new EvenementConcretEntiteAvantEdition();
                $message = "Avant édition de l'entité [" . $this->objetEcoute . "]";
                break;
            case Evenement::TYPE_ENTITE_AVANT_SUPPRESSION:
                $e = new EvenementConcretEntiteAvantSuppression();
                $message = "Avant suppression de l'entité [" . $this->objetEcoute . "]";
                break;
            case Evenement::TYPE_ENTITE_APRES_ENREGISTREMENT:
                $e = new EvenementConcretEntiteApresAjout();
                $message = "Après enregistrement de l'entité [" . $this->objetEcoute . "]";
                break;
            case Evenement::TYPE_ENTITE_APRES_EDITION:
                $e = new EvenementConcretEntiteApresEdition();
                $message = "Après édition de l'entité [" . $this->objetEcoute . "]";
                break;
            case Evenement::TYPE_ENTITE_APRES_SUPPRESSION:
                $e = new EvenementConcretEntiteApresSuppression();
                $message = "Après suppression de l'entité [" . $this->objetEcoute . "]";
                break;
            case Evenement::TYPE_ENTITE_APRES_CHARGEMENT:
                $e = new EvenementConcretEntiteApresChargement();
                $message = "Après chargement de l'entité [" . $this->objetEcoute . "]";
                break;

            default:
                # code...
                break;
        }

        if ($e !== null) {
            $e->setValueFormat(Evenement::FORMAT_VALUE_ENTITY);
            $e->setDonnees([
                Evenement::CHAMP_DONNEE => $this->objetEcoute,
                Evenement::CHAMP_OLD_VALUE => null,
                Evenement::CHAMP_NEW_VALUE => $this->objetEcoute,
                Evenement::CHAMP_MESSAGE => $message,
            ]);
            $this->objetEcoute->notifierLesObservateurs($e);
        }
    }
}
