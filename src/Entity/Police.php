<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\PoliceRepository;
use App\Service\ServiceCalculateur;
use App\Service\ServiceMonnaie;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PoliceRepository::class)]
class Police extends CalculableEntity
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

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne]
    private ?Utilisateur $utilisateur = null;

    #[ORM\ManyToOne]
    private ?Utilisateur $gestionnaire = null;

    #[ORM\Column(nullable: true)]
    private ?float $partExceptionnellePartenaire = null;

    #[ORM\OneToMany(mappedBy: 'police', targetEntity: DocPiece::class)]
    private Collection $docPieces;

    #[ORM\ManyToOne(inversedBy: 'police')]
    private ?Client $client = null;

    #[ORM\OneToOne(inversedBy: 'police', cascade: ['persist', 'remove'])]
    private ?Cotation $cotation = null;

    #[ORM\ManyToOne(inversedBy: 'police')]
    private ?Produit $produit = null;

    #[ORM\ManyToOne(inversedBy: 'police')]
    private ?Partenaire $partenaire = null;

    #[ORM\ManyToOne(inversedBy: 'police')]
    private ?Assureur $assureur = null;

    #[ORM\OneToMany(mappedBy: 'police', targetEntity: PaiementCommission::class)]
    private Collection $paiementCommissions;

    #[ORM\OneToMany(mappedBy: 'police', targetEntity: PaiementPartenaire::class)]
    private Collection $paiementPartenaires;

    #[ORM\OneToMany(mappedBy: 'police', targetEntity: PaiementTaxe::class)]
    private Collection $paiementTaxes;

    #[ORM\OneToMany(mappedBy: 'police', targetEntity: Sinistre::class)]
    private Collection $sinistres;

    #[ORM\OneToMany(mappedBy: 'police', targetEntity: ActionCRM::class)]
    private Collection $actionCRMs;
    
    #[ORM\OneToMany(mappedBy: 'police', targetEntity: Automobile::class)]
    private Collection $automobiles;

    #[ORM\Column(nullable: true)]
    private ?float $unpaidcommission = null;

    #[ORM\Column(nullable: true)]
    private ?float $unpaidretrocommission = null;

    #[ORM\Column(nullable: true)]
    private ?float $unpaidtaxecourtier = null;

    #[ORM\Column(nullable: true)]
    private ?float $unpaidtaxeassureur = null;

    #[ORM\Column(nullable: true)]
    private ?float $paidcommission = null;

    #[ORM\Column(nullable: true)]
    private ?float $paidretrocommission = null;

    #[ORM\Column(nullable: true)]
    private ?float $paidtaxecourtier = null;

    #[ORM\Column(nullable: true)]
    private ?float $paidtaxeassureur = null;

    #[ORM\Column(nullable: true)]
    private ?float $unpaidtaxe = null;

    #[ORM\Column(nullable: true)]
    private ?float $paidtaxe = null;

    #[ORM\OneToMany(mappedBy: 'police', targetEntity: Piste::class)]
    private Collection $pistes;

    #[ORM\OneToMany(mappedBy: 'police', targetEntity: ElementFacture::class, cascade:['remove', 'persist', 'refresh'])]
    private Collection $elementFactures;

    #[ORM\ManyToMany(targetEntity: Facture::class, inversedBy: 'police', cascade:['remove', 'persist', 'refresh'])]
    private Collection $factures;

    public function __construct()
    {
        $this->docPieces = new ArrayCollection();
        $this->paiementCommissions = new ArrayCollection();
        $this->paiementPartenaires = new ArrayCollection();
        $this->paiementTaxes = new ArrayCollection();
        $this->sinistres = new ArrayCollection();
        $this->actionCRMs = new ArrayCollection();
        $this->automobiles = new ArrayCollection();
        $this->pistes = new ArrayCollection();
        $this->elementFactures = new ArrayCollection();
        $this->factures = new ArrayCollection();
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

    public function __toString()
    {
        return "Réf.: " . $this->getReference() . " / Avenant: " . $this->getIdavenant();// . " / " . $this->getPrimetotale()/100 . " / " . $this->client->getNom() . " / " . $this->getAssureur()->getNom() . " / " . $this->getProduit()->getNom();
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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

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


    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): self
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    public function getGestionnaire(): ?Utilisateur
    {
        return $this->gestionnaire;
    }

    public function setGestionnaire(?Utilisateur $gestionnaire): self
    {
        $this->gestionnaire = $gestionnaire;

        return $this;
    }

    public function getPartExceptionnellePartenaire(): ?float
    {
        return $this->partExceptionnellePartenaire;
    }

    public function setPartExceptionnellePartenaire(?float $partExceptionnellePartenaire): self
    {
        $this->partExceptionnellePartenaire = $partExceptionnellePartenaire;

        return $this;
    }

    /**
     * @return Collection<int, DocPiece>
     */
    public function getDocPieces(): Collection
    {
        return $this->docPieces;
    }

    public function addDocPiece(DocPiece $docPiece): self
    {
        if (!$this->docPieces->contains($docPiece)) {
            $this->docPieces->add($docPiece);
            $docPiece->setPolice($this);
        }

        return $this;
    }

    public function removeDocPiece(DocPiece $docPiece): self
    {
        if ($this->docPieces->removeElement($docPiece)) {
            // set the owning side to null (unless already changed)
            if ($docPiece->getPolice() === $this) {
                $docPiece->setPolice(null);
            }
        }

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

    public function getCotation(): ?Cotation
    {
        return $this->cotation;
    }

    public function setCotation(?Cotation $cotation): self
    {
        $this->cotation = $cotation;

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

    public function getAssureur(): ?Assureur
    {
        return $this->assureur;
    }

    public function setAssureur(?Assureur $assureur): self
    {
        $this->assureur = $assureur;

        return $this;
    }

    /**
     * @return Collection<int, PaiementCommission>
     */
    public function getPaiementCommissions(): Collection
    {
        return $this->paiementCommissions;
    }

    public function addPaiementCommission(PaiementCommission $paiementCommission): self
    {
        if (!$this->paiementCommissions->contains($paiementCommission)) {
            $this->paiementCommissions->add($paiementCommission);
            $paiementCommission->setPolice($this);
        }

        return $this;
    }

    public function removePaiementCommission(PaiementCommission $paiementCommission): self
    {
        if ($this->paiementCommissions->removeElement($paiementCommission)) {
            // set the owning side to null (unless already changed)
            if ($paiementCommission->getPolice() === $this) {
                $paiementCommission->setPolice(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PaiementPartenaire>
     */
    public function getPaiementPartenaires(): Collection
    {
        return $this->paiementPartenaires;
    }

    public function addPaiementPartenaire(PaiementPartenaire $paiementPartenaire): self
    {
        if (!$this->paiementPartenaires->contains($paiementPartenaire)) {
            $this->paiementPartenaires->add($paiementPartenaire);
            $paiementPartenaire->setPolice($this);
        }

        return $this;
    }

    public function removePaiementPartenaire(PaiementPartenaire $paiementPartenaire): self
    {
        if ($this->paiementPartenaires->removeElement($paiementPartenaire)) {
            // set the owning side to null (unless already changed)
            if ($paiementPartenaire->getPolice() === $this) {
                $paiementPartenaire->setPolice(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PaiementTaxe>
     */
    public function getPaiementTaxes(): Collection
    {
        return $this->paiementTaxes;
    }

    public function addPaiementTax(PaiementTaxe $paiementTax): self
    {
        if (!$this->paiementTaxes->contains($paiementTax)) {
            $this->paiementTaxes->add($paiementTax);
            $paiementTax->setPolice($this);
        }

        return $this;
    }

    public function removePaiementTax(PaiementTaxe $paiementTax): self
    {
        if ($this->paiementTaxes->removeElement($paiementTax)) {
            // set the owning side to null (unless already changed)
            if ($paiementTax->getPolice() === $this) {
                $paiementTax->setPolice(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Sinistre>
     */
    public function getSinistres(): Collection
    {
        return $this->sinistres;
    }

    public function addSinistre(Sinistre $sinistre): self
    {
        if (!$this->sinistres->contains($sinistre)) {
            $this->sinistres->add($sinistre);
            $sinistre->setPolice($this);
        }

        return $this;
    }

    public function removeSinistre(Sinistre $sinistre): self
    {
        if ($this->sinistres->removeElement($sinistre)) {
            // set the owning side to null (unless already changed)
            if ($sinistre->getPolice() === $this) {
                $sinistre->setPolice(null);
            }
        }

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
            $actionCRM->setPolice($this);
        }

        return $this;
    }

    public function removeActionCRM(ActionCRM $actionCRM): self
    {
        if ($this->actionCRMs->removeElement($actionCRM)) {
            // set the owning side to null (unless already changed)
            if ($actionCRM->getPolice() === $this) {
                $actionCRM->setPolice(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Automobile>
     */
    public function getAutomobiles(): Collection
    {
        return $this->automobiles;
    }

    public function addAutomobile(Automobile $automobile): self
    {
        if (!$this->automobiles->contains($automobile)) {
            $this->automobiles->add($automobile);
            $automobile->setPolice($this);
        }

        return $this;
    }

    public function removeAutomobile(Automobile $automobile): self
    {
        if ($this->automobiles->removeElement($automobile)) {
            // set the owning side to null (unless already changed)
            if ($automobile->getPolice() === $this) {
                $automobile->setPolice(null);
            }
        }

        return $this;
    }

    public function getUnpaidcommission(): ?float
    {
        return $this->unpaidcommission;
    }

    public function setUnpaidcommission(?float $unpaidcommission): self
    {
        $this->unpaidcommission = $unpaidcommission;

        return $this;
    }

    public function getUnpaidretrocommission(): ?float
    {
        return $this->unpaidretrocommission;
    }

    public function setUnpaidretrocommission(?float $unpaidretrocommission): self
    {
        $this->unpaidretrocommission = $unpaidretrocommission;

        return $this;
    }

    public function getUnpaidtaxecourtier(): ?float
    {
        return $this->unpaidtaxecourtier;
    }

    public function setUnpaidtaxecourtier(?float $unpaidtaxecourtier): self
    {
        $this->unpaidtaxecourtier = $unpaidtaxecourtier;

        return $this;
    }

    public function getUnpaidtaxeassureur(): ?float
    {
        return $this->unpaidtaxeassureur;
    }

    public function setUnpaidtaxeassureur(?float $unpaidtaxeassureur): self
    {
        $this->unpaidtaxeassureur = $unpaidtaxeassureur;

        return $this;
    }

    public function getPaidcommission(): ?float
    {
        return $this->paidcommission;
    }

    public function setPaidcommission(?float $paidcommission): self
    {
        $this->paidcommission = $paidcommission;

        return $this;
    }

    public function getPaidretrocommission(): ?float
    {
        return $this->paidretrocommission;
    }

    public function setPaidretrocommission(?float $paidretrocommission): self
    {
        $this->paidretrocommission = $paidretrocommission;

        return $this;
    }

    public function getPaidtaxecourtier(): ?float
    {
        return $this->paidtaxecourtier;
    }

    public function setPaidtaxecourtier(?float $paidtaxecourtier): self
    {
        $this->paidtaxecourtier = $paidtaxecourtier;

        return $this;
    }

    public function getPaidtaxeassureur(): ?float
    {
        return $this->paidtaxeassureur;
    }

    public function setPaidtaxeassureur(?float $paidtaxeassureur): self
    {
        $this->paidtaxeassureur = $paidtaxeassureur;

        return $this;
    }

    public function getUnpaidtaxe(): ?float
    {
        return $this->unpaidtaxe;
    }

    public function setUnpaidtaxe(?float $unpaidtaxe): self
    {
        $this->unpaidtaxe = $unpaidtaxe;

        return $this;
    }

    public function getPaidtaxe(): ?float
    {
        return $this->paidtaxe;
    }

    public function setPaidtaxe(?float $paidtaxe): self
    {
        $this->paidtaxe = $paidtaxe;

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
            $this->pistes->add($piste);
            $piste->setPolice($this);
        }

        return $this;
    }

    public function removePiste(Piste $piste): self
    {
        if ($this->pistes->removeElement($piste)) {
            // set the owning side to null (unless already changed)
            if ($piste->getPolice() === $this) {
                $piste->setPolice(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ElementFacture>
     */
    public function getElementFactures(): Collection
    {
        return $this->elementFactures;
    }

    public function addElementFacture(ElementFacture $elementFacture): self
    {
        if (!$this->elementFactures->contains($elementFacture)) {
            $this->elementFactures->add($elementFacture);
            $elementFacture->setPolice($this);
        }

        return $this;
    }

    public function removeElementFacture(ElementFacture $elementFacture): self
    {
        if ($this->elementFactures->removeElement($elementFacture)) {
            // set the owning side to null (unless already changed)
            if ($elementFacture->getPolice() === $this) {
                $elementFacture->setPolice(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Facture>
     */
    public function getFactures(): Collection
    {
        return $this->factures;
    }

    public function addFacture(Facture $facture): self
    {
        if (!$this->factures->contains($facture)) {
            $this->factures->add($facture);
        }

        return $this;
    }

    public function removeFacture(Facture $facture): self
    {
        $this->factures->removeElement($facture);

        return $this;
    }
}
