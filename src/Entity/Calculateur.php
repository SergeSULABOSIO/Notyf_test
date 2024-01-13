<?php

namespace App\Entity;

use App\Controller\Admin\RevenuCrudController;
use App\Controller\Admin\MonnaieCrudController;
use App\Controller\Admin\ChargementCrudController;
use Doctrine\Common\Collections\Collection;

class Calculateur
{
    public const PARAMETRE_TAXE_forCOURTIER = "Taxe_forCourtier";
    public const PARAMETRE_isPARTAGEABLE = "isPartageable";
    public const PARAMETRE_isPAYABLE_PAR_CLIENT = "isPayableParClient";
    public const PARAMETRE_TRANCHE = "tranche";
    public const PARAMETRE_REVENU = "revenu";



    private ?Police $police;
    private ?Cotation $cotation;
    private ?Piste $piste;
    private ?Client $client;
    private ?Entreprise $entreprise;
    private ?Collection $comptesBancaires;
    private ?Utilisateur $utilisateur;
    private ?Produit $produit;
    private ?Partenaire $partenaire;
    private ?Assureur $assureur;
    private ?string $codeMonnaie;
    private ?Monnaie $monnaie;
    private ?Taxe $taxeAssureur;
    private ?Taxe $taxeCourtier;





    public function __construct()
    {
    }


    public function getMontantTaxe(?Revenu $revenu, ?bool $forCourtier): float
    {
        //dd($parametres);
        $this->setCotation($revenu->getCotation());
        $montantTaxe = 0;
        if ($revenu != null & $this->piste != null && $this->client != null) {
            foreach ($this->getTaxes() as $taxe) {
                if ($taxe->isPayableparcourtier() == $forCourtier) {
                    if ($this->client->isExoneree() == true || $revenu->getTaxable() == false) {
                        $montantTaxe = (0 * $this->getRevenufinaleHT_valeur($revenu));
                        break;
                    } else {
                        if ($this->produit->isIard()) {
                            $montantTaxe = ($taxe->getTauxIARD() * $this->getRevenufinaleHT_valeur($revenu));
                            break;
                        } else {
                            $montantTaxe = ($taxe->getTauxVIE() * $this->getRevenufinaleHT_valeur($revenu));
                            break;
                        }
                    }
                }
            }
        }
        //dd($montantTaxe);
        return $montantTaxe;
    }

    public function getRevenufinaleHT_valeur(?Revenu $revenu)
    {
        //dd($parametres);
        return $this->getRevenufinaleHT($revenu)["montant_ht_valeur_numerique"];
    }

    public function getRevenufinaleHT_description(?Revenu $revenu)
    {
        return $this->getRevenufinaleHT($revenu)["montant_ht_description"];
    }

    public function getRevenufinaleHT_formule(?Revenu $revenu)
    {
        return $this->getRevenufinaleHT($revenu)["montant_ht_formule"];
    }

    public function getRevenufinaleHTGlobale(?bool $isPartageable)
    {
        $tot = 0;
        foreach ($this->cotation->getRevenus() as $revenu) {
            if ($isPartageable == $revenu->getPartageable()) {
                $tot = $tot + $this->getRevenufinaleHT_valeur($revenu);
            }
        }
        return $tot;
    }

    public function getPrimeTotale(?array $parametres)
    {
        if (isset($parametres[self::PARAMETRE_TRANCHE])) {
            return $this->getChargement([]) * ($parametres[self::PARAMETRE_TRANCHE])->getTaux();
        } else {
            return $this->getChargement([]);
        }
    }

    public function getChargement(?array $parametres)
    {
        $tot = 0;
        if ($this->cotation->getChargements()) {
            foreach ($this->cotation->getChargements() as $chargement) {
                if (isset($parametres["type"])) {
                    if ($chargement->getType() == $parametres["type"]) {
                        $tot = $tot + $chargement->getMontant();
                    }
                } else {
                    $tot = $tot + $chargement->getMontant();
                }
            }
        }
        return $tot;
    }

