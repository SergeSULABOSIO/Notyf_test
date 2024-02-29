<?php

namespace App\Service\RefactoringJS\AutresClasses;

use App\Entity\Taxe;
use NumberFormatter;
use App\Entity\Entreprise;
use App\Controller\Admin\MonnaieCrudController;

abstract class JSAbstractFinances
{
    public ?float $montantReceivedPerDestination = 0;
    public ?float $montantReceivedPerTypeNote = 0;
    public ?float $montantInvoicedPerDestination = 0;
    public ?float $montantInvoicedPerTypeNote = 0;

    public abstract function initEntreprise():?Entreprise;

    private function getMonnaie($fonction)
    {
        if ($this->initEntreprise() != null) {
            foreach ($this->initEntreprise()->getMonnaies() as $monnaie) {
                //dd($fonction);
                if ($monnaie->getFonction() == $fonction) {
                    return $monnaie;
                }
            }
        }

        return null;
    }

    public function getMonnaie_Saisie()
    {
        $monnaie = $this->getMonnaie(MonnaieCrudController::TAB_MONNAIE_FONCTIONS[MonnaieCrudController::FONCTION_SAISIE_ET_AFFICHAGE]);
        if ($monnaie == null) {
            $monnaie = $this->getMonnaie(MonnaieCrudController::TAB_MONNAIE_FONCTIONS[MonnaieCrudController::FONCTION_SAISIE_UNIQUEMENT]);
        }
        return $monnaie;
    }

    public function getMonnaie_Affichage()
    {
        $monnaie = $this->getMonnaie(MonnaieCrudController::TAB_MONNAIE_FONCTIONS[MonnaieCrudController::FONCTION_SAISIE_ET_AFFICHAGE]);
        if ($monnaie == null) {
            $monnaie = $this->getMonnaie(MonnaieCrudController::TAB_MONNAIE_FONCTIONS[MonnaieCrudController::FONCTION_AFFICHAGE_UNIQUEMENT]);
        }
        return $monnaie;
    }

    public function getMontantEnMonnaieAffichage($montant): string
    {
        //Monnaie de saisie
        $monnaieSaisie = $this->getMonnaie_Saisie();
        if ($monnaieSaisie != null) {
            $tauxUSDSaisie = $monnaieSaisie->getTauxusd() / 100;
            //Montant saisie en USD
            $mntInputInUSD = $montant * $tauxUSDSaisie;
            //Monnaie d'affichage
            $monnaieAffichage = $this->getMonnaie_Affichage();
            $tauxUSDAffichage = $monnaieAffichage->getTauxusd() / 100;
            //Montant saisie en Monnaie d'affichage
            $mntOutput = ($mntInputInUSD / $tauxUSDAffichage) / 100;
            //Application du format monnétaire en anglais
            $fmt = numfmt_create('en_US', NumberFormatter::CURRENCY);
            return numfmt_format_currency($fmt, $mntOutput, $monnaieAffichage->getCode());
        }else{
            return $montant . "...";
        }
    }


    public function getTaxe(bool $payableParCourtier): ?Taxe
    {
        if ($this->initEntreprise() != null) {
            foreach ($this->initEntreprise()->getTaxes() as $taxe) {
                if ($taxe->isPayableparcourtier() == $payableParCourtier) {
                    return $taxe;
                }
            }
        }
        return null;
    }

    public function getNomTaxeCourtier()
    {
        /** @var Taxe */
        $taxe = $this->getTaxe(true);
        $txt = $taxe != null ? strtolower($taxe->getNom() . "") : "Tx Courtier";
        return $txt;
    }

    public function getTauxTaxeBranche(bool $isIard, bool $isForCourtier)
    {
        /** @var Taxe */
        $taxe = $this->getTaxe($isForCourtier);
        if ($isIard == true) {
            return $taxe != null ? $taxe->getTauxIARD() : 0;
        } else {
            return $taxe != null ? $taxe->getTauxVIE() : 0;
        }
    }

    public function getNomTaxeAssureur()
    {
        /** @var Taxe */
        $taxe = $this->getTaxe(false);
        $txt = $taxe != null ? strtolower($taxe->getNom() . "") : "Tx Assureur";
        return $txt;
    }

    /**
     * Get the value of montantReceivedPerDestination
     */ 
    public abstract function getMontantReceivedPerDestination(?int $destination);

    /**
     * Get the value of montantReceivedPerTypeNote
     */ 
    public abstract function getMontantReceivedPerTypeNote(?int $typeNote);

    /**
     * Get the value of montantInvoicedPerDestination
     */ 
    public abstract function getMontantInvoicedPerDestination(?int $destination);

    /**
     * Get the value of montantInvoicedPerTypeNote
     */ 
    public abstract function getMontantInvoicedPerTypeNote(?int $typeNote);
}
