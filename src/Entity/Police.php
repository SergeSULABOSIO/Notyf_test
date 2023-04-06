<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\PoliceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PoliceRepository::class)]
class Police
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank(message:"Veuillez fournir la référence de la police.")]
    #[ORM\Column(length: 255)]
    private ?string $reference = null;
    
    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: false)]
    private ?\DateTimeInterface $dateoperation = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: false)]
    private ?\DateTimeInterface $dateemission = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: false)]
    private ?\DateTimeInterface $dateeffet = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateexpiration = null;

    #[Assert\NotBlank(message:"Veuillez préciser l'ID de cet avenant.")]
    #[ORM\Column]
    private ?int $idavenant = null;

    #[ORM\Column(length: 255)]
    private ?string $typeavenant = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $capital = null;

    #[Assert\NotBlank(message:"Veuillez fournir la prime nette.")]
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $primenette = null;

    #[Assert\NotBlank(message:"Ce champ ne peut pas être vide.")]
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $fronting = null;

    #[Assert\NotBlank(message:"Ce champ ne peut pas être vide.")]
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $arca = null;

    #[Assert\NotBlank(message:"Ce champ ne peut pas être vide.")]
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $tva = null;

    #[Assert\NotBlank(message:"Ce champ ne peut pas être vide.")]
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $fraisadmin = null;

    #[Assert\NotBlank(message:"Ce champ ne peut pas être vide.")]
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $primetotale = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $discount = null;

    #[ORM\Column(length: 255)]
    private ?string $modepaiement = null;

    #[Assert\NotBlank(message:"Ce champ ne peut pas être vide.")]
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $ricom = null;

    #[Assert\NotBlank(message:"Ce champ ne peut pas être vide.")]
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $localcom = null;

    #[Assert\NotBlank(message:"Ce champ ne peut pas être vide.")]
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $frontingcom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $remarques = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Entreprise $entreprise = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Monnaie $monnaie = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Client $client = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Produit $produit = null;

    #[ORM\ManyToOne]
    private ?Partenaire $partenaire = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $reassureurs = null;

    #[ORM\Column]
    private ?bool $cansharericom = null;

    #[ORM\Column]
    private ?bool $cansharelocalcom = null;

    #[ORM\Column]
    private ?bool $cansharefrontingcom = null;

    #[ORM\Column(length: 255)]
    private ?string $ricompayableby = null;

    #[ORM\Column(length: 255)]
    private ?string $localcompayableby = null;

    #[ORM\Column(length: 255)]
    private ?string $frontingcompayableby = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Assureur $assureur = null;


    public function __construct()
    {
        
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateoperation(): ?\DateTimeInterface
    {
        return $this->dateoperation;
    }

    public function setDateoperation(\DateTimeInterface $dateoperation): self
    {
        $this->dateoperation = $dateoperation;

        return $this;
    }

    public function getDateemission(): ?\DateTimeInterface
    {
        return $this->dateemission;
    }

    public function setDateemission(?\DateTimeInterface $dateemission): self
    {
        $this->dateemission = $dateemission;

        return $this;
    }

    public function getDateeffet(): ?\DateTimeInterface
    {
        return $this->dateeffet;
    }

    public function setDateeffet(?\DateTimeInterface $dateeffet): self
    {
        $this->dateeffet = $dateeffet;

        return $this;
    }

    public function getDateexpiration(): ?\DateTimeInterface
    {
        return $this->dateexpiration;
    }

    public function setDateexpiration(\DateTimeInterface $dateexpiration): self
    {
        $this->dateexpiration = $dateexpiration;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    public function getIdavenant(): ?int
    {
        return $this->idavenant;
    }

    public function setIdavenant(int $idavenant): self
    {
        $this->idavenant = $idavenant;

        return $this;
    }

    public function getTypeavenant(): ?string
    {
        return $this->typeavenant;
    }

    public function setTypeavenant(string $typeavenant): self
    {
        $this->typeavenant = $typeavenant;

        return $this;
    }

    public function getCapital(): ?string
    {
        return $this->capital;
    }

    public function setCapital(string $capital): self
    {
        $this->capital = $capital;

        return $this;
    }

    public function getPrimenette(): ?string
    {
        return $this->primenette;
    }

    public function setPrimenette(string $primenette): self
    {
        $this->primenette = $primenette;

        return $this;
    }

    public function getFronting(): ?string
    {
        return $this->fronting;
    }

    public function setFronting(string $fronting): self
    {
        $this->fronting = $fronting;

        return $this;
    }

    public function getArca(): ?string
    {
        return $this->arca;
    }

    public function setArca(string $arca): self
    {
        $this->arca = $arca;

        return $this;
    }

    public function getTva(): ?string
    {
        return $this->tva;
    }

    public function setTva(string $tva): self
    {
        $this->tva = $tva;

        return $this;
    }

    public function getFraisadmin(): ?string
    {
        return $this->fraisadmin;
    }

    public function setFraisadmin(string $fraisadmin): self
    {
        $this->fraisadmin = $fraisadmin;

        return $this;
    }

    public function getPrimetotale(): ?string
    {
        return $this->primetotale;
    }

    public function setPrimetotale(string $primetotale): self
    {
        $this->primetotale = $primetotale;

        return $this;
    }

    public function getDiscount(): ?string
    {
        return $this->discount;
    }

    public function setDiscount(string $discount): self
    {
        $this->discount = $discount;

        return $this;
    }

    public function getModepaiement(): ?string
    {
        return $this->modepaiement;
    }

    public function setModepaiement(string $modepaiement): self
    {
        $this->modepaiement = $modepaiement;

        return $this;
    }

    public function getRicom(): ?string
    {
        return $this->ricom;
    }

    public function setRicom(string $ricom): self
    {
        $this->ricom = $ricom;

        return $this;
    }

    public function getLocalcom(): ?string
    {
        return $this->localcom;
    }

    public function setLocalcom(string $localcom): self
    {
        $this->localcom = $localcom;

        return $this;
    }

    public function getFrontingcom(): ?string
    {
        return $this->frontingcom;
    }

    public function setFrontingcom(string $frontingcom): self
    {
        $this->frontingcom = $frontingcom;

        return $this;
    }

    public function getRemarques(): ?string
    {
        return $this->remarques;
    }

    public function setRemarques(?string $remarques): self
    {
        $this->remarques = $remarques;

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

    public function getMonnaie(): ?Monnaie
    {
        return $this->monnaie;
    }

    public function setMonnaie(?Monnaie $monnaie): self
    {
        $this->monnaie = $monnaie;

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getProduit(): ?Produit
    {
        return $this->produit;
    }

    public function setProduit(?Produit $produit): self
    {
        $this->produit = $produit;

        return $this;
    }

    public function getPartenaire(): ?Partenaire
    {
        return $this->partenaire;
    }

    public function setPartenaire(?Partenaire $partenaire): self
    {
        $this->partenaire = $partenaire;

        return $this;
    }

    public function __toString()
    {
        return "Réf. Police: " . $this->getReference() . " | Prime TTC: " . $this->getPrimetotale() . " " . $this->monnaie->getCode(). " | Client: " . $this->client->getNom();
    }

    public function getReassureurs(): ?string
    {
        return $this->reassureurs;
    }

    public function setReassureurs(?string $reassureurs): self
    {
        $this->reassureurs = $reassureurs;

        return $this;
    }

    public function isCansharericom(): ?bool
    {
        return $this->cansharericom;
    }

    public function setCansharericom(bool $cansharericom): self
    {
        $this->cansharericom = $cansharericom;

        return $this;
    }

    public function isCansharelocalcom(): ?bool
    {
        return $this->cansharelocalcom;
    }

    public function setCansharelocalcom(bool $cansharelocalcom): self
    {
        $this->cansharelocalcom = $cansharelocalcom;

        return $this;
    }

    public function isCansharefrontingcom(): ?bool
    {
        return $this->cansharefrontingcom;
    }

    public function setCansharefrontingcom(bool $cansharefrontingcom): self
    {
        $this->cansharefrontingcom = $cansharefrontingcom;

        return $this;
    }

    public function getRicompayableby(): ?string
    {
        return $this->ricompayableby;
    }

    public function setRicompayableby(string $ricompayableby): self
    {
        $this->ricompayableby = $ricompayableby;

        return $this;
    }

    public function getLocalcompayableby(): ?string
    {
        return $this->localcompayableby;
    }

    public function setLocalcompayableby(string $localcompayableby): self
    {
        $this->localcompayableby = $localcompayableby;

        return $this;
    }

    public function getFrontingcompayableby(): ?string
    {
        return $this->frontingcompayableby;
    }

    public function setFrontingcompayableby(string $frontingcompayableby): self
    {
        $this->frontingcompayableby = $frontingcompayableby;

        return $this;
    }

    public function getAssureur(): ?Assureur
    {
        return $this->assureur;
    }

    public function setAssureur(?Assureur $assureur): self
    {
        $this->assureur = $assureur;

        return $this;
    }
}