    public function getRetroComPartenaire(?array $parametres): float
    {
        $taux = 0;
        if ($this->cotation->getTauxretrocompartenaire() == 0) {
            if ($this->partenaire) {
                $taux = $this->partenaire->getPart();
            }
        } else {
            $taux = $this->cotation->getTauxretrocompartenaire();
        }

        if (isset($parametres[self::PARAMETRE_TRANCHE])) {
            return $this->getRevenuPureGlobalePartageable() * $taux * $parametres[self::PARAMETRE_TRANCHE]->getTaux();
        } else {
            return $this->getRevenuPureGlobalePartageable() * $taux;
        }
    }

    public function getRevenuPureGlobalePartageable()
    {
        return $this->getRevenufinaleHTGlobale(true) - $this->getMontantTaxeGlobal(null, true);
    }

    public function getRevenuPureGlobale(?Tranche $tranche, ?bool $isPartageable, ?bool $forCourtier)
    {
        return $this->getRevenufinaleHTGlobale($isPartageable) - $this->getMontantTaxeGlobal($tranche, $forCourtier);
    }

    /* public function getRevenuPure(?Revenu $revenu, ?bool $forCourtier)
    {
        $net = $this->getRevenufinaleHT_valeur($revenu);
        $parametres[Calculateur::PARAMETRE_TAXE_forCOURTIER] = true;
        $taxe = $this->getMontantTaxe($revenu, $forCourtier);
        $comPure = $net - $taxe;
        //dd($parametres, $comPure);
        return $comPure;
    } */

    public function getRevenuTTC(?Revenu $revenu)
    {
        $net = $this->getRevenufinaleHT_valeur($revenu);
        // $parametres[Calculateur::PARAMETRE_TAXE_forCOURTIER] = false;
        $comPure = $net + $this->getMontantTaxe($revenu, true);
        return $comPure;
    }

    public function getRevenuTTCGlobal(?Revenu $revenu, ?bool $forCourtier, ?Tranche $tranche)
    {
        $tot = 0;
        if ($revenu != null) {
            $tot = $tot + $this->getRevenuTTC($revenu, $forCourtier);
        } else {
            foreach ($this->cotation->getRevenus() as $rev) {
                $tot = $tot + $this->getRevenuTTC($rev, $forCourtier);
            }
        }

        if ($tranche != null) {
            $tot = $tot * $tranche->getTaux();
        }
        return $tot;
    }

    public function getCommissionTTCGlobal(?array $parametres)
    {
        $tot = 0;
        foreach ($this->cotation->getRevenus() as $revenu) {
            if ($revenu->getType() == RevenuCrudController::TAB_TYPE[RevenuCrudController::TYPE_COM_LOCALE]) {
                $parametres[Calculateur::PARAMETRE_REVENU] = $revenu;
                $tot = $tot + $this->getRevenuTTC($revenu, true);
            }
        }
        if (isset($parametres[self::PARAMETRE_TRANCHE])) {
            $tot = $tot * $parametres[self::PARAMETRE_TRANCHE]->getTaux();
        }
        return $tot;
    }

    public function getFraisGestionTTCGlobal(?Tranche $tranche)
    {
        $tot = 0;
        foreach ($this->cotation->getRevenus() as $revenu) {
            if ($revenu->getType() == RevenuCrudController::TAB_TYPE[RevenuCrudController::TYPE_FRAIS_DE_GESTION]) {
                $tot = $tot + $this->getRevenuTTC($revenu);
            }
        }
        if ($tranche != null) {
            $tot = $tot * $tranche->getTaux();
        }
        return $tot;
    }


    public function getMontantTaxeGlobal(?Tranche $tranche, ?bool $forCourtier)
    {
        $tot = 0;
        foreach ($this->cotation->getRevenus() as $revenu) {
            $tot = $tot + $this->getMontantTaxe($revenu, $forCourtier);
        }
        if ($tranche != null) {
            return $tot * $tranche->getTaux();
        } else {
            return $tot;
        }
    }

