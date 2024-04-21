<?php

namespace App\Entity;

use App\Entity\Traits\TraitEcouteurEvenements;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\TaxeRepository;
use App\Service\RefactoringJS\Evenements\Sujet;
use Doctrine\Common\Collections\ArrayCollection;
use App\Service\RefactoringJS\Commandes\Commande;
use App\Service\RefactoringJS\Evenements\Evenement;
use App\Service\RefactoringJS\Evenements\Observateur;
use Symfony\Component\Validator\Constraints as Assert;
use App\Service\RefactoringJS\Commandes\CommandeExecuteur;
use App\Service\RefactoringJS\Commandes\ComDetecterEvenementAttribut;
use App\Service\RefactoringJS\Commandes\Piste\CommandePisteNotifierEvenement;

#[ORM\Entity(repositoryClass: TaxeRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Taxe implements Sujet, CommandeExecuteur
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
    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[Assert\NotBlank(message: "Ce champ ne peut pas être vide.")]
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $tauxIARD = null;

    #[Assert\NotBlank(message: "Ce champ ne peut pas être vide.")]
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $tauxVIE = null;

    #[Assert\NotBlank(message: "Ce champ ne peut pas être vide.")]
    #[ORM\Column(length: 255)]
    private ?string $organisation = null;

    #[ORM\Column]
    private ?bool $payableparcourtier = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne]
    private ?Utilisateur $utilisateur = null;

    #[ORM\ManyToOne(inversedBy: 'taxes')]
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

    public function getOrganisation(): ?string
    {
        return $this->organisation;
    }

    public function setOrganisation(string $organisation): self
    {
        $oldValue = $this->getOrganisation();
        $newValue = $organisation;
        $this->organisation = $organisation;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Organisation", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

        return $this;
    }

    public function __toString()
    {
        $txt = " (" . $this->tauxIARD * 100 . "%@IARD & " . $this->tauxVIE * 100 . "%@VIE)";
        if ($this->tauxIARD == $this->tauxVIE) {
            $txt = " (" . $this->tauxIARD * 100 . "%)";
        }
        return $this->nom . $txt;
    }

    public function isPayableparcourtier(): ?bool
    {
        return $this->payableparcourtier;
    }

    public function setPayableparcourtier(bool $payableparcourtier): self
    {
        $oldValue = $this->isPayableparcourtier();
        $newValue = $payableparcourtier;
        $this->payableparcourtier = $payableparcourtier;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Payable par le courtier? (O/N)", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

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
     * Get the value of tauxIARD
     */
    public function getTauxIARD()
    {
        return $this->tauxIARD;
    }

    /**
     * Set the value of tauxIARD
     *
     * @return  self
     */
    public function setTauxIARD($tauxIARD)
    {
        $oldValue = $this->getTauxIARD();
        $newValue = $tauxIARD;
        $this->tauxIARD = $tauxIARD;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Taux IARD", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

        return $this;
    }

    /**
     * Get the value of tauxVIE
     */
    public function getTauxVIE()
    {
        return $this->tauxVIE;
    }

    /**
     * Set the value of tauxVIE
     *
     * @return  self
     */
    public function setTauxVIE($tauxVIE)
    {
        $oldValue = $this->getTauxVIE();
        $newValue = $tauxVIE;
        $this->tauxVIE = $tauxVIE;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Taux VIE", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

        return $this;
    }

    public function transfererObservateur(?Observateur $observateur)
    {
        // dd("Fonction non encore définie");
    }
}
