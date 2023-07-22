<?php

namespace App\Entity;

use App\Repository\PisteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PisteRepository::class)]
class Piste extends CalculableEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $objectif = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Entreprise $entreprise = null;

    #[ORM\Column(nullable: true)]
    private ?float $montant = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $expiredAt = null;

    #[ORM\ManyToOne(inversedBy: 'pistes')]
    private ?EtapeCrm $etape = null;

    #[ORM\OneToMany(mappedBy: 'piste', targetEntity: ActionCRM::class)]
    private Collection $actionCRMs;

    #[ORM\ManyToMany(targetEntity: Contact::class, mappedBy: 'piste')]
    private Collection $contacts;

    #[ORM\OneToMany(mappedBy: 'piste', targetEntity: Cotation::class)]
    private Collection $cotations;


    public function __construct()
    {
        $this->actionCRMs = new ArrayCollection();
        $this->contacts = new ArrayCollection();
        $this->cotations = new ArrayCollection();
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

    public function getObjectif(): ?string
    {
        return $this->objectif;
    }

    public function setObjectif(string $objectif): self
    {
        $this->objectif = $objectif;

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

    public function getEntreprise(): ?Entreprise
    {
        return $this->entreprise;
    }

    public function setEntreprise(?Entreprise $entreprise): self
    {
        $this->entreprise = $entreprise;

        return $this;
    }

    public function getMontant(): ?float
    {
        return $this->montant;
    }

    public function setMontant(?float $montant): self
    {
        $this->montant = $montant;

        return $this;
    }

    public function getExpiredAt(): ?\DateTimeImmutable
    {
        return $this->expiredAt;
    }

    public function setExpiredAt(\DateTimeImmutable $expiredAt): self
    {
        $this->expiredAt = $expiredAt;

        return $this;
    }

    public function __toString()
    {
        return $this->nom . ", ". ($this->updatedAt)->format('d/m/Y à H:m:s');
    }

    public function getEtape(): ?EtapeCrm
    {
        return $this->etape;
    }

    public function setEtape(?EtapeCrm $etape): self
    {
        $this->etape = $etape;

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
            $actionCRM->setPiste($this);
        }

        return $this;
    }

    public function removeActionCRM(ActionCRM $actionCRM): self
    {
        if ($this->actionCRMs->removeElement($actionCRM)) {
            // set the owning side to null (unless already changed)
            if ($actionCRM->getPiste() === $this) {
                $actionCRM->setPiste(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Contact>
     */
    public function getContacts(): Collection
    {
        return $this->contacts;
    }

    public function addContact(Contact $contact): self
    {
        if (!$this->contacts->contains($contact)) {
            $this->contacts->add($contact);
            $contact->addPiste($this);
        }

        return $this;
    }

    public function removeContact(Contact $contact): self
    {
        if ($this->contacts->removeElement($contact)) {
            $contact->removePiste($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Cotation>
     */
    public function getCotations(): Collection
    {
        return $this->cotations;
    }

    public function addCotation(Cotation $cotation): self
    {
        if (!$this->cotations->contains($cotation)) {
            $this->cotations->add($cotation);
            $cotation->setPiste($this);
        }

        return $this;
    }

    public function removeCotation(Cotation $cotation): self
    {
        if ($this->cotations->removeElement($cotation)) {
            // set the owning side to null (unless already changed)
            if ($cotation->getPiste() === $this) {
                $cotation->setPiste(null);
            }
        }

        return $this;
    }
}
