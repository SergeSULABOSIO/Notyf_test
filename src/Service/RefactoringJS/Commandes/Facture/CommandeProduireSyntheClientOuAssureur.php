<?php

namespace App\Service\RefactoringJS\Commandes\Facture;

use App\Entity\Facture;
use App\Controller\Admin\FactureCrudController;
use App\Controller\Admin\RevenuCrudController;
use App\Entity\ElementFacture;
use App\Entity\Tranche;
use App\Service\RefactoringJS\Commandes\Commande;

class CommandeProduireSyntheClientOuAssureur implements Commande
{
    private $data = [];
    //A trouver
    private $totalApayer = 0;
    //A calculer
    private $nbArticles = 0;

    public function __construct(private ?Facture $facture)
    {
    }

    private function resetAggregats()
    {
        $this->totalApayer = 0;
        $this->nbArticles = 0;
    }

    private function chargerData()
    {
        //Chargement des cellules du tableau
        $this->data[self::NOMBRE_ARTICLE] = $this->nbArticles;
        $this->data[self::NOTE_TOTAL_A_PAYER] = $this->totalApayer / 100;
        //Chargement du tableau dans la facture
        $this->facture->setSynthseNDClientOuAssureur($this->data);
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
             * DESTINATION CLIENT
             */
            if (FactureCrudController::TAB_DESTINATION[FactureCrudController::DESTINATION_CLIENT] == $this->facture->getDestination()) {
                //POUR PRIME UNIQUEMENT
                $prime = 0;
                if ($elementFacture->getIncludePrime() == true) {
                    /** @var Tranche */
                    $tranche = $elementFacture->getTranche();
                    if ($tranche != null) {
                        $prime = $prime + $tranche->getPrimeTotaleTranche();
                        //Incrémente le compteur d'articles
                        $this->nbArticles++;
                    }
                }
                //POUR FRAIS DE GESTION
                $frais_de_gestion = 0;
                if ($elementFacture->getIncludeFraisGestion() == true) {
                    /** @var Tranche */
                    $tranche = $elementFacture->getTranche();
                    if ($tranche != null) {
                        $frais_de_gestion = $frais_de_gestion + $tranche->getComFraisGestion();
                        //Incrémente le compteur d'articles
                        $this->nbArticles++;
                    }
                }
                $this->totalApayer = $prime + $frais_de_gestion;
            }
            /**
             * DESTINATION ASSUREUR
             */
            if (FactureCrudController::TAB_DESTINATION[FactureCrudController::DESTINATION_ASSUREUR] == $this->facture->getDestination()) {
                //POUR COMMISSION DE REASSURANCE
                $commission_reassurance = 0;
                if ($elementFacture->getIncludeComReassurance() == true) {
                    /** @var Tranche */
                    $tranche = $elementFacture->getTranche();
                    if ($tranche != null) {
                        $commission_reassurance = $commission_reassurance + $tranche->getComReassurance();
                        //Incrémente le compteur d'articles
                        $this->nbArticles++;
                    }
                }
                //POUR COMMISSION LOCALE
                $commission_locale = 0;
                if ($elementFacture->getIncludeComLocale() == true) {
                    /** @var Tranche */
                    $tranche = $elementFacture->getTranche();
                    if ($tranche != null) {
                        $commission_locale = $commission_locale + $tranche->getComLocale();
                        //Incrémente le compteur d'articles
                        $this->nbArticles++;
                    }
                }
                //POUR COMMISSION SUR FRONTING
                $commission_fronting = 0;
                if ($elementFacture->getIncludeComFronting() == true) {
                    /** @var Tranche */
                    $tranche = $elementFacture->getTranche();
                    if ($tranche != null) {
                        $commission_fronting = $commission_fronting + $tranche->getComFronting();
                        //Incrémente le compteur d'articles
                        $this->nbArticles++;
                    }
                }
                $this->totalApayer = $commission_reassurance + $commission_locale + $commission_fronting;
            }
        }
        $this->chargerData();
    }
}
