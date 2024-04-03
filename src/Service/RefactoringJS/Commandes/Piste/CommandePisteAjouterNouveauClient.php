<?php

namespace App\Service\RefactoringJS\Commandes\Piste;

use App\Entity\Piste;
use App\Entity\Client;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\Admin\ClientCrudController;
use App\Service\RefactoringJS\Commandes\Commande;
use App\Service\RefactoringJS\Evenements\Evenement;

class CommandePisteAjouterNouveauClient implements Commande
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ?Evenement $evenement
    ) {

    }

    public function executer()
    {
        if ($this->evenement->getDonnees()[Evenement::CHAMP_DONNEE] instanceof Piste && $this->evenement->getDonnees()[Evenement::CHAMP_NEW_VALUE] instanceof Client) {
            /** @var Piste */
            $piste = $this->evenement->getDonnees()[Evenement::CHAMP_DONNEE];
            /** @var Client */
            $client = $this->evenement->getDonnees()[Evenement::CHAMP_NEW_VALUE];
            //On ne tolère pas le secteur vide
            if ($client->getSecteur() == null) {
                $client->setSecteur(ClientCrudController::TAB_CLIENT_SECTEUR["Autres Secteurs"]);
            }
            //ici il faut actualiser la base de données
            $this->entityManager->persist($client);
            $this->entityManager->flush();
            $piste->setClient($client);
            
            //On vide la liste des prospects
            $tabProspect = $piste->getProspect();
            foreach ($tabProspect as $pros) {
                $piste->removeProspect($pros);
            }
        }
    }
}