    public function getRevenufinaleHT(?Revenu $revenu): array
    {
        $this->setEntreprise($revenu->getEntreprise());
        $strBase = "";
        foreach (RevenuCrudController::TAB_BASE as $key => $value) {
            if ($value == $revenu->getBase()) {
                $strBase = $key;
            }
        }
        $data = [];
        $prmNette = 0;
        $fronting = 0;
        if ($revenu->getCotation()) {
            $prmNette = ($revenu->getCotation()->getChargement(ChargementCrudController::TAB_TYPE_CHARGEMENT_ORDINAIRE[ChargementCrudController::TYPE_PRIME_NETTE]) / 100);
            $fronting = ($revenu->getCotation()->getChargement(ChargementCrudController::TAB_TYPE_CHARGEMENT_ORDINAIRE[ChargementCrudController::TYPE_FRONTING]) / 100);
        }
        switch ($strBase) {
            case RevenuCrudController::BASE_PRIME_NETTE:
                $valeur = ($revenu->getTaux() * $prmNette);
                $data[self::DATA_VALEUR] = $valeur;
                $data[self::DATA_DESCRIPTION] = number_format($valeur, 2, ",", ".") . $this->codeMonnaie;
                $data[self::DATA_FORMULE] = "" . number_format(($revenu->getTaux() * 100), 2, ",", ".") . "% de la prime nette de " . number_format($prmNette, 2, ",", ".") . $this->codeMonnaie;
                break;
            case RevenuCrudController::BASE_FRONTING:
                $valeur = ($revenu->getTaux() * $fronting);
                $data[self::DATA_VALEUR] = $valeur;
                $data[self::DATA_DESCRIPTION] = number_format($valeur, 2, ",", ".") . $this->codeMonnaie;
                $data[self::DATA_FORMULE] = "" . number_format(($revenu->getTaux() * 100), 2, ",", ".") . "% du fronting de " . number_format($fronting, 2, ",", ".") . $this->codeMonnaie;
                break;
            case RevenuCrudController::BASE_MONTANT_FIXE:
                $valeur = ($revenu->getMontantFlat() / 100);
                $data[self::DATA_VALEUR] = $valeur;
                $data[self::DATA_DESCRIPTION] = number_format($valeur, 2, ",", ".") . $this->codeMonnaie;
                $data[self::DATA_FORMULE] = "une valeur fixe";
                break;
            default:
                # code...
                break;
        }
        return $data;
    }

    /**
     * Get the value of cotation
     */
    public function getCotation()
    {
        return $this->cotation;
    }

    /**
     * Set the value of cotation
     *
     * @return  self
     */
    public function setCotation($cotation)
    {
        $this->cotation = $cotation;
        if ($this->cotation != null) {
            $this
                ->setPiste($this->cotation->getPiste())
                ->setAssureur($this->cotation->getAssureur())
                ->setEntreprise($this->cotation->getEntreprise())
                ->setUtilisateur($this->cotation->getUtilisateur());
            if ($this->piste != null) {
                $this
                    ->setClient($this->piste->getClient())
                    ->setProduit($this->piste->getProduit())
                    ->setPartenaire($this->piste->getPartenaire());
            }
            if ($this->cotation->isValidated()) {
                if (count($this->cotation->getPolices()) != 0) {
                    $this->police = $this->cotation->getPolices()[0];
                }
            }
        }
        return $this;
    }

    /**
     * Get the value of piste
     */
    public function getPiste()
    {
        return $this->piste;
    }

    /**
     * Set the value of piste
     *
     * @return  self
     */
    public function setPiste($piste)
    {
        $this->piste = $piste;

        return $this;
    }

