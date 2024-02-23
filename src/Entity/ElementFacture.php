<?php

namespace App\Entity;

use App\Entity\Facture;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ElementFactureRepository;

#[ORM\Entity(repositoryClass: ElementFactureRepository::class)]
class ElementFacture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'elementFactures')]//, cascade: ['persist', 'remove'])]
    private ?Tranche $tranche = null;

    #[ORM\Column(nullable: true)]
    private ?float $montant = null;

    #[ORM\ManyToOne]
    private ?Entreprise $entreprise = null;

    #[ORM\ManyToOne]
    private ?Utilisateur $utilisateur = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'elementFactures', cascade:['remove', 'persist', 'refresh'])]
    private ?Facture $facture = null;

    #[ORM\Column(nullable: true)]
    private ?int $idavenant = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $typeavenant = null;

    //Variables pour des questions des case à cocher
    #[ORM\Column(nullable: true)]
    private ?bool $includePrime = null;
    #[ORM\Column(nullable: true)]
    private ?bool $includeComLocale = null;
    #[ORM\Column(nullable: true)]
    private ?bool $includeComFronting = null;
    #[ORM\Column(nullable: true)]
    private ?bool $includeComReassurance = null;
    #[ORM\Column(nullable: true)]
    private ?bool $includeFraisGestion = null;
    #[ORM\Column(nullable: true)]
    private ?bool $includeRetroCom = null;
    #[ORM\Column(nullable: true)]
    private ?bool $includeTaxeCourtier = null;
    #[ORM\Column(nullable: true)]
    private ?bool $includeTaxeAssureur = null;

    
    private ?float $primeTotale = 0;
    private ?float $commissionTotale = 0;
    private ?float $commissionLocale = 0;
    private ?float $commissionFronting = 0;
    private ?float $commissionReassurance = 0;
    private ?float $fraisGestionTotale = 0;
    private ?float $revenuTotal = 0;
    private ?float $retroCommissionTotale = 0;
    private ?float $taxeCourtierTotale = 0;
    private ?float $taxeAssureurTotale = 0;
    

    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMontant(): ?float
    {
        return $this->montant;
    }

    public function setMontant(?float $montant): self
    {
        $this->montant = $montant;

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

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): self
    {
        $this->utilisateur = $utilisateur;

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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function __toString()
    {
        return "Id: " . $this->getId() . " | Article: " . $this->getTranche() . " | Mnt: " . round(($this->getMontant()/100), 2). "";
    }

    public function getFacture(): ?Facture
    {
        return $this->facture;
    }

    public function setFacture(?Facture $facture): self
    {
        $this->facture = $facture;

        return $this;
    }

    public function getIdavenant(): ?int
    {
        return $this->idavenant;
    }

    public function setIdavenant(?int $idavenant): self
    {
        $this->idavenant = $idavenant;

        return $this;
    }

    public function getTypeavenant(): ?string
    {
        return $this->typeavenant;
    }

    public function setTypeavenant(?string $typeavenant): self
    {
        $this->typeavenant = $typeavenant;

        return $this;
    }

    // public function getTranche(): ?Tranche
    // {
    //     return $this->tranche;
    // }

    // public function setTranche(?Tranche $tranche): self
    // {
    //     $this->tranche = $tranche;

    //     return $this;
    // }

    /**
     * Get the value of primeTotale
     */ 
    public function getPrimeTotale()
    {
        $this->primeTotale = $this->getTranche()->getPrimeTotaleTranche();
        return $this->primeTotale;
    }

    /**
     * Get the value of commissionTotale
     */ 
    public function getCommissionTotale()
    {
        $this->commissionTotale = 
        $this->getTranche()->getComLocale() + 
        $this->getTranche()->getComFronting() + 
        $this->getTranche()->getComReassurance() +
        $this->getTranche()->getComAutreChargement()
        ;
        return $this->commissionTotale;
    }

    /**
     * Get the value of fraisGestionTotale
     */ 
    public function getFraisGestionTotale()
    {
        $this->fraisGestionTotale = $this->getTranche()->getComFraisGestion();
        return $this->fraisGestionTotale;
    }

    /**
     * Get the value of revenuTotal
     */ 
    public function getRevenuTotal()
    {
        $this->revenuTotal = $this->getTranche()->getRevenuTotal();
        return $this->revenuTotal;
    }

    /**
     * Get the value of retroCommissionTotale
     */ 
    public function getRetroCommissionTotale()
    {
        $this->retroCommissionTotale = $this->getTranche()->getRetroCommissionTotale();
        return $this->retroCommissionTotale;
    }

    /**
     * Get the value of taxeCourtierTotale
     */ 
    public function getTaxeCourtierTotale()
    {
        $this->taxeCourtierTotale = $this->getTranche()->getTaxeCourtierTotale();
        return $this->taxeCourtierTotale;
    }

    /**
     * Get the value of taxeAssureurTotale
     */ 
    public function getTaxeAssureurTotale()
    {
        $this->taxeAssureurTotale = $this->getTranche()->getTaxeAssureurTotale();
        return $this->taxeAssureurTotale;
    }

    public function getTranche(): ?Tranche
    {
        return $this->tranche;
    }

    public function setTranche(?Tranche $tranche): self
    {
        $this->tranche = $tranche;

        return $this;
    }

    /**
     * Get the value of commissionLocale
     */ 
    public function getCommissionLocale()
    {
        $this->commissionLocale = $this->getTranche()->getComLocale();
        return $this->commissionLocale;
    }

    /**
     * Get the value of commissionFronting
     */ 
    public function getCommissionFronting()
    {
        $this->commissionFronting = $this->getTranche()->getComFronting();
        return $this->commissionFronting;
    }

    /**
     * Get the value of commissionReassurance
     */ 
    public function getCommissionReassurance()
    {
        $this->commissionReassurance = $this->getTranche()->getComReassurance();
        return $this->commissionReassurance;
    }

    /**
     * Get the value of includePrime
     */ 
    public function getIncludePrime()
    {
        return $this->includePrime;
    }

    /**
     * Set the value of includePrime
     *
     * @return  self
     */ 
    public function setIncludePrime($includePrime)
    {
        $this->includePrime = $includePrime;

        return $this;
    }

    /**
     * Get the value of includeComLocale
     */ 
    public function getIncludeComLocale()
    {
        return $this->includeComLocale;
    }

    /**
     * Set the value of includeComLocale
     *
     * @return  self
     */ 
    public function setIncludeComLocale($includeComLocale)
    {
        $this->includeComLocale = $includeComLocale;

        return $this;
    }

    /**
     * Get the value of includeComFronting
     */ 
    public function getIncludeComFronting()
    {
        return $this->includeComFronting;
    }

    /**
     * Set the value of includeComFronting
     *
     * @return  self
     */ 
    public function setIncludeComFronting($includeComFronting)
    {
        $this->includeComFronting = $includeComFronting;

        return $this;
    }

    /**
     * Get the value of includeComReassurance
     */ 
    public function getIncludeComReassurance()
    {
        return $this->includeComReassurance;
    }

    /**
     * Set the value of includeComReassurance
     *
     * @return  self
     */ 
    public function setIncludeComReassurance($includeComReassurance)
    {
        $this->includeComReassurance = $includeComReassurance;

        return $this;
    }

    /**
     * Get the value of includeFraisGestion
     */ 
    public function getIncludeFraisGestion()
    {
        return $this->includeFraisGestion;
    }

    /**
     * Set the value of includeFraisGestion
     *
     * @return  self
     */ 
    public function setIncludeFraisGestion($includeFraisGestion)
    {
        $this->includeFraisGestion = $includeFraisGestion;

        return $this;
    }

    /**
     * Get the value of includeRetroCom
     */ 
    public function getIncludeRetroCom()
    {
        return $this->includeRetroCom;
    }

    /**
     * Set the value of includeRetroCom
     *
     * @return  self
     */ 
    public function setIncludeRetroCom($includeRetroCom)
    {
        $this->includeRetroCom = $includeRetroCom;

        return $this;
    }

    /**
     * Get the value of includeTaxeCourtier
     */ 
    public function getIncludeTaxeCourtier()
    {
        return $this->includeTaxeCourtier;
    }

    /**
     * Set the value of includeTaxeCourtier
     *
     * @return  self
     */ 
    public function setIncludeTaxeCourtier($includeTaxeCourtier)
    {
        $this->includeTaxeCourtier = $includeTaxeCourtier;

        return $this;
    }

    /**
     * Get the value of includeTaxeAssureur
     */ 
    public function getIncludeTaxeAssureur()
    {
        return $this->includeTaxeAssureur;
    }

    /**
     * Set the value of includeTaxeAssureur
     *
     * @return  self
     */ 
    public function setIncludeTaxeAssureur($includeTaxeAssureur)
    {
        $this->includeTaxeAssureur = $includeTaxeAssureur;

        return $this;
    }
}
