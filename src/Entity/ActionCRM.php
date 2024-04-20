<?php

namespace App\Entity;

use App\Entity\Police;
use App\Entity\Cotation;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ActionRepository;
use Doctrine\Common\Collections\Collection;
use App\Entity\Traits\TraitEcouteurEvenements;
use App\Service\RefactoringJS\Evenements\Sujet;
use Doctrine\Common\Collections\ArrayCollection;
use App\Service\RefactoringJS\Commandes\Commande;
use App\Service\RefactoringJS\Evenements\Evenement;
use App\Service\RefactoringJS\Evenements\Observateur;
use App\Service\RefactoringJS\Commandes\CommandeExecuteur;
use App\Service\RefactoringJS\Commandes\ComDetecterEvenementAttribut;

#[ORM\Entity(repositoryClass: ActionRepository::class)]
#[ORM\HasLifecycleCallbacks]
class ActionCRM implements Sujet, CommandeExecuteur
{
    use TraitEcouteurEvenements;
    
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

    private ?string $status;


    public function __construct()
    {
        //$this->feedbackCRMs = new ArrayCollection();
        $this->documents = new ArrayCollection();
        $this->feedbacks = new ArrayCollection();
        $this->listeObservateurs = new ArrayCollection();
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
        $oldValue = $this->getMission();
        $newValue = $mission;
        $this->mission = $mission;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Tâche/Mission", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));
        return $this;
    }

    public function getObjectif(): ?string
    {
        return $this->objectif;
    }

    public function setObjectif(string $objectif): self
    {
        $oldValue = $this->getObjectif();
        $newValue = $objectif;
        $this->objectif = $objectif;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Objectif", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));
        return $this;
    }

    public function getStartedAt(): ?\DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function setStartedAt(\DateTimeImmutable $startedAt): self
    {
        $oldValue = $this->getStartedAt();
        $newValue = $startedAt;
        $this->startedAt = $startedAt;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Date d'effet", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));
        return $this;
    }

    public function getEndedAt(): ?\DateTimeImmutable
    {
        return $this->endedAt;
    }

    public function setEndedAt(\DateTimeImmutable $endedAt): self
    {
        $oldValue = $this->getEndedAt();
        $newValue = $endedAt;
        $this->endedAt = $endedAt;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Echéance", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));
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
        return $this->mission . "(...)\" | status : " . ($this->getClosed() == true ? "clôturée." : "encours.") . " | Attribuée à " . $this->attributedTo . " | Echéance: " . $this->endedAt->format('d-m-Y');
    }

    public function getAttributedTo(): ?Utilisateur
    {
        return $this->attributedTo;
    }

    public function setAttributedTo(?Utilisateur $attributedTo): self
    {
        $oldValue = $this->getAttributedTo();
        $newValue = $attributedTo;
        $this->attributedTo = $attributedTo;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Attribué à", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
        return $this;
    }

    public function getPiste(): ?Piste
    {
        return $this->piste;
    }

    public function setPiste(?Piste $piste): self
    {
        $oldValue = $this->getPiste();
        $newValue = $piste;
        $this->piste = $piste;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Piste", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
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
            $oldValue = null;
            $newValue = $document;
            $this->documents->add($document);
            $document->setActionCRM($this);
            //Ecouteur d'action
            $this->executer(new ComDetecterEvenementAttribut($this, "Document", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
        }

        return $this;
    }

    public function removeDocument(DocPiece $document): self
    {
        if ($this->documents->removeElement($document)) {
            // set the owning side to null (unless already changed)
            if ($document->getActionCRM() === $this) {
                $oldValue = $document;
                $newValue = null;
                $document->setActionCRM(null);
                //Ecouteur d'action
                $this->executer(new ComDetecterEvenementAttribut($this, "Document", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
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
        // dd("C'est ici qu'il faut pieger l'écouter d'actions.");
        if (!$this->feedbacks->contains($feedback)) {
            $oldValue = null;
            $newValue = $feedback;
            $this->feedbacks->add($feedback);
            $feedback->setActionCRM($this);
            //Ecouteur d'action
            $this->executer(new ComDetecterEvenementAttribut($this, "Feedback", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
        }

        return $this;
    }

    public function removeFeedback(FeedbackCRM $feedback): self
    {
        if ($this->feedbacks->removeElement($feedback)) {
            // set the owning side to null (unless already changed)
            if ($feedback->getActionCRM() === $this) {
                $oldValue = $feedback;
                $newValue = null;
                $feedback->setActionCRM(null);
                //Ecouteur d'action
                $this->executer(new ComDetecterEvenementAttribut($this, "Feedback", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
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
        $oldValue = $this->getClosed();
        $newValue = $closed;
        $this->closed = $closed;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "is Closed?", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));
        return $this;
    }

    /**
     * Get the value of police
     */
    public function getPolice()
    {
        if ($this->getPiste()) {
            if ($this->getPiste()->getPolices()[0]) {
                $this->police = $this->getPiste()->getPolices()[0];
            }
        }
        return $this->police;
    }

    /**
     * Get the value of cotation
     */
    public function getCotation()
    {
        if ($this->getPiste()) {
            if ($this->getPiste()->getPolices()[0]) {
                $this->cotation = $this->getPiste()->getPolices()[0]->getCotation();
            }
        }
        return $this->cotation;
    }

    /**
     * Get the value of status
     */
    public function getStatus()
    {
        if ($this->getClosed()) {
            $this->status = "Achevé.";
        } else {
            $this->status = "Encours.";
        }
        return $this->status;
    }

    public function transfererObservateur(?Observateur $observateur)
    {
        //Transfer de l'observateur chez Feedback
        if (count($this->getFeedbacks()) != 0) {
            foreach ($this->getFeedbacks() as $feedback) {
                $feedback->ajouterObservateur($observateur);
            }
        }
    }
}
