<?php

namespace App\Entity;

use App\Entity\Traits\TraitEcouteurEvenements;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\EntrepriseRepository;
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
use Doctrine\ORM\Query\Expr\Func;

#[ORM\Entity(repositoryClass: EntrepriseRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Entreprise implements Sujet, CommandeExecuteur
{
    use TraitEcouteurEvenements;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank(message: "Veuillez fournir le nom de l'entreprise.")]
    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[Assert\NotBlank(message: "Veuillez fournir l'adresse'.")]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adresse = null;

    #[Assert\NotBlank(message: "Veuillez fournir le numéro de téléphone.")]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $telephone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $rccm = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $idnat = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $numimpot = null;

    #[Assert\NotBlank(message: "Veuillez préciser le domaine d'activité.")]
    #[ORM\Column(nullable: true)]
    private ?int $secteur = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne]
    private ?Utilisateur $utilisateur = null;

    #[ORM\OneToMany(mappedBy: 'entreprise', targetEntity: Utilisateur::class)]
    private Collection $utilisateurs;

    #[ORM\OneToMany(mappedBy: 'entreprise', targetEntity: Paiement::class)]
    private Collection $paiements;

    #[ORM\OneToMany(mappedBy: 'entreprise', targetEntity: CompteBancaire::class)]
    private Collection $compteBancaires;

    #[ORM\OneToMany(mappedBy: 'entreprise', targetEntity: Taxe::class)]
    private Collection $taxes;

    #[ORM\OneToMany(mappedBy: 'entreprise', targetEntity: Monnaie::class)]
    private Collection $monnaies;

    public function __construct()
    {
        $this->utilisateurs = new ArrayCollection();
        $this->paiements = new ArrayCollection();
        $this->compteBancaires = new ArrayCollection();
        $this->taxes = new ArrayCollection();
        $this->monnaies = new ArrayCollection();
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

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): self
    {
        $oldValue = $this->getAdresse();
        $newValue = $adresse;
        $this->adresse = $adresse;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Adresse", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): self
    {
        $oldValue = $this->getTelephone();
        $newValue = $telephone;
        $this->telephone = $telephone;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Numéro de téléphone", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

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
        $this->executer(new ComDetecterEvenementAttribut($this, "Numéro du registre de commerce (RCCM)", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

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
        $this->executer(new ComDetecterEvenementAttribut($this, "Numéro d'identification nationale (IDNAT)", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

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

    public function __toString(): string
    {
        return $this->nom;
    }

    public function getSecteur(): ?int
    {
        return $this->secteur;
    }

    public function setSecteur(?int $secteur): self
    {
        $oldValue = $this->getSecteur();
        $newValue = $secteur;
        $this->secteur = $secteur;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Code du secteur", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

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
     * @return Collection<int, Utilisateur>
     */
    public function getUtilisateurs(): Collection
    {
        return $this->utilisateurs;
    }

    public function addUtilisateur(Utilisateur $utilisateur): self
    {
        if (!$this->utilisateurs->contains($utilisateur)) {
            $oldValue = null;
            $newValue = $utilisateur;
            $this->utilisateurs->add($utilisateur);
            $utilisateur->setEntreprise($this);
            //Ecouteur d'action
            $this->executer(new ComDetecterEvenementAttribut($this, "Utilisateur", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
        }

        return $this;
    }

    public function removeUtilisateur(Utilisateur $utilisateur): self
    {
        if ($this->utilisateurs->removeElement($utilisateur)) {
            // set the owning side to null (unless already changed)
            if ($utilisateur->getEntreprise() === $this) {
                $oldValue = $utilisateur;
                $newValue = null;
                $utilisateur->setEntreprise(null);
                //Ecouteur d'action
                $this->executer(new ComDetecterEvenementAttribut($this, "Utilisateur", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Paiement>
     */
    public function getPaiements(): Collection
    {
        return $this->paiements;
    }

    public function addPaiement(Paiement $paiement): self
    {
        if (!$this->paiements->contains($paiement)) {
            $oldValue = null;
            $newValue = $paiement;
            $this->paiements->add($paiement);
            $paiement->setEntreprise($this);
            //Ecouteur d'action
            $this->executer(new ComDetecterEvenementAttribut($this, "Paiement", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
        }

        return $this;
    }

    public function removePaiement(Paiement $paiement): self
    {
        if ($this->paiements->removeElement($paiement)) {
            // set the owning side to null (unless already changed)
            if ($paiement->getEntreprise() === $this) {
                $oldValue = $paiement;
                $newValue = null;
                $paiement->setEntreprise(null);
                //Ecouteur d'action
                $this->executer(new ComDetecterEvenementAttribut($this, "Paiement", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CompteBancaire>
     */
    public function getCompteBancaires(): Collection
    {
        return $this->compteBancaires;
    }

    public function addCompteBancaire(CompteBancaire $compteBancaire): self
    {
        if (!$this->compteBancaires->contains($compteBancaire)) {
            $oldValue = null;
            $newValue = $compteBancaire;
            $this->compteBancaires->add($compteBancaire);
            $compteBancaire->setEntreprise($this);
            //Ecouteur d'action
            $this->executer(new ComDetecterEvenementAttribut($this, "Compte Bancaire", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
        }

        return $this;
    }

    public function removeCompteBancaire(CompteBancaire $compteBancaire): self
    {
        if ($this->compteBancaires->removeElement($compteBancaire)) {
            // set the owning side to null (unless already changed)
            if ($compteBancaire->getEntreprise() === $this) {
                $oldValue = $compteBancaire;
                $newValue = null;
                $compteBancaire->setEntreprise(null);
                //Ecouteur d'action
                $this->executer(new ComDetecterEvenementAttribut($this, "Compte Bancaire", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Taxe>
     */
    public function getTaxes(): Collection
    {
        return $this->taxes;
    }

    public function addTax(Taxe $tax): self
    {
        if (!$this->taxes->contains($tax)) {
            $oldValue = null;
            $newValue = $tax;
            $this->taxes->add($tax);
            $tax->setEntreprise($this);
            //Ecouteur d'action
            $this->executer(new ComDetecterEvenementAttribut($this, "Taxe", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
        }

        return $this;
    }

    public function removeTax(Taxe $tax): self
    {
        if ($this->taxes->removeElement($tax)) {
            // set the owning side to null (unless already changed)
            if ($tax->getEntreprise() === $this) {
                $oldValue = $tax;
                $newValue = null;
                $tax->setEntreprise(null);
                //Ecouteur d'action
                $this->executer(new ComDetecterEvenementAttribut($this, "Taxe", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Monnaie>
     */
    public function getMonnaies(): Collection
    {
        return $this->monnaies;
    }

    public function addMonnaie(Monnaie $monnaie): self
    {
        if (!$this->monnaies->contains($monnaie)) {
            $oldValue = null;
            $newValue = $monnaie;
            $this->monnaies->add($monnaie);
            $monnaie->setEntreprise($this);
            //Ecouteur d'action
            $this->executer(new ComDetecterEvenementAttribut($this, "Monnaie", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
        }

        return $this;
    }

    public function removeMonnaie(Monnaie $monnaie): self
    {
        if ($this->monnaies->removeElement($monnaie)) {
            // set the owning side to null (unless already changed)
            if ($monnaie->getEntreprise() === $this) {
                $oldValue = $monnaie;
                $newValue = null;
                $monnaie->setEntreprise(null);
                //Ecouteur d'action
                $this->executer(new ComDetecterEvenementAttribut($this, "Monnaie", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
            }
        }

        return $this;
    }

    public function setEntreprise(?Entreprise $entreprise)
    {
        //Rien à signaler car on n'utilisera jamais cette fonction.
    }

    public function getEntreprise(): ?Entreprise
    {
        return $this;
    }

    public function transfererObservateur(?Observateur $observateur)
    {
        // dd("Cette fonction n'est pas encore définie");
    }
}
