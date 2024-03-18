<?php

namespace App\Service\RefactoringJS\Commandes\Facture;

use App\Entity\Facture;
use App\Controller\Admin\FactureCrudController;
use App\Controller\Admin\RevenuCrudController;
use App\Entity\ElementFacture;
use App\Entity\Police;
use App\Entity\Tranche;
use App\Service\RefactoringJS\Commandes\Commande;

class CommandeProduireArticlesGrouperSelonNotes implements Commande
{
    private $notesElementsFactures = [];

    public function __construct(private ?Facture $facture)
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
                                        "No" => $indexLigne,
                                        "Reference_Police" => $police->getReference(),
                                        "Avenant" => $police->getTypeavenant(),
                                        "Risque" => $police->getProduit()->getCode(),
                                        "Tranche" => $tranche->getNom(),
                                        "Note" => FactureCrudController::TYPE_NOTE_PRIME,
                                        "Prime_TTC" => $primeTTC / 100,
                                        "Prime_HT" => $primeHt / 100,
                                        "Fronting" => $primeFronting / 100,
                                        "Taxe_Assureur" => $primeTva / 100,
                                        "Taux" => ($primeHt != 0) ? (($mntHT / $primeHt) * 100) : 0,
                                        "Montant" => $mntHT / 100,
                                        "Taxes" => $primeTva / 100,
                                        "Total_Dû" => $primeTTC / 100
                                    ];
                                $indexLigne = $indexLigne + 1;
                            }
                        }

                        /**
                         * FRAIS DE GESTION
                         */
                        /** @var ElementFacture */
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
                                $tva = $tranche->getIndicaRevenuTaxeAssureur(RevenuCrudController::TAB_TYPE[
                                    RevenuCrudController::TYPE_FRAIS_DE_GESTION
                                ]);
                                // dd($tva);
                                $mntHT = $mntTTC - $tva;
                                $this->notesElementsFactures[] =
                                    [
                                        "No" => $indexLigne,
                                        "Reference_Police" => $police->getReference(),
                                        "Avenant" => $police->getTypeavenant(),
                                        "Risque" => $police->getProduit()->getCode(),
                                        "Tranche" => $tranche->getNom(),
                                        "Note" => FactureCrudController::TYPE_NOTE_FRAIS_DE_GESTION,
                                        "Prime_TTC" => $primeTTC / 100,
                                        "Prime_HT" => $primeHt / 100,
                                        "Fronting" => $primeFronting / 100,
                                        "Taxe_Assureur" => $primeTva / 100,
                                        "Taux" => ($primeHt != 0) ? (($mntHT / $primeHt) * 100) : 0,
                                        "Montant" => $mntHT / 100,
                                        "Taxes" => $tva / 100,
                                        "Total_Dû" => $mntTTC / 100
                                    ];
                                $indexLigne = $indexLigne + 1;
                            }
                        }
                    }
                }
            }
        }
        $this->facture->setNotesElementsFactures($this->notesElementsFactures);
    }
}
