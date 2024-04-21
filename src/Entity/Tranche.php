<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\TrancheRepository;
use Doctrine\Common\Collections\Collection;
use App\Controller\Admin\RevenuCrudController;
use App\Controller\Admin\FactureCrudController;
use App\Service\RefactoringJS\Evenements\Sujet;
use Doctrine\Common\Collections\ArrayCollection;
use App\Controller\Admin\ChargementCrudController;
use App\Entity\Traits\TraitEcouteurEvenements;
use App\Service\RefactoringJS\Evenements\Evenement;
use App\Service\RefactoringJS\Evenements\Observateur;
use App\Service\RefactoringJS\AutresClasses\IndicateursJS;
use App\Service\RefactoringJS\Commandes\CommandeExecuteur;
use App\Service\RefactoringJS\AutresClasses\JSAbstractFinances;
use App\Service\RefactoringJS\Commandes\ComDetecterEvenementAttribut;


#[ORM\Entity(repositoryClass: TrancheRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Tranche extends JSAbstractFinances implements IndicateursJS, Sujet, CommandeExecuteur
{
    use TraitEcouteurEvenements;

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
    private ?float $primeNetteTranche = 0;
    private ?float $frontingTranche = 0;
    private ?float $tvaTranche = 0;

    //les type de revenu
    private ?float $comReassurance = 0;
    private ?float $comReassurancePayee = 0;
    private ?float $comReassuranceSolde = 0;

    private ?float $comLocale = 0;
    private ?float $comLocalePayee = 0;
    private ?float $comLocaleSolde = 0;

    private ?float $comFronting = 0;
    private ?float $comFrontingPayee = 0;
    private ?float $comFrontingSolde = 0;

    private ?float $comFraisGestion = 0;
    private ?float $comFraisGestionPayee = 0;
    private ?float $comFraisGestionSolde = 0;

    private ?float $comAutreChargement = 0;

    private ?float $revenuTotal = 0;
    private ?float $revenuTotalPayee = 0;
    private ?float $revenuTotalSolde = 0;

    //Partenaire
    private ?float $retroCommissionTotale = 0;
    private ?float $retroCommissionTotalePayee = 0;
    private ?float $retroCommissionTotaleSolde = 0;

    //Taxe Courtier
    private ?float $taxeCourtierTotale = 0;
    private ?float $taxeCourtierPayee = 0;
    private ?float $taxeCourtierSolde = 0;

    //Taxe Assureur
    private ?float $taxeAssureurTotale = 0;
    private ?float $taxeAssureurPayee = 0;
    private ?float $taxeAssureurSolde = 0;

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
    //constantes
    public const FACTURE = "facture";
    public const PAIEMENTS = "paiements";
    public const MONTANT_DU = "montantDu";
    public const MONTANT_INVOICED = "montantInvoiced";
    public const MONTANT_TO_BE_INVOICED = "montantToBeInvoiced";
    public const MONTANT_PAYE = "montantPaye";
    public const TARGET = "target";
    public const MESSAGE = "message";
    public const DATA = "data";
    public const MONNAIE = "monnaie";
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
        $this->listeObservateurs = new ArrayCollection();
    }

    public function getTotalInvoiced_destination(?int $destinationFacture): ?float
    {
        $montantInvoiced = 0;
        /** @var ElementFacture */
        foreach ($this->getElementFactures() as $ef) {
            if ($ef->getFacture() != null) {
                if ($destinationFacture == $ef->getFacture()->getDestination()) {
                    $montantInvoiced = $montantInvoiced + $ef->getMontant();
                }
            }
        }
        return round($montantInvoiced);
    }

    public function getTotalPaid_destination(?int $destinationFacture): ?float
    {
        $montantPaid = 0;
        /** @var ElementFacture */
        foreach ($this->getElementFactures() as $ef) {
            if ($ef->getFacture() != null) {
                if ($destinationFacture == $ef->getFacture()->getDestination()) {
                    /** @var Paiement */
                    foreach ($ef->getFacture()->getPaiements() as $paiement) {
                        $montantPaid = $montantPaid + $paiement->getMontant();
                    }
                }
            }
        }
        return round($montantPaid);
    }

    public function getTotalPaid_type_note(?int $typeNote): ?float
    {
        dd("Je suis ici");
        // $montantPaid = 0;
        // /** @var ElementFacture */
        // foreach ($this->getElementFactures() as $ef) {
        //     if ($ef->getFacture() != null) {
        //         if ($typeNote == FactureCrudController::TYPE_FACTURE_COMMISSION_FRONTING) {
        //             /** @var Paiement */
        //             foreach ($ef->getFacture()->getPaiements() as $paiement) {
        //                 $montantPaid = $montantPaid + $paiement->getMontant();
        //             }
        //         }
        //     }
        // }
        // return round($montantPaid);
        return 0;
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
        $oldValue = $this->getTaux();
        $newValue = $taux;
        $this->taux = $taux;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Taux", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

        return $this;
    }

    /**
     * Get the value of police
     */
    public function getPolice()
    {
        if ($this->getCotation() != null) {
            if ($this->getCotation()->getPolices()[0]) {
                if ($this->getCotation() != null) {
                    if ($this->getCotation()->getPolices()) {
                        if (count($this->getCotation()->getPolices()) != 0) {
                            $this->police = (new Calculateur())

                                ->setCotation($this->getCotation())

                                ->getPolice();
                        }
                    }
                }
            }
        }
        // dd($this->police);
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
        $oldValue = $this->getNom();
        $newValue = $nom;
        $this->nom = $nom;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Nom", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

        return $this;
    }


    public function getCotation(): ?Cotation
    {
        return $this->cotation;
    }

    public function setCotation(?Cotation $cotation): self
    {
        $oldValue = $this->getCotation();
        $newValue = $cotation;
        $this->cotation = $cotation;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Cotation", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));

        return $this;
    }

    public function initEntreprise(): ?Entreprise
    {
        return $this->getEntreprise();
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
        $oldValue = $this->getDuree();
        $newValue = $duree;
        $this->duree = $duree;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Durée de la couverture", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

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
        if ($this->getCotation()) {
            $str_police_reference = "Inconnue";
            if ($this->getCotation()->getPolice() != null) {
                $str_police_reference = $this->getCotation()->getPolice()->getReference();
            }
            $strPeriode = " pour " . $this->getDuree() . " mois. ";
            if ($this->getStartedAt() != null & $this->getEndedAt() != null) {
                $strPeriode = " du " . (($this->startedAt)->format('d-m-Y')) . " au " . (($this->endedAt)->format('d-m-Y')) . ".";
            }
            $strMont = " " . $this->getMontantEnMonnaieAffichage($this->getPrimeTotaleTranche()) . " soit " . ($this->getTaux() * 100) . "% de " . $this->getMontantEnMonnaieAffichage($this->getCotation()->getPrimeTotale()) . $strPeriode;
            return $this->getNom() . " / Police: " . $str_police_reference . ": " . $strMont;
        } else {
            return $this->getNom() . " : Cotation introuvable!";
        }
    }

    /**
     * Get the value of retroCommissionTotale
     */
    public function getRetroCommissionTotale(): float
    {
        $this->retroCommissionTotale = $this->getIndicaPartenaireRetrocom();
        return round($this->retroCommissionTotale);
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
        return round($this->taxeCourtierTotale);
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
        return round($this->taxeAssureurTotale);
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
        $oldValue = $this->getStartedAt();
        $newValue = $startedAt;
        $this->startedAt = $startedAt;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Date d'effet de la tranche", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

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
        $oldValue = $this->getEndedAt();
        $newValue = $endedAt;
        $this->endedAt = $endedAt;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Date d'échéance de la tranche", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

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
        $oldValue = $this->getDateEffet();
        $newValue = $dateEffet;
        $this->dateEffet = $dateEffet;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Date d'effet", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

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
        $oldValue = $this->getDateExpiration();
        $newValue = $dateExpiration;
        $this->dateExpiration = $dateExpiration;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Date d'expiration", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

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
        $oldValue = $this->getDateOperation();
        $newValue = $dateOperation;
        $this->dateOperation = $dateOperation;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Date de l'opération", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

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
        $oldValue = $this->getDateEmition();
        $newValue = $dateEmition;
        $this->dateEmition = $dateEmition;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Date d'émition", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

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
            $oldValue = null;
            $newValue = $elementFacture;
            $this->elementFactures->add($elementFacture);
            $elementFacture->setTranche($this);
            //Ecouteur d'action
            $this->executer(new ComDetecterEvenementAttribut($this, "Article de la facture", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
        }

        return $this;
    }

    public function removeElementFacture(ElementFacture $elementFacture): self
    {
        if ($this->elementFactures->removeElement($elementFacture)) {
            // set the owning side to null (unless already changed)
            if ($elementFacture->getTranche() === $this) {
                $oldValue = $elementFacture;
                $newValue = null;
                $elementFacture->setTranche(null);
                //Ecouteur d'action
                $this->executer(new ComDetecterEvenementAttribut($this, "Article de la facture", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
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
        return round($this->reserve);
    }

    /**
     * Get the value of primeTotaleTranche
     */
    public function getPrimeTotaleTranche()
    {
        $this->primeTotaleTranche = 0;
        $this->primeTotaleTranche = round($this->getChargementPrime(null));
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
        return round($this->comReassurance);
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
        return round($this->comLocale);
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
        return round($this->comFronting);
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
        return round($this->comFraisGestion);
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
        return round($this->comAutreChargement);
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
        return round($this->revenuTotal);
    }

    public function getFacturesEmisesPerTypeNote(?int $typeNote): ?ArrayCollection
    {
        $invoices = new ArrayCollection();
        foreach ($this->getElementFactures() as $ef) {
            $isTrue = false;
            if ($ef->getIncludeComFronting() == true && $typeNote == FactureCrudController::TYPE_NOTE_COMMISSION_FRONTING) {
                $isTrue = true;
            } else if ($ef->getIncludeComLocale() == true && $typeNote == FactureCrudController::TYPE_NOTE_COMMISSION_LOCALE) {
                $isTrue = true;
            } else if ($ef->getIncludeComReassurance() == true && $typeNote == FactureCrudController::TYPE_NOTE_COMMISSION_REASSURANCE) {
                $isTrue = true;
            } else if ($ef->getIncludeFraisGestion() == true && $typeNote == FactureCrudController::TYPE_NOTE_FRAIS_DE_GESTION) {
                $isTrue = true;
            } else if ($ef->getIncludePrime() == true && $typeNote == FactureCrudController::TYPE_NOTE_PRIME) {
                $isTrue = true;
            } else if ($ef->getIncludeRetroCom() == true && $typeNote == FactureCrudController::TYPE_NOTE_RETROCOMMISSIONS) {
                $isTrue = true;
            } else if ($ef->getIncludeTaxeAssureur() == true && $typeNote == FactureCrudController::TYPE_NOTE_NOTE_DE_PERCEPTION_TVA) {
                $isTrue = true;
            } else if ($ef->getIncludeTaxeCourtier() == true && $typeNote == FactureCrudController::TYPE_NOTE_NOTE_DE_PERCEPTION_ARCA) {
                $isTrue = true;
            }
            if ($isTrue == true) {
                if (!$invoices->contains($ef->getFacture())) {
                    $invoices->add($ef->getFacture());
                }
            }
        }
        return $invoices;
    }

    public function getPaiementsRecus(?int $destinationFacture): ?ArrayCollection
    {
        $payments = new ArrayCollection();
        foreach ($this->getElementFactures() as $ef) {
            if ($ef->getFacture() != null) {
                if ($ef->getFacture()->getDestination() == $destinationFacture) {
                    foreach ($ef->getFacture()->getPaiements() as $paiement) {
                        if (!$payments->contains($paiement)) {
                            $payments->add($paiement);
                        }
                    }
                }
            }
        }
        return $payments;
    }

    private function editMessage(array $tab): array
    {
        $target = $tab[self::TARGET];
        $montantDue = $tab[self::FACTURE][self::MONTANT_DU];
        $montantPaid = $tab[self::PAIEMENTS][self::MONTANT_PAYE];
        $montantInvoiced = $tab[self::FACTURE][self::MONTANT_INVOICED];
        $montantToBeInvoiced = $tab[self::FACTURE][self::MONTANT_TO_BE_INVOICED];

        if ($target == 0) {
            $tab[self::MESSAGE] = "Ne pas facturer, car pas un revenu";
        } else if ($montantPaid != 0 || $montantDue == $montantInvoiced) {
            $tab[self::MESSAGE] = "Note émise et reglée à " . number_format(($montantPaid / $montantDue) * 100, 0, ',', '.') . "%";
        } else {
            $tab[self::MESSAGE] = "Vous pouvez émettre la note pour " . $this->getMontantEnMonnaieAffichage($montantToBeInvoiced);
        }
        // dd($tab);
        return $tab;
    }

    private function calculerDetails_type_note($total_du, $type_note): ?array
    {
        $payments_amount = $this->getMontantReceivedPerTypeNote($type_note);
        $solde_du = $total_du - $payments_amount;
        $mntInvoiced = $this->getMontantInvoicedPerTypeNote($type_note);
        $mntToBeInvoiced = $total_du - $mntInvoiced;
        return $this->editMessage([
            self::MONNAIE => $this->getMonnaie_Affichage()->getCode(),
            self::TARGET => $total_du,
            self::FACTURE => [
                self::DATA => $this->getFacturesEmisesPerTypeNote($type_note),
                self::MONTANT_DU => $total_du,
                self::MONTANT_INVOICED => $mntInvoiced,
                self::MONTANT_TO_BE_INVOICED => $mntToBeInvoiced
            ],
            self::PAIEMENTS => [
                self::DATA => $this->getPaiementsRecus($type_note),
                self::MONTANT_PAYE => $payments_amount
            ],
            self::SOLDE_DU => $solde_du,
            self::PRODUIRE_FACTURE => $total_du != $mntInvoiced
        ]);
    }

    /**
     * Get the value of fraisGestionInvoiceDetails
     */
    public function getFraisGestionInvoiceDetails(): ?array
    {
        //les paramètres
        return $this->calculerDetails_type_note(
            $this->getComFraisGestion(),
            FactureCrudController::TAB_TYPE_NOTE[FactureCrudController::TYPE_NOTE_FRAIS_DE_GESTION]
        );
    }

    /**
     * Get the value of premiumInvoiceDetails
     */
    public function getPremiumInvoiceDetails(): ?array
    {
        //les paramètres
        return $this->calculerDetails_type_note(
            $this->getPrimeTotaleTranche(),
            FactureCrudController::TAB_TYPE_NOTE[FactureCrudController::TYPE_NOTE_PRIME]
        );
    }

    public function canInvoiceClient(): ?Bool
    {
        $rep =
            $this->getPremiumInvoiceDetails()[self::PRODUIRE_FACTURE] ||
            $this->getFraisGestionInvoiceDetails()[self::PRODUIRE_FACTURE];
        return $rep;
    }

    public function canInvoiceAssureur(): ?Bool
    {
        $rep =
            $this->getComLocaleInvoiceDetails()[self::PRODUIRE_FACTURE] ||
            $this->getComFrontingInvoiceDetails()[self::PRODUIRE_FACTURE] ||
            $this->getComReassuranceInvoiceDetails()[self::PRODUIRE_FACTURE];
        return $rep;
    }

    public function canInvoicePartenaire(): ?Bool
    {
        $rep = $this->getRetrocomInvoiceDetails()[self::PRODUIRE_FACTURE];
        return $rep;
    }

    public function canInvoiceARCA(): ?Bool
    {
        $rep = $this->getTaxCourtierInvoiceDetails()[self::PRODUIRE_FACTURE];
        return $rep;
    }

    public function canInvoiceDGI(): ?Bool
    {
        $rep = $this->getTaxAssureurInvoiceDetails()[self::PRODUIRE_FACTURE];
        return $rep;
    }

    /**
     * Get the value of retrocomInvoiceDetails
     */
    public function getRetrocomInvoiceDetails()
    {
        //les paramètres
        return $this->calculerDetails_type_note(
            $this->getRetroCommissionTotale(),
            FactureCrudController::TAB_TYPE_NOTE[FactureCrudController::TYPE_NOTE_RETROCOMMISSIONS]
        );
    }

    /**
     * Get the value of taxInvoiceDetails
     */
    public function getTaxCourtierInvoiceDetails()
    {
        //les paramètres
        return $this->calculerDetails_type_note(
            $this->getTaxeCourtierTotale(),
            FactureCrudController::TAB_TYPE_NOTE[FactureCrudController::TYPE_NOTE_NOTE_DE_PERCEPTION_ARCA]
        );
    }

    /**
     * Get the value of taxAssureurInvoiceDetails
     */
    public function getTaxAssureurInvoiceDetails()
    {
        //les paramètres
        return $this->calculerDetails_type_note(
            $this->getTaxeAssureurTotale(),
            FactureCrudController::TAB_TYPE_NOTE[FactureCrudController::TYPE_NOTE_NOTE_DE_PERCEPTION_TVA]
        );
    }

    /**
     * Get the value of comLocaleInvoiceDetails
     */
    public function getComLocaleInvoiceDetails()
    {
        //les paramètres
        return $this->calculerDetails_type_note(
            $this->getComLocale(),
            FactureCrudController::TAB_TYPE_NOTE[FactureCrudController::TYPE_NOTE_COMMISSION_LOCALE]
        );
    }

    /**
     * Get the value of comReassuranceInvoiceDetails
     */
    public function getComReassuranceInvoiceDetails()
    {
        //les paramètres
        return $this->calculerDetails_type_note(
            $this->getComReassurance(),
            FactureCrudController::TAB_TYPE_NOTE[FactureCrudController::TYPE_NOTE_COMMISSION_REASSURANCE]
        );
    }

    /**
     * Get the value of comFrontingInvoiceDetails
     */
    public function getComFrontingInvoiceDetails()
    {
        //les paramètres
        return $this->calculerDetails_type_note(
            $this->getComFronting(),
            FactureCrudController::TAB_TYPE_NOTE[FactureCrudController::TYPE_NOTE_COMMISSION_FRONTING]
        );
    }

    /**
     * Get the value of primeNetteTranche
     */
    public function getPrimeNetteTranche()
    {
        $this->primeNetteTranche = 0;
        $type = ChargementCrudController::TAB_TYPE_CHARGEMENT_ORDINAIRE[ChargementCrudController::TYPE_PRIME_NETTE];
        $this->primeNetteTranche = round($this->getChargementPrime($type));
        // dd($this->primeNetteTranche);
        return $this->primeNetteTranche;
    }

    public function getChargementPrime(?int $type)
    {
        $montant = 0;
        if ($this->getCotation()) {
            /** @var Chargement */
            foreach ($this->getCotation()->getChargements() as $chargement) {
                if ($type === $chargement->getType()) {
                    $montant = $montant + $chargement->getMontant();
                } else if ($type === null) {
                    $montant = $montant + $chargement->getMontant();
                }
            }
        }
        return $montant * $this->getTaux();
    }

    /**
     * Get the value of montantReceivedPerDestination
     */
    public function getMontantReceivedPerDestination(?int $destination)
    {
        $this->montantReceivedPerDestination = 0;
        /** @var ElementFacture */
        foreach ($this->elementFactures as $elementFacture) {
            $this->montantReceivedPerDestination = $this->montantReceivedPerDestination + $elementFacture->getMontantReceivedPerDestination($destination);
        }
        return $this->montantReceivedPerDestination;
    }

    /**
     * Get the value of montantReceivedPerTypeNote
     */
    public function getMontantReceivedPerTypeNote(?int $typeNote)
    {
        $this->montantReceivedPerTypeNote = 0;
        /** @var ElementFacture */
        foreach ($this->elementFactures as $elementFacture) {
            $this->montantReceivedPerTypeNote = $this->montantReceivedPerTypeNote + $elementFacture->getMontantReceivedPerTypeNote($typeNote);
        }
        return $this->montantReceivedPerTypeNote;
    }

    /**
     * Get the value of montantInvoicedPerDestination
     */
    public function getMontantInvoicedPerDestination(?int $destination)
    {
        $this->montantInvoicedPerDestination = 0;
        /** @var ElementFacture */
        foreach ($this->elementFactures as $elementFacture) {
            $this->montantInvoicedPerDestination = $this->montantInvoicedPerDestination + $elementFacture->getMontantInvoicedPerDestination($destination);
        }
        return $this->montantInvoicedPerDestination;
    }

    /**
     * Get the value of montantInvoicedPerTypeNote
     */
    public function getMontantInvoicedPerTypeNote(?int $typeNote)
    {
        $this->montantInvoicedPerTypeNote = 0;
        /** @var ElementFacture */
        foreach ($this->elementFactures as $elementFacture) {
            $this->montantInvoicedPerTypeNote = $this->montantInvoicedPerTypeNote + $elementFacture->getMontantInvoicedPerTypeNote($typeNote);
        }
        return $this->montantInvoicedPerTypeNote;
    }

    public function getTaxeCourtierPayee()
    {
        $this->taxeCourtierPayee = $this->getTaxCourtierInvoiceDetails()[self::PAIEMENTS][self::MONTANT_PAYE];
        // dd($this->taxeCourtierPayee);
        return round($this->taxeCourtierPayee);
    }

    public function getTaxeCourtierSolde()
    {
        $this->taxeCourtierSolde = $this->getTaxCourtierInvoiceDetails()[self::SOLDE_DU];
        return round($this->taxeCourtierSolde);
    }

    public function getTaxeAssureurPayee()
    {
        $this->taxeAssureurPayee = $this->getTaxAssureurInvoiceDetails()[self::PAIEMENTS][self::MONTANT_PAYE];
        return round($this->taxeAssureurPayee);
    }

    public function getTaxeAssureurSolde()
    {
        $this->taxeAssureurSolde = $this->getTaxAssureurInvoiceDetails()[self::SOLDE_DU];
        return round($this->taxeAssureurSolde);
    }


    /**
     * Get the value of fontingTranche
     *
     * @return ?float
     */
    public function getFrontingTranche(): ?float
    {
        $this->frontingTranche = 0;
        $type = ChargementCrudController::TAB_TYPE_CHARGEMENT_ORDINAIRE[ChargementCrudController::TYPE_FRONTING];
        $this->frontingTranche = round($this->getChargementPrime($type));
        return $this->frontingTranche;
    }

    /**
     * Get the value of tvaTranche
     */
    public function getTvaTranche()
    {
        $this->tvaTranche = 0;
        $type = ChargementCrudController::TAB_TYPE_CHARGEMENT_ORDINAIRE[ChargementCrudController::TYPE_TVA];
        $this->tvaTranche = round($this->getChargementPrime($type));
        return $this->tvaTranche;
    }



    /**
     * Les fonctions de l'interface
     */
    public function getIndicaRisquePolice(): ?Police
    {
        return $this->getCotation()->getPolice();
    }

    public function getIndicaRisqueCotation(): ?Cotation
    {
        return $this->getCotation();
    }

    public function getIndicaRisqueClient(): ?Client
    {
        return $this->getCotation()->getPiste()->getClient();
    }

    public function getIndicaRisqueAssureur(): ?Assureur
    {
        return $this->getCotation()->getPiste()->getAssureur();
    }

    public function getIndicaRisque(): ?Produit
    {
        return $this->getCotation()->getPiste()->getProduit();
    }

    public function getIndicaRisqueContacts(): ?ArrayCollection
    {
        return $this->getCotation()->getPiste()->getContacts();
    }

    public function getIndicaRisqueReferencePolice(): ?string
    {
        return $this->getCotation()->getPolice()->getReference();
    }

    public function getIndicaRisquePrimeReassurance(): ?float
    {
        return 0;
    }

    public function getIndicaRisquePrimeTotale(): ?float
    {
        return $this->getCotation()->getPrimeTotale();
    }

    public function getIndicaRisquePrimeNette(): ?float
    {
        return round($this->getCotation()->getIndicaRisquePrimeNette() * $this->getTaux());
    }

    public function getIndicaRisqueAccessoires(): ?float
    {
        return round($this->getCotation()->getIndicaRisqueAccessoires() * $this->getTaux());
    }

    public function getIndicaRisqueTaxeRegulateur(): ?float
    {
        return round($this->getCotation()->getIndicaRisqueTaxeRegulateur() * $this->getTaux());
    }

    public function getIndicaRisqueTaxeAssureur(): ?float
    {
        return round($this->getCotation()->getIndicaRevenuTaxeAssureur() * $this->getTaux());
    }

    public function getIndicaRisqueFronting(): ?float
    {
        return round($this->getCotation()->getIndicaRisqueFronting() * $this->getTaux());
    }

    public function getIndicaRevenuNet(?int $typeRevenu = null, ?int $partageable = null): ?float
    {
        return round($this->getCotation()->getIndicaRevenuNet($typeRevenu, $partageable) * $this->getTaux());
    }

    public function getIndicaRevenuTaxeAssureur(?int $typeRevenu = null, ?int $partageable = null): ?float
    {
        return round($this->getCotation()->getIndicaRevenuTaxeAssureur($typeRevenu, $partageable) * $this->getTaux());
    }

    public function getIndicaRevenuTaxeCourtier(?int $typeRevenu = null, ?int $partageable = null): ?float
    {
        return round($this->getCotation()->getIndicaRevenuTaxeCourtier($typeRevenu, $partageable) * $this->getTaux());
    }

    public function getIndicaRevenuPartageable(?int $typeRevenu = null, ?int $partageable = null): ?float
    {
        return round($this->getCotation()->getIndicaRevenuNet($typeRevenu, $partageable) * $this->getTaux());
    }

    public function getIndicaRevenuTotal(?int $typeRevenu = null, ?int $partageable = null): ?float
    {
        return round($this->getCotation()->getIndicaRevenuTotal($typeRevenu, $partageable) * $this->getTaux());
    }

    public function getIndicaPartenaire(): ?Partenaire
    {
        return $this->getCotation()->getPiste()->getPartenaire();
    }

    public function getIndicaPartenaireRetrocom(?int $typeRevenu = null): ?float
    {
        return round($this->getCotation()->getIndicaPartenaireRetrocom($typeRevenu) * $this->getTaux());
    }

    public function getIndicaRevenuReserve(?int $typeRevenu = null): ?float
    {
        return round($this->getCotation()->getIndicaRevenuReserve($typeRevenu) * $this->getTaux());
    }

    /**
     * Get the value of comReassurancePayee
     */
    public function getComReassurancePayee()
    {
        $this->comReassurancePayee = $this->getComReassuranceInvoiceDetails()[self::PAIEMENTS][self::MONTANT_PAYE];
        return $this->comReassurancePayee;
    }

    /**
     * Get the value of comLocalePayee
     */
    public function getComLocalePayee()
    {
        $this->comLocalePayee = $this->getComLocaleInvoiceDetails()[self::PAIEMENTS][self::MONTANT_PAYE];
        return $this->comLocalePayee;
    }

    /**
     * Get the value of comFrontingPayee
     */
    public function getComFrontingPayee()
    {
        $this->comFrontingPayee = $this->getComFrontingInvoiceDetails()[self::PAIEMENTS][self::MONTANT_PAYE];
        return $this->comFrontingPayee;
    }

    /**
     * Get the value of comFraisGestionPayee
     */
    public function getComFraisGestionPayee()
    {
        $this->comFraisGestionPayee = $this->getFraisGestionInvoiceDetails()[self::PAIEMENTS][self::MONTANT_PAYE];
        return $this->comFraisGestionPayee;
    }

    /**
     * Get the value of revenuTotalPayee
     */
    public function getRevenuTotalPayee()
    {
        $this->revenuTotalPayee = round(
            $this->getComReassurancePayee() +
                $this->getComFraisGestionPayee() +
                $this->getComFrontingPayee() +
                $this->getComLocalePayee()
        );
        return $this->revenuTotalPayee;
    }

    /**
     * Get the value of comReassuranceSolde
     */
    public function getComReassuranceSolde()
    {
        $this->comReassuranceSolde = round(
            $this->getComReassurance() - $this->getComReassurancePayee()
        );
        return $this->comReassuranceSolde;
    }

    /**
     * Get the value of comLocaleSolde
     */
    public function getComLocaleSolde()
    {
        $this->comLocaleSolde = round(
            $this->getComLocale() - $this->getComLocalePayee()
        );
        return $this->comLocaleSolde;
    }

    /**
     * Get the value of comFrontingSolde
     */
    public function getComFrontingSolde()
    {
        $this->comFrontingSolde = round(
            $this->getComFronting() - $this->getComFrontingPayee()
        );
        return $this->comFrontingSolde;
    }

    /**
     * Get the value of comFraisGestionSolde
     */
    public function getComFraisGestionSolde()
    {
        $this->comFraisGestionSolde = round(
            $this->getComFraisGestion() - $this->getComFraisGestionPayee()
        );
        return $this->comFraisGestionSolde;
    }

    /**
     * Get the value of revenuTotalSolde
     */
    public function getRevenuTotalSolde()
    {
        $this->revenuTotalSolde = round(
            $this->getRevenuTotal() - $this->getRevenuTotalPayee()
        );
        return $this->revenuTotalSolde;
    }

    /**
     * Get the value of retroCommissionTotalePayee
     */
    public function getRetroCommissionTotalePayee()
    {
        $this->retroCommissionTotalePayee = $this->getRetrocomInvoiceDetails()[self::PAIEMENTS][self::MONTANT_PAYE];
        return $this->retroCommissionTotalePayee;
    }

    /**
     * Get the value of retroCommissionTotaleSolde
     */
    public function getRetroCommissionTotaleSolde()
    {
        $this->retroCommissionTotaleSolde = round(
            $this->getRetroCommissionTotale() - $this->getRetroCommissionTotalePayee()
        );
        return $this->retroCommissionTotaleSolde;
    }

    public function transfererObservateur(?Observateur $observateur)
    {
        // dd("Cette fonction n'est pas encore définie");
    }
}
