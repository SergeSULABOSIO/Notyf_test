<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ProduitRepository;
use Doctrine\Common\Collections\Collection;
use App\Controller\Admin\ProduitCrudController;
use App\Entity\Traits\TraitEcouteurEvenements;
use App\Service\RefactoringJS\Evenements\Sujet;
use Doctrine\Common\Collections\ArrayCollection;
use App\Service\RefactoringJS\Commandes\Commande;
use App\Service\RefactoringJS\Evenements\Evenement;
use App\Service\RefactoringJS\Evenements\Observateur;
use Symfony\Component\Validator\Constraints as Assert;
use App\Service\RefactoringJS\Commandes\CommandeExecuteur;
use App\Service\RefactoringJS\Commandes\ComDetecterEvenementAttribut;
use App\Service\RefactoringJS\Commandes\Piste\CommandePisteNotifierEvenement;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Produit implements Sujet, CommandeExecuteur
{
    use TraitEcouteurEvenements;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 4)]
    private ?string $code = null;

    #[Assert\NotBlank(message: "Ce champ ne peut pas être vide.")]
    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[Assert\NotBlank(message: "Ce champ ne peut pas être vide.")]
    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[Assert\NotBlank(message: "Ce champ ne peut pas être vide.")]
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $tauxarca = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Entreprise $entreprise = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne]
    private ?Utilisateur $utilisateur = null;

    #[ORM\OneToMany(mappedBy: 'produit', targetEntity: Piste::class)]
    private Collection $pistes;

    #[ORM\Column]
    private ?bool $abonnement = null;

    #[ORM\Column]
    private ?bool $obligatoire = null;
    private ?string $obligatoireTxt;

    #[ORM\Column]
    private ?bool $iard = null;
    private ?string $iardTxt;

    public function __construct()
    {
        $this->pistes = new ArrayCollection();
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

    public function getTauxarca(): ?string
    {
        return $this->tauxarca;
    }

    public function setTauxarca(string $tauxarca): self
    {
        $oldValue = $this->getTauxarca();
        $newValue = $tauxarca;
        $this->tauxarca = $tauxarca;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Taux", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

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

    public function __toString()
    {
        return  "[" . ($this->tauxarca * 100) . "%] " . " - " . $this->getCode() . " - " . $this->nom;
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

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $oldValue = $this->getCode();
        $newValue = $code;
        $this->code = $code;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Code", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

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
            $oldValue = null;
            $newValue = $piste;
            $this->pistes->add($piste);
            $piste->setProduit($this);
            //Ecouteur d'action
            $this->executer(new ComDetecterEvenementAttribut($this, "Piste", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));
        }

        return $this;
    }

    public function removePiste(Piste $piste): self
    {
        if ($this->pistes->removeElement($piste)) {
            // set the owning side to null (unless already changed)
            if ($piste->getProduit() === $this) {
                $oldValue = $piste;
                $newValue = null;
                $piste->setProduit(null);
                //Ecouteur d'action
                $this->executer(new ComDetecterEvenementAttribut($this, "Piste", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));
            }
        }

        return $this;
    }

    public function isAbonnement(): ?bool
    {
        return $this->abonnement;
    }

    public function setAbonnement(bool $abonnement): self
    {
        $oldValue = $this->isAbonnement();
        $newValue = $abonnement;
        $this->abonnement = $abonnement;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Par abonnement? (O/N)", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

        return $this;
    }

    public function isObligatoire(): ?bool
    {
        return $this->obligatoire;
    }

    public function setObligatoire(bool $obligatoire): self
    {
        $oldValue = $this->isObligatoire();
        $newValue = $obligatoire;
        $this->obligatoire = $obligatoire;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Obligatoire? (O/N)", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

        return $this;
    }

    public function isIard(): ?bool
    {
        return $this->iard;
    }

    public function setIard(bool $iard): self
    {
        $oldValue = $this->isIard();
        $newValue = $iard;
        $this->iard = $iard;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "IARD? (O/N)", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

        return $this;
    }

    /**
     * Get the value of iardTxt
     */
    public function getIardTxt()
    {
        if ($this->isIard() == true) {
            $this->iardTxt = "Branche: IARD";
        } else {
            $this->iardTxt = "Branche: VIE";
        }
        return $this->iardTxt;
    }

    /**
     * Get the value of obligatoireTxt
     */
    public function getObligatoireTxt()
    {
        if ($this->isObligatoire() == true) {
            $this->obligatoireTxt = "Couverture Obligatoire";
        } else {
            $this->obligatoireTxt = "Couverture Non Obligatoire";
        }
        return $this->obligatoireTxt;
    }

    public function transfererObservateur(?Observateur $observateur)
    {
        // dd("Fonction non encore définie");
    }
}
