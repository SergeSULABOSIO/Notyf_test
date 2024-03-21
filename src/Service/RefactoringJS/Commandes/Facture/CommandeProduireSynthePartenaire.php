<?php

namespace App\Service\RefactoringJS\Commandes\Facture;

use App\Entity\Facture;
use App\Controller\Admin\FactureCrudController;
use App\Controller\Admin\RevenuCrudController;
use App\Entity\ElementFacture;
use App\Entity\Police;
use App\Entity\Tranche;
use App\Service\RefactoringJS\Commandes\Commande;

class CommandeProduireSynthePartenaire implements Commande
{
    private $data = [];

    public function __construct(private ?Facture $facture)
    {
    }

    public function executer()
    {
        if ($this->facture->getElementFactures() != null) {
            if (count($this->facture->getElementFactures()) != 0) {
                /** @var ElementFacture */
                foreach ($this->facture->getElementFactures() as $elementFacture) {
                    /**
                     * DESTINATION PARTENAIRE
                     */
                    if (FactureCrudController::TAB_DESTINATION[FactureCrudController::DESTINATION_PARTENAIRE] == $this->facture->getDestination()) {
                        //PRIME D'ASSURANCE
                        if ($elementFacture->getIncludeRetroCom() == true) {
                            $this->setSynthese($elementFacture);
                        }
                    }
                }
            }
        }
        $this->facture->setSynthseNCPartenaire($this->data);
    }

    public function setSynthese(?ElementFacture $elementFacture)
    {
        /** @var Tranche */
        $tranche = $elementFacture->getTranche();
        if ($tranche != null) {
            /** @var Police */
            $police = $tranche->getPolice();
            $primeTTC = $tranche->getPrimeTotaleTranche();
            $primeHt = $tranche->getPrimeNetteTranche();
            $primeTva = $tranche->getTvaTranche();
            $primeFronting = $tranche->getFrontingTranche();
            $mntHT = $primeHt;
            $this->notesElementsFactures[] =
                [
                    self::NOTE_NO => $this->indexLigne,
                    self::NOTE_REFERENCE_POLICE => $police->getReference(),
                    self::NOTE_AVENANT => $police->getTypeavenant(),
                    self::NOTE_RISQUE => $police->getProduit()->getCode(),
                    self::NOTE_TRANCHE => $tranche->getNom(),
                    self::NOTE_PERIODE => $tranche->getDateEffet()->format('d/m/Y') . " - " . $tranche->getDateExpiration()->format('d/m/Y'),
                    self::NOTE_TYPE => FactureCrudController::TYPE_NOTE_PRIME,
                    self::NOTE_PRIME_TTC => $primeTTC / 100,
                    self::NOTE_PRIME_NETTE => $primeHt / 100,
                    self::NOTE_PRIME_FRONTING => $primeFronting / 100,
                    self::NOTE_PRIME_TVA => $primeTva / 100,
                    self::NOTE_TAUX => ($primeHt != 0) ? (($mntHT / $primeHt) * 100) : 0,
                    self::NOTE_MONTANT_NET => $mntHT / 100,
                    self::NOTE_TVA => $primeTva / 100,
                    self::NOTE_MONTANT_TTC => $primeTTC / 100
                ];
            $this->indexLigne = $this->indexLigne + 1;
        }
    }
}
