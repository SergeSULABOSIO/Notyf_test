<?php

namespace App\Service\RefactoringJS\Commandes\Facture;

use App\Entity\Facture;
use App\Controller\Admin\FactureCrudController;
use App\Entity\ElementFacture;
use App\Entity\Tranche;
use App\Service\RefactoringJS\Commandes\Commande;

class CommandeProduireSyntheArca implements Commande
{
    public const MODE_SYNTHESE = 0;
    public const MODE_BORDEREAU = 1;
    private ?int $mode = self::MODE_SYNTHESE;


    private $data = [];
    private $dataDetails = [];
    //A trouver
    private $risquePrimeGross = 0;
    private $risquePrimeNette = 0;
    private $risqueFronting = 0;
    private $revenuNette = 0;
    private $revenuTaxeCourtier = 0;
    private $revenuTaxeCourtierPayee = 0;
    private $revenuTaxeCourtierSolde = 0;
    //A calculer
    private $revenuTaux = 0;
    private $nbArticles = 0;

    public function __construct(private ?Facture $facture, ?int $mode)
    {
        $this->mode = $mode;
    }

    private function resetAggregats()
    {
        $this->risquePrimeGross = 0;
        $this->risquePrimeNette = 0;
        $this->risqueFronting = 0;
        $this->revenuTaux = 0;
        $this->revenuNette = 0;
        $this->revenuTaxeCourtier = 0;
        $this->revenuTaxeCourtierPayee = 0;
        $this->revenuTaxeCourtierSolde = 0;
        $this->nbArticles = 0;
    }

    private function calculerTaux()
    {
        $this->revenuTaux = ($this->risquePrimeNette !== 0) ? round(($this->revenuNette / $this->risquePrimeNette) * 100, 2) : 0;
    }

    private function chargerData()
    {
        //Calcul des valeurs calculables
        $this->calculerTaux();
        switch ($this->mode) {
            case self::MODE_SYNTHESE:
                //Chargement des cellules du tableau
                $this->data[self::NOMBRE_ARTICLE] = $this->nbArticles;
                $this->data[self::NOTE_PRIME_TTC] = $this->risquePrimeGross / 100;
                $this->data[self::NOTE_PRIME_FRONTING] = $this->risqueFronting / 100;
                $this->data[self::NOTE_PRIME_NETTE] = $this->risquePrimeNette / 100;
                $this->data[self::NOTE_TAUX] = $this->revenuTaux;
                $this->data[self::REVENU_NET] = $this->revenuNette / 100;
                $this->data[self::REVENU_TAXE_COURTIER] = $this->revenuTaxeCourtier / 100;
                $this->data[self::REVENU_TAXE_COURTIER_PAYEE] = $this->revenuTaxeCourtierPayee / 100;
                $this->data[self::REVENU_TAXE_COURTIER_SOLDE] = $this->revenuTaxeCourtierSolde / 100;
                //Chargement du tableau dans la facture
                $this->facture->setSynthseNCArca($this->data);
                break;

            case self::MODE_BORDEREAU:
                //Chargement des cellules du tableau
                $this->facture->setNotesElementsNCArca($this->dataDetails);
                break;

            default:
                dd("Mode non pris en compte par l'application.");
                break;
        }
    }

    public function executer()
    {
        if ($this->facture->getElementFactures() != null) {
            if (count($this->facture->getElementFactures()) != 0) {
                $this->setSynthese();
            }
        }
        // dd($this->data);
    }

