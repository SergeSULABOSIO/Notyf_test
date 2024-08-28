<?php

namespace App\Service\RefactoringJS\Commandes\Piste;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Cotation;
use App\Entity\Piste;
use App\Entity\Police;
use App\Service\RefactoringJS\Commandes\Commande;
use App\Service\RefactoringJS\Evenements\Evenement;
use App\Service\RefactoringJS\Evenements\Sujet;
use App\Service\ServiceAvenant;

class ComDeleterEntite implements Commande
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ServiceAvenant $serviceAvenant,
        private ?Evenement $evenement
    ) {
    }

    public function executer()
    {
        $newEntityValue = $this->evenement->getDonnees()[Evenement::CHAMP_OLD_VALUE];
        dd("Je suis ici", $newEntityValue, $this->evenement);
        if ($newEntityValue != null) {
            if ($newEntityValue instanceof Sujet) {

                //Si c'est l'instance de la police, il faudra lui donner une idAvenant
                if ($newEntityValue instanceof Police) {
                    //La cotation doit savoir qu'elle a été invalidée et donc déttachée de sa police/avenant
                    /** @var Cotation */
                    $exisitingQuote = $newEntityValue->getCotation();
                    $exisitingQuote->setValidated(false);
                    $newEntityValue->getCotation()->getPiste()->setPolice(null);
                    $newEntityValue->setCotation(null);
                    $newEntityValue->setEntreprise(null);
                    $newEntityValue->setUtilisateur(null);
                }

                //ici il faut actualiser la base de données
                $this->entityManager->remove($newEntityValue);
                $this->entityManager->flush();
            }
        }
    }
}
