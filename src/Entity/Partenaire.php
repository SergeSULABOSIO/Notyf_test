<?php

namespace App\Entity;


use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\PartenaireRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PartenaireRepository::class)]
class Partenaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank(message:"Ce champ ne peut pas être vide.")]
    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[Assert\NotBlank(message:"Ce champ ne peut pas être vide.")]
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $part = null;

    #[Assert\NotBlank(message:"Ce champ ne peut pas être vide.")]
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




    //LES CHAMPS CALCULABLES
    //POLICES
    public $calc_polices_tab = [];
    public $calc_polices_primes_nette = 0;
    public $calc_polices_primes_totale = 0;
    public $calc_polices_fronting = 0;
    //SECTION - REVENU
    public $calc_revenu_reserve = 0;
    public $calc_revenu_partageable = 0;
    public $calc_revenu_ht = 0;
    public $calc_revenu_ttc = 0;
    public $calc_revenu_ttc_encaisse = 0;
    public $calc_revenu_ttc_encaisse_tab_ref_factures = [];
    public $calc_revenu_ttc_solde_restant_du = 0;
    //SECTION - PARTENAIRES
    public $calc_retrocom = 0;
    public $calc_retrocom_payees = 0;
    public $calc_retrocom_payees_tab_factures = [];
    public $calc_retrocom_solde = 0;
    //SECTION - TAXES - COURTIER
    public $calc_taxes_courtier_tab = [];
    public $calc_taxes_courtier = 0;
    public $calc_taxes_courtier_payees = 0;
    public $calc_taxes_courtier_payees_tab_ref_factures = [];
    public $calc_taxes_courtier_solde = 0;
    //SECTION - TAXES - ASSUREUR
    public $calc_taxes_assureurs_tab = [];
    public $calc_taxes_assureurs = 0;
    public $calc_taxes_assureurs_payees = 0;
    public $calc_taxes_assureurs_payees_tab_ref_factures = [];
    public $calc_taxes_assureurs_solde = 0;




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
        $this->nom = $nom;

        return $this;
    }

    public function getPart(): ?string
    {
        return $this->part;
    }

    public function setPart(string $part): self
    {
        $this->part = $part;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): self
    {
        $this->adresse = $adresse;

        return $this;
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

    public function getSiteweb(): ?string
    {
        return $this->siteweb;
    }

    public function setSiteweb(?string $siteweb): self
    {
        $this->siteweb = $siteweb;

        return $this;
    }

    public function getRccm(): ?string
    {
        return $this->rccm;
    }

    public function setRccm(?string $rccm): self
    {
        $this->rccm = $rccm;

        return $this;
    }

    public function getIdnat(): ?string
    {
        return $this->idnat;
    }

    public function setIdnat(?string $idnat): self
    {
        $this->idnat = $idnat;

        return $this;
    }

    public function getNumimpot(): ?string
    {
        return $this->numimpot;
    }

    public function setNumimpot(?string $numimpot): self
    {
        $this->numimpot = $numimpot;

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
        return $this->nom . " (" . $this->part . "%)";
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
}
