<?php

namespace App\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\PaiementCommissionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PaiementCommissionRepository::class)]
class PaiementCommission
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $montant = null;

    #[Assert\NotBlank(message:"Ce champ ne peut pas être vide.")]
    #[ORM\Column(length: 255)]
    private ?string $refnotededebit = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Entreprise $entreprise = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Monnaie $monnaie = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Police $police = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToMany(targetEntity: DocPiece::class)]
    private Collection $pieces;

    #[ORM\ManyToOne]
    private ?Utilisateur $utilisateur = null;

    public function __construct()
    {
        $this->pieces = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getMontant(): ?string
    {
        return $this->montant;
    }

    public function setMontant(string $montant): self
    {
        $this->montant = $montant;

        return $this;
    }

    public function getRefnotededebit(): ?string
    {
        return $this->refnotededebit;
    }

    public function setRefnotededebit(string $refnotededebit): self
    {
        $this->refnotededebit = $refnotededebit;

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
        return $this->montant . " " . $this->monnaie->getCode() . " - Facture: " . $this->refnotededebit;
    }

    public function getMonnaie(): ?Monnaie
    {
        return $this->monnaie;
    }

    public function setMonnaie(?Monnaie $monnaie): self
    {
        $this->monnaie = $monnaie;

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
