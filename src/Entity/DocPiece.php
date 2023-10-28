<?php

namespace App\Entity;

use App\Repository\DocPieceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DocPieceRepository::class)]
class DocPiece
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Entreprise $entreprise = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'docPieces')]
    private ?Cotation $cotation = null;

    #[ORM\ManyToOne(inversedBy: 'docPieces')]
    private ?Police $police = null;

    #[ORM\ManyToOne(inversedBy: 'docPieces')]
    private ?Sinistre $sinistre = null;

    #[ORM\OneToMany(mappedBy: 'piece', targetEntity: PaiementCommission::class)]
    private Collection $paiementCommissions;

    #[ORM\OneToMany(mappedBy: 'piece', targetEntity: PaiementPartenaire::class)]
    private Collection $paiementPartenaires;

    #[ORM\OneToMany(mappedBy: 'piece', targetEntity: PaiementTaxe::class)]
    private Collection $paiementTaxes;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fichier = null;

    #[ORM\ManyToOne(inversedBy: 'docPieces')]
    private ?DocCategorie $categorie = null;

    #[ORM\ManyToOne(inversedBy: 'docPieces')]
    private ?DocClasseur $classeur = null;

    #[ORM\ManyToOne(inversedBy: 'pieces', cascade:['remove', 'persist', 'refresh'])]
    private ?Paiement $paiement = null;

    public function __construct()
    {
        $this->paiementCommissions = new ArrayCollection();
        $this->paiementPartenaires = new ArrayCollection();
        $this->paiementTaxes = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

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

    public function __toString()
    {
        return $this->nom;
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

    public function getPolice(): ?Police
    {
        return $this->police;
    }

    public function setPolice(?Police $police): self
    {
        $this->police = $police;

        return $this;
    }

    public function getSinistre(): ?Sinistre
    {
        return $this->sinistre;
    }

    public function setSinistre(?Sinistre $sinistre): self
    {
        $this->sinistre = $sinistre;

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
            $paiementCommission->setPiece($this);
        }

        return $this;
    }

    public function removePaiementCommission(PaiementCommission $paiementCommission): self
    {
        if ($this->paiementCommissions->removeElement($paiementCommission)) {
            // set the owning side to null (unless already changed)
            if ($paiementCommission->getPiece() === $this) {
                $paiementCommission->setPiece(null);
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
            $paiementPartenaire->setPiece($this);
        }

        return $this;
    }

    public function removePaiementPartenaire(PaiementPartenaire $paiementPartenaire): self
    {
        if ($this->paiementPartenaires->removeElement($paiementPartenaire)) {
            // set the owning side to null (unless already changed)
            if ($paiementPartenaire->getPiece() === $this) {
                $paiementPartenaire->setPiece(null);
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
            $paiementTax->setPiece($this);
        }

        return $this;
    }

    public function removePaiementTax(PaiementTaxe $paiementTax): self
    {
        if ($this->paiementTaxes->removeElement($paiementTax)) {
            // set the owning side to null (unless already changed)
            if ($paiementTax->getPiece() === $this) {
                $paiementTax->setPiece(null);
            }
        }

        return $this;
    }

    public function getFichier(): ?string
    {
        return $this->fichier;
    }

    public function setFichier(?string $fichier): self
    {
        $this->fichier = $fichier;

        return $this;
    }

    public function getCategorie(): ?DocCategorie
    {
        return $this->categorie;
    }

    public function setCategorie(?DocCategorie $categorie): self
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getClasseur(): ?DocClasseur
    {
        return $this->classeur;
    }

    public function setClasseur(?DocClasseur $classeur): self
    {
        $this->classeur = $classeur;

        return $this;
    }

    public function getPaiement(): ?Paiement
    {
        return $this->paiement;
    }

    public function setPaiement(?Paiement $paiement): self
    {
        $this->paiement = $paiement;

        return $this;
    }
}
