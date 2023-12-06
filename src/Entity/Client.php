<?php

namespace App\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\ClientRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ClientRepository::class)]
class Client extends CalculableEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank(message: "Ce champ ne peut pas être vide.")]
    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adresse = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $telephone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $siteweb = null;

    #[ORM\Column]
    private ?bool $ispersonnemorale = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $rccm = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $idnat = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $numipot = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Entreprise $entreprise = null;

    #[ORM\Column]
    private ?int $secteur = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;
    
    #[ORM\ManyToOne]
    private ?Utilisateur $utilisateur = null;

    // #[ORM\OneToMany(mappedBy: 'client', targetEntity: Police::class)]
    // private Collection $police;

    // #[ORM\OneToMany(mappedBy: 'client', targetEntity: Cotation::class)]
    // private Collection $cotations;

    #[ORM\OneToMany(mappedBy: 'client', targetEntity: Piste::class)]
    private Collection $pistes;

    #[ORM\ManyToOne(inversedBy: 'prospect')]
    private ?Piste $piste = null;

    private ?Collection $cotations;

    #[ORM\Column(nullable: true)]
    private ?bool $exoneree = null;

    

    public function __construct()
    {
        //$this->police = new ArrayCollection();
        $this->cotations = new ArrayCollection();
        $this->pistes = new ArrayCollection();
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

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): self
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getSiteweb(): ?string
    {
        return $this->siteweb;
    }

    public function setSiteweb(?string $siteweb): self
    {
        $this->siteweb = $siteweb;

        return $this;
    }

    public function isIspersonnemorale(): ?bool
    {
        return $this->ispersonnemorale;
    }

    public function setIspersonnemorale(bool $ispersonnemorale): self
    {
        $this->ispersonnemorale = $ispersonnemorale;

        return $this;
    }

    public function getRccm(): ?string
    {
        return $this->rccm;
    }

    public function setRccm(?string $rccm): self
    {
        $this->rccm = $rccm;

        return $this;
    }

    public function getIdnat(): ?string
    {
        return $this->idnat;
    }

    public function setIdnat(?string $idnat): self
    {
        $this->idnat = $idnat;

        return $this;
    }

    public function getNumipot(): ?string
    {
        return $this->numipot;
    }

    public function setNumipot(?string $numipot): self
    {
        $this->numipot = $numipot;

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

    public function getSecteur(): ?int
    {
        return $this->secteur;
    }

    public function setSecteur(int $secteur): self
    {
        $this->secteur = $secteur;

        return $this;
    }

    public function __toString()
    {
        $txtExoneree = "";
        $tx = "taxe";
        if($this->isExoneree()){
            if($this->getEntreprise()){
                if($this->getEntreprise()->getTaxes()){
                    foreach ($this->getEntreprise()->getTaxes() as $taxe) {
                        if($taxe->isPayableparcourtier() == false){
                            $tx = "" . $taxe->getNom();
                            break;
                        }
                    }
                }
            }
            $txtExoneree = " (exoneré de la ". $tx .")";
        }
        return "" . $this->nom . "" . $txtExoneree;
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
     * @return Collection<int, Piste>
     */
    public function getPistes(): Collection
    {
        return $this->pistes;
    }

    public function addPiste(Piste $piste): self
    {
        if (!$this->pistes->contains($piste)) {
            $this->pistes->add($piste);
            $piste->setClient($this);
        }

        return $this;
    }

    public function removePiste(Piste $piste): self
    {
        if ($this->pistes->removeElement($piste)) {
            // set the owning side to null (unless already changed)
            if ($piste->getClient() === $this) {
                $piste->setClient(null);
            }
        }

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

    public function isExoneree(): ?bool
    {
        return $this->exoneree;
    }

    public function setExoneree(?bool $exoneree): self
    {
        $this->exoneree = $exoneree;

        return $this;
    }

    /**
     * Get the value of cotations
     */ 
    public function getCotations()
    {
        /**
         * On doit filtrer toutes les propositions
         * produites pour le compte de ce client;
         */
        //dd(count($this->getPistes()[0]->getCotations()));
        $tab = new ArrayCollection();
        if($this->getPistes()){
            foreach ($this->getPistes() as $piste) {
                foreach ($piste->getCotations() as $cotation) {
                    $tab->add($cotation);
                    //dd("ici = " . count($tab));
                    //$this->cotations->add($cotation);
                }
            }
        }
        //$this->cotations->add()
        //dd($tab);
        $this->cotations = $tab;
        return $this->cotations;
    }
}
