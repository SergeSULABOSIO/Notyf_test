<?php

namespace App\Service\RefactoringJS\Commandes\Piste;

use App\Entity\ActionCRM;
use App\Entity\Piste;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Contact;
use App\Service\RefactoringJS\Commandes\Commande;
use App\Service\RefactoringJS\Evenements\Evenement;

class ComPisteAjouterNouvelleTache implements Commande
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ?Evenement $evenement
    ) {

    }

    public function executer()
    {
        if ($this->evenement->getDonnees()[Evenement::CHAMP_DONNEE] instanceof Piste && $this->evenement->getDonnees()[Evenement::CHAMP_NEW_VALUE] instanceof ActionCRM) {
            /** @var Piste */
            $piste = $this->evenement->getDonnees()[Evenement::CHAMP_DONNEE];
            /** @var ActionCRM */
            $action = $this->evenement->getDonnees()[Evenement::CHAMP_NEW_VALUE];
            //ici il faut actualiser la base de données
            $this->entityManager->persist($action);
            $this->entityManager->flush();
        }
    }
}
