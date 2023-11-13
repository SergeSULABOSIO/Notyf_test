<?php

namespace App\Entity;

use App\Repository\CotationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CotationRepository::class)]
class Cotation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;
    
    #[ORM\ManyToOne(inversedBy: 'cotations', cascade:['remove', 'persist', 'refresh'])]
    private ?Piste $piste = null;
    
    #[ORM\ManyToOne(inversedBy: 'cotations')]
    private ?Assureur $assureur = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Entreprise $entreprise = null;

    #[ORM\Column(nullable: true)]
    private ?int $validated = null;

    #[ORM\OneToMany(mappedBy: 'cotation', targetEntity: Revenu::class, cascade:['remove', 'persist', 'refresh'])]
    private Collection $revenus;

    #[ORM\Column(nullable: true)]
    private ?float $primeNette = 0;

    #[ORM\Column(nullable: true)]
    private ?float $fronting = null;

    #[ORM\Column(nullable: true)]
    private ?float $accessoires = null;

    #[ORM\Column(nullable: true)]
    private ?float $taxes = null;

    #[ORM\Column(nullable: true)]
    private ?float $fraisArca = null;
    
    #[ORM\Column]
    private ?float $primeTotale = null;


    public function __construct()
    {
        $this->revenus = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrimeTotale(): ?float
    {
        return $this->primeTotale;
    }

    public function setPrimeTotale(float $primeTotale): self
    {
        $this->primeTotale = $primeTotale;

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

    public function __toString()
    {
        $strCommission = "";
        if($this->getRevenus()){
            $totRev = 0;
            foreach ($this->getRevenus() as $revenu) {
                $totRev = $totRev + $revenu->calc_getRevenuFinal();
            }
            $strCommission = " | Com. ht: " . number_format($totRev, 2, ",", ".")."";
        }

        return "" . $this->nom . " | " . $this->assureur->getNom() . " | " . $this->piste->getProduit()->getNom() . " | Prime ttc: " . number_format(($this->primeTotale / 100), 2, ",", ".") . $strCommission . ($this->validated == 0 ? " [validée].":".");
    }

    public function getPiste(): ?Piste
    {
        return $this->piste;
    }

    public function setPiste(?Piste $piste): self
    {
        $this->piste = $piste;

        return $this;
    }

    public function getAssureur(): ?Assureur
    {
        return $this->assureur;
    }

    public function setAssureur(?Assureur $assureur): self
    {
        $this->assureur = $assureur;

        return $this;
    }

    public function getValidated(): ?int
    {
        return $this->validated;
    }

    public function setValidated(?int $validated): self
    {
        $this->validated = $validated;

        return $this;
    }

    /**
     * @return Collection<int, Revenu>
     */
    public function getRevenus(): Collection
    {
        return $this->revenus;
    }

    public function addRevenu(Revenu $revenu): self
    {
        if (!$this->revenus->contains($revenu)) {
            $this->revenus->add($revenu);
            $revenu->setCotation($this);
        }

        return $this;
    }

    public function removeRevenu(Revenu $revenu): self
    {
        if ($this->revenus->removeElement($revenu)) {
            // set the owning side to null (unless already changed)
            if ($revenu->getCotation() === $this) {
                $revenu->setCotation(null);
            }
        }

        return $this;
    }

    public function getPrimeNette(): ?float
    {
        return $this->primeNette;
    }

    public function setPrimeNette(?float $primeNette): self
    {
        $this->primeNette = $primeNette;

        return $this;
    }

    public function getFronting(): ?float
    {
        return $this->fronting;
    }

    public function setFronting(?float $fronting): self
    {
        $this->fronting = $fronting;

        return $this;
    }

    public function getAccessoires(): ?float
    {
        return $this->accessoires;
    }

    public function setAccessoires(?float $accessoires): self
    {
        $this->accessoires = $accessoires;

        return $this;
    }

    public function getTaxes(): ?float
    {
        return $this->taxes;
    }

    public function setTaxes(?float $taxes): self
    {
        $this->taxes = $taxes;

        return $this;
    }

    public function getFraisArca(): ?float
    {
        return $this->fraisArca;
    }

    public function setFraisArca(?float $fraisArca): self
    {
        $this->fraisArca = $fraisArca;

        return $this;
    }
}
