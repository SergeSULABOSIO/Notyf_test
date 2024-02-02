<?php

namespace App\Entity;

use DateInterval;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\TrancheRepository;
use Doctrine\Common\Collections\Collection;
use App\Controller\Admin\RevenuCrudController;
use App\Controller\Admin\FactureCrudController;
use App\Controller\Admin\MonnaieCrudController;
use Doctrine\Common\Collections\ArrayCollection;

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
    private ?string $description = "";
    private ?string $codeMonnaieAffichage = "";
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

    private ?array $premiumInvoiceDetails;
    private ?array $retrocomInvoiceDetails;
    private ?array $comLocaleInvoiceDetails;
    private ?array $comReassuranceInvoiceDetails;
    private ?array $comFrontingInvoiceDetails;
    private ?array $taxCourtierInvoiceDetails;
    private ?array $taxAssureurInvoiceDetails;
    private ?array $fraisGestionInvoiceDetails;

    //Les objets
    private ?Monnaie $monnaie_Affichage;
    private ?Client $client;
    private ?Police $police = null;
    private ?Assureur $assureur;
    private ?Produit $produit;
    private ?Partenaire $partenaire;
    private ?Piste $piste;
    //constantes
    public const FACTURE = "facture";
    public const PAIEMENTS = "paiements";
    public const MONTANT_DU = "montantDu";
    public const MONTANT_PAYE = "montantPaye";
    public const TARGET = "target";
    public const DATA = "data";
    public const SOLDE_DU = "solde";
    public const PRODUIRE_FACTURE = "produire";
    public const TOBE_INVOICED = "toBeInvoiced";

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
        // dd($this->getCotation()==null);
        if ($this->getCotation()) {
            $strMonnaie = $this->getCodeMonnaieAffichage();
            // dd("Ici", $strMonnaie);
            $strPeriode = " pour durée de " . $this->getDuree() . " mois. ";
            if ($this->getStartedAt() != null & $this->getEndedAt() != null) {
                $strPeriode = ". Cette tranche est valide du " . (($this->startedAt)->format('d-m-Y')) . " au " . (($this->endedAt)->format('d-m-Y')) . ".";
            }
            $strMont = " " . number_format($this->getPrimeTotaleTranche() / 100, 2, ",", ".") . $strMonnaie . " soit " . ($this->getTaux() * 100) . "% de " . number_format(($this->getCotation()->getPrimeTotale() / 100), 2, ",", ".") . $strMonnaie . $strPeriode;
            // dd($this->getNom() . ": " . $strMont);
            return $this->getNom() . ": " . $strMont;
        } else {
            return "RAS";
        }
    }

    /**
     * Get the value of codeMonnaieAffichage
     */
    public function getCodeMonnaieAffichage()
    {
        $this->codeMonnaieAffichage = (new Calculateur())
            ->setCotation($this->getCotation())
            ->getCodeMonnaie();
        // dd($code);
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
    public function getRetroCommissionTotale():float
    {
        $this->retroCommissionTotale = (new Calculateur())
            ->getRetroCommissionTotale(
                null,
                null,
                $this,
                null,
                null,
                Calculateur::Param_from_tranche
            ) * 100;
        return $this->retroCommissionTotale;
    }

    /**
     * Get the value of taxeCourtierTotale
     */
    public function getTaxeCourtierTotale()
    {
        $this->taxeCourtierTotale = (new Calculateur())
            ->getTaxePourCourtier(
                null,
                null,
                $this,
                null,
                null,
                Calculateur::Param_from_tranche
            ) * 100;
        return $this->taxeCourtierTotale;
    }

    /**
     * Get the value of taxeAssureurTotale
     */
    public function getTaxeAssureurTotale()
    {
        $this->taxeAssureurTotale = (new Calculateur())
            ->getTaxePourAssureur(
                null,
                null,
                $this,
                null,
                null,
                Calculateur::Param_from_tranche
            ) * 100;
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
            ->setCotation($this->getCotation())
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
        // dd($this->comReassurance);
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

    /**
     * Get the value of premiumInvoiceDetails
     */
    public function getPremiumInvoiceDetails(): ?array
    {
        //les paramètres
        $invoices = [];
        $invoice_amount = 0;
        $total_due = 0;
        $payments = [];
        $payments_amount = 0;

        
        foreach ($this->getElementFactures() as $ef) {
            $facture = $ef->getFacture();
            if ($facture->getType() == FactureCrudController::TAB_TYPE_FACTURE[FactureCrudController::TYPE_FACTURE_PRIME]) {
                //Facture
                $invoices[] = $facture;
                $invoice_amount = $invoice_amount + ($facture->getTotalDu());
                //Paiements
                $payments[] = $facture->getPaiements();
                foreach ($facture->getPaiements() as $paiement) {
                    $payments_amount = $payments_amount + $paiement->getMontant();
                }
            }
        }
        $this->premiumInvoiceDetails = [
            self::TARGET => $this->getPrimeTotaleTranche(),
            self::FACTURE => [
                self::DATA => $invoices,
                self::MONTANT_DU => $invoice_amount
            ],
            self::PAIEMENTS => [
                self::DATA => $payments,
                self::MONTANT_PAYE => $payments_amount
            ],
            self::SOLDE_DU => $this->getPrimeTotaleTranche() - $payments_amount,
            self::PRODUIRE_FACTURE => $this->getPrimeTotaleTranche() != $invoice_amount
        ];

        return $this->premiumInvoiceDetails;
    }

    /**
     * Get the value of fraisGestionInvoiceDetails
     */
    public function getFraisGestionInvoiceDetails(): ?array
    {
        //les paramètres
        $invoices = [];
        $invoice_amount = 0;
        $payments = [];
        $payments_amount = 0;

        foreach ($this->getElementFactures() as $ef) {
            $facture = $ef->getFacture();
            if ($facture->getType() == FactureCrudController::TAB_TYPE_FACTURE[FactureCrudController::TYPE_FACTURE_FRAIS_DE_GESTION]) {
                //Facture
                $invoices[] = $facture;
                $invoice_amount = $invoice_amount + ($facture->getTotalDu());
                //Paiements
                $payments[] = $facture->getPaiements();
                foreach ($facture->getPaiements() as $paiement) {
                    $payments_amount = $payments_amount + $paiement->getMontant();
                }
            }
        }
        $this->fraisGestionInvoiceDetails = [
            self::TARGET => $this->getComFraisGestion(),
            self::FACTURE => [
                self::DATA => $invoices,
                self::MONTANT_DU => $invoice_amount
            ],
            self::PAIEMENTS => [
                self::DATA => $payments,
                self::MONTANT_PAYE => $payments_amount
            ],
            self::SOLDE_DU => $this->getComFraisGestion() - $payments_amount,
            self::PRODUIRE_FACTURE => $this->getComFraisGestion() != $invoice_amount
        ];
        return $this->fraisGestionInvoiceDetails;
    }

    /**
     * Get the value of retrocomInvoiceDetails
     */ 
    public function getRetrocomInvoiceDetails()
    {
        //les paramètres
        $invoices = [];
        $invoice_amount = 0;
        $payments = [];
        $payments_amount = 0;

        foreach ($this->getElementFactures() as $ef) {
            $facture = $ef->getFacture();
            if ($facture->getType() == FactureCrudController::TAB_TYPE_FACTURE[FactureCrudController::TYPE_FACTURE_RETROCOMMISSIONS]) {
                //Facture
                // dd($facture->getTotalDu() * 1000000);
                $invoices[] = $facture;
                $invoice_amount = $invoice_amount + ($facture->getTotalDu()/100);
                //Paiements
                $payments[] = $facture->getPaiements();
                foreach ($facture->getPaiements() as $paiement) {
                    $payments_amount = $payments_amount + $paiement->getMontant();
                }
            }
        }
        $this->retrocomInvoiceDetails = [
            self::TARGET => $this->getRetroCommissionTotale() / 100,
            self::FACTURE => [
                self::DATA => $invoices,
                self::MONTANT_DU => $invoice_amount
            ],
            self::PAIEMENTS => [
                self::DATA => $payments,
                self::MONTANT_PAYE => $payments_amount
            ],
            self::SOLDE_DU => ($this->getRetroCommissionTotale() / 100) - $payments_amount,
            self::PRODUIRE_FACTURE => ($this->getRetroCommissionTotale() / 100) !== $invoice_amount
        ];
        // dd(($this->getRetroCommissionTotale()) - ($invoice_amount));
        return $this->retrocomInvoiceDetails;
    }

    /**
     * Get the value of taxInvoiceDetails
     */ 
    public function getTaxCourtierInvoiceDetails()
    {
        //les paramètres
        $invoices = [];
        $invoice_amount = 0;
        $payments = [];
        $payments_amount = 0;

        foreach ($this->getElementFactures() as $ef) {
            $facture = $ef->getFacture();
            if ($facture->getType() == FactureCrudController::TAB_TYPE_FACTURE[FactureCrudController::TYPE_FACTURE_NOTE_DE_PERCEPTION_ARCA]) {
                //Facture
                $invoices[] = $facture;
                $invoice_amount = $invoice_amount + ($facture->getTotalDu());
                //Paiements
                $payments[] = $facture->getPaiements();
                foreach ($facture->getPaiements() as $paiement) {
                    $payments_amount = $payments_amount + $paiement->getMontant();
                }
            }
        }
        $this->taxCourtierInvoiceDetails = [
            self::TARGET => $this->getTaxeCourtierTotale(),
            self::FACTURE => [
                self::DATA => $invoices,
                self::MONTANT_DU => $invoice_amount
            ],
            self::PAIEMENTS => [
                self::DATA => $payments,
                self::MONTANT_PAYE => $payments_amount
            ],
            self::SOLDE_DU => $this->getTaxeCourtierTotale() - $payments_amount,
            self::PRODUIRE_FACTURE => $this->getTaxeCourtierTotale() != $invoice_amount
        ];
        return $this->taxCourtierInvoiceDetails;
    }

    /**
     * Get the value of taxAssureurInvoiceDetails
     */ 
    public function getTaxAssureurInvoiceDetails()
    {
        //les paramètres
        $invoices = [];
        $invoice_amount = 0;
        $payments = [];
        $payments_amount = 0;

        foreach ($this->getElementFactures() as $ef) {
            $facture = $ef->getFacture();
            if ($facture->getType() == FactureCrudController::TAB_TYPE_FACTURE[FactureCrudController::TYPE_FACTURE_NOTE_DE_PERCEPTION_TVA]) {
                //Facture
                $invoices[] = $facture;
                $invoice_amount = $invoice_amount + ($facture->getTotalDu());
                //Paiements
                $payments[] = $facture->getPaiements();
                foreach ($facture->getPaiements() as $paiement) {
                    $payments_amount = $payments_amount + $paiement->getMontant();
                }
            }
        }
        $this->taxAssureurInvoiceDetails = [
            self::TARGET => $this->getTaxeAssureurTotale(),
            self::FACTURE => [
                self::DATA => $invoices,
                self::MONTANT_DU => $invoice_amount
            ],
            self::PAIEMENTS => [
                self::DATA => $payments,
                self::MONTANT_PAYE => $payments_amount
            ],
            self::SOLDE_DU => $this->getTaxeAssureurTotale() - $payments_amount,
            self::PRODUIRE_FACTURE => $this->getTaxeAssureurTotale() != $invoice_amount
        ];
        return $this->taxAssureurInvoiceDetails;
    }

    /**
     * Get the value of comLocaleInvoiceDetails
     */ 
    public function getComLocaleInvoiceDetails()
    {
        //les paramètres
        $invoices = [];
        $invoice_amount = 0;
        $payments = [];
        $payments_amount = 0;

        foreach ($this->getElementFactures() as $ef) {
            $facture = $ef->getFacture();
            if ($facture->getType() == FactureCrudController::TAB_TYPE_FACTURE[FactureCrudController::TYPE_FACTURE_COMMISSION_LOCALE]) {
                //Facture
                $invoices[] = $facture;
                $invoice_amount = $invoice_amount + ($facture->getTotalDu());
                //Paiements
                $payments[] = $facture->getPaiements();
                foreach ($facture->getPaiements() as $paiement) {
                    $payments_amount = $payments_amount + $paiement->getMontant();
                }
            }
        }
        $this->comLocaleInvoiceDetails = [
            self::TARGET => $this->getComLocale(),
            self::FACTURE => [
                self::DATA => $invoices,
                self::MONTANT_DU => $invoice_amount
            ],
            self::PAIEMENTS => [
                self::DATA => $payments,
                self::MONTANT_PAYE => $payments_amount
            ],
            self::SOLDE_DU => $this->getComLocale() - $payments_amount,
            self::PRODUIRE_FACTURE => $this->getComLocale() != $invoice_amount
        ];
        return $this->comLocaleInvoiceDetails;
    }

    /**
     * Get the value of comReassuranceInvoiceDetails
     */ 
    public function getComReassuranceInvoiceDetails()
    {
        //les paramètres
        $invoices = [];
        $invoice_amount = 0;
        $payments = [];
        $payments_amount = 0;

        foreach ($this->getElementFactures() as $ef) {
            $facture = $ef->getFacture();
            // dd($facture);
            if ($facture->getType() == FactureCrudController::TAB_TYPE_FACTURE[FactureCrudController::TYPE_FACTURE_COMMISSION_REASSURANCE]) {
                //Facture
                // dd($facture->getTotalDu());
                $invoices[] = $facture;
                $invoice_amount = $invoice_amount + ($facture->getTotalDu());
                //Paiements
                $payments[] = $facture->getPaiements();
                foreach ($facture->getPaiements() as $paiement) {
                    $payments_amount = $payments_amount + $paiement->getMontant();
                }
            }
        }
        $this->comReassuranceInvoiceDetails = [
            self::TARGET => $this->getComReassurance(),
            self::FACTURE => [
                self::DATA => $invoices,
                self::MONTANT_DU => $invoice_amount
            ],
            self::PAIEMENTS => [
                self::DATA => $payments,
                self::MONTANT_PAYE => $payments_amount
            ],
            self::SOLDE_DU => $this->getComReassurance() - $payments_amount,
            self::PRODUIRE_FACTURE => $this->getComReassurance() != $invoice_amount
        ];
        // dd($this->getComReassurance(), $invoice_amount);
        return $this->comReassuranceInvoiceDetails;
    }

    /**
     * Get the value of comFrontingInvoiceDetails
     */ 
    public function getComFrontingInvoiceDetails()
    {
        //les paramètres
        $invoices = [];
        $invoice_amount = 0;
        $payments = [];
        $payments_amount = 0;

        foreach ($this->getElementFactures() as $ef) {
            $facture = $ef->getFacture();
            if ($facture->getType() == FactureCrudController::TAB_TYPE_FACTURE[FactureCrudController::TYPE_FACTURE_COMMISSION_FRONTING]) {
                //Facture
                $invoices[] = $facture;
                $invoice_amount = $invoice_amount + ($facture->getTotalDu());
                //Paiements
                $payments[] = $facture->getPaiements();
                foreach ($facture->getPaiements() as $paiement) {
                    $payments_amount = $payments_amount + $paiement->getMontant();
                }
            }
        }
        $this->comFrontingInvoiceDetails = [
            self::TARGET => $this->getComFronting(),
            self::FACTURE => [
                self::DATA => $invoices,
                self::MONTANT_DU => $invoice_amount
            ],
            self::PAIEMENTS => [
                self::DATA => $payments,
                self::MONTANT_PAYE => $payments_amount
            ],
            self::SOLDE_DU => $this->getComFronting() - $payments_amount,
            self::PRODUIRE_FACTURE => $this->getComFronting() != $invoice_amount
        ];
        return $this->comFrontingInvoiceDetails;
    }
}
