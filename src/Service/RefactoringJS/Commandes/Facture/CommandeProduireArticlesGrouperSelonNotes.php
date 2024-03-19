<?php

namespace App\Service\RefactoringJS\Commandes\Facture;

use App\Entity\Facture;
use App\Controller\Admin\FactureCrudController;
use App\Controller\Admin\RevenuCrudController;
use App\Entity\ElementFacture;
use App\Entity\Police;
use App\Entity\Tranche;
use App\Service\RefactoringJS\Commandes\Commande;
use App\Service\ServiceDates;

class CommandeProduireArticlesGrouperSelonNotes implements Commande
{
    private $notesElementsFactures = [];
    

    public function __construct(private ?Facture $facture, private ?ServiceDates $serviceDates)
    {
    }

    public function executer()
    {
        $indexLigne = 1;
        if ($this->facture->getElementFactures() != null) {
            if (count($this->facture->getElementFactures()) != 0) {
                /** @var ElementFacture */
                foreach ($this->facture->getElementFactures() as $elementFacture) {
                    /**
                     * DESTINATION CLIENT
                     */
                    if (FactureCrudController::TAB_DESTINATION[FactureCrudController::DESTINATION_CLIENT] == $this->facture->getDestination()) {
                        /**
                         * PRIME D'ASSURANCE
                         */
                        $this->addNotePourPrime($elementFacture, $indexLigne);

                        /**
                         * FRAIS DE GESTION
                         */
                        $this->addNotePourFraisDeGestion($elementFacture, $indexLigne);
                    }
                }
            }
        }
        $this->facture->setNotesElementsFactures($this->notesElementsFactures);
    }

    public function addNotePourPrime(?ElementFacture $elementFacture, $indexLigne)
    {
        if ($elementFacture->getIncludePrime() == true) {
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
                        self::NOTE_NO => $indexLigne,
                        self::NOTE_REFERENCE_POLICE => $police->getReference(),
                        self::NOTE_AVENANT => $police->getTypeavenant(),
                        self::NOTE_RISQUE => $police->getProduit()->getCode(),
                        self::NOTE_TRANCHE => $tranche->getNom(),
                        self::NOTE_PERIODE => $this->serviceDates->getTexteSimple($tranche->getDateEffet()) . " - " . $this->serviceDates->getTexteSimple($tranche->getDateExpiration()),
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
                $indexLigne = $indexLigne + 1;
            }
        }
    }

    public function addNotePourFraisDeGestion(?ElementFacture $elementFacture, $indexLigne)
    {
        if ($elementFacture->getIncludeFraisGestion() == true) {
            /** @var Tranche */
            $tranche = $elementFacture->getTranche();
            if ($tranche != null) {
                /** @var Police */
                $police = $tranche->getPolice();
                $primeTTC = $tranche->getPrimeTotaleTranche();
                $primeHt = $tranche->getPrimeNetteTranche();
                $primeTva = $tranche->getTvaTranche();
                $primeFronting = $tranche->getFrontingTranche();
                $mntTTC = $elementFacture->getMontantInvoicedPerTypeNote(FactureCrudController::TAB_TYPE_NOTE[FactureCrudController::TYPE_NOTE_FRAIS_DE_GESTION]);
                $tva = $tranche->getIndicaRevenuTaxeAssureur(RevenuCrudController::TAB_TYPE[RevenuCrudController::TYPE_FRAIS_DE_GESTION]);
                $mntHT = $mntTTC - $tva;
                $this->notesElementsFactures[] =
                    [
                        self::NOTE_NO => $indexLigne,
                        self::NOTE_REFERENCE_POLICE => $police->getReference(),
                        self::NOTE_AVENANT => $police->getTypeavenant(),
                        self::NOTE_RISQUE => $police->getProduit()->getCode(),
                        self::NOTE_TRANCHE => $tranche->getNom(),
                        self::NOTE_PERIODE => $this->serviceDates->getTexteSimple($tranche->getDateEffet()) . " - " . $this->serviceDates->getTexteSimple($tranche->getDateExpiration()),
                        self::NOTE_TYPE => FactureCrudController::TYPE_NOTE_FRAIS_DE_GESTION,
                        self::NOTE_PRIME_TTC => $primeTTC / 100,
                        self::NOTE_PRIME_NETTE => $primeHt / 100,
                        self::NOTE_PRIME_FRONTING => $primeFronting / 100,
                        self::NOTE_PRIME_TVA => $primeTva / 100,
                        self::NOTE_TAUX => ($primeHt != 0) ? (($mntHT / $primeHt) * 100) : 0,
                        self::NOTE_MONTANT_NET => $mntHT / 100,
                        self::NOTE_TVA => $tva / 100,
                        self::NOTE_MONTANT_TTC => $mntTTC / 100
                    ];
                $indexLigne = $indexLigne + 1;
            }
        }
    }
}
