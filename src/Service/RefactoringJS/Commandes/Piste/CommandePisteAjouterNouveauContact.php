<?php

namespace App\Service\RefactoringJS\Commandes\Piste;

use App\Entity\Piste;
use App\Entity\Client;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\Admin\ClientCrudController;
use App\Entity\Contact;
use App\Service\RefactoringJS\Commandes\Commande;
use App\Service\RefactoringJS\Evenements\Evenement;

class CommandePisteAjouterNouveauContact implements Commande
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ?Evenement $evenement
    ) {

    }

    public function executer()
    {
        if ($this->evenement->getDonnees()[Evenement::CHAMP_DONNEE] instanceof Piste && $this->evenement->getDonnees()[Evenement::CHAMP_NEW_VALUE] instanceof Contact) {
            /** @var Piste */
            $piste = $this->evenement->getDonnees()[Evenement::CHAMP_DONNEE];
            /** @var Contact */
            $contact = $this->evenement->getDonnees()[Evenement::CHAMP_NEW_VALUE];
            //ici il faut actualiser la base de données
            $this->entityManager->persist($contact);
            $this->entityManager->flush();
        }
    }
}
