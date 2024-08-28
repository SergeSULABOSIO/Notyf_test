<?php

namespace App\Service\RefactoringJS\Commandes\Piste;

use App\Entity\ActionCRM;
use App\Entity\Chargement;
use App\Entity\Contact;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Cotation;
use App\Entity\DocPiece;
use App\Entity\FeedbackCRM;
use App\Entity\Piste;
use App\Entity\Police;
use App\Entity\Revenu;
use App\Entity\Tranche;
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
        // dd("Old Value: ", $oldEntityValue);
        if ($oldEntityValue != null) {
            if ($oldEntityValue instanceof Sujet) {
                //Si l'on a supprimé la police
                if ($oldEntityValue instanceof Police) {
                    // dd("Old Value: ", $oldEntityValue);
                    /** @var Cotation */
                    $exisitingQuote = $oldEntityValue->getCotation();
                    $exisitingQuote->setValidated(false);
                    $exisitingQuote->setPolice(null);
                    $exisitingQuote->getPiste()->setPolice(null);
                    //Destruction des documents
                    foreach ($oldEntityValue->getDocuments() as $document) {
                        $oldEntityValue->removeDocument($document);
                    }

                    //ici il faut actualiser la base de données
                    $this->updateBase($oldEntityValue);
                } else if ($oldEntityValue instanceof Cotation) {
                    /** @var Cotation */
                    $quote = $oldEntityValue;
                    // dd("Old Value: ", $oldEntityValue);
                    foreach ($oldEntityValue->getChargements() as $chargement) {
                        $quote->removeChargement($chargement);
                    }
                    foreach ($oldEntityValue->getRevenus() as $revenu) {
                        $quote->removeRevenu($revenu);
                    }
                    foreach ($oldEntityValue->getTranches() as $tranche) {
                        $quote->removeTranch($tranche);
                    }
                    foreach ($oldEntityValue->getDocuments() as $document) {
                        $oldEntityValue->removeDocument($document);
                    }
                    //ici il faut actualiser la base de données
                    $this->updateBase($oldEntityValue);
                } else if ($oldEntityValue instanceof Chargement) {
                    //ici il faut actualiser la base de données
                    $this->updateBase($oldEntityValue);
                } else if ($oldEntityValue instanceof Revenu) {
                    //ici il faut actualiser la base de données
                    $this->updateBase($oldEntityValue);
                }else if ($oldEntityValue instanceof Tranche) {
                    //ici il faut actualiser la base de données
                    $this->updateBase($oldEntityValue);
                }else if ($oldEntityValue instanceof FeedbackCRM) {
                    // dd("Je suis ici", $oldEntityValue);
                    //ici il faut actualiser la base de données
                    $this->updateBase($oldEntityValue);
                }else if ($oldEntityValue instanceof DocPiece) {
                    //ici il faut actualiser la base de données
                    $this->updateBase($oldEntityValue);
                }else if ($oldEntityValue instanceof Contact) {
                    $oldEntityValue->setPiste(null);
                    //ici il faut actualiser la base de données
                    $this->updateBase($oldEntityValue);
                }else if ($oldEntityValue instanceof ActionCRM) {
                    /** @var ActionCRM */
                    $action = $oldEntityValue;
                    $action->setPiste(null);
                    $action->setAttributedTo(null);
                    foreach ($action->getFeedbacks() as $feedback) {
                        $action->removeFeedback($feedback);
                    }
                    foreach ($action->getDocuments() as $document) {
                        $action->removeDocument($document);
                    }
                    //ici il faut actualiser la base de données
                    $this->updateBase($oldEntityValue);
                }
            }
        }
    }

    private function updateBase($objet)
    {
        $this->entityManager->remove($objet);
        $this->entityManager->flush();
    }
}
