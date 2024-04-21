<?php

namespace App\Entity;

use App\Entity\Traits\TraitEcouteurEvenements;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\VictimeRepository;
use Doctrine\Common\Collections\Collection;
use App\Service\RefactoringJS\Evenements\Sujet;
use Doctrine\Common\Collections\ArrayCollection;
use App\Service\RefactoringJS\Commandes\Commande;
use App\Service\RefactoringJS\Evenements\Evenement;
use App\Service\RefactoringJS\Evenements\Observateur;
use App\Service\RefactoringJS\Commandes\CommandeExecuteur;
use App\Service\RefactoringJS\Commandes\ComDetecterEvenementAttribut;
use App\Service\RefactoringJS\Commandes\Piste\CommandePisteNotifierEvenement;

#[ORM\Entity(repositoryClass: VictimeRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Victime implements Sujet, CommandeExecuteur
{
    use TraitEcouteurEvenements;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $adresse = null;

    #[ORM\Column(length: 255)]
    private ?string $telephone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Entreprise $entreprise = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne]
    private ?Utilisateur $utilisateur = null;

    #[ORM\ManyToOne(inversedBy: 'victimes')]
    private ?Sinistre $sinistre = null;

    public function __construct()
    {
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

    public function setAdresse(string $adresse): self
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

    public function setTelephone(string $telephone): self
    {
        $oldValue = $this->getTelephone();
        $newValue = $telephone;
        $this->telephone = $telephone;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Numéro de téléphone", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $oldValue = $this->getEmail();
        $newValue = $email;
        $this->email = $email;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Email", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

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

    public function __toString()
    {
        return $this->nom . " / Tél. : " . $this->telephone;
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

    public function getSinistre(): ?Sinistre
    {
        return $this->sinistre;
    }

    public function setSinistre(?Sinistre $sinistre): self
    {
        $oldValue = $this->getSinistre();
        $newValue = $sinistre;
        $this->sinistre = $sinistre;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Sinistre", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));

        return $this;
    }

    public function transfererObservateur(?Observateur $observateur)
    {
        // dd("Cette fonction n'est pas encore définie");
    }
}
