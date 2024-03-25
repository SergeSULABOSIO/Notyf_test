<?php

namespace App\Service\RefactoringJS\Commandes\Facture;

use App\Entity\Police;
use App\Entity\Facture;
use App\Entity\Tranche;
use App\Entity\ElementFacture;
use App\Controller\Admin\RevenuCrudController;
use App\Controller\Admin\FactureCrudController;
use App\Service\RefactoringJS\Commandes\Commande;

class CommandeProduireArticlesClientOuAssureur implements Commande
{
    private $data = [];
    private $indexLigne = 1;


    public function __construct(private ?Facture $facture)
    {
    }

    public function executer()
    {
        $this->indexLigne = 1;
        if ($this->facture->getElementFactures() != null) {
            if (count($this->facture->getElementFactures()) != 0) {
                /** @var ElementFacture */
                foreach ($this->facture->getElementFactures() as $elementFacture) {
                    /**
                     * DESTINATION CLIENT
                     */
                    if (FactureCrudController::TAB_DESTINATION[FactureCrudController::DESTINATION_CLIENT] == $this->facture->getDestination()) {
                        //PRIME D'ASSURANCE
                        if ($elementFacture->getIncludePrime() == true) {
                            $this->addNotePourPrime($elementFacture);
                        }
                        //FRAIS DE GESTION
                        if ($elementFacture->getIncludeFraisGestion() == true) {
                            $this->addNotes($elementFacture, FactureCrudController::TYPE_NOTE_FRAIS_DE_GESTION, RevenuCrudController::TYPE_FRAIS_DE_GESTION);
                        }
                    }
                    /**
                     * DESTINATION ASSUREUR
                     */
                    if (FactureCrudController::TAB_DESTINATION[FactureCrudController::DESTINATION_ASSUREUR] == $this->facture->getDestination()) {
                        //COMMISSION DE REASSURANCE
                        if ($elementFacture->getIncludeComReassurance() == true) {
                            $this->addNotes(
                                $elementFacture,
                                FactureCrudController::TYPE_NOTE_COMMISSION_REASSURANCE,
                                RevenuCrudController::TYPE_COM_REA
                            );
                        }
                        //COMMISSION LOCALE
                        if ($elementFacture->getIncludeComLocale() == true) {
                            $this->addNotes(
                                $elementFacture,
                                FactureCrudController::TYPE_NOTE_COMMISSION_LOCALE,
                                RevenuCrudController::TYPE_COM_LOCALE
                            );
                        }
                        //COMMISSION FRONTING
                        if ($elementFacture->getIncludeComFronting() == true) {
                            $this->addNotes(
                                $elementFacture,
                                FactureCrudController::TYPE_NOTE_COMMISSION_FRONTING,
                                RevenuCrudController::TYPE_COM_FRONTING
                            );
                        }
                    }
                    /**
                     * DESTINATION PARTENAIRE
                     */
                    if (FactureCrudController::TAB_DESTINATION[FactureCrudController::DESTINATION_PARTENAIRE] == $this->facture->getDestination()) {
                        //RETRO-COMMISSION
                        if ($elementFacture->getIncludeRetroCom() == true) {
                            $this->addNotes(
                                $elementFacture,
                                FactureCrudController::TYPE_NOTE_RETROCOMMISSIONS,
                                RevenuCrudController::TYPE_COM_REA
                            );
                        }
                    }
                }
            }
        }
        $this->facture->setArticlesNDClientOuAssureur($this->data);
    }

    public function addNotePourPrime(?ElementFacture $elementFacture)
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
            $this->data[] =
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

    public function addNotes(?ElementFacture $elementFacture, ?string $typeFacture, ?string $typeRevenu)
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
            $mntTTC = $elementFacture->getMontantInvoicedPerTypeNote(FactureCrudController::TAB_TYPE_NOTE[$typeFacture]);
            $tva = $tranche->getIndicaRevenuTaxeAssureur(RevenuCrudController::TAB_TYPE[$typeRevenu]);
            $mntHT = $mntTTC - $tva;
            $this->data[] =
                [
                    self::NOTE_NO => $this->indexLigne,
                    self::NOTE_REFERENCE_POLICE => $police->getReference(),
                    self::NOTE_AVENANT => $police->getTypeavenant(),
                    self::NOTE_RISQUE => $police->getProduit()->getCode(),
                    self::NOTE_TRANCHE => $tranche->getNom(),
                    self::NOTE_PERIODE => $tranche->getDateEffet()->format('d/m/Y') . " - " . $tranche->getDateExpiration()->format('d/m/Y'),
                    self::NOTE_TYPE => $typeFacture,
                    self::NOTE_PRIME_TTC => $primeTTC / 100,
                    self::NOTE_PRIME_NETTE => $primeHt / 100,
                    self::NOTE_PRIME_FRONTING => $primeFronting / 100,
                    self::NOTE_PRIME_TVA => $primeTva / 100,
                    self::NOTE_TAUX => ($primeHt != 0) ? (($mntHT / $primeHt) * 100) : 0,
                    self::NOTE_MONTANT_NET => $mntHT / 100,
                    self::NOTE_TVA => $tva / 100,
                    self::NOTE_MONTANT_TTC => $mntTTC / 100
                ];
            $this->indexLigne = $this->indexLigne + 1;
        }
    }
}
