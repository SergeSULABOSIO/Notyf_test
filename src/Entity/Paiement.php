<?php

namespace App\Entity;

use App\Repository\PaiementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PaiementRepository::class)]
class Paiement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $paidAt = null;

    #[ORM\Column]
    private ?float $montant = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'paiements')]
    private ?Facture $facture = null;

    #[ORM\ManyToOne(inversedBy: 'paiements')]
    private ?Entreprise $entreprise = null;

    #[ORM\ManyToOne(inversedBy: 'paiements')]
    private ?Utilisateur $utilisateur = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'paiements')]
    private ?CompteBancaire $compteBancaire = null;

    #[ORM\OneToMany(mappedBy: 'paiement', targetEntity: DocPiece::class, cascade:['remove', 'persist', 'refresh'])]
    private Collection $documents;

    #[ORM\Column]
    private ?int $type = null;

    public function __construct()
    {
        $this->documents = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPaidAt(): ?\DateTimeImmutable
    {
        return $this->paidAt;
    }

    public function setPaidAt(\DateTimeImmutable $paidAt): self
    {
        $this->paidAt = $paidAt;

        return $this;
    }

    public function getMontant(): ?float
    {
        return $this->montant;
    }

    public function setMontant(float $montant): self
    {
        $this->montant = $montant;

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

    public function getFacture(): ?Facture
    {
        return $this->facture;
    }

    public function setFacture(?Facture $facture): self
    {
        $this->facture = $facture;

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

    public function __toString()
    {
        $ref = "Null";
        if($this->facture != null){
            $ref = $this->facture->getReference();
        }
        return "Paiement du " . $this->paidAt->format('d-m-Y') . " | Mont.: " . ($this->montant/100) . " | Réf. ND: " . $ref . " | Desc.: " . $this->description;
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

    public function getCompteBancaire(): ?CompteBancaire
    {
        return $this->compteBancaire;
    }

    public function setCompteBancaire(?CompteBancaire $compteBancaire): self
    {
        $this->compteBancaire = $compteBancaire;

        return $this;
    }

     /**
     * @return Collection<int, DocPiece>
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function addDocument(DocPiece $document): self
    {
        if (!$this->documents->contains($document)) {
            $this->documents->add($document);
            $document->setPaiement($this);
        }

        return $this;
    }

    public function removeDocument(DocPiece $document): self
    {
        if ($this->documents->removeElement($document)) {
            // set the owning side to null (unless already changed)
            if ($document->getPaiement() === $this) {
                $document->setPaiement(null);
            }
        }

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
}
