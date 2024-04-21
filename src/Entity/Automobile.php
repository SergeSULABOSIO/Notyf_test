<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\AutomobileRepository;
use Doctrine\Common\Collections\Collection;
use App\Entity\Traits\TraitEcouteurEvenements;
use App\Service\RefactoringJS\Evenements\Sujet;
use Doctrine\Common\Collections\ArrayCollection;
use App\Service\RefactoringJS\Evenements\Observateur;
use Symfony\Component\Validator\Constraints as Assert;
use App\Service\RefactoringJS\Commandes\CommandeExecuteur;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

#[ORM\Entity(repositoryClass: AutomobileRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Automobile implements Sujet, CommandeExecuteur
{
    use TraitEcouteurEvenements;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank(message:"Ce champ ne peut pas être vide.")]
    #[ORM\Column(length: 255)]
    private ?string $model = null;

    #[Assert\NotBlank(message:"Ce champ ne peut pas être vide.")]
    #[ORM\Column(length: 255)]
    private ?string $marque = null;

    #[Assert\NotBlank(message:"Ce champ ne peut pas être vide.")]
    #[ORM\Column(length: 255)]
    private ?string $annee = null;

    #[Assert\NotBlank(message:"Ce champ ne peut pas être vide.")]
    #[ORM\Column(length: 255)]
    private ?string $puissance = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $valeur = null;

    #[Assert\NotBlank(message:"Ce champ ne peut pas être vide.")]
    #[ORM\Column]
    private ?int $nbsieges = null;

    #[ORM\Column(length: 255)]
    private ?string $utilite = null;

    #[ORM\Column]
    private ?int $nature = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Entreprise $entreprise = null;

    #[Assert\NotBlank(message:"Ce champ ne peut pas être vide.")]
    #[ORM\Column(length: 255)]
    private ?string $plaque = null;

    #[Assert\NotBlank(message:"Ce champ ne peut pas être vide.")]
    #[ORM\Column(length: 255)]
    private ?string $chassis = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne]
    private ?Utilisateur $utilisateur = null;

    #[ORM\ManyToOne(inversedBy: 'automobiles')]
    private ?Police $police = null;

    public function __construct()
    {
        $this->listeObservateurs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(string $model): self
    {
        $this->model = $model;

        return $this;
    }

    public function getMarque(): ?string
    {
        return $this->marque;
    }

    public function setMarque(string $marque): self
    {
        $this->marque = $marque;

        return $this;
    }

    public function getAnnee(): ?string
    {
        return $this->annee;
    }

    public function setAnnee(string $annee): self
    {
        $this->annee = $annee;

        return $this;
    }

    public function getPuissance(): ?string
    {
        return $this->puissance;
    }

    public function setPuissance(string $puissance): self
    {
        $this->puissance = $puissance;

        return $this;
    }

    public function getValeur(): ?string
    {
        return $this->valeur;
    }

    public function setValeur(?string $valeur): self
    {
        $this->valeur = $valeur;

        return $this;
    }

    public function getNbsieges(): ?int
    {
        return $this->nbsieges;
    }

    public function setNbsieges(int $nbsieges): self
    {
        $this->nbsieges = $nbsieges;

        return $this;
    }

    public function getUtilite(): ?string
    {
        return $this->utilite;
    }

    public function setUtilite(string $utilite): self
    {
        $this->utilite = $utilite;

        return $this;
    }

    public function getNature(): ?int
    {
        return $this->nature;
    }

    public function setNature(int $nature): self
    {
        $this->nature = $nature;

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

    public function getPlaque(): ?string
    {
        return $this->plaque;
    }

    public function setPlaque(string $plaque): self
    {
        $this->plaque = $plaque;

        return $this;
    }

    public function getChassis(): ?string
    {
        return $this->chassis;
    }

    public function setChassis(string $chassis): self
    {
        $this->chassis = $chassis;

        return $this;
    }

    public function __toString()
    {
        return "Marque: " . $this->marque . " / Plaque: " . $this->plaque . " / Chassis: " . $this->chassis . " / Année: " . $this->annee;
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

    public function getPolice(): ?Police
    {
        return $this->police;
    }

    public function setPolice(?Police $police): self
    {
        $this->police = $police;

        return $this;
    }

    public function transfererObservateur(?Observateur $observateur)
    {
        // dd("Cette fonction n'est pas encore définie");
    }
}
