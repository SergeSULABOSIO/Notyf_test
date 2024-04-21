<?php

namespace App\Entity;

use App\Entity\Traits\TraitEcouteurEvenements;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\MonnaieRepository;
use App\Service\RefactoringJS\Evenements\Sujet;
use Doctrine\Common\Collections\ArrayCollection;
use App\Service\RefactoringJS\Commandes\Commande;
use App\Service\RefactoringJS\Evenements\Evenement;
use App\Service\RefactoringJS\Evenements\Observateur;
use Symfony\Component\Validator\Constraints as Assert;
use App\Service\RefactoringJS\Commandes\CommandeExecuteur;
use App\Service\RefactoringJS\Commandes\ComDetecterEvenementAttribut;
use App\Service\RefactoringJS\Commandes\Piste\CommandePisteNotifierEvenement;

#[ORM\Entity(repositoryClass: MonnaieRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Monnaie implements Sujet, CommandeExecuteur
{
    use TraitEcouteurEvenements;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[Assert\NotBlank(message: "Le code ne peut pas être vide.")]
    #[ORM\Column(length: 255)]
    private ?string $code = null;

    #[Assert\NotBlank(message: "Le taux ne peut pas être vide.")]
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $tauxusd = null;

    #[ORM\Column]
    private ?bool $islocale = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne]
    private ?Utilisateur $utilisateur = null;

    #[ORM\Column]
    private ?int $fonction = null;

    #[ORM\ManyToOne(inversedBy: 'monnaies')]
    private ?Entreprise $entreprise = null;

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

    public function getTauxusd(): ?string
    {
        return $this->tauxusd;
    }

    public function setTauxusd(string $tauxusd): self
    {
        $oldValue = $this->getTauxusd();
        $newValue = $tauxusd;
        $this->tauxusd = $tauxusd;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Taux en USD", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

        return $this;
    }

    public function isIslocale(): ?bool
    {
        return $this->islocale;
    }

    public function setIslocale(bool $islocale): self
    {
        $oldValue = $this->isIslocale();
        $newValue = $islocale;
        $this->islocale = $islocale;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Monaie locale? (O/N)", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

        return $this;
    }

    public function __toString()
    {
        return $this->code . " / " . $this->nom;
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

    public function getFonction(): ?int
    {
        return $this->fonction;
    }

    public function setFonction(int $fonction): self
    {
        $oldValue = $this->getFonction();
        $newValue = $fonction;
        $this->fonction = $fonction;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Fonction", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

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

    public function transfererObservateur(?Observateur $observateur)
    {
        // dd("Cette fonction n'est pas encore définie");
    }
}
