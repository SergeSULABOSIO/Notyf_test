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

    #[ORM\ManyToOne(inversedBy: 'elementFactures')]
    private ?Police $police = null;

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

    public function getId(): ?int
    {
        return $this->id;
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
        return "Id: " . $this->id . " | Police: " . $this->police . " | Mnt: " . ($this->montant/100). "";
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
}
