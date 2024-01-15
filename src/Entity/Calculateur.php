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


    private function getMontantTaxe(?Revenu $revenu, ?bool $forCourtier): float
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

    private function getRevenufinaleHT_valeur(?Revenu $revenu)
    {
        //dd($parametres);
        return $this->getRevenufinaleHT($revenu)["montant_ht_valeur_numerique"];
    }

    private function getRevenufinaleHT_description(?Revenu $revenu)
    {
        return $this->getRevenufinaleHT($revenu)["montant_ht_description"];
    }

    private function getRevenufinaleHT_formule(?Revenu $revenu)
    {
        return $this->getRevenufinaleHT($revenu)["montant_ht_formule"];
    }

    private function getRevenufinaleHTGlobale(?bool $isPartageable)
    {
        $tot = 0;
        foreach ($this->cotation->getRevenus() as $revenu) {
            if ($isPartageable == $revenu->getPartageable()) {
                $tot = $tot + $this->getRevenufinaleHT_valeur($revenu);
            }
        }
        return $tot;
    }

    public function getPrimeTotale(?int $typeChargement, ?Tranche $tranche)
    {
        if ($tranche != null) {
            $this->setCotation($tranche->getCotation());
            return $this->getChargement($typeChargement) * ($tranche)->getTaux();
        } else {
            return $this->getChargement($typeChargement);
        }
    }

    public function getChargement(?int $typeChargement)
    {
        $tot = 0;
        if ($this->cotation->getChargements()) {
            foreach ($this->cotation->getChargements() as $chargement) {
                if ($typeChargement != null) {
                    if ($chargement->getType() == $typeChargement) {
                        $tot = $tot + $chargement->getMontant();
                    }
                } else {
                    $tot = $tot + $chargement->getMontant();
                }
            }
        }
        return $tot;
    }

    private function getRetroComPartenaire(?array $parametres): float
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

    private function getRevenuTTC(?Revenu $revenu)
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

    private function getCommissionTTCGlobal(?array $parametres)
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

    private function getFraisGestionTTCGlobal(?Tranche $tranche)
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


    private function getMontantTaxeGlobal(?Tranche $tranche, ?bool $forCourtier)
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
        if ($this->client->isExoneree() == true) {
            $tx = 0;
        }
        $tx = $this->dataTaxe($montant_ht, $forCourtier);
        return $tx;
    }

    public function getRev_ht(?Revenu $revenu): float
    {
        if ($revenu != null) {
            return $this->dataHT($revenu)[self::DATA_VALEUR];
        } else {
            $cumulValeur = 0;
            foreach ($this->cotation->getRevenus() as $revenu) {
                $cumulValeur = $cumulValeur + $this->dataHT($revenu)[self::DATA_VALEUR];
            }
            return $cumulValeur;
        }
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

    private function getRevenu(?string $typeRevenu): Revenu
    {
        foreach ($this->cotation->getRevenus() as $revenu) {
            if ($revenu->getType() == RevenuCrudController::TAB_TYPE[$typeRevenu]) {
                // dd($this->cotation->getRevenus()[0]->getType(), RevenuCrudController::TAB_TYPE[$typeRevenu], $revenu);
                return $revenu;
            }
        }
        return null;
    }

    private function setCotation_getRevenu(?string $typeRevenu, ?Revenu $revenu, ?Tranche $tranche)
    {
        $rev = null;
        if ($typeRevenu != null) {
            $rev = $this
                ->setCotation($tranche->getCotation())
                ->getRevenu($typeRevenu);
        } else if ($revenu != null) {
            $rev = $revenu;
            $this->setCotation($revenu->getCotation());
        } else if ($tranche != null) {
            $this->setCotation($tranche->getCotation());
        }
        // dd($rev);
        return $rev;
    }

    public function getRevenuTotale(?string $typeRevenu, ?Revenu $revenu, ?Tranche $tranche, ?Cotation $cotation): float
    {
        $revenuTotale = 0;
        // dd($typeRevenu, $revenu, $tranche);
        // dd($typeRevenu, $revenu, $this->getCotation());
        $rev = $this->setCotation_getRevenu($typeRevenu, $revenu, $tranche);
        // dd($rev);
        if ($rev != null) {
            $revenuTotale = $this
                ->setCotation($rev->getCotation())
                ->getRev_total(
                    $rev,
                    self::Param_rev_mode_ttc
                );
        } else if ($cotation != null) {
            $this->setCotation($cotation);
            foreach ($this->cotation->getRevenus() as $revenu) {
                $revenuTotale = $revenuTotale + $this
                    ->getRev_total(
                        $revenu,
                        self::Param_rev_mode_ttc
                    );
            }
            // dd($this->cotation->getRevenus());
        }
        // dd("Ici", $revenuTotale);
        $revenuTotale = $tranche == null ? $revenuTotale : $revenuTotale * $tranche->getTaux();
        //dd($rev, "Revenu total", $revenuTotale);
        return $revenuTotale;
    }

    public function getRevenuPure(?string $typeRevenu, ?Revenu $revenu, ?Tranche $tranche, ?Cotation $cotation, ?bool $partageable): float
    {
        $revenuPure = 0;
        $rev = $this->setCotation_getRevenu($typeRevenu, $revenu, $tranche);
        if ($rev != null) {
            $go = false;
            if ($partageable == null) {
                $go = true;
            } else if ($partageable == $revenu->getPartageable()) {
                $go = true;
            }
            if ($go == true) {
                $revenuPure = $this
                    ->setCotation($this->cotation)
                    ->getRev_total(
                        $rev,
                        self::Param_rev_mode_pure
                    );
            }
        } else if ($cotation != null) {
            $this->setCotation($cotation);
            foreach ($this->cotation->getRevenus() as $revenu) {
                $go = false;
                if ($partageable == null) {
                    $go = true;
                } else if ($partageable == $revenu->getPartageable()) {
                    $go = true;
                }
                if ($go == true) {
                    $revenuPure = $this
                        // ->setCotation($this->cotation)
                        ->getRev_total(
                            $revenu,
                            self::Param_rev_mode_pure
                        );
                }
            }
        }

        $revenuPure = $tranche == null ? $revenuPure : $revenuPure * $tranche->getTaux();
        return $revenuPure;
    }

    public function getRevenuNet(?string $typeRevenu, ?Revenu $revenu, ?Tranche $tranche, ?Cotation $cotation, ?bool $partageable): float
    {
        $revenuNet = 0;
        $rev = $this->setCotation_getRevenu($typeRevenu, $revenu, $tranche);
        if ($rev != null) {
            $go = false;
            if ($partageable == null) {
                $go = true;
            } else if ($partageable == $revenu->getPartageable()) {
                $go = true;
            }
            if ($go == true) {
                $revenuNet = $this
                    ->setCotation($this->cotation)
                    ->getRev_total(
                        $rev,
                        self::Param_rev_mode_net
                    );
            }
        } else if ($cotation != null) {
            $this->setCotation($cotation);
            foreach ($this->cotation->getRevenus() as $revenu) {
                $go = false;
                if ($partageable == null) {
                    $go = true;
                } else if ($partageable == $revenu->getPartageable()) {
                    $go = true;
                }
                if ($go == true) {
                    $revenuNet = $revenuNet + $this
                        // ->setCotation($this->cotation)
                        ->getRev_total(
                            $revenu,
                            self::Param_rev_mode_net
                        );
                }
            }
            // dd($revenuNet);
        }
        $revenuNet = $tranche == null ? $revenuNet : $revenuNet * $tranche->getTaux();
        return $revenuNet;
    }

    public function getRetrocommissionTotale(?string $typeRevenu, ?Revenu $revenu, ?Tranche $tranche, ?Cotation $cotation, ?bool $partageable): float
    {
        $retrocommissionTotale = 0;
        $rev = $this->setCotation_getRevenu($typeRevenu, $revenu, $tranche);
        if ($rev != null) {
            $go = false;
            if ($partageable == null) {
                $go = true;
            } else if ($partageable == $rev->getPartageable()) {
                $go = true;
            }
            if ($go) {
                $retrocommissionTotale = $this
                    // ->setCotation($this->cotation)
                    ->getRetroCom_total(
                        $rev
                    );
            }
        } else if ($cotation != null) {
            $this->setCotation($cotation);
            foreach ($this->cotation->getRevenus() as $revenu) {
                $go = false;
                if ($partageable == null) {
                    $go = true;
                } else if ($partageable == $revenu->getPartageable()) {
                    $go = true;
                }
                if ($go) {
                    $retrocommissionTotale = $retrocommissionTotale + $this
                        ->getRetroCom_total(
                            $revenu
                        );
                }
            }
        }
        $retrocommissionTotale = ($tranche == null) ? $retrocommissionTotale : $retrocommissionTotale * $tranche->getTaux();
        return $retrocommissionTotale;
    }

    public function getTaxePourCourtier(?string $typeRevenu, ?Revenu $revenu, ?Tranche $tranche, ?Cotation $cotation, ?bool $partageable): float
    {
        $taxeCourtier = 0;
        $rev = $this->setCotation_getRevenu($typeRevenu, $revenu, $tranche);
        if ($rev != null) {
            $go = false;
            if ($partageable == null) {
                $go = true;
            } else if ($partageable == $rev->getPartageable()) {
                $go = true;
            }
            if ($go) {
                $taxeCourtier = $this
                    ->setCotation($this->cotation)
                    ->getRev_taxe(
                        $rev,
                        true
                    );
            }
        } else if ($cotation != null) {
            $this->setCotation($cotation);

            foreach ($this->cotation->getRevenus() as $revenu) {
                $go = false;
                if ($partageable == null) {
                    $go = true;
                } else if ($partageable == $revenu->getPartageable()) {
                    $go = true;
                }
                if ($go) {
                    $taxeCourtier = $taxeCourtier + $this
                        // ->setCotation($this->cotation)
                        ->getRev_taxe(
                            $revenu,
                            true
                        );
                }
            }
        }

        $taxeCourtier = $tranche == null ? $taxeCourtier : $taxeCourtier * $tranche->getTaux();
        return $taxeCourtier;
    }

    public function getTaxePourAssureur(?string $typeRevenu, ?Revenu $revenu, ?Tranche $tranche, ?Cotation $cotation): float
    {
        $taxeAssureur = 0;
        $rev = $this->setCotation_getRevenu($typeRevenu, $revenu, $tranche);
        if ($rev != null) {
            $taxeAssureur = $this
                ->setCotation($this->cotation)
                ->getRev_taxe(
                    $rev,
                    false
                );
        } else if ($cotation != null) {
            $this->setCotation($cotation);
            foreach ($this->cotation->getRevenus() as $revenu) {
                $taxeAssureur = $taxeAssureur + $this
                    ->getRev_taxe(
                        $revenu,
                        false
                    );
            }
        }

        $taxeAssureur = $tranche == null ? $taxeAssureur : $taxeAssureur * $tranche->getTaux();
        return $taxeAssureur;
    }

    public function getReserve(?string $typeRevenu, ?Revenu $revenu, ?Tranche $tranche, ?Cotation $cotation, ?bool $partageable)
    {
        $rev = $this->setCotation_getRevenu($typeRevenu, $revenu, $tranche);
        $reserve = $this->getRevenuPure($typeRevenu, $rev, $tranche, $cotation, $partageable) - $this->getRetrocommissionTotale($typeRevenu, $rev, $tranche, $cotation, $partageable);
        $reserve = $tranche == null ? $reserve : $reserve * $tranche->getTaux();
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
