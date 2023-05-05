<?php

namespace App\Entity;


use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\ProduitRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 10)]
    private ?string $code = null;

    #[Assert\NotBlank(message:"Ce champ ne peut pas être vide.")]
    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[Assert\NotBlank(message:"Ce champ ne peut pas être vide.")]
    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[Assert\NotBlank(message:"Ce champ ne peut pas être vide.")]
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $tauxarca = null;

    #[ORM\Column]
    private ?bool $isobligatoire = null;
    
    #[ORM\Column]
    private ?bool $isabonnement = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Entreprise $entreprise = null;

    #[ORM\Column]
    private ?int $categorie = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne]
    private ?Utilisateur $utilisateur = null;

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

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getTauxarca(): ?string
    {
        return $this->tauxarca;
    }

    public function setTauxarca(string $tauxarca): self
    {
        $this->tauxarca = $tauxarca;

        return $this;
    }

    public function isIsobligatoire(): ?bool
    {
        return $this->isobligatoire;
    }

    public function setIsobligatoire(bool $isobligatoire): self
    {
        $this->isobligatoire = $isobligatoire;

        return $this;
    }

    public function isIsabonnement(): ?bool
    {
        return $this->isabonnement;
    }

    public function setIsabonnement(bool $isabonnement): self
    {
        $this->isabonnement = $isabonnement;

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
        return  "[" . ($this->tauxarca * 100). "%] " . $this->nom;
    }

    public function getCategorie(): ?int
    {
        return $this->categorie;
    }

    public function setCategorie(int $categorie): self
    {
        $this->categorie = $categorie;

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

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

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
}
