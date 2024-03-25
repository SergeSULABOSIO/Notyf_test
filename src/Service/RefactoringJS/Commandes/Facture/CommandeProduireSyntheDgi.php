<?php

namespace App\Service\RefactoringJS\Commandes\Facture;

use App\Entity\Facture;
use App\Controller\Admin\FactureCrudController;
use App\Controller\Admin\RevenuCrudController;
use App\Entity\ElementFacture;
use App\Entity\Tranche;
use App\Service\RefactoringJS\Commandes\Commande;

class CommandeProduireSyntheDgi implements Commande
{
    public const MODE_SYNTHSE = 0;
    public const MODE_BORDEREAU = 1;
    private ?int $mode = self::MODE_SYNTHSE;


    private $data = [];
    private $dataDetails = [];
    //A trouver
    private $risquePrimeGross = 0;
    private $risquePrimeNette = 0;
    private $risqueFronting = 0;
    private $revenuNette = 0;
    private $revenuTaxeAssureur = 0;
    private $revenuTaxeAssureurPayee = 0;
    private $revenuTaxeAssureurSolde = 0;
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
        $this->revenuTaxeAssureur = 0;
        $this->revenuTaxeAssureurPayee = 0;
        $this->revenuTaxeAssureurSolde = 0;
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
            case self::MODE_SYNTHSE:
                //Chargement des cellules du tableau
                $this->data[self::NOMBRE_ARTICLE] = $this->nbArticles;
                $this->data[self::NOTE_PRIME_TTC] = $this->risquePrimeGross / 100;
                $this->data[self::NOTE_PRIME_FRONTING] = $this->risqueFronting / 100;
                $this->data[self::NOTE_PRIME_NETTE] = $this->risquePrimeNette / 100;
                $this->data[self::NOTE_TAUX] = $this->revenuTaux;
                $this->data[self::REVENU_NET] = $this->revenuNette / 100;
                $this->data[self::REVENU_TAXE_ASSUREUR] = $this->revenuTaxeAssureur / 100;
                $this->data[self::REVENU_TAXE_ASSUREUR_PAYEE] = $this->revenuTaxeAssureurPayee / 100;
                $this->data[self::REVENU_TAXE_ASSUREUR_SOLDE] = $this->revenuTaxeAssureurSolde / 100;
                //Chargement du tableau dans la facture
                $this->facture->setSynthseNCDgi($this->data);
                break;

            case self::MODE_BORDEREAU:
                //Chargement des cellules du tableau
                dd("En cours de construction.");
                $this->facture->setNotesElementsNCDgi($this->dataDetails);
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
             * DESTINATION DGI
             */
            if (FactureCrudController::TAB_DESTINATION[FactureCrudController::DESTINATION_DGI] == $this->facture->getDestination()) {
                //POUR TAXE ASSUREUR UNIQUEMENT
                if ($elementFacture->getIncludeTaxeAssureur() == true) {
                    /** @var Tranche */
                    $tranche = $elementFacture->getTranche();
                    if ($tranche != null) {
                        switch ($this->mode) {
                            case self::MODE_SYNTHSE:
                                //Calculs sur la prime d'assurance
                                $this->risquePrimeGross = $this->risquePrimeGross + $tranche->getPrimeTotaleTranche();
                                $this->risquePrimeNette = $this->risquePrimeNette + $tranche->getPrimeNetteTranche();
                                $this->risqueFronting = $this->risqueFronting + $tranche->getFrontingTranche();

                                $this->revenuNette = $this->revenuNette + $tranche->getIndicaRevenuNet();
                                $this->revenuTaxeAssureur = $this->revenuTaxeAssureur + $tranche->getIndicaRevenuTaxeAssureur();
                                $this->revenuTaxeAssureurPayee = $this->revenuTaxeAssureurPayee + $tranche->getTaxeAssureurPayee();
                                $this->revenuTaxeAssureurSolde = $this->revenuTaxeAssureurSolde + $tranche->getTaxeAssureurSolde();
                                break;

                            case self::MODE_BORDEREAU:
                                dd("Chargement des lignes...");
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

    public function addNotes(?ElementFacture $elementFacture, ?string $typeFacture, ?string $typeRevenu)
    {
        /** @var Tranche */
        $tranche = $elementFacture->getTranche();
        if ($tranche != null) {
            /** @var Police */

            $this->revenuTaxeAssureur = $this->revenuTaxeAssureur + $tranche->getIndicaRevenuTaxeAssureur();
            $this->revenuTaxeAssureurPayee = $this->revenuTaxeAssureurPayee + $tranche->getTaxeAssureurPayee();
            $this->revenuTaxeAssureurSolde = $this->revenuTaxeAssureurSolde + $tranche->getTaxeAssureurSolde();
            
            
            $police = $tranche->getPolice();
            $primeTTC = $tranche->getPrimeTotaleTranche();
            $primeHt = $tranche->getPrimeNetteTranche();
            $primeTva = $tranche->getTvaTranche();
            $primeFronting = $tranche->getFrontingTranche();
            
            $revenuNet = $tranche->getIndicaRevenuNet();
            

            $this->data[] =
                [
                    self::NOTE_NO => $this->nbArticles,
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
        }
    }
}
