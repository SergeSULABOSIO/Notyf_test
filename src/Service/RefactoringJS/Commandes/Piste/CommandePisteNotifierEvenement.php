<?php
namespace App\Service\RefactoringJS\Commandes\Piste;

use App\Entity\Facture;
use App\Controller\Admin\FactureCrudController;
use App\Entity\ElementFacture;
use App\Entity\Tranche;
use App\Service\RefactoringJS\Commandes\Commande;
use App\Service\RefactoringJS\Evenements\Evenement;
use Doctrine\Common\Collections\ArrayCollection;

class CommandePisteNotifierEvenement implements Commande
{
    public function __construct(private ?ArrayCollection $tabObservateurs, private ?Evenement $evenement)
    {

    }

    public function executer()
    {
        /** @var Observateur */
        foreach ($this->tabObservateurs as $observateur) {
            if ($observateur->getType() == $this->evenement->getType()) {
                $observateur->ecouter($this->evenement);
            }
        }
    }
}
