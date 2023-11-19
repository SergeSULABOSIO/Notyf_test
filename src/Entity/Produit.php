<?php

namespace App\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\ProduitRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
class Produit extends CalculableEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 4)]
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

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Entreprise $entreprise = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne]
    private ?Utilisateur $utilisateur = null;

    #[ORM\OneToMany(mappedBy: 'produit', targetEntity: Police::class)]
    private Collection $police;

    #[ORM\OneToMany(mappedBy: 'produit', targetEntity: Piste::class)]
    private Collection $pistes;

    #[ORM\Column]
    private ?bool $abonnement = null;

    #[ORM\Column]
    private ?bool $obligatoire = null;

    #[ORM\Column]
    private ?bool $iard = null;

    public function __construct()
    {
        //$this->cotations = new ArrayCollection();
        $this->police = new ArrayCollection();
        $this->pistes = new ArrayCollection();
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

    // public function isIsobligatoire(): ?bool
    // {
    //     return $this->isobligatoire;
    // }

    // public function setIsobligatoire(bool $isobligatoire): self
    // {
    //     $this->isobligatoire = $isobligatoire;

    //     return $this;
    // }

    // public function isIsabonnement(): ?bool
    // {
    //     return $this->isabonnement;
    // }

    // public function setIsabonnement(bool $isabonnement): self
    // {
    //     $this->isabonnement = $isabonnement;

    //     return $this;
    // }

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

    // public function getCategorie(): ?int
    // {
    //     return $this->categorie;
    // }

    // public function setCategorie(int $categorie): self
    // {
    //     $this->categorie = $categorie;

    //     return $this;
    // }

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

    // /**
    //  * @return Collection<int, Cotation>
    //  */
    // public function getCotations(): Collection
    // {
    //     return $this->cotations;
    // }

    // public function addCotation(Cotation $cotation): self
    // {
    //     if (!$this->cotations->contains($cotation)) {
    //         $this->cotations->add($cotation);
    //         $cotation->setProduit($this);
    //     }

    //     return $this;
    // }

    // public function removeCotation(Cotation $cotation): self
    // {
    //     if ($this->cotations->removeElement($cotation)) {
    //         // set the owning side to null (unless already changed)
    //         if ($cotation->getProduit() === $this) {
    //             $cotation->setProduit(null);
    //         }
    //     }

    //     return $this;
    // }

    /**
     * @return Collection<int, Police>
     */
    public function getPolice(): Collection
    {
        return $this->police;
    }

    public function addPolice(Police $police): self
    {
        if (!$this->police->contains($police)) {
            $this->police->add($police);
            $police->setProduit($this);
        }

        return $this;
    }

    public function removePolice(Police $police): self
    {
        if ($this->police->removeElement($police)) {
            // set the owning side to null (unless already changed)
            if ($police->getProduit() === $this) {
                $police->setProduit(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Piste>
     */
    public function getPistes(): Collection
    {
        return $this->pistes;
    }

    public function addPiste(Piste $piste): self
    {
        if (!$this->pistes->contains($piste)) {
            $this->pistes->add($piste);
            $piste->setProduit($this);
        }

        return $this;
    }

    public function removePiste(Piste $piste): self
    {
        if ($this->pistes->removeElement($piste)) {
            // set the owning side to null (unless already changed)
            if ($piste->getProduit() === $this) {
                $piste->setProduit(null);
            }
        }

        return $this;
    }

    public function isAbonnement(): ?bool
    {
        return $this->abonnement;
    }

    public function setAbonnement(bool $abonnement): self
    {
        $this->abonnement = $abonnement;

        return $this;
    }

    public function isObligatoire(): ?bool
    {
        return $this->obligatoire;
    }

    public function setObligatoire(bool $obligatoire): self
    {
        $this->obligatoire = $obligatoire;

        return $this;
    }

    public function isIard(): ?bool
    {
        return $this->iard;
    }

    public function setIard(bool $iard): self
    {
        $this->iard = $iard;

        return $this;
    }
}
