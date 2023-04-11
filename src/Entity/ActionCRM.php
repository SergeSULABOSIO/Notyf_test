<?php

namespace App\Entity;

use App\Repository\ActionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

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

    #[ORM\ManyToMany(targetEntity: Utilisateur::class)]
    private Collection $attributedTo;

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

    #[ORM\ManyToOne(inversedBy: 'actions')]
    private ?Piste $piste = null;

    #[ORM\ManyToOne(inversedBy: 'action')]
    private ?FeedbackCRM $feedback = null;

    #[ORM\OneToMany(mappedBy: 'action', targetEntity: FeedbackCRM::class, orphanRemoval: true)]
    private Collection $feedbacks;

    #[ORM\Column]
    private ?bool $clos = null;

    public function __construct()
    {
        $this->attributedTo = new ArrayCollection();
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

    /**
     * @return Collection<int, Utilisateur>
     */
    public function getAttributedTo(): Collection
    {
        return $this->attributedTo;
    }

    public function addAttributedTo(Utilisateur $attributedTo): self
    {
        if (!$this->attributedTo->contains($attributedTo)) {
            $this->attributedTo->add($attributedTo);
        }

        return $this;
    }

    public function removeAttributedTo(Utilisateur $attributedTo): self
    {
        $this->attributedTo->removeElement($attributedTo);

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

    public function getPiste(): ?Piste
    {
        return $this->piste;
    }

    public function setPiste(?Piste $piste): self
    {
        $this->piste = $piste;

        return $this;
    }

    public function getFeedback(): ?FeedbackCRM
    {
        return $this->feedback;
    }

    public function setFeedback(?FeedbackCRM $feedback): self
    {
        $this->feedback = $feedback;

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
            $feedback->setAction($this);
        }

        return $this;
    }

    public function removeFeedback(FeedbackCRM $feedback): self
    {
        if ($this->feedbacks->removeElement($feedback)) {
            // set the owning side to null (unless already changed)
            if ($feedback->getAction() === $this) {
                $feedback->setAction(null);
            }
        }

        return $this;
    }

    public function isClos(): ?bool
    {
        return $this->clos;
    }

    public function setClos(bool $clos): self
    {
        $this->clos = $clos;

        return $this;
    }

}
