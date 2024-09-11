<?php

namespace App\Entity;

use App\Entity\Traits\TraitEcouteurEvenements;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\FeedbackCRMRepository;
use App\Service\RefactoringJS\Evenements\Sujet;
use Doctrine\Common\Collections\ArrayCollection;
use App\Service\RefactoringJS\Commandes\Commande;
use App\Service\RefactoringJS\Evenements\Evenement;
use App\Service\RefactoringJS\Evenements\Observateur;
use App\Service\RefactoringJS\Commandes\CommandeExecuteur;
use App\Service\RefactoringJS\Commandes\ComDetecterEvenementAttribut;
use App\Service\RefactoringJS\Commandes\Piste\CommandePisteNotifierEvenement;

#[ORM\Entity(repositoryClass: FeedbackCRMRepository::class)]
#[ORM\HasLifecycleCallbacks]
class FeedbackCRM implements Sujet, CommandeExecuteur
{
    use TraitEcouteurEvenements;

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
        $this->listeObservateurs = new ArrayCollection();
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
        $oldValue = $this->getMessage();
        $newValue = $message;
        $this->message = $message;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Message", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

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

    public function __toString(): string
    {
        $user = $this->utilisateur != null ? $this->utilisateur->getNom() : " Utilisateur Inconnu";
        $createAt = $this->getCreatedAt() != null ? " le " . (($this->getCreatedAt())->format('d/m/Y à H:m:s')) : " Date de création inconnue";
        return "[" . strip_tags($this->getMessage()) . "], " . $user . $createAt;
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
        $oldValue = $this->getActionCRM();
        $newValue = $actionCRM;
        $this->actionCRM = $actionCRM;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Tâche", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));

        return $this;
    }

    public function isClosed(): ?bool
    {
        return $this->closed;
    }

    public function setClosed(?bool $closed): self
    {
        $oldValue = $this->isClosed();
        $newValue = $closed;
        $this->closed = $closed;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Cloturé", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

        return $this;
    }

    /**
     * Get the value of status
     */
    public function getStatus()
    {
        if ($this->isClosed()) {
            $this->status = "Achevé.";
        } else {
            $this->status = "Encours.";
        }
        return $this->status;
    }

    public function transfererObservateur(?Observateur $observateur)
    {
        // dd("Cette fonction n'est pas encore définie");
    }
}