    public function setSynthese()
    {
        $this->resetAggregats();

        /** @var ElementFacture */
        foreach ($this->facture->getElementFactures() as $elementFacture) {
            /**
             * DESTINATION ARCA
             */
            if (FactureCrudController::TAB_DESTINATION[FactureCrudController::DESTINATION_ARCA] == $this->facture->getDestination()) {
                //POUR TAXE COURTIER UNIQUEMENT
                if ($elementFacture->getIncludeTaxeCourtier() == true) {
                    /** @var Tranche */
                    $tranche = $elementFacture->getTranche();
                    if ($tranche != null) {
                        switch ($this->mode) {
                            case self::MODE_SYNTHESE:
                                //Calculs sur la prime d'assurance
                                $this->risquePrimeGross = $this->risquePrimeGross + $tranche->getPrimeTotaleTranche();
                                $this->risquePrimeNette = $this->risquePrimeNette + $tranche->getPrimeNetteTranche();
                                $this->risqueFronting = $this->risqueFronting + $tranche->getFrontingTranche();
                                //Calculs sur le revenu partageable
                                $this->revenuNette = $this->revenuNette + $tranche->getIndicaRevenuNet();
                                $this->revenuTaxeCourtier = $this->revenuTaxeCourtier + $tranche->getIndicaRevenuTaxeCourtier();
                                $this->revenuTaxeCourtierPayee = $this->revenuTaxeCourtierPayee + $tranche->getTaxeCourtierPayee();
                                $this->revenuTaxeCourtierSolde = $this->revenuTaxeCourtierSolde + $tranche->getTaxeCourtierSolde();
                                //Incrémente le compteur d'articles
                                break;

                            case self::MODE_BORDEREAU:
                                // dd("Chargement des lignes...");
                                $this->addNote($elementFacture);
                                break;

                            default:
                                # code...
                                break;
                        }
                        //Incrémente le compteur d'articles
                        $this->nbArticles++;
                    }
                }
            }
        }
        $this->chargerData();
    }

    public function addNote(?ElementFacture $elementFacture)
    {
        /** @var Tranche */
        $tranche = $elementFacture->getTranche();
        if ($tranche != null) {
            /** @var Police */
            $police = $tranche->getPolice();

            /** @var Client */
            $client = $tranche->getClient();

            $primeTTC = $tranche->getPrimeTotaleTranche();
            $primeHt = $tranche->getPrimeNetteTranche();
            $primeFronting = $tranche->getFrontingTranche();

            $revenuNet = $tranche->getIndicaRevenuNet();
            $revenuTaxeCourtier = $tranche->getIndicaRevenuTaxeCourtier();
            $revenuTaxeCourtierPayee = $tranche->getTaxeCourtierPayee();
            $revenuTaxeCourtierSolde = $tranche->getTaxeCourtierSolde();

            $this->dataDetails[] =
                [
                    self::NOTE_NO => $this->nbArticles,
                    self::NOTE_REFERENCE_POLICE => $police->getReference(),
                    self::NOTE_AVENANT => $police->getTypeavenant(),
                    self::NOTE_RISQUE => $police->getProduit()->getCode(),
                    self::NOTE_CLIENT => $client->getNom(),
                    self::NOTE_TRANCHE => $tranche->getNom(),
                    self::NOTE_PERIODE => $tranche->getDateEffet()->format('d/m/Y') . " - " . $tranche->getDateExpiration()->format('d/m/Y'),
                    self::NOTE_PRIME_TTC => $primeTTC / 100,
                    self::NOTE_PRIME_NETTE => $primeHt / 100,
                    self::NOTE_PRIME_FRONTING => $primeFronting / 100,
                    self::NOTE_TAUX => ($primeHt != 0) ? (($revenuNet / $primeHt) * 100) : 0,
                    self::REVENU_NET => $revenuNet / 100,
                    self::REVENU_TAXE_COURTIER_TAUX => ($revenuNet != 0) ? (($revenuTaxeCourtier / $revenuNet) * 100) : 0,
                    self::REVENU_TAXE_COURTIER => $revenuTaxeCourtier / 100,
                    self::REVENU_TAXE_COURTIER_PAYEE => $revenuTaxeCourtierPayee / 100,
                    self::REVENU_TAXE_COURTIER_SOLDE => $revenuTaxeCourtierSolde / 100,
                ];
        }
    }
}
