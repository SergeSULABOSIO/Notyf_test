<?php

namespace App\Entity;

use App\Entity\Police;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ActionRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity(repositoryClass: ActionRepository::class)]
class ActionCRM
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $mission = null;

    #[ORM\Column(length: 255)]
    private ?string $objectif = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $startedAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $endedAt = null;

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

    private ?bool $closed = null;

    #[ORM\ManyToOne(inversedBy: 'actionCRMs')]
    private ?Utilisateur $attributedTo = null;

    // #[ORM\ManyToOne(inversedBy: 'actionCRMs')]
    private ?Police $police = null;

    // #[ORM\ManyToOne(inversedBy: 'actionCRMs')]
    private ?Cotation $cotation = null;

    // #[ORM\ManyToOne(inversedBy: 'actionCRMs')]
    // private ?Sinistre $sinistre = null;

    #[ORM\ManyToOne(inversedBy: 'actionsCRMs', cascade: ['remove', 'persist', 'refresh'])]
    private ?Piste $piste = null;

    #[ORM\OneToMany(mappedBy: 'actionCRM', targetEntity: DocPiece::class, cascade: ['remove', 'persist', 'refresh'])]
    private Collection $documents;

    #[ORM\OneToMany(mappedBy: 'actionCRM', targetEntity: FeedbackCRM::class, cascade: ['remove', 'persist', 'refresh'])]
    private Collection $feedbacks;


    public function __construct()
    {
        //$this->feedbackCRMs = new ArrayCollection();
        $this->documents = new ArrayCollection();
        $this->feedbacks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMission(): ?string
    {
        return $this->mission;
    }

    public function setMission(string $mission): self
    {
        $this->mission = $mission;

        return $this;
    }

    public function getObjectif(): ?string
    {
        return $this->objectif;
    }

    public function setObjectif(string $objectif): self
    {
        $this->objectif = $objectif;

        return $this;
    }

    public function getStartedAt(): ?\DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function setStartedAt(\DateTimeImmutable $startedAt): self
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    public function getEndedAt(): ?\DateTimeImmutable
    {
        return $this->endedAt;
    }

    public function setEndedAt(\DateTimeImmutable $endedAt): self
    {
        $this->endedAt = $endedAt;

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

    public function __toString()
    {
        return "La mission \"" . $this->mission . "(...)\" | status : " . ($this->getClosed() == true ? "clôturée." : "encours.") . " | Attribuée à " . $this->attributedTo . " | Echéance: " . $this->endedAt->format('d-m-Y');
    }

    public function getAttributedTo(): ?Utilisateur
    {
        return $this->attributedTo;
    }

    public function setAttributedTo(?Utilisateur $attributedTo): self
    {
        $this->attributedTo = $attributedTo;

        return $this;
    }

    public function getPiste(): ?Piste
    {
        return $this->piste;
    }

    public function setPiste(?Piste $piste): self
    {
        $this->piste = $piste;

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
            $document->setActionCRM($this);
        }

        return $this;
    }

    public function removeDocument(DocPiece $document): self
    {
        if ($this->documents->removeElement($document)) {
            // set the owning side to null (unless already changed)
            if ($document->getActionCRM() === $this) {
                $document->setActionCRM(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, FeedbackCRM>
     */
    public function getFeedbacks(): Collection
    {
        return $this->feedbacks;
    }

    public function addFeedback(FeedbackCRM $feedback): self
    {
        if (!$this->feedbacks->contains($feedback)) {
            $this->feedbacks->add($feedback);
            $feedback->setActionCRM($this);
        }

        return $this;
    }

    public function removeFeedback(FeedbackCRM $feedback): self
    {
        if ($this->feedbacks->removeElement($feedback)) {
            // set the owning side to null (unless already changed)
            if ($feedback->getActionCRM() === $this) {
                $feedback->setActionCRM(null);
            }
        }

        return $this;
    }

    /**
     * Get the value of closed
     */
    public function getClosed()
    {
        $this->closed = false;
        foreach ($this->getFeedbacks() as $feedback) {
            if ($feedback->isClosed() == true) {
                $this->closed = true;
                break;
            }
        }
        return $this->closed;
    }

    /**
     * Set the value of closed
     *
     * @return  self
     */
    public function setClosed($closed)
    {
        $this->closed = $closed;

        return $this;
    }
}
