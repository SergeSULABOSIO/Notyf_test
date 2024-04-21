<?php

namespace App\Entity;

use App\Entity\Traits\TraitEcouteurEvenements;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\EtapeSinistreRepository;
use Doctrine\Common\Collections\Collection;
use App\Service\RefactoringJS\Evenements\Sujet;
use Doctrine\Common\Collections\ArrayCollection;
use App\Service\RefactoringJS\Evenements\Evenement;
use App\Service\RefactoringJS\Evenements\Observateur;
use App\Service\RefactoringJS\Commandes\CommandeExecuteur;
use App\Service\RefactoringJS\Commandes\ComDetecterEvenementAttribut;

#[ORM\Entity(repositoryClass: EtapeSinistreRepository::class)]
#[ORM\HasLifecycleCallbacks]
class EtapeSinistre implements Sujet, CommandeExecuteur
{
    use TraitEcouteurEvenements;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Entreprise $entreprise = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?int $indice = null;

    #[ORM\ManyToOne]
    private ?Utilisateur $utilisateur = null;

    #[ORM\OneToMany(mappedBy: 'etape', targetEntity: Sinistre::class)]
    private Collection $sinistres;


    public function __construct()
    {
        $this->sinistres = new ArrayCollection();
        $this->listeObservateurs = new ArrayCollection();
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
        $oldValue = $this->getNom();
        $newValue = $nom;
        $this->nom = $nom;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Nom", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

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

    public function getEntreprise(): ?Entreprise
    {
        return $this->entreprise;
    }

    public function setEntreprise(?Entreprise $entreprise): self
    {
        $this->entreprise = $entreprise;

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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function __toString()
    {
        return $this->nom;
    }

    public function getIndice(): ?int
    {
        return $this->indice;
    }

    public function setIndice(?int $indice): self
    {
        $oldValue = $this->getIndice();
        $newValue = $indice;
        $this->indice = $indice;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Indice", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

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
     * @return Collection<int, Sinistre>
     */
    public function getSinistres(): Collection
    {
        return $this->sinistres;
    }

    public function addSinistre(Sinistre $sinistre): self
    {
        if (!$this->sinistres->contains($sinistre)) {
            $oldValue = null;
            $newValue = $sinistre;
            $this->sinistres->add($sinistre);
            $sinistre->setEtape($this);
            //Ecouteur d'action
            $this->executer(new ComDetecterEvenementAttribut($this, "Sinistre", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
        }

        return $this;
    }

    public function removeSinistre(Sinistre $sinistre): self
    {
        if ($this->sinistres->removeElement($sinistre)) {
            // set the owning side to null (unless already changed)
            if ($sinistre->getEtape() === $this) {
                $oldValue = $sinistre;
                $newValue = null;
                $sinistre->setEtape(null);
                //Ecouteur d'action
                $this->executer(new ComDetecterEvenementAttribut($this, "Sinistre", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
            }
        }

        return $this;
    }

    public function transfererObservateur(?Observateur $observateur)
    {
        // dd("Cette fonction n'est pas encore définie");
    }
}
