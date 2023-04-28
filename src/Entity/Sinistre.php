<?php

namespace App\Entity;

use App\Repository\SinistreRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SinistreRepository::class)]
class Sinistre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'sinistre', targetEntity: Victime::class)]
    private Collection $victimes;

    #[ORM\ManyToMany(targetEntity: Victime::class, inversedBy: 'sinistres')]
    private Collection $victime;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;

    #[ORM\ManyToMany(targetEntity: Expert::class, inversedBy: 'sinistres')]
    private Collection $experts;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Police $police = null;

    #[ORM\Column]
    private ?float $cout = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Entreprise $entreprise = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?EtapeSinistre $etape = null;

    #[ORM\Column]
    private ?float $montantPaye = null;

    #[ORM\OneToMany(mappedBy: 'sinistre', targetEntity: CommentaireSinistre::class)]
    private Collection $commentaire;

    #[ORM\Column]
    private ?\DateTimeImmutable $occuredAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $paidAt = null;

    #[ORM\ManyToMany(targetEntity: DocPiece::class)]
    private Collection $pieces;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Monnaie $monnaie = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $numero = null;

    public function __construct()
    {
        $this->victimes = new ArrayCollection();
        $this->victime = new ArrayCollection();
        $this->experts = new ArrayCollection();
        $this->commentaire = new ArrayCollection();
        $this->pieces = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = $titre;

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
     * @return Collection<int, Victime>
     */
    public function getVictimes(): Collection
    {
        return $this->victimes;
    }

    public function addVictime(Victime $victime): self
    {
        if (!$this->victimes->contains($victime)) {
            $this->victimes->add($victime);
            $victime->setSinistre($this);
        }

        return $this;
    }

    public function removeVictime(Victime $victime): self
    {
        if ($this->victimes->removeElement($victime)) {
            // set the owning side to null (unless already changed)
            if ($victime->getSinistre() === $this) {
                $victime->setSinistre(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Victime>
     */
    public function getVictime(): Collection
    {
        return $this->victime;
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
     * @return Collection<int, Expert>
     */
    public function getExperts(): Collection
    {
        return $this->experts;
    }

    public function addExpert(Expert $expert): self
    {
        if (!$this->experts->contains($expert)) {
            $this->experts->add($expert);
        }

        return $this;
    }

    public function removeExpert(Expert $expert): self
    {
        $this->experts->removeElement($expert);

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

    public function getCout(): ?float
    {
        return $this->cout;
    }

    public function setCout(float $cout): self
    {
        $this->cout = $cout;

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

    public function getDateIncident(): ?\DateTimeInterface
    {
        return $this->dateIncident;
    }

    public function setDateIncident(\DateTimeInterface $dateIncident): self
    {
        $this->dateIncident = $dateIncident;

        return $this;
    }

    public function getEtape(): ?EtapeSinistre
    {
        return $this->etape;
    }

    public function setEtape(?EtapeSinistre $etape): self
    {
        $this->etape = $etape;

        return $this;
    }

    public function getMontantPaye(): ?float
    {
        return $this->montantPaye;
    }

    public function setMontantPaye(float $montantPaye): self
    {
        $this->montantPaye = $montantPaye;

        return $this;
    }

    public function getDatePayement(): ?\DateTimeInterface
    {
        return $this->datePayement;
    }

    public function setDatePayement(?\DateTimeInterface $datePayement): self
    {
        $this->datePayement = $datePayement;

        return $this;
    }

    /**
     * @return Collection<int, CommentaireSinistre>
     */
    public function getCommentaire(): Collection
    {
        return $this->commentaire;
    }

    public function addCommentaire(CommentaireSinistre $commentaire): self
    {
        if (!$this->commentaire->contains($commentaire)) {
            $this->commentaire->add($commentaire);
            $commentaire->setSinistre($this);
        }

        return $this;
    }

    public function removeCommentaire(CommentaireSinistre $commentaire): self
    {
        if ($this->commentaire->removeElement($commentaire)) {
            // set the owning side to null (unless already changed)
            if ($commentaire->getSinistre() === $this) {
                $commentaire->setSinistre(null);
            }
        }

        return $this;
    }

    public function getOccuredAt(): ?\DateTimeImmutable
    {
        return $this->occuredAt;
    }

    public function setOccuredAt(\DateTimeImmutable $occuredAt): self
    {
        $this->occuredAt = $occuredAt;

        return $this;
    }

    public function getPaidAt(): ?\DateTimeImmutable
    {
        return $this->paidAt;
    }

    public function setPaidAt(?\DateTimeImmutable $paidAt): self
    {
        $this->paidAt = $paidAt;

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

    public function getMonnaie(): ?Monnaie
    {
        return $this->monnaie;
    }

    public function setMonnaie(?Monnaie $monnaie): self
    {
        $this->monnaie = $monnaie;

        return $this;
    }

    public function __toString()
    {
        return $this->numero . " / " . $this->titre;
    }

    public function getNumero(): ?string
    {
        return $this->numero;
    }

    public function setNumero(?string $numero): self
    {
        $this->numero = $numero;

        return $this;
    }
}
