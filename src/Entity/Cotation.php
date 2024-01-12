<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\CotationRepository;
use Doctrine\Common\Collections\Collection;
use App\Controller\Admin\MonnaieCrudController;
use App\Controller\Admin\RevenuCrudController;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity(repositoryClass: CotationRepository::class)]
class Cotation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nom = null;

    #[ORM\ManyToOne(inversedBy: 'cotations')] //, cascade: ['remove', 'persist', 'refresh'])]
    private ?Piste $piste = null;

    #[ORM\ManyToOne(inversedBy: 'cotations')]
    private ?Assureur $assureur = null;

    #[ORM\ManyToOne(inversedBy: 'cotations')]
    private ?Police $police = null;

    #[ORM\ManyToOne(inversedBy: 'cotations')]
    private ?Produit $produit = null;

    #[ORM\ManyToOne(inversedBy: 'cotations')]
    private ?Client $client = null;

    #[ORM\ManyToOne(inversedBy: 'cotations')]
    private ?Partenaire $partenaire = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $dateEffet = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $dateExpiration = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $dateOperation = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $dateEmition = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?Utilisateur $gestionnaire = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?Utilisateur $assistant = null;


    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Entreprise $entreprise = null;

    #[ORM\OneToMany(mappedBy: 'cotation', targetEntity: Revenu::class, cascade: ['remove', 'persist', 'refresh'])]
    private Collection $revenus;

    #[ORM\OneToMany(mappedBy: 'cotation', targetEntity: Chargement::class, cascade: ['remove', 'persist', 'refresh'])]
    private Collection $chargements;

    #[ORM\OneToMany(mappedBy: 'cotation', targetEntity: Tranche::class, cascade: ['remove', 'persist', 'refresh'])]
    private Collection $tranches;

    #[ORM\Column]
    private ?int $dureeCouverture = null;

    #[ORM\Column]
    private ?bool $validated = null;
    private ?string $status;

    //Les champs calculables automatiquement sur base des données existantes
    private ?float $primeTotale;
    //parties partageable et non partageable tous confondues
    private ?float $revenuTotalHT;
    private ?float $revenuTotalTTC;
    private ?float $revenuNetTotal;
    private ?float $commissionTotaleTTC;
    private ?float $taxeAssureurTotale;
    private ?float $taxeCourtierTotale;

    //partie partageable
    private ?float $revenuTotalHTPartageable;
    //private ?Partenaire $partenaire;
    private ?float $taxeCourtierTotalePartageable;
    private ?float $revenuNetTotalPartageable;
    #[ORM\Column]
    private ?float $tauxretrocompartenaire = 0;
    private ?float $retroComPartenaire;
    private ?Taxe $taxeCourtier;
    private ?Taxe $taxeAssureur;
    private ?Collection $taxes;
    private ?Monnaie $monnaie_Affichage;

    #[ORM\OneToMany(mappedBy: 'cotation', targetEntity: Police::class, cascade: ['remove', 'persist', 'refresh'])]
    private Collection $polices;

    #[ORM\OneToMany(mappedBy: 'cotation', targetEntity: DocPiece::class, cascade: ['remove', 'persist', 'refresh'])]
    private Collection $documents;


    public function __construct()
    {
        $this->revenus = new ArrayCollection();
        $this->chargements = new ArrayCollection();
        $this->tranches = new ArrayCollection();
        $this->polices = new ArrayCollection();
        $this->documents = new ArrayCollection();
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
        $this->nom = $nom;

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



    public function getChargement($type)
    {
        return (new Calculateur())->setCotation($this)->getChargement(["type" => $type]);
    }

    public function __toString()
    {
        $strValidation = ($this->isValidated() == true ? " (offre validée)" : "");
        return  "" . $this->nom . $strValidation;
    }

    public function getPiste(): ?Piste
    {
        return $this->piste;
    }

    public function setPiste(?Piste $piste): self
    {
        $this->piste = $piste;

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
     * @return Collection<int, Revenu>
     */
    public function getRevenus(): Collection
    {
        return $this->revenus;
    }

    public function addRevenu(Revenu $revenu): self
    {
        if (!$this->revenus->contains($revenu)) {
            $this->revenus->add($revenu);
            $revenu->setCotation($this);
        }

        return $this;
    }

    public function removeRevenu(Revenu $revenu): self
    {
        if ($this->revenus->removeElement($revenu)) {
            // set the owning side to null (unless already changed)
            if ($revenu->getCotation() === $this) {
                $revenu->setCotation(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Chargement>
     */
    public function getChargements(): Collection
    {
        return $this->chargements;
    }

    public function addChargement(Chargement $chargement): self
    {
        if (!$this->chargements->contains($chargement)) {
            $this->chargements->add($chargement);
            $chargement->setCotation($this);
        }

        return $this;
    }

    public function removeChargement(Chargement $chargement): self
    {
        if ($this->chargements->removeElement($chargement)) {
            // set the owning side to null (unless already changed)
            if ($chargement->getCotation() === $this) {
                $chargement->setCotation(null);
            }
        }

        return $this;
    }

    /**
     * Get the value of primeTotale
     */
    public function getPrimeTotale()
    {
        $this->primeTotale = (new Calculateur())->setCotation($this)->getPrimeTotale([]);
        return $this->primeTotale;
    }

    /**
     * Set the value of primeTotale
     *
     * @return  self
     */
    public function setPrimeTotale($primeTotale)
    {
        $this->primeTotale = $primeTotale;

        return $this;
    }

    /**
     * Get the value of revenuTotalHT
     */
    public function getRevenuTotalHT()
    {
        $this->revenuTotalHT = (new Calculateur())
            ->setCotation($this)
            ->getRevenufinaleHTGlobale(true) * 100;
        return $this->revenuTotalHT;
    }

    /**
     * Set the value of revenuTotalHT
     *
     * @return  self
     */
    public function setRevenuTotalHT($revenuTotalHT)
    {
        $this->revenuTotalHT = $revenuTotalHT;

        return $this;
    }

    /**
     * @return Collection<int, Tranche>
     */
    public function getTranches(): Collection
    {
        return $this->tranches;
    }

    public function addTranch(Tranche $tranch): self
    {
        if (!$this->tranches->contains($tranch)) {
            $this->tranches->add($tranch);
            $tranch->setCotation($this);
        }

        return $this;
    }

    public function removeTranch(Tranche $tranch): self
    {
        if ($this->tranches->removeElement($tranch)) {
            // set the owning side to null (unless already changed)
            if ($tranch->getCotation() === $this) {
                $tranch->setCotation(null);
            }
        }

        return $this;
    }

    public function getDureeCouverture(): ?int
    {
        return $this->dureeCouverture;
    }

    public function setDureeCouverture(int $dureeCouverture): self
    {
        $this->dureeCouverture = $dureeCouverture;

        return $this;
    }

    public function isValidated(): ?bool
    {
        return $this->validated;
    }

    public function setValidated(bool $validated): self
    {
        $this->validated = $validated;

        return $this;
    }

    /**
     * Get the value of taxes
     */
    public function getTaxes()
    {
        $this->taxes = (new Calculateur())->setCotation($this)->getTaxes();
        return $this->taxes;
    }

    /**
     * Set the value of taxes
     *
     * @return  self
     */
    public function setTaxes($taxes)
    {
        $this->taxes = $taxes;

        return $this;
    }


    /**
     * Get the value of taxeCourtier
     */
    public function getTaxeCourtier()
    {
        $this->taxeCourtier = (new Calculateur())->setCotation($this)->getTaxeCourtier();
        return $this->taxeCourtier;
    }

    /**
     * Get the value of retroComPartenaire
     */
    public function getRetroComPartenaire()
    {
        $this->retroComPartenaire = (new Calculateur())->setCotation($this)->getRetroComPartenaire([]) * 100;
        return $this->retroComPartenaire;
    }

    /**
     * Get the value of taxeCourtierTotale
     */
    public function getTaxeCourtierTotale()
    {
        $this->taxeCourtierTotale = (new Calculateur())
            ->setCotation($this)
            ->getMontantTaxeGlobal(
                null,
                true
            ) * 100;
        return $this->taxeCourtierTotale;
    }

    public function getTauxretrocompartenaire(): ?float
    {
        return $this->tauxretrocompartenaire;
    }

    public function setTauxretrocompartenaire(?float $tauxretrocompartenaire): self
    {
        $this->tauxretrocompartenaire = $tauxretrocompartenaire;

        return $this;
    }

    /**
     * Get the value of revenuTotalHTPartageable
     */
    public function getRevenuTotalHTPartageable()
    {
        $this->revenuTotalHTPartageable = (new Calculateur())
            ->setCotation($this)
            ->getRevenufinaleHTGlobale(true) * 100;
        return $this->revenuTotalHTPartageable;
    }

    /**
     * Get the value of revenuNetTotal
     */
    public function getRevenuNetTotal()
    {
        $this->revenuNetTotal = (new Calculateur())
            ->setCotation($this)
            ->getRevenuPureGlobale(
                null,
                true,
                true
            ) * 100;
        return $this->revenuNetTotal;
    }

    /**
     * Get the value of taxeCourtierTotalePartageable
     */
    public function getTaxeCourtierTotalePartageable()
    {
        $this->taxeCourtierTotalePartageable = (new Calculateur())
            ->setCotation($this)
            ->getMontantTaxeGlobal(
                null,
                true
            ) * 100;
        return $this->taxeCourtierTotalePartageable;
    }

    /**
     * Get the value of revenuNetTotalPartageable
     */
    public function getRevenuNetTotalPartageable()
    {
        $this->revenuNetTotalPartageable = (new Calculateur())->setCotation($this)->getRevenuPureGlobalePartageable() * 100;
        return $this->revenuNetTotalPartageable;
    }

    /**
     * Get the value of taxeAssureur
     */
    public function getTaxeAssureur()
    {
        $this->taxeAssureur = (new Calculateur())->setCotation($this)->getTaxeAssureur();
        return $this->taxeAssureur;
    }

    /**
     * @return Collection<int, Police>
     */
    public function getPolices(): Collection
    {
        return $this->polices;
    }

    public function addPolice(Police $police): self
    {
        if (!$this->polices->contains($police)) {
            $this->polices->add($police);
            $police->setCotation($this);
        }

        return $this;
    }

    public function removePolice(Police $police): self
    {
        if ($this->polices->removeElement($police)) {
            // set the owning side to null (unless already changed)
            if ($police->getCotation() === $this) {
                $police->setCotation(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, DocPiece>
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function addDocument(DocPiece $document): self
    {
        if (!$this->documents->contains($document)) {
            $this->documents->add($document);
            $document->setCotation($this);
        }

        return $this;
    }

    public function removeDocument(DocPiece $document): self
    {
        if ($this->documents->removeElement($document)) {
            // set the owning side to null (unless already changed)
            if ($document->getCotation() === $this) {
                $document->setCotation(null);
            }
        }
        return $this;
    }

    /**
     * Get the value of monnaie_Affichage
     */
    public function getMonnaie_Affichage()
    {
        $this->monnaie_Affichage = (new Calculateur())->setCotation($this)->getMonnaie();
        return $this->monnaie_Affichage;
    }

    /**
     * Get the value of status
     */
    public function getStatus()
    {
        if ($this->isValidated() == true) {
            $this->status = "(validée)";
        } else {
            $this->status = "";
        }
        return $this->status;
    }

    /**
     * Get the value of police
     */
    public function getPolice()
    {
        return $this->police;
    }

    /**
     * Set the value of police
     *
     * @return  self
     */
    public function setPolice($police)
    {
        $this->police = $police;

        return $this;
    }

    /**
     * Get the value of partenaire
     */
    public function getPartenaire()
    {
        if ($this->getPiste()) {
            $this->partenaire = $this->getPiste()->getPartenaire();
        }
        return $this->partenaire;
    }

    /**
     * Set the value of partenaire
     *
     * @return  self
     */
    public function setPartenaire($partenaire)
    {
        $this->partenaire = $partenaire;

        return $this;
    }

    /**
     * Get the value of client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Set the value of client
     *
     * @return  self
     */
    public function setClient($client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get the value of produit
     */
    public function getProduit()
    {
        return $this->produit;
    }

    /**
     * Set the value of produit
     *
     * @return  self
     */
    public function setProduit($produit)
    {
        $this->produit = $produit;

        return $this;
    }

    /**
     * Get the value of dateEffet
     */
    public function getDateEffet()
    {
        return $this->dateEffet;
    }

    /**
     * Set the value of dateEffet
     *
     * @return  self
     */
    public function setDateEffet($dateEffet)
    {
        $this->dateEffet = $dateEffet;

        return $this;
    }

    /**
     * Get the value of dateExpiration
     */
    public function getDateExpiration()
    {
        return $this->dateExpiration;
    }

    /**
     * Set the value of dateExpiration
     *
     * @return  self
     */
    public function setDateExpiration($dateExpiration)
    {
        $this->dateExpiration = $dateExpiration;

        return $this;
    }

    /**
     * Get the value of dateOperation
     */
    public function getDateOperation()
    {
        return $this->dateOperation;
    }

    /**
     * Set the value of dateOperation
     *
     * @return  self
     */
    public function setDateOperation($dateOperation)
    {
        $this->dateOperation = $dateOperation;

        return $this;
    }


    /**
     * Get the value of dateEmition
     */
    public function getDateEmition()
    {
        return $this->dateEmition;
    }

    /**
     * Set the value of dateEmition
     *
     * @return  self
     */
    public function setDateEmition($dateEmition)
    {
        $this->dateEmition = $dateEmition;

        return $this;
    }

    /**
     * Get the value of commissionTotaleTTC
     */
    public function getCommissionTotaleTTC()
    {
        $this->commissionTotaleTTC = $this->getTaxeAssureurTotale() + $this->getRevenuTotalHT();
        return $this->commissionTotaleTTC;
    }

    /**
     * Get the value of taxeAssureurTotal
     */
    public function getTaxeAssureurTotale()
    {
        $this->taxeAssureurTotale = (new Calculateur())
            ->setCotation($this)
            ->getMontantTaxeGlobal(
                null,
                false
            ) * 100;
        return $this->taxeAssureurTotale;
    }


    /**
     * Get the value of gestionnaire
     */
    public function getGestionnaire()
    {
        return $this->gestionnaire;
    }

    /**
     * Set the value of gestionnaire
     *
     * @return  self
     */
    public function setGestionnaire($gestionnaire)
    {
        $this->gestionnaire = $gestionnaire;

        return $this;
    }

    /**
     * Get the value of assistant
     */
    public function getAssistant()
    {
        return $this->assistant;
    }

    /**
     * Set the value of assistant
     *
     * @return  self
     */
    public function setAssistant($assistant)
    {
        $this->assistant = $assistant;

        return $this;
    }

    /**
     * Get the value of revenuTotalTTC
     */
    public function getRevenuTotalTTC()
    {
        $this->revenuTotalTTC = (new Calculateur())
            ->setCotation($this)
            ->getRevenuTTCGlobal(null, true, null) * 100;
        return $this->revenuTotalTTC;
    }
}
