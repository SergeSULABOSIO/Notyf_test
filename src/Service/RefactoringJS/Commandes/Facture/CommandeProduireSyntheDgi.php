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
    private $data = [];
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
        $this->revenuTaxeAssureur = 0;
        $this->revenuTaxeAssureurPayee = 0;
        $this->revenuTaxeAssureurSolde = 0;
        $this->nbArticles = 0;
    }

    private function calculerTaux()
    {
        $this->revenuTaux = ($this->risquePrimeNette !== 0) ? round(($this->revenuNette / $this->risquePrimeNette) * 100) : 0;
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
        $this->data[self::REVENU_TAXE_ASSUREUR] = $this->revenuTaxeAssureur / 100;
        $this->data[self::REVENU_TAXE_ASSUREUR_PAYEE] = $this->revenuTaxeAssureurPayee / 100;
        $this->data[self::REVENU_TAXE_ASSUREUR_SOLDE] = $this->revenuTaxeAssureurSolde / 100;
        //Chargement du tableau dans la facture
        $this->facture->setSynthseNCDgi($this->data);
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
            if (FactureCrudController::TAB_DESTINATION[FactureCrudController::DESTINATION_DGI] == $this->facture->getDestination()) {
                //POUR TAXE ASSUREUR UNIQUEMENT
                if ($elementFacture->getIncludeTaxeAssureur() == true) {
                    /** @var Tranche */
                    $tranche = $elementFacture->getTranche();
                    if ($tranche != null) {
                        //Calculs sur la prime d'assurance
                        $this->risquePrimeGross = $this->risquePrimeGross + $tranche->getPrimeTotaleTranche();
                        $this->risquePrimeNette = $this->risquePrimeNette + $tranche->getPrimeNetteTranche();
                        $this->risqueFronting = $this->risqueFronting + $tranche->getFrontingTranche();
                        //Calculs sur le revenu partageable
                        $this->revenuNette = $this->revenuNette + $tranche->getIndicaRevenuNet();
                        $this->revenuTaxeAssureur = $this->revenuTaxeAssureur + $tranche->getIndicaRevenuTaxeAssureur();
                        $this->revenuTaxeAssureurPayee = $this->revenuTaxeAssureurPayee + $tranche->getTaxeAssureurPayee();
                        $this->revenuTaxeAssureurSolde = $this->revenuTaxeAssureurSolde + $tranche->getTaxeAssureurSolde();
                        //Incrémente le compteur d'articles
                        $this->nbArticles++;
                    }
                }
            }
        }
        $this->chargerData();
    }
}
