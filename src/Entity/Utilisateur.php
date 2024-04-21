<?php

namespace App\Entity;

use App\Entity\ActionCRM;
use App\Entity\Traits\TraitEcouteurEvenements;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UtilisateurRepository;
use Doctrine\Common\Collections\Collection;
use App\Service\RefactoringJS\Evenements\Sujet;
use Doctrine\Common\Collections\ArrayCollection;
use App\Service\RefactoringJS\Commandes\Commande;
use App\Service\RefactoringJS\Evenements\Evenement;
use App\Service\RefactoringJS\Evenements\Observateur;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Service\RefactoringJS\Commandes\CommandeExecuteur;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use App\Service\RefactoringJS\Commandes\ComDetecterEvenementAttribut;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use App\Service\RefactoringJS\Commandes\Piste\CommandePisteNotifierEvenement;

#[UniqueEntity('email')]
#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface, Sujet, CommandeExecuteur
{
    use TraitEcouteurEvenements;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    private ?string $plainPassword = null;

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 50)]
    private ?string $nom = null;

    #[ORM\Column(length: 20)]
    private ?string $pseudo = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(targetEntity: self::class)]
    private ?self $utilisateur = null;

    #[ORM\ManyToOne(inversedBy: 'utilisateurs')]
    private ?Entreprise $entreprise = null;

    #[ORM\OneToMany(mappedBy: 'attributedTo', targetEntity: ActionCRM::class)]
    private Collection $actionCRMs;

    #[ORM\OneToMany(mappedBy: 'utilisateur', targetEntity: Paiement::class)]
    private Collection $paiements;

    #[ORM\OneToMany(mappedBy: 'utilisateur', targetEntity: CompteBancaire::class)]
    private Collection $compteBancaires;

    #[ORM\OneToMany(mappedBy: 'gestionnaire', targetEntity: Piste::class)]
    private Collection $pistes;

    public function __construct()
    {
        //$this->actionCRMs = new ArrayCollection();
        $this->paiements = new ArrayCollection();
        $this->compteBancaires = new ArrayCollection();
        $this->pistes = new ArrayCollection();
        $this->listeObservateurs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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
        $this->executer(new ComDetecterEvenementAttribut($this, "Adresse mail", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = '';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $oldValue = $this->getRoles();
        $newValue = $roles;
        $this->roles = $roles;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Rôle", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function getPlainPassword(): string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $password): self
    {
        $oldValue = $this->getPassword();
        $newValue = $password;
        $this->plainPassword = $password;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Mot de passe", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

        return $this;
    }

    public function setPassword(string $password): self
    {
        $oldValue = $this->getPassword();
        $newValue = $password;
        $this->password = $password;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Mot de passe", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): self
    {
        $oldValue = $this->getPseudo();
        $newValue = $pseudo;
        $this->pseudo = $pseudo;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Pseudo code", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

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
        return "" . $this->nom;
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

    public function getUtilisateur(): ?self
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?self $utilisateur): self
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
            $actionCRM->setAttributedTo($this);
            //Ecouteur d'action
            $this->executer(new ComDetecterEvenementAttribut($this, "Tâche", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
        }

        return $this;
    }

    public function removeActionCRM(ActionCRM $actionCRM): self
    {
        if ($this->actionCRMs->removeElement($actionCRM)) {
            // set the owning side to null (unless already changed)
            if ($actionCRM->getAttributedTo() === $this) {
                $oldValue = $actionCRM;
                $newValue = null;
                $actionCRM->setAttributedTo(null);
                //Ecouteur d'action
                $this->executer(new ComDetecterEvenementAttribut($this, "Tâche", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
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
            $paiement->setUtilisateur($this);
            //Ecouteur d'action
            $this->executer(new ComDetecterEvenementAttribut($this, "Paiement", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
        }

        return $this;
    }

    public function removePaiement(Paiement $paiement): self
    {
        if ($this->paiements->removeElement($paiement)) {
            // set the owning side to null (unless already changed)
            if ($paiement->getUtilisateur() === $this) {
                $oldValue = $paiement;
                $newValue = null;
                $paiement->setUtilisateur(null);
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
            $compteBancaire->setUtilisateur($this);
            //Ecouteur d'action
            $this->executer(new ComDetecterEvenementAttribut($this, "Compte bancaire", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
        }

        return $this;
    }

    public function removeCompteBancaire(CompteBancaire $compteBancaire): self
    {
        if ($this->compteBancaires->removeElement($compteBancaire)) {
            // set the owning side to null (unless already changed)
            if ($compteBancaire->getUtilisateur() === $this) {
                $oldValue = $compteBancaire;
                $newValue = null;
                $compteBancaire->setUtilisateur(null);
                //Ecouteur d'action
                $this->executer(new ComDetecterEvenementAttribut($this, "Compte bancaire", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
            }
        }

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
            $piste->setGestionnaire($this);
            //Ecouteur d'action
            $this->executer(new ComDetecterEvenementAttribut($this, "Piste", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
        }

        return $this;
    }

    public function removePiste(Piste $piste): self
    {
        if ($this->pistes->removeElement($piste)) {
            // set the owning side to null (unless already changed)
            if ($piste->getGestionnaire() === $this) {
                $oldValue = $piste;
                $newValue = null;
                $piste->setGestionnaire(null);
                //Ecouteur d'action
                $this->executer(new ComDetecterEvenementAttribut($this, "Piste", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
            }
        }

        return $this;
    }

    public function transfererObservateur(?Observateur $observateur)
    {
        // dd("Cette fonction n'est pas encore définie");
    }
}
