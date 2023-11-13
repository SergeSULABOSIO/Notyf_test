<?php

namespace App\Entity;

use App\Controller\Admin\RevenuCrudController;
use App\Repository\RevenuRepository;
use App\Service\ServiceMonnaie;
use Doctrine\ORM\Mapping as ORM;

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

    public function __toString()
    {
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
        return $strType . " (" . $data['comNette'] . ", soit " . $data['formule'] . ")";
    }

    public function calc_getRevenuFinal(){
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
        $data = [];
        $prmNette = ($this->getCotation()->getPrimeNette() / 100);
        $fronting = ($this->getCotation()->getFronting() / 100);
        $montantFlat = ($this->montant / 100);
        $taux = $this->taux;
        switch ($strBase) {
            case RevenuCrudController::BASE_PRIME_NETTE:
                $data['revenufinal'] = ($taux * $prmNette);
                $data['comNette'] = number_format(($taux * $prmNette), 2, ",", ".");
                $data['formule'] = "" . number_format(($taux * 100), 2, ",", ".") . "% de la prime nette de " . number_format($prmNette, 2, ",", ".");
                break;
            case RevenuCrudController::BASE_FRONTING:
                $data['revenufinal'] = ($taux * $fronting);
                $data['comNette'] = number_format(($taux * $fronting), 2, ",", ".");
                $data['formule'] = "" . number_format(($taux * 100), 2, ",", ".") . "% du fronting de " . number_format($fronting, 2, ",", ".");
                break;
            case RevenuCrudController::BASE_MONTANT_FIXE:
                $data['revenufinal'] = ($montantFlat);
                $data['comNette'] = number_format($montantFlat, 2, ",", ".");
                $data['formule'] = "une valeur fixe";
                break;
            default:
                # code...
                break;
        }
        return $data;
    }
}
