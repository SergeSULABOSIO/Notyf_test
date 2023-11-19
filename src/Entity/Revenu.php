<?php

namespace App\Entity;

use App\Controller\Admin\ChargementCrudController;
use App\Controller\Admin\MonnaieCrudController;
use App\Controller\Admin\RevenuCrudController;
use App\Repository\RevenuRepository;
use App\Service\ServiceMonnaie;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use PhpParser\ErrorHandler\Collecting;

#[ORM\Entity(repositoryClass: RevenuRepository::class)]
class Revenu
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $type = null;

    #[ORM\ManyToOne]
    private ?Utilisateur $utilisateur = null;

    #[ORM\ManyToOne]
    private ?Entreprise $entreprise = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column]
    private ?int $partageable = null;

    #[ORM\Column]
    private ?int $taxable = null;

    #[ORM\Column]
    private ?int $base = null;

    #[ORM\Column]
    private ?float $taux = null;

    #[ORM\Column]
    private ?float $montant = null;

    #[ORM\ManyToOne(inversedBy: 'revenus', cascade: ['remove', 'persist', 'refresh'])]
    private ?Cotation $cotation = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isparttranche = null;

    #[ORM\Column(nullable: true)]
    private ?bool $ispartclient = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): self
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    public function getEntreprise(): ?Entreprise
    {
        return $this->entreprise;
    }

    public function setEntreprise(?Entreprise $entreprise): self
    {
        $this->entreprise = $entreprise;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getPartageable(): ?int
    {
        return $this->partageable;
    }

    public function setPartageable(int $partageable): self
    {
        $this->partageable = $partageable;

        return $this;
    }

    public function getTaxable(): ?int
    {
        return $this->taxable;
    }

    public function setTaxable(int $taxable): self
    {
        $this->taxable = $taxable;

        return $this;
    }

    public function getBase(): ?int
    {
        return $this->base;
    }

    public function setBase(int $base): self
    {
        $this->base = $base;

        return $this;
    }

    public function getTaux(): ?float
    {
        return $this->taux;
    }

    public function setTaux(float $taux): self
    {
        $this->taux = $taux;

        return $this;
    }

    public function getMontant(): ?float
    {
        return $this->montant;
    }

    public function setMontant(float $montant): self
    {
        $this->montant = $montant;

        return $this;
    }

    public function getCotation(): ?Cotation
    {
        return $this->cotation;
    }

    public function setCotation(?Cotation $cotation): self
    {
        $this->cotation = $cotation;

        return $this;
    }

    private function getMonnaie($fonction)
    {
        $tabMonnaies = $this->getEntreprise()->getMonnaies();
        foreach ($tabMonnaies as $monnaie) {
            if($monnaie->getFonction() == $fonction){
                return $monnaie;
            }
        }
        return null;
    }

    private function getMonnaie_Affichage()
    {
        $monnaie = $this->getMonnaie(MonnaieCrudController::TAB_MONNAIE_FONCTIONS[MonnaieCrudController::FONCTION_SAISIE_ET_AFFICHAGE]);
        if($monnaie == null){
            $monnaie = $this->getMonnaie(MonnaieCrudController::TAB_MONNAIE_FONCTIONS[MonnaieCrudController::FONCTION_AFFICHAGE_UNIQUEMENT]);
        }
        return $monnaie;
    }

    private function getCodeMonnaieAffichage(): string{
        $strMonnaie = "";
        $monnaieAff = $this->getMonnaie_Affichage();
        if($monnaieAff != null){
            $strMonnaie = " " . $this->getMonnaie_Affichage()->getCode();
        }
        return $strMonnaie;
    }

    public function __toString()
    {
        $strMonnaie = $this->getCodeMonnaieAffichage();
        $strType = "";
        foreach (RevenuCrudController::TAB_TYPE as $key => $value) {
            if ($value == $this->type) {
                $strType = $key;
            }
        }

        $strBase = "";
        foreach (RevenuCrudController::TAB_BASE as $key => $value) {
            if ($value == $this->base) {
                $strBase = $key;
            }
        }
        //On calcul le revennu total
        $data = $this->getComNette($strBase);


        $strRedevablePar = "";
        if($this->isIspartclient() == true){
            $strRedevablePar = "par le client";
            if($this->getCotation()){
                if($this->getCotation()->getPiste()){
                    if($this->getCotation()->getPiste()->getClient()){
                        $strRedevablePar = "par " . $this->getCotation()->getPiste()->getClient()->getNom();
                    }
                }
            }
        }else{
            $strRedevablePar = "par l'assureur";
            if($this->getCotation()){
                if($this->getCotation()){
                    if($this->getCotation()->getAssureur()){
                        $strRedevablePar = "par " . $this->getCotation()->getAssureur()->getNom();
                    }
                }
            }
        }

        //Décomposition en tranches
        $strTranches = ", commission payable " . $strRedevablePar . " en une tranche sans délai.";
        if ($this->isIsparttranche() == true) {
            if ($this->getCotation()) {
                if ($this->getCotation()->getTranches()) {
                    $tabTranches = $this->getCotation()->getTranches();
                    $portions = " ";
                    /** @var Tranche */
                    $i = 0;
                    foreach ($tabTranches as $tranche) {
                        $i = $i + 1;
                        $comTranche = (($tranche->getTaux() / 100) * $data['revenufinal']) * 100;
                        if ($i == count($tabTranches)) {
                            $portions = $portions . " et " . $comTranche . $strMonnaie;
                        } else if ($i == 1) {
                            $portions = $comTranche . $strMonnaie;
                        } else {
                            $portions = $portions . ", " . $comTranche . $strMonnaie;
                        }
                    }
                    $strTranches = ", commission payable " . $strRedevablePar . " en " . count($tabTranches) . " tranche(s) de " . $portions . " hors taxes.";
                }
            }
        }



        return $strType . " (" . $data['comNette'] . ", soit " . $data['formule'] . ")" . $strTranches;
    }

    public function calc_getRevenuFinal()
    {
        $strBase = "";
        foreach (RevenuCrudController::TAB_BASE as $key => $value) {
            if ($value == $this->base) {
                $strBase = $key;
            }
        }
        //On calcul le revennu total
        return $this->getComNette($strBase)['revenufinal'];
    }

    private function getComNette($strBase)
    {
        $strMonnaie = $this->getCodeMonnaieAffichage();
        $data = [];
        $prmNette = 0;
        $fronting = 0;
        if ($this->getCotation()) {
            /** @var Cotation */
            $quote = $this->getCotation();
            $prmNette = ($quote->calc_getChargement(ChargementCrudController::TAB_TYPE_CHARGEMENT_ORDINAIRE[ChargementCrudController::TYPE_PRIME_NETTE]) / 100);
            $fronting = ($quote->calc_getChargement(ChargementCrudController::TAB_TYPE_CHARGEMENT_ORDINAIRE[ChargementCrudController::TYPE_FRONTING]) / 100);
        }
        $montantFlat = ($this->montant / 100);
        $taux = $this->taux;
        switch ($strBase) {
            case RevenuCrudController::BASE_PRIME_NETTE:
                $data['revenufinal'] = ($taux * $prmNette);
                $data['comNette'] = number_format(($taux * $prmNette), 2, ",", ".") . $strMonnaie;
                $data['formule'] = "" . number_format(($taux * 100), 2, ",", ".") . "% de la prime nette de " . number_format($prmNette, 2, ",", ".") . $strMonnaie;
                break;
            case RevenuCrudController::BASE_FRONTING:
                $data['revenufinal'] = ($taux * $fronting);
                $data['comNette'] = number_format(($taux * $fronting), 2, ",", ".") . $strMonnaie;
                $data['formule'] = "" . number_format(($taux * 100), 2, ",", ".") . "% du fronting de " . number_format($fronting, 2, ",", ".") . $strMonnaie;
                break;
            case RevenuCrudController::BASE_MONTANT_FIXE:
                $data['revenufinal'] = ($montantFlat);
                $data['comNette'] = number_format($montantFlat, 2, ",", ".") . $strMonnaie;
                $data['formule'] = "une valeur fixe";
                break;
            default:
                # code...
                break;
        }
        return $data;
    }

    public function isIsparttranche(): ?bool
    {
        return $this->isparttranche;
    }

    public function setIsparttranche(bool $isparttranche): self
    {
        $this->isparttranche = $isparttranche;

        return $this;
    }

    public function isIspartclient(): ?bool
    {
        return $this->ispartclient;
    }

    public function setIspartclient(?bool $ispartclient): self
    {
        $this->ispartclient = $ispartclient;

        return $this;
    }
}
