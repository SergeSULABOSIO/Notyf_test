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

    private ?float $montant = 0;
    private ?\DateTimeImmutable $startedAt = null;
    private ?\DateTimeImmutable $endedAt = null;
    private ?string $description;
    private ?string $codeMonnaieAffichage;
    private ?Monnaie $monnaie_Affichage;

    //valeurs monnétaires caculables
    private ?float $primeTotale = 0;
    private ?float $commissionTotale = 0;
    private ?float $retroCommissionTotale = 0;
    private ?float $taxeCourtierTotale = 0;
    private ?float $taxeAssureurTotale = 0;
    private ?Police $police = null;
    private ?string $periodeValidite;

    private ?Client $client;
    private ?Assureur $assureur;
    private ?Produit $produit;
    private ?Partenaire $partenaire;
    private ?string $autoriteTaxeCourtier;
    private ?string $autoriteTaxeAssureur;
    //#[ORM\Column]
    private ?bool $validee;


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

    public function getTotalDureeTranchesPrecedantes($indiceCourant)
    {
        $totalDureesCumulees = 0;
        /** @var Tranche */
        foreach ($this->getPolice()->getTranches() as $tranche) {
            if ($this->getPolice()->getTranches()->indexOf($tranche) < $indiceCourant) {
                $totalDureesCumulees = $totalDureesCumulees + $tranche->getDuree();
            }
        }
        return $totalDureesCumulees;
    }

    public function getStartedAt(): ?\DateTimeInterface
    {
        /** @var Police */
        $police = $this->getPolice();
        //dd($indice);
        if ($police != null) {
            $indiceCourant = ($police->getTranches()->indexOf($this));
            $dureesPrecedantes = $this->getTotalDureeTranchesPrecedantes($indiceCourant);
            $this->startedAt = $police->getDateeffet()->add(new DateInterval("P" . ($dureesPrecedantes) . "M"));
            $this->endedAt = $this->startedAt->add(new DateInterval("P" . ($this->getDuree()) . "M"));
            $this->endedAt = $this->endedAt->modify("-1 day");
        }
        //dd($this->startedAt);
        return $this->startedAt;
    }

    public function getEndedAt(): ?\DateTimeInterface
    {
        return $this->endedAt;
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

    public function setStartedAt(\DateTimeInterface $startedAt): self
    {
        $this->startedAt = $startedAt;

        return $this;
    }



    public function setEndedAt(\DateTimeInterface $endedAt): self
    {
        $this->endedAt = $endedAt;

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
        if ($this->getPolice() != null) {
            $this->primeTotale = $this->getPolice()->getPrimeTotale() * $this->getTaux();
        }
        return $this->primeTotale;
    }

    /**
     * Get the value of commissionTotale
     */
    public function getCommissionTotale()
    {
        if ($this->getPolice() != null) {
            $this->commissionTotale = $this->getPolice()->getCommissionTotaleTTC() * $this->getTaux();
        }
        return $this->commissionTotale;
    }

    /**
     * Get the value of retroCommissionTotale
     */
    public function getRetroCommissionTotale()
    {
        if ($this->getPolice() != null) {
            $this->retroCommissionTotale = $this->getPolice()->getRetroComPartenaire() * $this->getTaux();
        }
        return $this->retroCommissionTotale;
    }

    /**
     * Get the value of taxeCourtierTotale
     */
    public function getTaxeCourtierTotale()
    {
        if ($this->getPolice() != null) {
            $this->taxeCourtierTotale = $this->getPolice()->getTaxeCourtierTotale() * $this->getTaux();
        }
        return $this->taxeCourtierTotale;
    }

    /**
     * Get the value of taxeAssureurTotale
     */
    public function getTaxeAssureurTotale()
    {
        if ($this->getPolice() != null) {
            $this->taxeAssureurTotale = $this->getPolice()->getTaxeAssureur() * $this->getTaux();
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
     * Get the value of validee
     */
    public function getValidee()
    {
        if ($this->getCotation() != null) {
            $this->validee = $this->getCotation()->isValidated();
        }
        return $this->validee;
    }
}
