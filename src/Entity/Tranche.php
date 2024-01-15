<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\TrancheRepository;
use App\Controller\Admin\MonnaieCrudController;
use App\Controller\Admin\RevenuCrudController;
use DateInterval;

#[ORM\Entity(repositoryClass: TrancheRepository::class)]
class Tranche
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    #[ORM\Column]
    private ?float $taux = null;
    #[ORM\ManyToOne]
    private ?Utilisateur $utilisateur = null;
    #[ORM\ManyToOne]
    private ?Entreprise $entreprise = null;
    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;
    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;
    #[ORM\Column(length: 255)]
    private ?string $nom = null;
    #[ORM\ManyToOne(inversedBy: 'tranches', cascade: ['remove', 'persist', 'refresh'])]
    private ?Cotation $cotation = null;
    #[ORM\Column]
    private ?int $duree = null;


    /**
     * Les attributs non mappées
     */
    private ?string $description;
    private ?string $codeMonnaieAffichage;
    //valeurs monnétaires caculables
    private ?float $primeTotaleTranche = 0;
    //les type de revenu
    private ?float $comReassurance = 0;
    private ?float $comLocale = 0;
    private ?float $comFronting = 0;
    private ?float $comFraisGestion = 0;
    private ?float $comAutreChargement = 0;
    private ?float $revenuTotal = 0;

    private ?float $retroCommissionTotale = 0;
    private ?float $taxeCourtierTotale = 0;
    private ?float $taxeAssureurTotale = 0;
    private ?float $reserve = 0;
    //Autres champs
    private ?string $periodeValidite;
    private ?string $autoriteTaxeCourtier;
    private ?string $autoriteTaxeAssureur;
    private ?bool $validated;
    //Les objets
    private ?Monnaie $monnaie_Affichage;
    private ?Client $client;
    private ?Police $police = null;
    private ?Assureur $assureur;
    private ?Produit $produit;
    private ?Partenaire $partenaire;
    private ?Piste $piste;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $startedAt = null;
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $endedAt = null;
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $dateEffet = null;
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $dateExpiration = null;
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $dateOperation = null;
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $dateEmition = null;

    #[ORM\OneToMany(mappedBy: 'tranche', targetEntity: ElementFacture::class)]
    private Collection $elementFactures;

    public function __construct()
    {
        $this->elementFactures = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTaux(): ?float
    {
        return $this->taux;
    }

    public function setTaux(float $taux): self
    {
        $this->taux = $taux;

        return $this;
    }

    /**
     * Get the value of police
     */
    public function getPolice()
    {
        $this->police = (new Calculateur())->setCotation($this->getCotation())->getPolice();
        return $this->police;
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

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

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

    public function __toString()
    {
        $texte = $this->generateDescription();
        return $texte;
    }

    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(int $duree): self
    {
        $this->duree = $duree;

        return $this;
    }


    /**
     * Get the value of description
     */
    public function getDescription()
    {
        $this->description = $this->generateDescription();
        return $this->description;
    }

    private function generateDescription()
    {
        $strMonnaie = $this->getCodeMonnaieAffichage();
        $strPeriode = " pour durée de " . $this->getDuree() . " mois. ";
        //dd($this->getStartedAt());
        if ($this->getStartedAt() != null & $this->getEndedAt() != null) {
            $strPeriode = ". Cette tranche est valide du " . (($this->startedAt)->format('d-m-Y')) . " au " . (($this->endedAt)->format('d-m-Y')) . ".";
        }
        $strMont = " " . number_format($this->getPrimeTotaleTranche() / 100, 2, ",", ".") . $strMonnaie . " soit " . ($this->getTaux() * 100) . "% de " . number_format(($this->getCotation()->getPrimeTotale() / 100), 2, ",", ".") . $strMonnaie . $strPeriode;
        return $this->getNom() . ": " . $strMont;
    }

    /**
     * Get the value of codeMonnaieAffichage
     */
    public function getCodeMonnaieAffichage()
    {
        $this->codeMonnaieAffichage = (new Calculateur())
            ->setCotation($this->getCotation())
            ->getCodeMonnaie();
        return $this->codeMonnaieAffichage;
    }

    /**
     * Get the value of monnaie_Affichage
     */
    public function getMonnaie_Affichage()
    {
        $this->monnaie_Affichage = (new Calculateur())
            ->setCotation($this->getCotation())
            ->getMonnaie();
        return $this->monnaie_Affichage;
    }

    /**
     * Get the value of retroCommissionTotale
     */
    public function getRetroCommissionTotale()
    {
        $this->retroCommissionTotale = (new Calculateur())
            ->getRetroCommissionTotale(null, null, $this, null, null) * 100;
        return $this->retroCommissionTotale;
    }

    /**
     * Get the value of taxeCourtierTotale
     */
    public function getTaxeCourtierTotale()
    {
        $this->taxeCourtierTotale = (new Calculateur())
            ->getTaxePourCourtier(null, null, $this, null, null) * 100;
        return $this->taxeCourtierTotale;
    }

    /**
     * Get the value of taxeAssureurTotale
     */
    public function getTaxeAssureurTotale()
    {
        $this->taxeAssureurTotale = (new Calculateur())
            ->getTaxePourAssureur(null, null, $this, null, null) * 100;
        return $this->taxeAssureurTotale;
    }

    /**
     * Get the value of periodeValidite
     */
    public function getPeriodeValidite()
    {
        $this->periodeValidite = "Inconnue";
        if ($this->getStartedAt() != null && $this->getEndedAt() != null) {
            $this->periodeValidite = "Du " . date_format($this->getStartedAt(), "d/m/Y") . " au " . date_format($this->getEndedAt(), "d/m/Y");
        }
        return $this->periodeValidite;
    }

    /**
     * Get the value of client
     */
    public function getClient()
    {
        $this->client = (new Calculateur())
            ->setCotation($this->getCotation())
            ->getClient();
        return $this->client;
    }

    /**
     * Get the value of assureur
     */
    public function getAssureur()
    {
        $this->assureur = (new Calculateur())
            ->setCotation($this->getCotation())
            ->getAssureur();
        return $this->assureur;
    }

    /**
     * Get the value of produit
     */
    public function getProduit()
    {
        $this->produit = (new Calculateur())
            ->setCotation($this->getCotation())
            ->getProduit();
        return $this->produit;
    }

    /**
     * Get the value of partenaire
     */
    public function getPartenaire()
    {
        $this->partenaire = (new Calculateur())
            ->setCotation($this->getCotation())
            ->getPartenaire();
        return $this->partenaire;
    }

    /**
     * Get the value of autoriteTaxeCourtier
     */
    public function getAutoriteTaxeCourtier()
    {
        $this->autoriteTaxeCourtier = (new Calculateur())
            ->setCotation($this->getCotation())
            ->getTaxeCourtier()
            ->getOrganisation();
        return $this->autoriteTaxeCourtier;
    }


    /**
     * Get the value of autoriteTaxeAssureur
     */
    public function getAutoriteTaxeAssureur()
    {
        $this->autoriteTaxeAssureur = (new Calculateur())
            ->setCotation($this->getCotation())
            ->getTaxeAssureur()
            ->getOrganisation();
        return $this->autoriteTaxeAssureur;
    }

    /**
     * Get the value of validated
     */
    public function getValidated()
    {
        if ($this->getCotation() != null) {
            $this->validated = $this->getCotation()
                ->isValidated();
        }
        return $this->validated;
    }

    /**
     * Get the value of startedAt
     */
    public function getStartedAt()
    {
        return $this->startedAt;
    }

    /**
     * Set the value of startedAt
     *
     * @return  self
     */
    public function setStartedAt($startedAt)
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    /**
     * Get the value of endedAt
     */
    public function getEndedAt()
    {
        return $this->endedAt;
    }

    /**
     * Set the value of endedAt
     *
     * @return  self
     */
    public function setEndedAt($endedAt)
    {
        $this->endedAt = $endedAt;

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
     * Get the value of piste
     */
    public function getPiste()
    {
        $this->piste = (new Calculateur())
            ->setCotation($this->getCotation())
            ->getPiste();
        return $this->piste;
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
            $elementFacture->setTranche($this);
        }

        return $this;
    }

    public function removeElementFacture(ElementFacture $elementFacture): self
    {
        if ($this->elementFactures->removeElement($elementFacture)) {
            // set the owning side to null (unless already changed)
            if ($elementFacture->getTranche() === $this) {
                $elementFacture->setTranche(null);
            }
        }
        return $this;
    }

    /**
     * Get the value of reserve
     */
    public function getReserve()
    {
        $this->reserve = (new Calculateur())
            ->getReserve(
                null,
                null,
                $this,
                null,
                null,
                Calculateur::Param_from_tranche
            ) * 100;
        return $this->reserve;
    }

    /**
     * Get the value of primeTotaleTranche
     */
    public function getPrimeTotaleTranche()
    {
        $this->primeTotaleTranche = (new Calculateur())
            ->getPrimeTotale(null, $this);
        // dd($this->primeTotaleTranche);
        return $this->primeTotaleTranche;
    }

    /**
     * Get the value of com_reassurance
     */
    public function getComReassurance()
    {
        $this->comReassurance = (new Calculateur)
            ->getRevenuTotale(
                RevenuCrudController::TYPE_COM_REA,
                null,
                $this,
                null,
                null,
                Calculateur::Param_from_tranche
            ) * 100;
        // dd($this->com_reassurance);
        return $this->comReassurance;
    }

    /**
     * Get the value of com_locale
     */
    public function getComLocale()
    {
        $this->comLocale = (new Calculateur)
            ->getRevenuTotale(
                RevenuCrudController::TYPE_COM_LOCALE,
                null,
                $this,
                null,
                null,
                Calculateur::Param_from_tranche
            ) * 100;
        // dd($this->com_locale);
        return $this->comLocale;
    }

    /**
     * Get the value of com_fronting
     */
    public function getComFronting()
    {
        $this->comFronting = (new Calculateur)
            ->getRevenuTotale(
                RevenuCrudController::TYPE_COM_FRONTING,
                null,
                $this,
                null,
                null,
                Calculateur::Param_from_tranche
            ) * 100;
        // dd($this->com_fronting);
        return $this->comFronting;
    }

    /**
     * Get the value of com_frais_gestion
     */
    public function getComFraisGestion()
    {
        $this->comFraisGestion = (new Calculateur)
            ->getRevenuTotale(
                RevenuCrudController::TYPE_FRAIS_DE_GESTION,
                null,
                $this,
                null,
                null,
                Calculateur::Param_from_tranche
            ) * 100;
        // dd($this->com_frais_gestion);
        return $this->comFraisGestion;
    }

    /**
     * Get the value of com_autre_chargement
     */
    public function getComAutreChargement()
    {
        $this->comAutreChargement = (new Calculateur)
            ->getRevenuTotale(
                RevenuCrudController::TYPE_AUTRE_CHARGEMENT,
                null,
                $this,
                null,
                null,
                Calculateur::Param_from_tranche
            ) * 100;
        return $this->comAutreChargement;
    }

    /**
     * Get the value of revenuTotal
     */
    public function getRevenuTotal()
    {
        $this->revenuTotal = (new Calculateur)
            ->getRevenuTotale(
                null,
                null,
                $this,
                null,
                null,
                Calculateur::Param_from_tranche
            ) * 100;
        //dd($this->revenuTotal);
        return $this->revenuTotal;
    }
}
