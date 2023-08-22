<?php

namespace App\Entity;

use App\Repository\FactureRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FactureRepository::class)]
class Facture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToMany(mappedBy: 'facture', targetEntity: ElementFacture::class)]
    private Collection $elementFactures;

    #[ORM\Column(length: 255)]
    private ?string $reference = null;

    #[ORM\ManyToOne]
    private ?Utilisateur $utilisateur = null;

    #[ORM\ManyToOne]
    private ?Entreprise $entreprise = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column]
    private ?int $type = null;

    #[ORM\ManyToOne(inversedBy: 'factures')]
    private ?Partenaire $partenaire = null;

    #[ORM\ManyToOne(inversedBy: 'factures')]
    private ?Assureur $assureur = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?DocPiece $piece = null;

    #[ORM\OneToMany(mappedBy: 'facture', targetEntity: PaiementCommission::class)]
    private Collection $paiementCommissions;

    #[ORM\OneToMany(mappedBy: 'facture', targetEntity: PaiementPartenaire::class)]
    private Collection $paiementPartenaires;

    #[ORM\OneToMany(mappedBy: 'facture', targetEntity: PaiementTaxe::class)]
    private Collection $paiementTaxes;

    public function __construct()
    {
        $this->elementFactures = new ArrayCollection();
        $this->paiementCommissions = new ArrayCollection();
        $this->paiementPartenaires = new ArrayCollection();
        $this->paiementTaxes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, ElementFacture>
     */
    public function getElementFactures(): Collection
    {
        return $this->elementFactures;
    }

    public function addElementFacture(ElementFacture $elementFacture): self
    {
        if (!$this->elementFactures->contains($elementFacture)) {
            $this->elementFactures->add($elementFacture);
            $elementFacture->setFacture($this);
        }

        return $this;
    }

    public function removeElementFacture(ElementFacture $elementFacture): self
    {
        if ($this->elementFactures->removeElement($elementFacture)) {
            // set the owning side to null (unless already changed)
            if ($elementFacture->getFacture() === $this) {
                $elementFacture->setFacture(null);
            }
        }

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): self
    {
        $this->reference = $reference;

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

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getPartenaire(): ?Partenaire
    {
        return $this->partenaire;
    }

    public function setPartenaire(?Partenaire $partenaire): self
    {
        $this->partenaire = $partenaire;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPiece(): ?DocPiece
    {
        return $this->piece;
    }

    public function setPiece(?DocPiece $piece): self
    {
        $this->piece = $piece;

        return $this;
    }

    /**
     * @return Collection<int, PaiementCommission>
     */
    public function getPaiementCommissions(): Collection
    {
        return $this->paiementCommissions;
    }

    public function addPaiementCommission(PaiementCommission $paiementCommission): self
    {
        if (!$this->paiementCommissions->contains($paiementCommission)) {
            $this->paiementCommissions->add($paiementCommission);
            $paiementCommission->setFacture($this);
        }

        return $this;
    }

    public function removePaiementCommission(PaiementCommission $paiementCommission): self
    {
        if ($this->paiementCommissions->removeElement($paiementCommission)) {
            // set the owning side to null (unless already changed)
            if ($paiementCommission->getFacture() === $this) {
                $paiementCommission->setFacture(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PaiementPartenaire>
     */
    public function getPaiementPartenaires(): Collection
    {
        return $this->paiementPartenaires;
    }

    public function addPaiementPartenaire(PaiementPartenaire $paiementPartenaire): self
    {
        if (!$this->paiementPartenaires->contains($paiementPartenaire)) {
            $this->paiementPartenaires->add($paiementPartenaire);
            $paiementPartenaire->setFacture($this);
        }

        return $this;
    }

    public function removePaiementPartenaire(PaiementPartenaire $paiementPartenaire): self
    {
        if ($this->paiementPartenaires->removeElement($paiementPartenaire)) {
            // set the owning side to null (unless already changed)
            if ($paiementPartenaire->getFacture() === $this) {
                $paiementPartenaire->setFacture(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PaiementTaxe>
     */
    public function getPaiementTaxes(): Collection
    {
        return $this->paiementTaxes;
    }

    public function addPaiementTax(PaiementTaxe $paiementTax): self
    {
        if (!$this->paiementTaxes->contains($paiementTax)) {
            $this->paiementTaxes->add($paiementTax);
            $paiementTax->setFacture($this);
        }

        return $this;
    }

    public function removePaiementTax(PaiementTaxe $paiementTax): self
    {
        if ($this->paiementTaxes->removeElement($paiementTax)) {
            // set the owning side to null (unless already changed)
            if ($paiementTax->getFacture() === $this) {
                $paiementTax->setFacture(null);
            }
        }

        return $this;
    }
}
