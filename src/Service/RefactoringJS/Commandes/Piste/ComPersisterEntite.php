<?php

namespace App\Service\RefactoringJS\Commandes\Piste;

use App\Entity\Piste;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Contact;
use App\Entity\Cotation;
use App\Entity\Police;
use App\Service\RefactoringJS\Commandes\Commande;
use App\Service\RefactoringJS\Evenements\Evenement;
use App\Service\RefactoringJS\Evenements\Sujet;
use App\Service\ServiceAvenant;

class ComPersisterEntite implements Commande
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ServiceAvenant $serviceAvenant,
        private ?Evenement $evenement
    ) {
    }

    public function executer()
    {
        $newEntityValue = $this->evenement->getDonnees()[Evenement::CHAMP_NEW_VALUE];
        if ($newEntityValue != null) {
            if ($newEntityValue instanceof Sujet) {

                //Si c'est l'instance de la police, il faudra lui donner une idAvenant
                if ($newEntityValue instanceof Police) {
                    /** @var Police */
                    $existingPolice = $newEntityValue->getCotation()->getPiste()->getPolice();
                    if ($existingPolice != null) {
                        $newEntityValue->setIdAvenant($this->serviceAvenant->generateIdAvenantByReference($existingPolice->getReference()));
                    } else {
                        $newEntityValue->setIdAvenant($this->serviceAvenant->generateIdAvenantByReference($newEntityValue->getReference()));
                        $newEntityValue->getCotation()->getPiste()->setPolice($newEntityValue);
                    }
                    //La cotation doit savoir qu'elle a été validée et donc attachée à une police/avenant
                    /** @var Cotation */
                    $exisitingQuote = $newEntityValue->getCotation();
                    $exisitingQuote->setValidated(true);
                    //ici il faut actualiser la base de données
                    //ici il faut actualiser la base de données
                    $this->updateBase($newEntityValue);
                }else if ($newEntityValue instanceof Cotation) {
                    $newEntityValue->setGestionnaire($newEntityValue->getGestionnaire());
                    //ici il faut actualiser la base de données
                    $this->updateBase($newEntityValue);
                }
            }
        }
    }

    private function updateBase($objet)
    {
        if ($objet->getId() == null) {
            $this->entityManager->persist($objet);
        } else {
            $this->entityManager->refresh($objet);
        }
        $this->entityManager->flush();
    }
}