    /**
     * Get the value of client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Set the value of client
     *
     * @return  self
     */
    public function setClient($client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get the value of entreprise
     */
    public function getEntreprise()
    {
        return $this->entreprise;
    }

    /**
     * Set the value of entreprise
     *
     * @return  self
     */
    public function setEntreprise($entreprise)
    {
        $this->entreprise = $entreprise;
        //On calcul la monnaie
        if ($this->entreprise) {
            //Définition de la monnaie
            foreach ($this->entreprise->getMonnaies() as $monnaie) {
                if ($monnaie->getFonction() == MonnaieCrudController::TAB_MONNAIE_FONCTIONS[MonnaieCrudController::FONCTION_SAISIE_ET_AFFICHAGE]) {
                    $this->monnaie = $monnaie;
                    break;
                } else if ($monnaie->getFonction() == MonnaieCrudController::TAB_MONNAIE_FONCTIONS[MonnaieCrudController::FONCTION_AFFICHAGE_UNIQUEMENT]) {
                    $this->monnaie = $monnaie;
                    break;
                }
            }
            if ($this->monnaie) {
                $this->codeMonnaie = " " . $this->monnaie->getCode();
            }
            //Définition des taxes
            foreach ($this->entreprise->getTaxes() as $taxe) {
                if ($taxe->isPayableparcourtier() == true) {
                    $this->taxeCourtier = $taxe;
                } else {
                    $this->taxeAssureur = $taxe;
                }
            }
            //On charge les comptes bancaires
            $this->comptesBancaires = $this->entreprise->getCompteBancaires();
        }
        return $this;
    }

    public function getTaxes()
    {
        return $this->entreprise->getTaxes();
    }

    /**
     * Get the value of utilisateur
     */
    public function getUtilisateur()
    {
        return $this->utilisateur;
    }

    /**
     * Set the value of utilisateur
     *
     * @return  self
     */
    public function setUtilisateur($utilisateur)
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    /**
     * Get the value of produit
     */
    public function getProduit()
    {
        return $this->produit;
    }

    /**
     * Set the value of produit
     *
     * @return  self
     */
    public function setProduit($produit)
    {
        $this->produit = $produit;
        if ($this->produit) {
            //$this->tauxCommissionCourtage = $this->produit->getTauxarca();
        }
        return $this;
    }

    /**
     * Get the value of police
     */
    public function getPolice()
    {
        return $this->police;
    }

    /**
     * Set the value of police
     *
     * @return  self
     */
    public function setPolice($police)
    {
        $this->police = $police;
        if ($this->police != null) {
            $this->setCotation($this->police->getCotation());
        }
        return $this;
    }

    /**
     * Get the value of partenaire
     */
    public function getPartenaire()
    {
        return $this->partenaire;
    }

    /**
     * Set the value of partenaire
     *
     * @return  self
     */
    public function setPartenaire($partenaire)
    {
        $this->partenaire = $partenaire;

        return $this;
    }

    /**
     * Get the value of assureur
     */
    public function getAssureur()
    {
        return $this->assureur;
    }

    /**
     * Set the value of assureur
     *
     * @return  self
     */
    public function setAssureur($assureur)
    {
        $this->assureur = $assureur;

        return $this;
    }


    /**
     * Get the value of codeMonnaie
     */
    public function getCodeMonnaie()
    {
        //dd($this);
        return $this->codeMonnaie;
    }

    /**
     * Set the value of codeMonnaie
     *
     * @return  self
     */
    public function setCodeMonnaie($codeMonnaie)
    {
        $this->codeMonnaie = $codeMonnaie;

        return $this;
    }


    /**
     * Get the value of monnaie
     */
    public function getMonnaie()
    {
        return $this->monnaie;
    }

    /**
     * Set the value of monnaie
     *
     * @return  self
     */
    public function setMonnaie($monnaie)
    {
        $this->monnaie = $monnaie;

        return $this;
    }

    /**
     * Get the value of taxeAssureur
     */
    public function getTaxeAssureur()
    {
        return $this->taxeAssureur;
    }

    /**
     * Set the value of taxeAssureur
     *
     * @return  self
     */
    public function setTaxeAssureur($taxeAssureur)
    {
        $this->taxeAssureur = $taxeAssureur;

        return $this;
    }

    /**
     * Get the value of taxeCourtier
     */
    public function getTaxeCourtier()
    {
        return $this->taxeCourtier;
    }

    /**
     * Set the value of taxeCourtier
     *
     * @return  self
     */
    public function setTaxeCourtier($taxeCourtier)
    {
        $this->taxeCourtier = $taxeCourtier;

        return $this;
    }

    /**
     * Get the value of comptesBancaires
     */
    public function getComptesBancaires()
    {
        return $this->comptesBancaires;
    }














    public const DATA_VALEUR = "montant_ht_valeur_numerique";
    public const DATA_DESCRIPTION = "montant_ht_description";
    public const DATA_FORMULE = "montant_ht_formule";
    public const Param_objet_tranche = "tranche";
    public const Param_objet_revenu = "revenu";
    public const Param_rev_type = "type";
    public const Param_rev_isPartageable = "isPartageable";
    public const Param_rev_isPartTranches = "isParTranches";
    public const Param_rev_isTaxable = "isTaxable";
    public const Param_rev_isExonere = "isExonere";
    public const Param_rev_mode_partageable = "partageable";
    public const Param_rev_mode_pure = "pure";
    public const Param_rev_mode_net = "net";
    public const Param_rev_mode_ttc = "ttc";


    //Les nouvelles fonctions unifiées
    private function getRev_taxe(?Revenu $revenu, ?bool $forCourtier): float
    {
        $tx = 0;
        $montant_ht = $this->getRev_ht($revenu);
        $parametres[self::Param_rev_isExonere] = $this->client->isExoneree();
        if (isset($parametres[self::Param_rev_mode_net])) {
            $tx = 0;
        }
        $tx = $this->dataTaxe($montant_ht, $forCourtier);
        return $tx;
    }

    public function getRev_ht(?Revenu $revenu): float
    {
        return $this->dataHT($revenu)[self::DATA_VALEUR];
    }

    private function getRev_total(?Revenu $revenu, ?string $mode): float
    {
        $tot = 0;
        $montant_ht = $this->getRev_ht($revenu);
        $tot = $this->appliquerPURE_NET_TTC($montant_ht, $mode);
        //dd("Com de réa", $tot, $parametres);
        return $tot;
    }

    private function getRetroCom_total(?Revenu $revenu): float
    {
        // ici
        $montant_ht = $this->getRev_ht($revenu);
        $taxe_courtier = $this->getRev_taxe($revenu, true);

        $tot = 0;
        if ($revenu->getPartageable() == true) {
            $taux = $this->cotation->getTauxretrocompartenaire();
            if ($taux == 0) {
                $taux = $this->partenaire->getPart();
            }
            $tot = ($montant_ht - $taxe_courtier) * $taux;
        }
        return $tot;
    }

    public function getRevenuTotale(?Revenu $revenu): float
    {
        $revenuTotale = $this
            ->setCotation($revenu->getCotation())
            ->getRev_total(
                $revenu,
                self::Param_rev_mode_ttc
            );
        return $revenuTotale;
    }

    public function getRevenuPure(?Revenu $revenu): float
    {
        $revenuPure = $this
            ->setCotation($revenu->getCotation())
            ->getRev_total(
                $revenu,
                self::Param_rev_mode_pure
            );
        return $revenuPure;
    }

    public function getRevenuNet(?Revenu $revenu): float
    {
        $revenuNet = $this
            ->setCotation($revenu->getCotation())
            ->getRev_total(
                $revenu,
                self::Param_rev_mode_net
            );
        //dd("Revenu net: ", $this->revenuNet);
        return $revenuNet;
    }

    public function getRetrocommissionTotale(?Revenu $revenu): float
    {
        $retrocommissionTotale = $this
            ->setCotation($revenu->getCotation())
            ->getRetroCom_total(
                $revenu
            );
        return $retrocommissionTotale;
    }

    public function getTaxePourCourtier(?Revenu $revenu): float
    {
        $taxeCourtier = $this
            ->setCotation($revenu->getCotation())
            ->getRev_taxe(
                $revenu,
                true
            );
        return $taxeCourtier;
    }

    public function getTaxePourAssureur(?Revenu $revenu): float
    {
        $taxeAssureur = $this
            ->setCotation($revenu->getCotation())
            ->getRev_taxe(
                $revenu,
                false
            );
        return $taxeAssureur;
    }

    public function getReserve(?Revenu $revenu)
    {
        $reserve = $this->getRevenuPure($revenu) - $this->getRetrocommissionTotale($revenu);
        return $reserve;
    }

    private function dataTaxe(?float $montantHT, ?bool $forCourtier): float
    {
        $tot = 0;
        /** @var Taxe */
        foreach ($this->cotation->getTaxes() as $taxe) {
            if ($taxe->isPayableparcourtier() == $forCourtier) {
                if ($this->produit->isIard()) {
                    $tot = $montantHT * $taxe->getTauxIARD();
                } else {
                    $tot = $montantHT * $taxe->getTauxVIE();
                }
            }
        }
        //dd($this->cotation->getTaxes());
        return $tot;
    }


    public function dataHT(?Revenu $revenu): array
    {
        $this->setEntreprise($revenu->getEntreprise());
        $strBase = "";
        foreach (RevenuCrudController::TAB_BASE as $key => $value) {
            if ($value == $revenu->getBase()) {
                $strBase = $key;
            }
        }
        $data = [];
        $prmNette = 0;
        $fronting = 0;
        if ($revenu->getCotation()) {
            $prmNette = ($revenu->getCotation()->getChargement(ChargementCrudController::TAB_TYPE_CHARGEMENT_ORDINAIRE[ChargementCrudController::TYPE_PRIME_NETTE]) / 100);
            $fronting = ($revenu->getCotation()->getChargement(ChargementCrudController::TAB_TYPE_CHARGEMENT_ORDINAIRE[ChargementCrudController::TYPE_FRONTING]) / 100);
        }
        switch ($strBase) {
            case RevenuCrudController::BASE_PRIME_NETTE:
                $valeur = ($revenu->getTaux() * $prmNette);
                $data[self::DATA_VALEUR] = $valeur;
                $data[self::DATA_DESCRIPTION] = number_format($valeur, 2, ",", ".") . $this->codeMonnaie;
                $data[self::DATA_FORMULE] = "" . number_format(($revenu->getTaux() * 100), 2, ",", ".") . "% de la prime nette de " . number_format($prmNette, 2, ",", ".") . $this->codeMonnaie;
                break;
            case RevenuCrudController::BASE_FRONTING:
                $valeur = ($revenu->getTaux() * $fronting);
                $data[self::DATA_VALEUR] = $valeur;
                $data[self::DATA_DESCRIPTION] = number_format($valeur, 2, ",", ".") . $this->codeMonnaie;
                $data[self::DATA_FORMULE] = "" . number_format(($revenu->getTaux() * 100), 2, ",", ".") . "% du fronting de " . number_format($fronting, 2, ",", ".") . $this->codeMonnaie;
                break;
            case RevenuCrudController::BASE_MONTANT_FIXE:
                $valeur = ($revenu->getMontantFlat() / 100);
                $data[self::DATA_VALEUR] = $valeur;
                $data[self::DATA_DESCRIPTION] = number_format($valeur, 2, ",", ".") . $this->codeMonnaie;
                $data[self::DATA_FORMULE] = "une valeur fixe";
                break;
            default:
                # code...
                break;
        }
        return $data;
    }

    private function appliquerPURE_NET_TTC($montant_ht, ?string $mode)
    {
        $tot = 0;
        $parametres[self::Param_rev_isExonere] = $this->client->isExoneree();
        if (self::Param_rev_mode_net == $mode) {
            $tot = $montant_ht;
        }
        if (self::Param_rev_mode_ttc == $mode) {
            $tot = $montant_ht + $this->dataTaxe($montant_ht, false);
        }
        if (self::Param_rev_mode_pure == $mode) {
            $tot = $montant_ht - $this->dataTaxe($montant_ht, true);
        }
        return $tot;
    }
}
