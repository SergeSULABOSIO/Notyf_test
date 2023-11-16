<?php

namespace App\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\EntrepriseRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EntrepriseRepository::class)]
class Entreprise
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank(message:"Veuillez fournir le nom de l'entreprise.")]
    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[Assert\NotBlank(message:"Veuillez fournir l'adresse'.")]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adresse = null;

    #[Assert\NotBlank(message:"Veuillez fournir le numéro de téléphone.")]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $telephone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $rccm = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $idnat = null;
    
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $numimpot = null;

    #[Assert\NotBlank(message:"Veuillez préciser le domaine d'activité.")]
    #[ORM\Column(nullable: true)]
    private ?int $secteur = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne]
    private ?Utilisateur $utilisateur = null;

    #[ORM\OneToMany(mappedBy: 'entreprise', targetEntity: Utilisateur::class)]
    private Collection $utilisateurs;

    #[ORM\OneToMany(mappedBy: 'entreprise', targetEntity: Paiement::class)]
    private Collection $paiements;

    #[ORM\OneToMany(mappedBy: 'entreprise', targetEntity: CompteBancaire::class)]
    private Collection $compteBancaires;

    #[ORM\OneToMany(mappedBy: 'entreprise', targetEntity: Taxe::class)]
    private Collection $taxes;

    #[ORM\OneToMany(mappedBy: 'entreprise', targetEntity: Monnaie::class)]
    private Collection $monnaies;

    public function __construct()
    {
        $this->utilisateurs = new ArrayCollection();
        $this->paiements = new ArrayCollection();
        $this->compteBancaires = new ArrayCollection();
        $this->taxes = new ArrayCollection();
        $this->monnaies = new ArrayCollection();
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

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): self
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getRccm(): ?string
    {
        return $this->rccm;
    }

    public function setRccm(?string $rccm): self
    {
        $this->rccm = $rccm;

        return $this;
    }

    public function getIdnat(): ?string
    {
        return $this->idnat;
    }

    public function setIdnat(?string $idnat): self
    {
        $this->idnat = $idnat;

        return $this;
    }

    public function getNumimpot(): ?string
    {
        return $this->numimpot;
    }

    public function setNumimpot(?string $numimpot): self
    {
        $this->numimpot = $numimpot;

        return $this;
    }

    public function __toString() :string
    {
        return $this->nom;
    }

    public function getSecteur(): ?int
    {
        return $this->secteur;
    }

    public function setSecteur(?int $secteur): self
    {
        $this->secteur = $secteur;

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

    /**
     * @return Collection<int, Utilisateur>
     */
    public function getUtilisateurs(): Collection
    {
        return $this->utilisateurs;
    }

    public function addUtilisateur(Utilisateur $utilisateur): self
    {
        if (!$this->utilisateurs->contains($utilisateur)) {
            $this->utilisateurs->add($utilisateur);
            $utilisateur->setEntreprise($this);
        }

        return $this;
    }

    public function removeUtilisateur(Utilisateur $utilisateur): self
    {
        if ($this->utilisateurs->removeElement($utilisateur)) {
            // set the owning side to null (unless already changed)
            if ($utilisateur->getEntreprise() === $this) {
                $utilisateur->setEntreprise(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Paiement>
     */
    public function getPaiements(): Collection
    {
        return $this->paiements;
    }

    public function addPaiement(Paiement $paiement): self
    {
        if (!$this->paiements->contains($paiement)) {
            $this->paiements->add($paiement);
            $paiement->setEntreprise($this);
        }

        return $this;
    }

    public function removePaiement(Paiement $paiement): self
    {
        if ($this->paiements->removeElement($paiement)) {
            // set the owning side to null (unless already changed)
            if ($paiement->getEntreprise() === $this) {
                $paiement->setEntreprise(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CompteBancaire>
     */
    public function getCompteBancaires(): Collection
    {
        return $this->compteBancaires;
    }

    public function addCompteBancaire(CompteBancaire $compteBancaire): self
    {
        if (!$this->compteBancaires->contains($compteBancaire)) {
            $this->compteBancaires->add($compteBancaire);
            $compteBancaire->setEntreprise($this);
        }

        return $this;
    }

    public function removeCompteBancaire(CompteBancaire $compteBancaire): self
    {
        if ($this->compteBancaires->removeElement($compteBancaire)) {
            // set the owning side to null (unless already changed)
            if ($compteBancaire->getEntreprise() === $this) {
                $compteBancaire->setEntreprise(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Taxe>
     */
    public function getTaxes(): Collection
    {
        return $this->taxes;
    }

    public function addTax(Taxe $tax): self
    {
        if (!$this->taxes->contains($tax)) {
            $this->taxes->add($tax);
            $tax->setEntreprise($this);
        }

        return $this;
    }

    public function removeTax(Taxe $tax): self
    {
        if ($this->taxes->removeElement($tax)) {
            // set the owning side to null (unless already changed)
            if ($tax->getEntreprise() === $this) {
                $tax->setEntreprise(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Monnaie>
     */
    public function getMonnaies(): Collection
    {
        return $this->monnaies;
    }

    public function addMonnaie(Monnaie $monnaie): self
    {
        if (!$this->monnaies->contains($monnaie)) {
            $this->monnaies->add($monnaie);
            $monnaie->setEntreprise($this);
        }

        return $this;
    }

    public function removeMonnaie(Monnaie $monnaie): self
    {
        if ($this->monnaies->removeElement($monnaie)) {
            // set the owning side to null (unless already changed)
            if ($monnaie->getEntreprise() === $this) {
                $monnaie->setEntreprise(null);
            }
        }

        return $this;
    }
}
