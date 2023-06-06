<?php

namespace App\Entity;

use App\Repository\PreferenceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PreferenceRepository::class)]
class Preference
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Entreprise $entreprise = null;

    #[ORM\Column(nullable: true)]
    private ?int $crmTaille = null;

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private array $crmMissions = [];

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private array $crmFeedbacks = [];

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private array $crmCotations = [];

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private array $crmEtapes = [];

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private array $crmPistes = [];

    public function getId(): ?int
    {
        return $this->id;
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

    public function setUtilisateur(Utilisateur $utilisateur): self
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

    public function getCrmTaille(): ?int
    {
        return $this->crmTaille;
    }

    public function setCrmTaille(?int $crmTaille): self
    {
        $this->crmTaille = $crmTaille;

        return $this;
    }

    public function getCrmMissions(): array
    {
        return $this->crmMissions;
    }

    public function setCrmMissions(?array $crmMissions): self
    {
        $this->crmMissions = $crmMissions;

        return $this;
    }

    public function getCrmFeedbacks(): array
    {
        return $this->crmFeedbacks;
    }

    public function setCrmFeedbacks(?array $crmFeedbacks): self
    {
        $this->crmFeedbacks = $crmFeedbacks;

        return $this;
    }

    public function getCrmCotations(): array
    {
        return $this->crmCotations;
    }

    public function setCrmCotations(?array $crmCotations): self
    {
        $this->crmCotations = $crmCotations;

        return $this;
    }

    public function getCrmEtapes(): array
    {
        return $this->crmEtapes;
    }

    public function setCrmEtapes(?array $crmEtapes): self
    {
        $this->crmEtapes = $crmEtapes;

        return $this;
    }

    public function getCrmPistes(): array
    {
        return $this->crmPistes;
    }

    public function setCrmPistes(?array $crmPistes): self
    {
        $this->crmPistes = $crmPistes;

        return $this;
    }
}
