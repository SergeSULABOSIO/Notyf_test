<?php

namespace App\Service\RefactoringJS\Commandes\Piste;

use App\Entity\Piste;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Contact;
use App\Service\RefactoringJS\Commandes\Commande;
use App\Service\RefactoringJS\Evenements\Evenement;
use App\Service\RefactoringJS\Evenements\Sujet;

class ComPersisterEntite implements Commande
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ?Evenement $evenement
    ) {
    }

    public function executer()
    {
        $newEntityValue = $this->evenement->getDonnees()[Evenement::CHAMP_NEW_VALUE];
        if ($newEntityValue != null) {
            if ($newEntityValue instanceof Sujet) {
                // dd("Ici", $newEntityValue);
                //ici il faut actualiser la base de données
                if ($newEntityValue->getId() == null) {
                    $this->entityManager->persist($newEntityValue);
                } else {
                    $this->entityManager->refresh($newEntityValue);
                }
                $this->entityManager->flush();
            }
        }
    }
}
