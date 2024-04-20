<?php

namespace App\Service\RefactoringJS\Commandes\Piste;

use App\Entity\Piste;
use App\Entity\Client;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\Admin\ClientCrudController;
use App\Entity\Cotation;
use App\Service\RefactoringJS\Commandes\Commande;
use App\Service\RefactoringJS\Evenements\Evenement;

class ComPisteAjouterNouveauCotation implements Commande
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ?Evenement $evenement
    ) {

    }

    public function executer()
    {
        if ($this->evenement->getDonnees()[Evenement::CHAMP_DONNEE] instanceof Piste && $this->evenement->getDonnees()[Evenement::CHAMP_NEW_VALUE] instanceof Cotation) {
            /** @var Piste */
            $piste = $this->evenement->getDonnees()[Evenement::CHAMP_DONNEE];
            /** @var Cotation */
            $cotation = $this->evenement->getDonnees()[Evenement::CHAMP_NEW_VALUE];
            //On ne tolère pas le secteur vide
            if ($cotation->isValidated() == null) {
                $cotation->setValidated(false);
            }
            if ($cotation->getTauxretrocompartenaire() == null) {
                $cotation->setTauxretrocompartenaire(0);
            }
            //ici il faut actualiser la base de données
            $this->entityManager->persist($cotation);
            $this->entityManager->flush();
        }
    }
}
