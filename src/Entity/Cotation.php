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

    #[ORM\Column]
    private ?float $primeTotale = null;

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

    #[ORM\ManyToOne(inversedBy: 'cotations')]
    private ?Piste $piste = null;

    #[ORM\ManyToOne(inversedBy: 'cotations')]
    private ?Assureur $assureur = null;

    #[ORM\OneToMany(mappedBy: 'cotation', targetEntity: DocPiece::class)]
    private Collection $docPieces;

    #[ORM\OneToOne(mappedBy: 'cotation', cascade: ['persist', 'remove'])]
    private ?Police $police = null;

    #[ORM\ManyToOne(inversedBy: 'cotations')]
    private ?Produit $produit = null;

    #[ORM\ManyToOne(inversedBy: 'cotations')]
    private ?Client $client = null;

    public function __construct()
    {
        $this->docPieces = new ArrayCollection();
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
        return $this->nom . " / " . $this->assureur . " / Prime: " . $this->primeTotale .  ", le " . ($this->updatedAt)->format('d/m/Y à H:m:s');
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

    /**
     * @return Collection<int, DocPiece>
     */
    public function getDocPieces(): Collection
    {
        return $this->docPieces;
    }

    public function addDocPiece(DocPiece $docPiece): self
    {
        if (!$this->docPieces->contains($docPiece)) {
            $this->docPieces->add($docPiece);
            $docPiece->setCotation($this);
        }

        return $this;
    }

    public function removeDocPiece(DocPiece $docPiece): self
    {
        if ($this->docPieces->removeElement($docPiece)) {
            // set the owning side to null (unless already changed)
            if ($docPiece->getCotation() === $this) {
                $docPiece->setCotation(null);
            }
        }

        return $this;
    }

    public function getPolice(): ?Police
    {
        return $this->police;
    }

    public function setPolice(?Police $police): self
    {
        // unset the owning side of the relation if necessary
        if ($police === null && $this->police !== null) {
            $this->police->setCotation(null);
        }

        // set the owning side of the relation if necessary
        if ($police !== null && $police->getCotation() !== $this) {
            $police->setCotation($this);
        }

        $this->police = $police;

        return $this;
    }

    public function getProduit(): ?Produit
    {
        return $this->produit;
    }

    public function setProduit(?Produit $produit): self
    {
        $this->produit = $produit;

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): self
    {
        $this->client = $client;

        return $this;
    }
}
