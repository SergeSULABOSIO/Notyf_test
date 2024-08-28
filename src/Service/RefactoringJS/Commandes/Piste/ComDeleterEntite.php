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
        $oldEntityValue = $this->evenement->getDonnees()[Evenement::CHAMP_OLD_VALUE];
        if ($oldEntityValue != null) {
            if ($oldEntityValue instanceof Sujet) {
                //Si l'on a supprimé la police
                if ($oldEntityValue instanceof Police) {
                    dd("Old Value: ", $oldEntityValue);
                    
                    /** @var Cotation */
                    $exisitingQuote = $oldEntityValue->getCotation();
                    $exisitingQuote->setValidated(false);
                    $exisitingQuote->setPolice(null);
                    $exisitingQuote->getPiste()->setPolice(null);

                    //ici il faut actualiser la base de données
                    $this->entityManager->remove($oldEntityValue);
                    $this->entityManager->flush();
                }
            }
        }
    }
}
