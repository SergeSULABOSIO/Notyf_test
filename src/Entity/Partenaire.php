<?php

namespace App\Entity;

use App\Entity\Traits\TraitEcouteurEvenements;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\PartenaireRepository;
use Doctrine\Common\Collections\Collection;
use App\Service\RefactoringJS\Evenements\Sujet;
use Doctrine\Common\Collections\ArrayCollection;
use App\Service\RefactoringJS\Commandes\Commande;
use App\Service\RefactoringJS\Evenements\Evenement;
use App\Service\RefactoringJS\Evenements\Observateur;
use Symfony\Component\Validator\Constraints as Assert;
use App\Service\RefactoringJS\Commandes\CommandeExecuteur;
use App\Service\RefactoringJS\Commandes\ComDetecterEvenementAttribut;
use App\Service\RefactoringJS\Commandes\Piste\CommandePisteNotifierEvenement;

#[ORM\Entity(repositoryClass: PartenaireRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Partenaire implements Sujet, CommandeExecuteur
{
    use TraitEcouteurEvenements;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank(message: "Ce champ ne peut pas être vide.")]
    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[Assert\NotBlank(message: "Ce champ ne peut pas être vide.")]
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $part = null;

    #[Assert\NotBlank(message: "Ce champ ne peut pas être vide.")]
    #[ORM\Column(length: 255)]
    private ?string $adresse = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $siteweb = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $rccm = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $idnat = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $numimpot = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Entreprise $entreprise = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne]
    private ?Utilisateur $utilisateur = null;

    #[ORM\OneToMany(mappedBy: 'partenaire', targetEntity: Piste::class)]
    private Collection $pistes;

    private Collection $polices;

    #[ORM\ManyToOne(inversedBy: 'newpartenaire')]
    private ?Piste $piste = null;

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

    public function getPart(): ?string
    {
        return $this->part;
    }

    public function setPart(string $part): self
    {
        $oldValue = $this->getPart();
        $newValue = $part;
        $this->part = $part;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Part", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): self
    {
        $oldValue = $this->getAdresse();
        $newValue = $adresse;
        $this->adresse = $adresse;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Adresse", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $oldValue = $this->getEmail();
        $newValue = $email;
        $this->email = $email;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "E-mail", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

        return $this;
    }

    public function getSiteweb(): ?string
    {
        return $this->siteweb;
    }

    public function setSiteweb(?string $siteweb): self
    {
        $oldValue = $this->getSiteweb();
        $newValue = $siteweb;
        $this->siteweb = $siteweb;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Site Web", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

        return $this;
    }

    public function getRccm(): ?string
    {
        return $this->rccm;
    }

    public function setRccm(?string $rccm): self
    {
        $oldValue = $this->getRccm();
        $newValue = $rccm;
        $this->rccm = $rccm;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Registre de commercer (RCCM)", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

        return $this;
    }

    public function getIdnat(): ?string
    {
        return $this->idnat;
    }

    public function setIdnat(?string $idnat): self
    {
        $oldValue = $this->getIdnat();
        $newValue = $idnat;
        $this->idnat = $idnat;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Identification nationale (IDNAT)", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

        return $this;
    }

    public function getNumimpot(): ?string
    {
        return $this->numimpot;
    }

    public function setNumimpot(?string $numimpot): self
    {
        $oldValue = $this->getNumimpot();
        $newValue = $numimpot;
        $this->numimpot = $numimpot;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Numéro Impôt", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

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
        return $this->nom . " (" . ($this->part * 100) . "% par défaut)";
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
            $oldValue = null;
            $newValue = $piste;
            $this->pistes->add($piste);
            $piste->setPartenaire($this);
            //Ecouteur d'action
            $this->executer(new ComDetecterEvenementAttribut($this, "Piste", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
        }

        return $this;
    }

    public function removePiste(Piste $piste): self
    {
        if ($this->pistes->removeElement($piste)) {
            // set the owning side to null (unless already changed)
            if ($piste->getPartenaire() === $this) {
                $oldValue = $piste;
                $newValue = null;
                $piste->setPartenaire(null);
                //Ecouteur d'action
                $this->executer(new ComDetecterEvenementAttribut($this, "Piste", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
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
        $oldValue = $this->getPiste();
        $newValue = $piste;
        $this->piste = $piste;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Piste", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));

        return $this;
    }

    /**
     * Get the value of polices
     */
    public function getPolices()
    {
        $tab = new ArrayCollection();
        if ($this->getPistes()) {
            foreach ($this->getPistes() as $piste) {
                foreach ($piste->getPolices() as $police) {
                    $tab->add($police);
                }
            }
        }
        $this->polices = $tab;
        return $this->polices;
    }

    public function transfererObservateur(?Observateur $observateur)
    {
        // dd("Cette fonction n'est pas encore définie");
    }
}
