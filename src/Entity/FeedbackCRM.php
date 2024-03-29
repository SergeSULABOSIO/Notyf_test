<?php

namespace App\Entity;

use App\Repository\FeedbackCRMRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FeedbackCRMRepository::class)]
class FeedbackCRM
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $message = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'feedbackCRMs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;

    #[ORM\ManyToOne(inversedBy: 'feedbackCRMs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Entreprise $entreprise = null;

    #[ORM\ManyToOne(inversedBy: 'feedbacks')]
    private ?ActionCRM $actionCRM = null;

    #[ORM\Column(nullable: true)]
    private ?bool $closed = null;

    private ?string $status = null;

    public function __construct()
    {
        
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

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

    public function __toString():string
    {
        return "[" . $this->getMessage() . "], ". $this->utilisateur->getNom() . " le " . ($this->getCreatedAt())->format('d/m/Y à H:m:s');
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

    public function getActionCRM(): ?ActionCRM
    {
        return $this->actionCRM;
    }

    public function setActionCRM(?ActionCRM $actionCRM): self
    {
        $this->actionCRM = $actionCRM;

        return $this;
    }

    public function isClosed(): ?bool
    {
        return $this->closed;
    }

    public function setClosed(?bool $closed): self
    {
        $this->closed = $closed;

        return $this;
    }

    /**
     * Get the value of status
     */ 
    public function getStatus()
    {
        if($this->isClosed()){
            $this->status = "Achevé.";
        }else{
            $this->status = "Encours.";
        }
        return $this->status;
    }
}
