<?php

namespace App\Service\RefactoringJS\Commandes\Piste;

use App\Entity\Piste;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Contact;
use App\Service\RefactoringJS\Commandes\Commande;
use App\Service\RefactoringJS\Evenements\Evenement;

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
        if($newEntityValue != null)
        {
            // dd("Champ new value", $newEntityValue);
            //ici il faut actualiser la base de données
            $this->entityManager->persist($newEntityValue);
            $this->entityManager->flush();
        }
    }
}
