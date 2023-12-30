<?php

namespace App\Entity;

use App\Controller\Admin\RevenuCrudController;
use App\Controller\Admin\MonnaieCrudController;
use App\Controller\Admin\ChargementCrudController;
use Doctrine\Common\Collections\Collection;

class Calculateur
{
    public const PARAMETRE_forCOURTIER = "forCourtier";
    public const PARAMETRE_isPARTAGEABLE = "isPartageable";
    public const PARAMETRE_isPAYABLE_PAR_CLIENT = "isPayableParClient";
    public const PARAMETRE_TRANCHE = "tranche";

    public const DATA_VALEUR = "montant_ht_valeur_numerique";
    public const DATA_DESCRIPTION = "montant_ht_description";
    public const DATA_FORMULE = "montant_ht_formule";

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
        $this->setCotation($revenu->getCotation());
        $montantTaxe = 0;
        if ($revenu != null & $this->piste != null) {
            foreach ($this->getTaxes() as $taxe) {
                if ($taxe->isPayableparcourtier() == $forCourtier) {
                    if ($this->client->isExoneree() == true || $revenu->getTaxable() == false) {
                        $montantTaxe = (0 * $this->getRevenufinaleHT_valeur($revenu)) / 100;
                        break;
                    } else {
                        if ($this->produit->isIard()) {
                            $montantTaxe = ($taxe->getTauxIARD() * $this->getRevenufinaleHT_valeur($revenu)) / 100;
                            break;
                        } else {
                            $montantTaxe = ($taxe->getTauxVIE() * $this->getRevenufinaleHT_valeur($revenu)) / 100;
                            break;
                        }
                    }
                }
            }
        }
        return $montantTaxe * 100;
    }

    public function getRevenufinaleHT_valeur(?Revenu $revenu)
    {
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

    public function getRevenufinaleHTGlobale(?array $parametres)
    {
        $tot = 0;
        foreach ($this->cotation->getRevenus() as $revenu) {
            if (isset($parametres[self::PARAMETRE_isPARTAGEABLE])) {
                if ($parametres[self::PARAMETRE_isPARTAGEABLE] == $revenu->getPartageable()) {
                    $tot = $tot + $this->getRevenufinaleHT_valeur($revenu);
                }
            } else {
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
        $parametres = [self::PARAMETRE_isPARTAGEABLE => true, self::PARAMETRE_forCOURTIER => true];
        return $this->getRevenufinaleHTGlobale($parametres) - $this->getMontantTaxeGlobal($parametres);
    }

    public function getRevenuPureGlobale()
    {
        $parametres = [self::PARAMETRE_forCOURTIER => true];
        return $this->getRevenufinaleHTGlobale($parametres) - $this->getMontantTaxeGlobal($parametres);
    }

    public function getRevenuPure(?Revenu $revenu)
    {
        $comPure = $this->getRevenufinaleHT_valeur($revenu) - $this->getMontantTaxe($revenu, true);
        return $comPure;
    }

    public function getRevenuTTC(?array $parametres, ?Revenu $revenu)
    {
        $comPure = 0;
        if (isset($parametres[self::PARAMETRE_isPAYABLE_PAR_CLIENT])) {
            if ($revenu->isIspartclient() == $parametres[self::PARAMETRE_isPAYABLE_PAR_CLIENT]) {
                $comPure = $this->getRevenufinaleHT_valeur($revenu) + $this->getMontantTaxe($revenu, false);
            }
        }else{
            $comPure = $this->getRevenufinaleHT_valeur($revenu) + $this->getMontantTaxe($revenu, false);
        }
        return $comPure;
    }

    public function getRevenuTTCGlobal(?array $parametres)
    {
        $tot = 0;
        foreach ($this->cotation->getRevenus() as $revenu) {
            $tot = $tot + $this->getRevenuTTC($parametres, $revenu);
        }
        if (isset($parametres[self::PARAMETRE_TRANCHE])) {
            return $tot * $parametres[self::PARAMETRE_TRANCHE]->getTaux();
        } else {
            return $tot;
        }
    }


    public function getMontantTaxeGlobal(?array $parametres)
    {
        $tot = 0;
        foreach ($this->cotation->getRevenus() as $revenu) {
            if (isset($parametres[self::PARAMETRE_isPARTAGEABLE])) {
                if ($parametres[self::PARAMETRE_isPARTAGEABLE] == $revenu->getPartageable()) {
                    $tot = $tot + $this->getMontantTaxe($revenu, $parametres[self::PARAMETRE_forCOURTIER]);
                }
            } else {
                $tot = $tot + $this->getMontantTaxe($revenu, $parametres[self::PARAMETRE_forCOURTIER]);
            }
        }
        if (isset($parametres[self::PARAMETRE_TRANCHE])) {
            return $tot * $parametres[self::PARAMETRE_TRANCHE]->getTaux();
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
                $data[self::DATA_VALEUR] = ($revenu->getTaux() * $prmNette);
                $data[self::DATA_DESCRIPTION] = number_format(($revenu->getTaux() * $prmNette), 2, ",", ".") . $this->codeMonnaie;
                $data[self::DATA_FORMULE] = "" . number_format(($revenu->getTaux() * 100), 2, ",", ".") . "% de la prime nette de " . number_format($prmNette, 2, ",", ".") . $this->codeMonnaie;
                break;
            case RevenuCrudController::BASE_FRONTING:
                $data[self::DATA_VALEUR] = ($revenu->getTaux() * $fronting);
                $data[self::DATA_DESCRIPTION] = number_format(($revenu->getTaux() * $fronting), 2, ",", ".") . $this->codeMonnaie;
                $data[self::DATA_FORMULE] = "" . number_format(($revenu->getTaux() * 100), 2, ",", ".") . "% du fronting de " . number_format($fronting, 2, ",", ".") . $this->codeMonnaie;
                break;
            case RevenuCrudController::BASE_MONTANT_FIXE:
                $data[self::DATA_VALEUR] = $revenu->getMontantFlat();
                $data[self::DATA_DESCRIPTION] = number_format($revenu->getMontantFlat(), 2, ",", ".") . $this->codeMonnaie;
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
                ->setClient($this->cotation->getClient())
                ->setPartenaire($this->cotation->getPartenaire())
                ->setPiste($this->cotation->getPiste())
                ->setProduit($this->cotation->getProduit())
                ->setAssureur($this->cotation->getAssureur())
                ->setEntreprise($this->cotation->getEntreprise())
                ->setUtilisateur($this->cotation->getUtilisateur());

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
}
