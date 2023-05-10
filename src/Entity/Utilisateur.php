<?php

namespace App\Entity;

use App\Repository\UtilisateurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[UniqueEntity('email')]
#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\Email()]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    
    private ?string $plainPassword = null;

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    #[Assert\NotBlank]
    private ?string $password = null;

    #[ORM\Column(length: 50)]
    #[Assert\Length(
        min: 3,
        max: 50,
        minMessage: 'Désolé, le texte doit avoir au moins {{ limit }} charactères.',
        maxMessage: 'Désolé, votre texte ne doit pas dépasser {{ limit }} charactères.',
    )]
    private ?string $nom = null;

    #[ORM\Column(length: 20)]
    #[Assert\Length(
        min: 10,
        max: 20,
        minMessage: 'Désolé, le texte doit avoir au moins {{ limit }} charactères.',
        maxMessage: 'Désolé, votre texte ne doit pas dépasser {{ limit }} charactères.',
    )]
    private ?string $pseudo = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\OneToMany(mappedBy: 'utilisateur', targetEntity: FeedbackCRM::class)]
    private Collection $feedbackCRMs;

    #[ORM\ManyToOne(targetEntity: self::class)]
    private ?self $utilisateur = null;

    #[ORM\OneToMany(mappedBy: 'attributedTo', targetEntity: ActionCRM::class)]
    private Collection $actionCRMs;

    #[ORM\ManyToOne(inversedBy: 'utilisateurs')]
    private ?Entreprise $entreprise = null;

    public function __construct()
    {
        $this->feedbackCRMs = new ArrayCollection();
        $this->actionCRMs = new ArrayCollection();
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
        $this->email = $email;

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
        $this->roles = $roles;

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
        $this->plainPassword = $password;
        
        return $this;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

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
        $this->nom = $nom;

        return $this;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): self
    {
        $this->pseudo = $pseudo;

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
        return $this->nom;
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

    /**
     * @return Collection<int, FeedbackCRM>
     */
    public function getFeedbackCRMs(): Collection
    {
        return $this->feedbackCRMs;
    }

    public function addFeedbackCRM(FeedbackCRM $feedbackCRM): self
    {
        if (!$this->feedbackCRMs->contains($feedbackCRM)) {
            $this->feedbackCRMs->add($feedbackCRM);
            $feedbackCRM->setUtilisateur($this);
        }

        return $this;
    }

    public function removeFeedbackCRM(FeedbackCRM $feedbackCRM): self
    {
        if ($this->feedbackCRMs->removeElement($feedbackCRM)) {
            // set the owning side to null (unless already changed)
            if ($feedbackCRM->getUtilisateur() === $this) {
                $feedbackCRM->setUtilisateur(null);
            }
        }

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
            $actionCRM->setAttributedTo($this);
        }

        return $this;
    }

    public function removeActionCRM(ActionCRM $actionCRM): self
    {
        if ($this->actionCRMs->removeElement($actionCRM)) {
            // set the owning side to null (unless already changed)
            if ($actionCRM->getAttributedTo() === $this) {
                $actionCRM->setAttributedTo(null);
            }
        }

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
}
