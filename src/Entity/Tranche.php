<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\TrancheRepository;
use App\Controller\Admin\MonnaieCrudController;
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
    private ?float $montant = 0;
    private ?float $primeTotale = 0;
    private ?float $commissionTotale = 0;
    private ?float $retroCommissionTotale = 0;
    private ?float $taxeCourtierTotale = 0;
    private ?float $taxeAssureurTotale = 0;
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
    private ?Partenaire $piste;

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
        /** @var Police */
        if ($this->cotation) {
            if ($this->cotation->isValidated()) {
                if (count($this->cotation->getPolices()) != 0) {
                    $this->police = $this->cotation->getPolices()[0];
                }
            }
        }
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

    /**
     * Get the value of montant
     */
    public function getMontant()
    {
        $mont = 0;
        if ($this->getCotation() != null) {
            $mont = (($this->getCotation()->getPrimeTotale()) * $this->getTaux());
        }
        $this->montant = $mont;
        return $this->montant;
    }

    /**
     * Set the value of montant
     *
     * @return  self
     */
    public function setMontant($montant)
    {
        $this->montant = $montant;

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

    private function getMonnaie($fonction)
    {
        $tabMonnaies = $this->getEntreprise()->getMonnaies();
        foreach ($tabMonnaies as $monnaie) {
            if ($monnaie->getFonction() == $fonction) {
                return $monnaie;
            }
        }
        return null;
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
        $strMont = " " . number_format($this->getMontant() / 100, 2, ",", ".") . $strMonnaie . " soit " . ($this->getTaux() * 100) . "% de " . number_format(($this->getCotation()->getPrimeTotale() / 100), 2, ",", ".") . $strMonnaie . $strPeriode;
        return $this->getNom() . ": " . $strMont;
    }

    /**
     * Get the value of codeMonnaieAffichage
     */
    public function getCodeMonnaieAffichage()
    {
        $this->codeMonnaieAffichage = "";
        $monnaieAff = $this->getMonnaie_Affichage();
        if ($monnaieAff != null) {
            $this->codeMonnaieAffichage = " " . $this->getMonnaie_Affichage()->getCode();
        }
        return $this->codeMonnaieAffichage;
    }

    /**
     * Get the value of monnaie_Affichage
     */
    public function getMonnaie_Affichage()
    {
        $this->monnaie_Affichage = $this->getMonnaie(MonnaieCrudController::TAB_MONNAIE_FONCTIONS[MonnaieCrudController::FONCTION_SAISIE_ET_AFFICHAGE]);
        if ($this->monnaie_Affichage == null) {
            $this->monnaie_Affichage = $this->getMonnaie(MonnaieCrudController::TAB_MONNAIE_FONCTIONS[MonnaieCrudController::FONCTION_AFFICHAGE_UNIQUEMENT]);
        }
        return $this->monnaie_Affichage;
    }

    /**
     * Get the value of primeTotale
     */
    public function getPrimeTotale()
    {
        if ($this->getCotation() != null) {
            $this->primeTotale = $this->getCotation()->getPrimeTotale() * $this->getTaux();
        }
        return $this->primeTotale;
    }

    /**
     * Get the value of commissionTotale
     */
    public function getCommissionTotale()
    {
        if ($this->getCotation() != null) {
            $this->commissionTotale = $this->getCotation()->getCommissionTotaleTTC() * $this->getTaux();
        }
        return $this->commissionTotale;
    }

    /**
     * Get the value of retroCommissionTotale
     */
    public function getRetroCommissionTotale()
    {
        if ($this->getCotation() != null) {
            $this->retroCommissionTotale = $this->getCotation()->getRetroComPartenaire() * $this->getTaux();
        }
        return $this->retroCommissionTotale;
    }

    /**
     * Get the value of taxeCourtierTotale
     */
    public function getTaxeCourtierTotale()
    {
        if ($this->getCotation() != null) {
            $this->taxeCourtierTotale = $this->getCotation()->getTaxeCourtierTotale() * $this->getTaux();
        }
        return $this->taxeCourtierTotale;
    }

    /**
     * Get the value of taxeAssureurTotale
     */
    public function getTaxeAssureurTotale()
    {
        if ($this->getCotation() != null) {
            $this->taxeAssureurTotale = $this->getCotation()->getTaxeAssureurTotale() * $this->getTaux();
        }
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
        if ($this->getCotation()) {
            $this->client = $this->getCotation()->getPiste()->getClient();
        }
        return $this->client;
    }

    /**
     * Get the value of assureur
     */
    public function getAssureur()
    {
        if ($this->getCotation()) {
            $this->assureur = $this->getCotation()->getAssureur();
        }
        return $this->assureur;
    }

    /**
     * Get the value of produit
     */
    public function getProduit()
    {
        if ($this->getCotation()) {
            $this->produit = $this->getCotation()->getPiste()->getProduit();
        }
        return $this->produit;
    }

    /**
     * Get the value of partenaire
     */
    public function getPartenaire()
    {
        if ($this->getCotation()) {
            $this->partenaire = $this->getCotation()->getPartenaire();
        }
        return $this->partenaire;
    }

    /**
     * Get the value of autoriteTaxeCourtier
     */
    public function getAutoriteTaxeCourtier()
    {
        $this->autoriteTaxeCourtier = $this->checkTaxe(true);
        return $this->autoriteTaxeCourtier;
    }

    private function checkTaxe(?bool $payableParCourtier): string
    {
        if ($this->getEntreprise()) {
            /** @var Taxe */
            foreach ($this->getEntreprise()->getTaxes() as $taxe) {
                if ($taxe->isPayableparcourtier() == $payableParCourtier) {
                    return $taxe->getOrganisation();
                }
            }
        } else {
            return "Inconnue";
        }
    }

    /**
     * Get the value of autoriteTaxeAssureur
     */
    public function getAutoriteTaxeAssureur()
    {
        $this->autoriteTaxeAssureur = $this->checkTaxe(false);
        return $this->autoriteTaxeAssureur;
    }

    /**
     * Get the value of validated
     */ 
    public function getValidated()
    {
        if ($this->getCotation() != null) {
            $this->validated = $this->getCotation()->isValidated();
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
        if ($this->getCotation() != null) {
            $this->piste = $this->getCotation()->getPiste();
        }
        return $this->piste;
    }
}
