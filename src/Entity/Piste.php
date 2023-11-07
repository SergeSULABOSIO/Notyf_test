<?php

namespace App\Entity;

use App\Repository\PisteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PisteRepository::class)]
class Piste
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

    #[ORM\OneToMany(mappedBy: 'piste', targetEntity: Contact::class, cascade:['remove', 'persist', 'refresh'])]
    private Collection $contacts;

    #[ORM\OneToMany(mappedBy: 'piste', targetEntity: Cotation::class, cascade:['remove', 'persist', 'refresh'])]
    private Collection $cotations;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $typeavenant = null;
    
    #[ORM\ManyToOne(inversedBy: 'pistes')]
    private ?Police $police = null;

    #[ORM\OneToMany(mappedBy: 'piste', targetEntity: ActionCRM::class, cascade:['remove', 'persist', 'refresh'])]
    private Collection $actionsCRMs;

    #[ORM\ManyToOne(inversedBy: 'pistes')]
    private ?Client $client = null;

    #[ORM\OneToMany(mappedBy: 'piste', targetEntity: Client::class)]
    private Collection $prospect;

    public function __construct()
    {
        $this->cotations = new ArrayCollection();
        $this->contacts = new ArrayCollection();
        $this->actionsCRMs = new ArrayCollection();
        $this->prospect = new ArrayCollection();
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
        return "la piste " . $this->nom;// . ", ". ($this->updatedAt)->format('d/m/Y à H:m:s');
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

    public function getTypeavenant(): ?string
    {
        return $this->typeavenant;
    }

    public function setTypeavenant(?string $typeavenant): self
    {
        $this->typeavenant = $typeavenant;

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
            $contact->setPiste($this);
        }

        return $this;
    }

    public function removeContact(Contact $contact): self
    {
        if ($this->contacts->removeElement($contact)) {
            // set the owning side to null (unless already changed)
            if ($contact->getPiste() === $this) {
                $contact->setPiste(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ActionCRM>
     */
    public function getActionsCRMs(): Collection
    {
        return $this->actionsCRMs;
    }

    public function addActionsCRM(ActionCRM $actionsCRM): self
    {
        if (!$this->actionsCRMs->contains($actionsCRM)) {
            $this->actionsCRMs->add($actionsCRM);
            $actionsCRM->setPiste($this);
        }

        return $this;
    }

    public function removeActionsCRM(ActionCRM $actionsCRM): self
    {
        if ($this->actionsCRMs->removeElement($actionsCRM)) {
            // set the owning side to null (unless already changed)
            if ($actionsCRM->getPiste() === $this) {
                $actionsCRM->setPiste(null);
            }
        }

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): self
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return Collection<int, Client>
     */
    public function getProspect(): Collection
    {
        return $this->prospect;
    }

    public function addProspect(Client $prospect): self
    {
        if (!$this->prospect->contains($prospect)) {
            $this->prospect->add($prospect);
            $prospect->setPiste($this);
        }

        return $this;
    }

    public function removeProspect(Client $prospect): self
    {
        if ($this->prospect->removeElement($prospect)) {
            // set the owning side to null (unless already changed)
            if ($prospect->getPiste() === $this) {
                $prospect->setPiste(null);
            }
        }

        return $this;
    }
}
