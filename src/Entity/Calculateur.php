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
        return $this->getRevenufinaleHT($revenu)["montant_ht_valeur_numerique"];
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
    public function setCotation(?Cotation $cotation)
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
        // dd("Ici", $this->codeMonnaie);
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
    public const Param_rev_isTaxable = "isTaxable";
    public const Param_rev_isExonere = "isExonere";
    public const Param_rev_mode_pure = "pure";
    public const Param_rev_mode_net = "net";
    public const Param_rev_mode_ttc = "ttc";
    public const Param_from_tranche = "from_tranche";
    public const Param_from_revenu = "from_revenu";
    public const Param_from_cotation = "from_cotation";


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
        return $tot;
    }

    private function getRetroCom_total(?Revenu $revenu): float
    {
        // ici
        $montant_ht = $this->getRev_ht($revenu);
        $taxe_courtier = $this->getRev_taxe($revenu, true);

        $tot = 0;
        if ($revenu->getPartageable() == true && $this->partenaire != null) {
            $taux = $this->cotation->getTauxretrocompartenaire();
            if ($taux == 0) {
                $taux = $this->partenaire->getPart();
            }
            $tot = ($montant_ht - $taxe_courtier) * $taux;
        }
        return $tot;
    }

    private function getRevenu(?string $typeRevenu): ?Revenu
    {
        foreach ($this->cotation->getRevenus() as $revenu) {
            if ($revenu->getType() == RevenuCrudController::TAB_TYPE[$typeRevenu]) {
                return $revenu;
            }
        }
        return null;
    }

    private function setCotation_getRevenu(?string $typeRevenu, ?Revenu $revenu, ?Tranche $tranche): ?Revenu
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

    public function getRevenuPure(?string $typeRevenu, ?Revenu $revenu, ?Tranche $tranche, ?Cotation $cotation, ?bool $partageable, ?string $from): float
    {
        return $this->processRevenu($typeRevenu, $revenu, $tranche, $cotation, $partageable, $from, self::Param_rev_mode_pure);
    }

    public function getRevenuNet(?string $typeRevenu, ?Revenu $revenu, ?Tranche $tranche, ?Cotation $cotation, ?bool $partageable, ?string $from): float
    {
        return $this->processRevenu($typeRevenu, $revenu, $tranche, $cotation, $partageable, $from, self::Param_rev_mode_net);
    }

    public function getRevenuTotale(?string $typeRevenu, ?Revenu $revenu, ?Tranche $tranche, ?Cotation $cotation, ?bool $partageable, ?string $from): float
    {
        return $this->processRevenu($typeRevenu, $revenu, $tranche, $cotation, $partageable, $from, self::Param_rev_mode_ttc);
    }

    public function processRevenu(?string $typeRevenu, ?Revenu $revenu, ?Tranche $tranche, ?Cotation $cotation, ?bool $partageable, ?string $from, ?string $mode): float
    {
        $revenuTotale = 0;
        switch ($from) {
            case self::Param_from_tranche:
                $this->setCotation($tranche->getCotation());
                if ($typeRevenu != null) {
                    $rev = $this->setCotation_getRevenu($typeRevenu, $revenu, $tranche);
                    if ($rev != null) {
                        if ($this->canGo($partageable, $rev) == true) {
                            $revenuTotale = $this->processTranche($this->getRev_total($rev, $mode), $tranche, $rev);
                            // $revenuTotale = $this->appliquerTrancheRevenu($revenuTotale, $rev, $tranche, $mode);
                        }
                    }
                } else {
                    foreach ($this->cotation->getRevenus() as $revenu) {
                        if ($this->canGo($partageable, $revenu) == true) {
                            $revenuTotale = $revenuTotale + $this->processTranche($this->getRev_total($revenu, $mode), $tranche, $revenu);
                            // $revenuTotale = $this->appliquerTrancheRevenu($revenuTotale, $revenu, $tranche, $mode);
                        }
                    }
                }
                break;

            case self::Param_from_revenu:
                $this->setCotation($revenu->getCotation());
                if ($this->canGo($partageable, $revenu) == true) {
                    $revenuTotale = $this->processTranche($this->getRev_total($revenu, $mode), $tranche, $revenu);
                    // $revenuTotale = $this->appliquerTrancheRevenu($revenuTotale, $revenu, $tranche, $mode);
                }
                break;

            case self::Param_from_cotation:
                $this->setCotation($cotation);
                if ($typeRevenu != null) {
                    $rev = $this->setCotation_getRevenu($typeRevenu, $revenu, $tranche);
                    if ($rev != null) {
                        if ($this->canGo($partageable, $rev) == true) {
                            $revenuTotale = $this->processTranche($this->getRev_total($rev, $mode), $tranche, $rev);
                            // $revenuTotale = $this->appliquerTrancheRevenu($revenuTotale, $rev, $tranche, $mode);
                        }
                    }
                } else {
                    foreach ($this->cotation->getRevenus() as $revenu) {
                        if ($this->canGo($partageable, $revenu) == true) {
                            $revenuTotale = $revenuTotale + $this->processTranche($this->getRev_total($revenu, $mode), $tranche, $revenu);
                            // $revenuTotale = $this->appliquerTrancheRevenu($revenuTotale, $revenu, $tranche, $mode);
                        }
                    }
                }
                break;
        }

        // $revenuTotale = $tranche == null ? $revenuTotale : $revenuTotale * $tranche->getTaux();

        return $revenuTotale;
    }

    private function processTranche($ancienneValeur, ?Tranche $tranche, ?Revenu $revenu): float
    {
        $nouvelleValeur = 0;
        $taux = 0;
        if ($tranche != null) {
            if ($revenu != null) {
                if ($revenu->isIsparttranche() == true) {
                    $taux = $tranche->getTaux();
                } else {
                    if ($this->cotation->getPolices()[0]->getDateeffet() == $tranche->getStartedAt()) {
                        $taux = 1;
                    } else {
                        $taux = 0;
                    }
                }
            }
        } else {
            $taux = 1;
        }
        $nouvelleValeur = $ancienneValeur * $taux;
        return $nouvelleValeur;
    }


    private function canGo(?bool $partageable, ?Revenu $revenu)
    {
        $go = false;
        if ($partageable == null) {
            $go = true;
        } else if ($partageable == $revenu->getPartageable()) {
            $go = true;
        }
        return $go;
    }



    public function getRetrocommissionTotale(?string $typeRevenu, ?Revenu $revenu, ?Tranche $tranche, ?Cotation $cotation, ?bool $partageable, ?string $from): float
    {
        $retrocommissionTotale = 0;
        switch ($from) {
            case self::Param_from_tranche:
                $this->setCotation($tranche->getCotation());
                if ($typeRevenu != null) {
                    $rev = $this->setCotation_getRevenu($typeRevenu, $revenu, $tranche);
                    if ($rev != null) {
                        if ($this->canGo($partageable, $rev) == true) {
                            $retrocommissionTotale = $this->processTranche($this->getRetroCom_total($rev), $tranche, $rev);
                        }
                    }
                } else {
                    foreach ($this->cotation->getRevenus() as $revenu) {
                        if ($this->canGo($partageable, $revenu) == true) {
                            $retrocommissionTotale = $retrocommissionTotale + $this->processTranche($this->getRetroCom_total($revenu), $tranche, $revenu);
                        }
                    }
                }
                break;
            case self::Param_from_revenu:
                $this->setCotation($revenu->getCotation());
                if ($this->canGo($partageable, $revenu) == true) {
                    $retrocommissionTotale = $this->processTranche($this->getRetroCom_total($revenu), $tranche, $revenu);
                }
                break;
            case self::Param_from_cotation:
                $this->setCotation($cotation);
                if ($typeRevenu != null) {
                    $rev = $this->setCotation_getRevenu($typeRevenu, $revenu, $tranche);
                    if ($rev != null) {
                        if ($this->canGo($partageable, $rev) == true) {
                            $retrocommissionTotale = $this->processTranche($this->getRetroCom_total($rev), $tranche, $rev);
                        }
                    }
                } else {
                    foreach ($this->cotation->getRevenus() as $revenu) {
                        if ($this->canGo($partageable, $revenu) == true) {
                            $retrocommissionTotale = $retrocommissionTotale + $this->processTranche($this->getRetroCom_total($revenu), $tranche, $revenu);
                        }
                    }
                }
                break;
        }
        // $retrocommissionTotale = ($tranche == null) ? $retrocommissionTotale : $retrocommissionTotale * $tranche->getTaux();
        return $retrocommissionTotale;
    }

    public function getTaxePourCourtier(?string $typeRevenu, ?Revenu $revenu, ?Tranche $tranche, ?Cotation $cotation, ?bool $partageable, ?string $from): float
    {
        return $this->processTaxe($typeRevenu, $revenu, $tranche, $cotation, $partageable, $from, true);
    }

    public function getTaxePourAssureur(?string $typeRevenu, ?Revenu $revenu, ?Tranche $tranche, ?Cotation $cotation, ?bool $partageable, ?string $from): float
    {
        return $this->processTaxe($typeRevenu, $revenu, $tranche, $cotation, $partageable, $from, false);
    }

    private function processTaxe(?string $typeRevenu, ?Revenu $revenu, ?Tranche $tranche, ?Cotation $cotation, ?bool $partageable, ?string $from, ?bool $forCourtier)
    {
        $taxe = 0;
        switch ($from) {
            case self::Param_from_tranche:
                $this->setCotation($tranche->getCotation());
                if ($typeRevenu != null) {
                    $rev = $this->setCotation_getRevenu($typeRevenu, $revenu, $tranche);
                    if ($rev != null) {
                        if ($this->canGo($partageable, $rev) == true) {
                            $taxe = $this->processTranche(
                                $this->getRev_taxe($rev, $forCourtier),
                                $tranche,
                                $rev
                            );
                        }
                    }
                } else {
                    foreach ($this->cotation->getRevenus() as $revenu) {
                        if ($this->canGo($partageable, $revenu) == true) {
                            $taxe = $taxe + $this->processTranche(
                                $this->getRev_taxe($revenu, $forCourtier),
                                $tranche,
                                $revenu
                            );
                        }
                    }
                }
                break;
            case self::Param_from_revenu:
                $this->setCotation($revenu->getCotation());
                if ($this->canGo($partageable, $revenu) == true) {
                    $taxe = $this->processTranche(
                        $this->getRev_taxe($revenu, $forCourtier),
                        $tranche,
                        $revenu
                    );
                }
                break;
            case self::Param_from_cotation:
                $this->setCotation($cotation);
                if ($typeRevenu != null) {
                    $rev = $this->setCotation_getRevenu($typeRevenu, $revenu, $tranche);
                    if ($rev != null) {
                        if ($this->canGo($partageable, $rev) == true) {
                            $taxe = $this->processTranche(
                                $this->getRev_taxe($rev, $forCourtier),
                                $tranche,
                                $revenu
                            );
                        }
                    }
                } else {
                    foreach ($this->cotation->getRevenus() as $revenu) {
                        if ($this->canGo($partageable, $revenu) == true) {
                            $taxe = $taxe + $this->processTranche(
                                $this->getRev_taxe($revenu, $forCourtier),
                                $tranche,
                                $revenu
                            );
                        }
                    }
                }
                break;
        }
        return $taxe;
    }

    public function getReserve(?string $typeRevenu, ?Revenu $revenu, ?Tranche $tranche, ?Cotation $cotation, ?bool $partageable, ?string $from)
    {
        $reserve = 0;
        switch ($from) {
            case self::Param_from_tranche:
                $this->setCotation($tranche->getCotation());
                if ($typeRevenu != null) {
                    $rev = $this->setCotation_getRevenu($typeRevenu, $revenu, $tranche);
                    if ($rev != null) {
                        if ($this->canGo($partageable, $rev) == true) {
                            $revenuPure = $this->getRevenuPure($typeRevenu, $rev, $tranche, $cotation, $partageable, $from);
                            $retrcom = $this->getRetrocommissionTotale($typeRevenu, $rev, $tranche, $cotation, $partageable, $from);
                            $reserve = $revenuPure - $retrcom;
                        }
                    }
                } else {
                    foreach ($this->cotation->getRevenus() as $revenu) {
                        if ($this->canGo($partageable, $revenu) == true) {
                            $revenuPure = $this->getRevenuPure($typeRevenu, $revenu, $tranche, $cotation, $partageable, $from);
                            $retrcom = $this->getRetrocommissionTotale($typeRevenu, $revenu, $tranche, $cotation, $partageable, $from);
                            // dd($revenuPure);
                            $reserve = ($revenuPure - $retrcom);
                        }
                    }
                }
                break;
            case self::Param_from_revenu:
                $this->setCotation($revenu->getCotation());
                if ($this->canGo($partageable, $revenu) == true) {
                    $revenuPure = $this->getRevenuPure($typeRevenu, $revenu, $tranche, $cotation, $partageable, $from);
                    $retrcom = $this->getRetrocommissionTotale($typeRevenu, $revenu, $tranche, $cotation, $partageable, $from);
                    $reserve = $revenuPure - $retrcom;
                }
                break;
            case self::Param_from_cotation:
                $this->setCotation($cotation);
                if ($typeRevenu != null) {
                    $rev = $this->setCotation_getRevenu($typeRevenu, $revenu, $tranche);
                    if ($rev != null) {
                        if ($this->canGo($partageable, $rev) == true) {
                            $revenuPure = $this->getRevenuPure($typeRevenu, $rev, $tranche, $cotation, $partageable, $from);
                            $retrcom = $this->getRetrocommissionTotale($typeRevenu, $rev, $tranche, $cotation, $partageable, $from);
                            $reserve = $revenuPure - $retrcom;
                        }
                    }
                } else {
                    foreach ($this->cotation->getRevenus() as $revenu) {
                        if ($this->canGo($partageable, $revenu) == true) {
                            $revenuPure = $this->getRevenuPure($typeRevenu, $revenu, $tranche, $cotation, $partageable, $from);
                            $retrcom = $this->getRetrocommissionTotale($typeRevenu, $revenu, $tranche, $cotation, $partageable, $from);
                            // dd($revenuPure);
                            $reserve = ($revenuPure - $retrcom);
                        }
                    }
                }
                break;
        }
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
