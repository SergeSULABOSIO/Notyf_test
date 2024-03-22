<?php

namespace App\Service\RefactoringJS\Commandes\Facture;

use App\Entity\Facture;
use App\Controller\Admin\FactureCrudController;
use App\Controller\Admin\RevenuCrudController;
use App\Entity\ElementFacture;
use App\Entity\Tranche;
use App\Service\RefactoringJS\Commandes\Commande;

class CommandeProduireSyntheArca implements Commande
{
    private $data = [];
    //A trouver
    private $risquePrimeGross = 0;
    private $risquePrimeNette = 0;
    private $risqueFronting = 0;
    private $revenuNette = 0;
    private $revenuTaxeCourtier = 0;
    private $revenuTaxeCourtierPayee = 0;
    private $revenuTaxeCourtierSolde = 0;
    //A calculer
    private $revenuTaxeCourtierTaux = 0;    //en %
    private $revenuTaux = 0;
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
        $this->revenuNette = 0;
        $this->revenuTaxeCourtier = 0;
        $this->revenuTaxeCourtierPayee = 0;
        $this->revenuTaxeCourtierSolde = 0;
        $this->revenuTaxeCourtierTaux = 0;
        $this->nbArticles = 0;
    }

    private function calculerTaux()
    {
        $this->revenuTaux = ($this->risquePrimeNette !== 0) ? round(($this->revenuNette / $this->risquePrimeNette) * 100) : 0;
        $this->revenuTaxeCourtierTaux = ($this->revenuTaxeCourtier !== 0) ? round(($this->revenuTaxeCourtier / $this->revenuNette) * 100) : 0;
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
        $this->data[self::REVENU_NET] = $this->revenuNette / 100;
        $this->data[self::REVENU_TAXE_COURTIER] = $this->revenuTaxeCourtier / 100;
        $this->data[self::REVENU_TAXE_COURTIER_PAYEE] = $this->revenuTaxeCourtierPayee / 100;
        $this->data[self::REVENU_TAXE_COURTIER_SOLDE] = $this->revenuTaxeCourtierSolde / 100;
        //Chargement du tableau dans la facture
        $this->facture->setSynthseNCArca($this->data);
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
                        $partageable_oui = RevenuCrudController::TAB_PARTAGEABLE[RevenuCrudController::PARTAGEABLE_OUI];
                        $typeRevenu = null;
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
                        $this->nbArticles++;
                    }
                }
            }
        }
        $this->chargerData();
    }
}
