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
    
    #[ORM\ManyToMany(targetEntity: DocCategorie::class)]
    private Collection $categorie;

    #[ORM\ManyToMany(targetEntity: DocClasseur::class)]
    private Collection $classeur;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fichierA = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fichierB = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fichierC = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fichierD = null;

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


    public function __construct()
    {
        $this->categorie = new ArrayCollection();
        $this->classeur = new ArrayCollection();
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

    public function getFichierA(): ?string
    {
        return $this->fichierA;
    }

    public function setFichierA(string $fichierA): self
    {
        $this->fichierA = $fichierA;

        return $this;
    }

    public function getFichierB(): ?string
    {
        return $this->fichierB;
    }

    public function setFichierB(?string $fichierB): self
    {
        $this->fichierB = $fichierB;

        return $this;
    }

    public function getFichierC(): ?string
    {
        return $this->fichierC;
    }

    public function setFichierC(?string $fichierC): self
    {
        $this->fichierC = $fichierC;

        return $this;
    }

    public function getFichierD(): ?string
    {
        return $this->fichierD;
    }

    public function setFichierD(?string $fichierD): self
    {
        $this->fichierD = $fichierD;

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

    /**
     * @return Collection<int, DocCategorie>
     */
    public function getCategorie(): Collection
    {
        return $this->categorie;
    }

    public function addCategorie(DocCategorie $categorie): self
    {
        if (!$this->categorie->contains($categorie)) {
            $this->categorie->add($categorie);
        }

        return $this;
    }

    public function removeCategorie(DocCategorie $categorie): self
    {
        $this->categorie->removeElement($categorie);

        return $this;
    }

    /**
     * @return Collection<int, DocClasseur>
     */
    public function getClasseur(): Collection
    {
        return $this->classeur;
    }

    public function addClasseur(DocClasseur $classeur): self
    {
        if (!$this->classeur->contains($classeur)) {
            $this->classeur->add($classeur);
        }

        return $this;
    }

    public function removeClasseur(DocClasseur $classeur): self
    {
        $this->classeur->removeElement($classeur);

        return $this;
    }

    public function __toString()
    {
        return $this->nom;
    }
}
