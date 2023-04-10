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

    #[ORM\ManyToMany(targetEntity: Assureur::class)]
    private Collection $assureur;

    #[ORM\Column]
    private ?float $primeTotale = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Produit $risque = null;

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

    #[ORM\ManyToMany(targetEntity: DocPiece::class)]
    private Collection $pieces;

    public function __construct()
    {
        $this->assureur = new ArrayCollection();
        $this->pieces = new ArrayCollection();
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

    /**
     * @return Collection<int, Assureur>
     */
    public function getAssureur(): Collection
    {
        return $this->assureur;
    }

    public function addAssureur(Assureur $assureur): self
    {
        if (!$this->assureur->contains($assureur)) {
            $this->assureur->add($assureur);
        }

        return $this;
    }

    public function removeAssureur(Assureur $assureur): self
    {
        $this->assureur->removeElement($assureur);

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

    public function getRisque(): ?Produit
    {
        return $this->risque;
    }

    public function setRisque(?Produit $risque): self
    {
        $this->risque = $risque;

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

    public function getPiste(): ?Piste
    {
        return $this->piste;
    }

    public function setPiste(?Piste $piste): self
    {
        $this->piste = $piste;

        return $this;
    }

    /**
     * @return Collection<int, DocPiece>
     */
    public function getPieces(): Collection
    {
        return $this->pieces;
    }

    public function addPiece(DocPiece $piece): self
    {
        if (!$this->pieces->contains($piece)) {
            $this->pieces->add($piece);
        }

        return $this;
    }

    public function removePiece(DocPiece $piece): self
    {
        $this->pieces->removeElement($piece);

        return $this;
    }
}