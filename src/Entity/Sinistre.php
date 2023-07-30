<?php

namespace App\Entity;

use App\Repository\SinistreRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SinistreRepository::class)]
class Sinistre extends CalculableEntity
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

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;

    #[ORM\ManyToMany(targetEntity: Expert::class, inversedBy: 'sinistres')]
    private Collection $experts;

    #[ORM\Column]
    private ?float $cout = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Entreprise $entreprise = null;

    #[ORM\Column]
    private ?float $montantPaye = null;

    #[ORM\OneToMany(mappedBy: 'sinistre', targetEntity: CommentaireSinistre::class)]
    private Collection $commentaire;

    #[ORM\Column]
    private ?\DateTimeImmutable $occuredAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $paidAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $numero = null;

    #[ORM\ManyToOne(inversedBy: 'sinistres')]
    private ?Police $police = null;

    #[ORM\ManyToOne(inversedBy: 'sinistres')]
    private ?EtapeSinistre $etape = null;

    #[ORM\OneToMany(mappedBy: 'sinistre', targetEntity: Victime::class)]
    private Collection $victimes;

    #[ORM\OneToMany(mappedBy: 'sinistre', targetEntity: DocPiece::class)]
    private Collection $docPieces;

    #[ORM\OneToMany(mappedBy: 'sinistre', targetEntity: ActionCRM::class)]
    private Collection $actionCRMs;

    public function __construct()
    {
        $this->experts = new ArrayCollection();
        $this->commentaire = new ArrayCollection();
        $this->victimes = new ArrayCollection();
        $this->docPieces = new ArrayCollection();
        $this->actionCRMs = new ArrayCollection();
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

    public function getMontantPaye(): ?float
    {
        return $this->montantPaye;
    }

    public function setMontantPaye(float $montantPaye): self
    {
        $this->montantPaye = $montantPaye;

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

    public function getPolice(): ?Police
    {
        return $this->police;
    }

    public function setPolice(?Police $police): self
    {
        $this->police = $police;

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
     * @return Collection<int, DocPiece>
     */
    public function getDocPieces(): Collection
    {
        return $this->docPieces;
    }

    public function addDocPiece(DocPiece $docPiece): self
    {
        if (!$this->docPieces->contains($docPiece)) {
            $this->docPieces->add($docPiece);
            $docPiece->setSinistre($this);
        }

        return $this;
    }

    public function removeDocPiece(DocPiece $docPiece): self
    {
        if ($this->docPieces->removeElement($docPiece)) {
            // set the owning side to null (unless already changed)
            if ($docPiece->getSinistre() === $this) {
                $docPiece->setSinistre(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ActionCRM>
     */
    public function getActionCRMs(): Collection
    {
        return $this->actionCRMs;
    }

    public function addActionCRM(ActionCRM $actionCRM): self
    {
        if (!$this->actionCRMs->contains($actionCRM)) {
            $this->actionCRMs->add($actionCRM);
            $actionCRM->setSinistre($this);
        }

        return $this;
    }

    public function removeActionCRM(ActionCRM $actionCRM): self
    {
        if ($this->actionCRMs->removeElement($actionCRM)) {
            // set the owning side to null (unless already changed)
            if ($actionCRM->getSinistre() === $this) {
                $actionCRM->setSinistre(null);
            }
        }

        return $this;
    }
}
