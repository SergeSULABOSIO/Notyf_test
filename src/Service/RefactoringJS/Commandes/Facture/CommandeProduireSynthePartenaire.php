<?php

namespace App\Service\RefactoringJS\Commandes\Facture;

use App\Entity\Facture;
use App\Controller\Admin\FactureCrudController;
use App\Entity\ElementFacture;
use App\Entity\Tranche;
use App\Service\RefactoringJS\Commandes\Commande;

class CommandeProduireSynthePartenaire implements Commande
{
    private $data = [];
    //A trouver
    private $risquePrimeGross = 0;
    private $risquePrimeNette = 0;
    private $risqueFronting = 0;
    private $revenuGrossPartageable = 0;
    private $revenuTvaPartageable = 0;
    private $revenuArcaPartageable = 0;
    private $revenuRetrocommissionPayee = 0;
    private $revenuRetrocommissionSolde = 0;
    //A calculer
    private $revenuTaux = 0;    //en %
    private $partPartenaire = 0;    //en %
    private $revenuAssiettePartageable = 0;
    private $revenuRetrocommission = 0;
    private $nbArticles = 0;

    public function __construct(private ?Facture $facture)
    {
    }

    private function resetAggregats()
    {
        $this->risquePrimeGross = 0;
        $this->risquePrimeNette = 0;
        $this->risqueFronting = 0;
        $this->revenuTaux = 0;
        $this->revenuGrossPartageable = 0;
        $this->revenuTvaPartageable = 0;
        $this->revenuArcaPartageable = 0;
        $this->revenuAssiettePartageable = 0;
        $this->partPartenaire = 0;
        $this->revenuRetrocommission = 0;
        $this->revenuRetrocommissionPayee = 0;
        $this->revenuRetrocommissionSolde = 0;
        $this->nbArticles = 0;
    }

    private function calculerTaux()
    {
        $this->revenuTaux = round(($this->revenuAssiettePartageable / $this->risquePrimeNette) * 100);
        $this->partPartenaire = ($this->revenuAssiettePartageable != 0) ? round(($this->revenuRetrocommission / $this->revenuAssiettePartageable) * 100) : 0 ;
        $this->revenuAssiettePartageable = round(
            $this->revenuGrossPartageable -
                $this->revenuTvaPartageable -
                $this->revenuArcaPartageable
        );
        $this->revenuRetrocommission = round(($this->partPartenaire * $this->revenuAssiettePartageable) * 100);
    }

    private function chargerData()
    {
        //Calcul des valeurs calculables
        $this->calculerTaux();
        //Chargement des cellules du tableau
        $this->data[self::NOMBRE_ARTICLE] = $this->nbArticles;
        $this->data[self::NOTE_PRIME_TTC] = $this->risquePrimeGross / 100;
        $this->data[self::NOTE_PRIME_FRONTING] = $this->risqueFronting / 100;
        $this->data[self::NOTE_PRIME_NETTE] = $this->risquePrimeNette / 100;
        $this->data[self::NOTE_TAUX] = $this->revenuTaux;
        $this->data[self::REVENU_GROSS_PARTAGEABLE] = $this->revenuGrossPartageable / 100;
        $this->data[self::REVENU_TVA_PARTAGEABLE] = $this->revenuTvaPartageable / 100;
        $this->data[self::REVENU_ARCA_PARTAGEABLE] = $this->revenuArcaPartageable / 100;
        $this->data[self::REVENU_ASSIETTE_PARTAGEABLE] = $this->revenuAssiettePartageable / 100;
        $this->data[self::PARTENAIRE_PART] = $this->partPartenaire;
        $this->data[self::PARTENAIRE_RETRCOMMISSION] = $this->revenuRetrocommission / 100;
        $this->data[self::PARTENAIRE_RETRCOMMISSION_PAYEE] = $this->revenuRetrocommissionPayee / 100;
        $this->data[self::PARTENAIRE_RETRCOMMISSION_SOLDE] = $this->revenuRetrocommissionSolde / 100;
        //Chargement du tableau dans la facture
        $this->facture->setSynthseNCPartenaire($this->data);
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
             * DESTINATION PARTENAIRE
             */
            if (FactureCrudController::TAB_DESTINATION[FactureCrudController::DESTINATION_PARTENAIRE] == $this->facture->getDestination()) {
                //POUR RETROCOMMISSION UNIQUEMENT
                if ($elementFacture->getIncludeRetroCom() == true) {
                    /** @var Tranche */
                    $tranche = $elementFacture->getTranche();
                    if ($tranche != null) {
                        $this->risquePrimeGross = $this->risquePrimeGross + $tranche->getPrimeTotaleTranche();
                        $this->risquePrimeNette = $this->risquePrimeNette + $tranche->getPrimeNetteTranche();
                        $this->risqueFronting = $this->risqueFronting + $tranche->getFrontingTranche();
                        //A completer
                        $this->revenuGrossPartageable = $this->revenuGrossPartageable + 0;
                        $this->revenuTvaPartageable = $this->revenuTvaPartageable + 0;
                        $this->revenuArcaPartageable = $this->revenuArcaPartageable + 0;
                        $this->revenuRetrocommissionPayee = $this->revenuRetrocommissionPayee + $tranche->getRetroCommissionTotalePayee();
                        $this->revenuRetrocommissionSolde = $this->revenuRetrocommissionSolde + $tranche->getRetroCommissionTotaleSolde();
                        //Incrémente le compteur d'articles
                        $this->nbArticles++;
                    }
                }
            }
        }
        $this->chargerData();
    }
}
