<?php

namespace App\Entity;

use App\Entity\Traits\TraitEcouteurEvenements;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\SinistreRepository;
use Doctrine\Common\Collections\Collection;
use App\Service\RefactoringJS\Evenements\Sujet;
use Doctrine\Common\Collections\ArrayCollection;
use App\Service\RefactoringJS\Commandes\Commande;
use App\Service\RefactoringJS\Evenements\Evenement;
use App\Service\RefactoringJS\Evenements\Observateur;
use App\Service\RefactoringJS\Commandes\CommandeExecuteur;
use App\Service\RefactoringJS\Commandes\ComDetecterEvenementAttribut;
use App\Service\RefactoringJS\Commandes\Piste\CommandePisteNotifierEvenement;

#[ORM\Entity(repositoryClass: SinistreRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Sinistre implements Sujet, CommandeExecuteur
{
    use TraitEcouteurEvenements;

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
        $this->victimes = new ArrayCollection();
        $this->docPieces = new ArrayCollection();
        $this->actionCRMs = new ArrayCollection();
        $this->listeObservateurs = new ArrayCollection();
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
        $oldValue = $this->getTitre();
        $newValue = $titre;
        $this->titre = $titre;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Titre", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $oldValue = $this->getDescription();
        $newValue = $description;
        $this->description = $description;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Description", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

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
            $oldValue = null;
            $newValue = $expert;
            $this->experts->add($expert);
            //Ecouteur d'action
            $this->executer(new ComDetecterEvenementAttribut($this, "Expert", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
        }

        return $this;
    }

    public function removeExpert(Expert $expert): self
    {
        $oldValue = $expert;
        $newValue = null;
        $this->experts->removeElement($expert);
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Expert", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));

        return $this;
    }

    public function getCout(): ?float
    {
        return $this->cout;
    }

    public function setCout(float $cout): self
    {
        $oldValue = $this->getCout();
        $newValue = $cout;
        $this->cout = $cout;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Coût", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

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
        $oldValue = $this->getMontantPaye();
        $newValue = $montantPaye;
        $this->montantPaye = $montantPaye;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Montant payé", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

        return $this;
    }

    public function getOccuredAt(): ?\DateTimeImmutable
    {
        return $this->occuredAt;
    }

    public function setOccuredAt(\DateTimeImmutable $occuredAt): self
    {
        $oldValue = $this->getOccuredAt();
        $newValue = $occuredAt;
        $this->occuredAt = $occuredAt;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Date de l'évènement", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

        return $this;
    }

    public function getPaidAt(): ?\DateTimeImmutable
    {
        return $this->paidAt;
    }

    public function setPaidAt(?\DateTimeImmutable $paidAt): self
    {
        $oldValue = $this->getPaidAt();
        $newValue = $paidAt;
        $this->paidAt = $paidAt;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Date de paiement", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

        return $this;
    }

    public function __toString()
    {
        return $this->numero;
    }

    public function getNumero(): ?string
    {
        return $this->numero;
    }

    public function setNumero(?string $numero): self
    {
        $oldValue = $this->getNumero();
        $newValue = $numero;
        $this->numero = $numero;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Numéro", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

        return $this;
    }

    public function getPolice(): ?Police
    {
        return $this->police;
    }

    public function setPolice(?Police $police): self
    {
        $oldValue = $this->getPolice();
        $newValue = $police;
        $this->police = $police;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Police", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));

        return $this;
    }

    public function getEtape(): ?EtapeSinistre
    {
        return $this->etape;
    }

    public function setEtape(?EtapeSinistre $etape): self
    {
        $oldValue = $this->getEtape();
        $newValue = $etape;
        $this->etape = $etape;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Etape sinistre", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));

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
            $oldValue = null;
            $newValue = $victime;
            $this->victimes->add($victime);
            $victime->setSinistre($this);
            //Ecouteur d'action
            $this->executer(new ComDetecterEvenementAttribut($this, "Victime", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
        }

        return $this;
    }

    public function removeVictime(Victime $victime): self
    {
        if ($this->victimes->removeElement($victime)) {
            // set the owning side to null (unless already changed)
            if ($victime->getSinistre() === $this) {
                $oldValue = $victime;
                $newValue = null;
                $victime->setSinistre(null);
                //Ecouteur d'action
                $this->executer(new ComDetecterEvenementAttribut($this, "Victime", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
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
            $oldValue = null;
            $newValue = $docPiece;
            $this->docPieces->add($docPiece);
            // $docPiece->setSinistre($this);
            //Ecouteur d'action
            $this->executer(new ComDetecterEvenementAttribut($this, "Document", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
        }

        return $this;
    }

    public function removeDocPiece(DocPiece $docPiece): self
    {
        if ($this->docPieces->removeElement($docPiece)) {
            // set the owning side to null (unless already changed)
            $oldValue = $docPiece;
            $newValue = null;
            //Ecouteur d'action
            $this->executer(new ComDetecterEvenementAttribut($this, "Document", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
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
            $oldValue = null;
            $newValue = $actionCRM;
            $this->actionCRMs->add($actionCRM);
            // $actionCRM->setSinistre($this);
            //Ecouteur d'action
            $this->executer(new ComDetecterEvenementAttribut($this, "Tâche", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
        }

        return $this;
    }

    public function removeActionCRM(ActionCRM $actionCRM): self
    {
        if ($this->actionCRMs->removeElement($actionCRM)) {
            $oldValue = $actionCRM;
            $newValue = null;
            //Ecouteur d'action
            $this->executer(new ComDetecterEvenementAttribut($this, "Tâche", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
        }

        return $this;
    }

    public function transfererObservateur(?Observateur $observateur)
    {
        // dd("Fonction non encore définie");
    }
}
