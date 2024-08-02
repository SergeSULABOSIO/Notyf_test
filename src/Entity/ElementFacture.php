<?php

namespace App\Entity;

use App\Entity\Facture;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ElementFactureRepository;
use App\Controller\Admin\FactureCrudController;
use App\Entity\Traits\TraitEcouteurEvenements;
use App\Service\RefactoringJS\Evenements\Sujet;
use Doctrine\Common\Collections\ArrayCollection;
use App\Service\RefactoringJS\Commandes\Commande;
use App\Service\RefactoringJS\Evenements\Evenement;
use App\Service\RefactoringJS\Evenements\Observateur;
use Symfony\Component\Validator\Constraints\Collection;
use App\Service\RefactoringJS\Commandes\CommandeExecuteur;
use App\Service\RefactoringJS\AutresClasses\JSAbstractFinances;
use App\Service\RefactoringJS\Commandes\ComDetecterEvenementAttribut;
use App\Service\RefactoringJS\Commandes\Piste\CommandePisteNotifierEvenement;

#[ORM\Entity(repositoryClass: ElementFactureRepository::class)]
#[ORM\HasLifecycleCallbacks]
class ElementFacture extends JSAbstractFinances implements Sujet, CommandeExecuteur
{
    use TraitEcouteurEvenements;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'elementFactures')] //, cascade: ['persist', 'remove'])]
    private ?Tranche $tranche = null;

    #[ORM\Column(nullable: true)]
    private ?float $montant = null;

    #[ORM\ManyToOne]
    private ?Entreprise $entreprise = null;

    #[ORM\ManyToOne]
    private ?Utilisateur $utilisateur = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'elementFactures', cascade: ['remove', 'persist', 'refresh'])]
    private ?Facture $facture = null;

    #[ORM\Column(nullable: true)]
    private ?int $idavenant = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $typeavenant = null;

    //Variables pour des questions des case à cocher
    #[ORM\Column(nullable: true)]
    private ?bool $includePrime = false;
    #[ORM\Column(nullable: true)]
    private ?bool $includeComLocale = false;
    #[ORM\Column(nullable: true)]
    private ?bool $includeComFronting = false;
    #[ORM\Column(nullable: true)]
    private ?bool $includeComReassurance = false;
    #[ORM\Column(nullable: true)]
    private ?bool $includeFraisGestion = false;
    #[ORM\Column(nullable: true)]
    private ?bool $includeRetroCom = false;
    #[ORM\Column(nullable: true)]
    private ?bool $includeTaxeCourtier = false;
    #[ORM\Column(nullable: true)]
    private ?bool $includeTaxeAssureur = false;

    private ?float $primeTotale = 0;
    private ?float $commissionTotale = 0;
    private ?float $commissionLocale = 0;
    private ?float $commissionFronting = 0;
    private ?float $commissionReassurance = 0;
    private ?float $fraisGestionTotale = 0;
    private ?float $revenuTotal = 0;
    private ?float $retroCommissionTotale = 0;
    private ?float $taxeCourtierTotale = 0;
    private ?float $taxeAssureurTotale = 0;

    // private ?float $montantReceivedPerDestination = 0;
    // private ?float $montantReceivedPerTypeNote = 0;
    // private ?float $montantInvoicedPerDestination = 0;
    // private ?float $montantInvoicedPerTypeNote = 0;

    public function __construct()
    {
        $this->listeObservateurs = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }


    public function getMontantInvoicedPerTypeNote(?int $typeNote): ?float
    {
        if ($typeNote == FactureCrudController::TAB_TYPE_NOTE[FactureCrudController::TYPE_NOTE_COMMISSION_FRONTING]) {
            $this->montantInvoicedPerTypeNote = ($this->getIncludeComFronting() == true) ? $this->getCommissionFronting() : 0;
        } else if ($typeNote == FactureCrudController::TAB_TYPE_NOTE[FactureCrudController::TYPE_NOTE_COMMISSION_LOCALE]) {
            $this->montantInvoicedPerTypeNote = ($this->getIncludeComLocale() == true) ? $this->getCommissionLocale() : 0;
        } else if ($typeNote == FactureCrudController::TAB_TYPE_NOTE[FactureCrudController::TYPE_NOTE_COMMISSION_REASSURANCE]) {
            $this->montantInvoicedPerTypeNote = ($this->getIncludeComReassurance() == true) ? $this->getCommissionReassurance() : 0;
        } else if ($typeNote == FactureCrudController::TAB_TYPE_NOTE[FactureCrudController::TYPE_NOTE_FRAIS_DE_GESTION]) {
            $this->montantInvoicedPerTypeNote = ($this->getIncludeFraisGestion() == true) ? $this->getFraisGestionTotale() : 0;
        } else if ($typeNote == FactureCrudController::TAB_TYPE_NOTE[FactureCrudController::TYPE_NOTE_NOTE_DE_PERCEPTION_ARCA]) {
            $this->montantInvoicedPerTypeNote = ($this->getIncludeTaxeCourtier() == true) ? $this->getTaxeCourtierTotale() : 0;
        } else if ($typeNote == FactureCrudController::TAB_TYPE_NOTE[FactureCrudController::TYPE_NOTE_NOTE_DE_PERCEPTION_TVA]) {
            $this->montantInvoicedPerTypeNote = ($this->getIncludeTaxeAssureur() == true) ? $this->getTaxeAssureurTotale() : 0;
        } else if ($typeNote == FactureCrudController::TAB_TYPE_NOTE[FactureCrudController::TYPE_NOTE_PRIME]) {
            $this->montantInvoicedPerTypeNote = ($this->getIncludePrime() == true) ? $this->getPrimeTotale() : 0;
        } else if ($typeNote == FactureCrudController::TAB_TYPE_NOTE[FactureCrudController::TYPE_NOTE_RETROCOMMISSIONS]) {
            $this->montantInvoicedPerTypeNote = ($this->getIncludeRetroCom() == true) ? $this->getRetroCommissionTotale() : 0;
        }
        // dd($typeNote, $this->montantInvoicedPerTypeNote);
        return $this->montantInvoicedPerTypeNote;
    }

    public function getMontantInvoicedPerDestination(?int $destination): ?float
    {
        if ($destination == FactureCrudController::TAB_DESTINATION[FactureCrudController::DESTINATION_ARCA]) {
            $this->montantInvoicedPerDestination = $this->getMontantInvoicedPerTypeNote(FactureCrudController::TYPE_NOTE_NOTE_DE_PERCEPTION_ARCA);
        } else if ($destination == FactureCrudController::TAB_DESTINATION[FactureCrudController::DESTINATION_DGI]) {
            $this->montantInvoicedPerDestination = $this->getMontantInvoicedPerTypeNote(FactureCrudController::TYPE_NOTE_NOTE_DE_PERCEPTION_TVA);
        } else if ($destination == FactureCrudController::TAB_DESTINATION[FactureCrudController::DESTINATION_ASSUREUR]) {
            $this->montantInvoicedPerDestination =
                $this->getMontantInvoicedPerTypeNote(FactureCrudController::TYPE_NOTE_COMMISSION_FRONTING) +
                $this->getMontantInvoicedPerTypeNote(FactureCrudController::TYPE_NOTE_COMMISSION_LOCALE) +
                $this->getMontantInvoicedPerTypeNote(FactureCrudController::TYPE_NOTE_COMMISSION_REASSURANCE);
        } else if ($destination == FactureCrudController::TAB_DESTINATION[FactureCrudController::DESTINATION_CLIENT]) {
            $this->montantInvoicedPerDestination =
                $this->getMontantInvoicedPerTypeNote(FactureCrudController::TYPE_NOTE_FRAIS_DE_GESTION) +
                $this->getMontantInvoicedPerTypeNote(FactureCrudController::TYPE_NOTE_PRIME);
        } else if ($destination == FactureCrudController::TAB_DESTINATION[FactureCrudController::DESTINATION_PARTENAIRE]) {
            $this->montantInvoicedPerDestination =
                $this->getMontantInvoicedPerTypeNote(FactureCrudController::TYPE_NOTE_RETROCOMMISSIONS);
        }
        return $this->montantInvoicedPerDestination;
    }


    public function getMontantReceivedPerDestination(?int $destination): ?float
    {
        if ($this->facture != null) {
            $this->montantReceivedPerDestination = round($this->getProportionPaiementFacture() * $this->getMontantInvoicedPerDestination($destination));
        }
        return $this->montantReceivedPerDestination;
    }

    public function getMontantReceivedPerTypeNote(?int $typeNote): ?float
    {
        if ($this->facture != null) {
            $this->montantReceivedPerTypeNote = round($this->getProportionPaiementFacture() * $this->getMontantInvoicedPerTypeNote($typeNote));
        }
        return $this->montantReceivedPerTypeNote;
    }

    private function getProportionPaiementFacture(): ?float
    {
        return $this->getTotalPaiementsFacture() / $this->facture->getMontantTTC();
    }

    private function getTotalPaiementsFacture(): ?float
    {
        $montantFacturePaid = 0;
        if ($this->facture) {
            foreach ($this->facture->getPaiements() as $paiement) {
                /** @var Paiement */
                $montantFacturePaid = $montantFacturePaid + $paiement->getMontant();
            }
        }
        return $montantFacturePaid;
    }

    public function getMontant(): ?float
    {
        return $this->montant;
    }

    public function setMontant(?float $montant): self
    {
        $oldValue = $this->getMontant();
        $newValue = $montant;
        $this->montant = $montant;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Montant", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

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

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): self
    {
        $this->utilisateur = $utilisateur;

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

    public function initEntreprise(): ?Entreprise
    {
        return $this->getEntreprise();
    }

    public function __toString()
    {
        $str = "";
        $nom = "";
        // dd("ICi", $this);
        if ($this->getTranche()) {
            $nom = $this->getTranche()->getNom();
            $produit = $this->getTranche()->getPolice()->getProduit()->getCode();
            $assureur = $this->getTranche()->getPolice()->getAssureur()->getNom();
            $client = $this->getTranche()->getPolice()->getClient()->getNom();
            $reference = $this->getTranche()->getPolice()->getReference();
            if ($this->getFacture() != null) {
                $destination = $this->getFacture()->getDestination();
                if ($destination == FactureCrudController::TAB_DESTINATION[FactureCrudController::DESTINATION_ARCA]) {
                    $mntantDu = $this->getMontantEnMonnaieAffichage($this->getTaxeCourtierTotale());
                    $str = "Montant " . ucfirst($this->getNomTaxeCourtier()) . " dû: " . $mntantDu . " :: " . FactureCrudController::DESTINATION_ARCA;
                } else if ($destination == FactureCrudController::TAB_DESTINATION[FactureCrudController::DESTINATION_DGI]) {
                    $mntantDu = $this->getMontantEnMonnaieAffichage($this->getTaxeAssureurTotale());
                    $str = "Montant " . ucfirst($this->getNomTaxeAssureur()) . " dû: " . $mntantDu . " :: " . FactureCrudController::DESTINATION_DGI;
                } else if ($destination == FactureCrudController::TAB_DESTINATION[FactureCrudController::DESTINATION_ASSUREUR]) {
                    $mntantDu = $this->getMontantEnMonnaieAffichage($this->getCommissionTotale());
                    $str = "Totale Commission dûe: " . $mntantDu . " :: " . FactureCrudController::DESTINATION_ASSUREUR;
                } else if ($destination == FactureCrudController::TAB_DESTINATION[FactureCrudController::DESTINATION_CLIENT]) {
                    $mntantDu = $this->getMontantEnMonnaieAffichage($this->getFraisGestionTotale() + $this->getPrimeTotale());
                    $str = "Totale dûe: " . $mntantDu . " :: " . FactureCrudController::DESTINATION_CLIENT;
                } else if ($destination == FactureCrudController::TAB_DESTINATION[FactureCrudController::DESTINATION_PARTENAIRE]) {
                    $mntantDu = $this->getMontantEnMonnaieAffichage($this->getRetroCommissionTotale());
                    $str = $this->getTranche()->getPolice()->getPartenaire() . " / Rétro-com. totale dûe: " . $mntantDu . " :: " . FactureCrudController::DESTINATION_PARTENAIRE;
                }
            }
        }
        return $nom . "@" . $reference . " / " . $produit . " / " . $client . " / " . $assureur . " / " . $str;
    }

    public function getFacture(): ?Facture
    {
        return $this->facture;
    }

    public function setFacture(?Facture $facture): self
    {
        $oldValue = $this->getFacture();
        $newValue = $facture;
        $this->facture = $facture;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Facture", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));

        return $this;
    }

    public function getIdavenant(): ?int
    {
        return $this->idavenant;
    }

    public function setIdavenant(?int $idavenant): self
    {
        $oldValue = $this->getIdavenant();
        $newValue = $idavenant;
        $this->idavenant = $idavenant;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Id de l'avenant", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

        return $this;
    }

    public function getTypeavenant(): ?string
    {
        return $this->typeavenant;
    }

    public function setTypeavenant(?string $typeavenant): self
    {
        $oldValue = $this->getTypeavenant();
        $newValue = $typeavenant;
        $this->typeavenant = $typeavenant;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Type d'avenant", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

        return $this;
    }

    /**
     * Get the value of primeTotale
     */
    public function getPrimeTotale()
    {
        if ($this->getTranche()) {
            if ($this->getIncludePrime()) {
                $this->primeTotale = $this->getTranche()->getPrimeTotaleTranche();
            }
        }
        return $this->primeTotale;
    }

    /**
     * Get the value of commissionTotale
     */
    public function getCommissionTotale()
    {
        // $this->commissionTotale =
        //     $this->getTranche()->getComLocale() +
        //     $this->getTranche()->getComFronting() +
        //     $this->getTranche()->getComReassurance() +
        //     $this->getTranche()->getComAutreChargement();

        $this->commissionTotale =
            ($this->getIncludeComReassurance() == true ? $this->getCommissionReassurance() : 0) +
            ($this->getIncludeComFronting() == true ? $this->getCommissionFronting() : 0) +
            ($this->getIncludeComLocale() == true ? $this->getCommissionLocale() : 0) +
            ($this->getIncludeFraisGestion() == true ? $this->getFraisGestionTotale() : 0);
        return $this->commissionTotale;
    }

    /**
     * Get the value of fraisGestionTotale
     */
    public function getFraisGestionTotale()
    {
        if ($this->getTranche()) {
            $this->fraisGestionTotale = $this->getTranche()->getComFraisGestion();
        }
        return $this->fraisGestionTotale;
    }

    /**
     * Get the value of revenuTotal
     */
    public function getRevenuTotal()
    {
        if ($this->getTranche()) {
            $this->revenuTotal = $this->getTranche()->getRevenuTotal();
        }
        return $this->revenuTotal;
    }

    /**
     * Get the value of retroCommissionTotale
     */
    public function getRetroCommissionTotale()
    {
        if ($this->getTranche()) {
            $this->retroCommissionTotale = $this->getTranche()->getRetroCommissionTotale();
        }
        return $this->retroCommissionTotale;
    }

    /**
     * Get the value of taxeCourtierTotale
     */
    public function getTaxeCourtierTotale()
    {
        if ($this->getTranche()) {
            $this->taxeCourtierTotale = $this->getTranche()->getTaxeCourtierTotale();
        }
        return $this->taxeCourtierTotale;
    }

    /**
     * Get the value of taxeAssureurTotale
     */
    public function getTaxeAssureurTotale()
    {
        if ($this->getTranche()) {
            $this->taxeAssureurTotale = $this->getTranche()->getTaxeAssureurTotale();
        }
        return $this->taxeAssureurTotale;
    }

    public function getTranche(): ?Tranche
    {
        return $this->tranche;
    }

    public function setTranche(?Tranche $tranche): self
    {
        $oldValue = $this->getTranche();
        $newValue = $tranche;
        $this->tranche = $tranche;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Tranche", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));

        return $this;
    }

    /**
     * Get the value of commissionLocale
     */
    public function getCommissionLocale()
    {
        if ($this->getTranche() != null) {
            $this->commissionLocale = $this->getTranche()->getComLocale();
        }
        return $this->commissionLocale;
    }

    /**
     * Get the value of commissionFronting
     */
    public function getCommissionFronting()
    {
        if ($this->getTranche() != null) {
            $this->commissionFronting = $this->getTranche()->getComFronting();
        }
        return $this->commissionFronting;
    }

    /**
     * Get the value of commissionReassurance
     */
    public function getCommissionReassurance()
    {
        if ($this->getTranche() != null) {
            $this->commissionReassurance = $this->getTranche()->getComReassurance();
        }
        return $this->commissionReassurance;
    }

    /**
     * Get the value of includePrime
     */
    public function getIncludePrime()
    {
        return $this->includePrime;
    }

    /**
     * Set the value of includePrime
     *
     * @return  self
     */
    public function setIncludePrime($includePrime)
    {
        $this->includePrime = $includePrime;

        return $this;
    }

    /**
     * Get the value of includeComLocale
     */
    public function getIncludeComLocale()
    {
        return $this->includeComLocale;
    }

    /**
     * Set the value of includeComLocale
     *
     * @return  self
     */
    public function setIncludeComLocale($includeComLocale)
    {
        $this->includeComLocale = $includeComLocale;

        return $this;
    }

    /**
     * Get the value of includeComFronting
     */
    public function getIncludeComFronting()
    {
        return $this->includeComFronting;
    }

    /**
     * Set the value of includeComFronting
     *
     * @return  self
     */
    public function setIncludeComFronting($includeComFronting)
    {
        $this->includeComFronting = $includeComFronting;

        return $this;
    }

    /**
     * Get the value of includeComReassurance
     */
    public function getIncludeComReassurance()
    {
        return $this->includeComReassurance;
    }

    /**
     * Set the value of includeComReassurance
     *
     * @return  self
     */
    public function setIncludeComReassurance($includeComReassurance)
    {
        $this->includeComReassurance = $includeComReassurance;

        return $this;
    }

    /**
     * Get the value of includeFraisGestion
     */
    public function getIncludeFraisGestion()
    {
        return $this->includeFraisGestion;
    }

    /**
     * Set the value of includeFraisGestion
     *
     * @return  self
     */
    public function setIncludeFraisGestion($includeFraisGestion)
    {
        $this->includeFraisGestion = $includeFraisGestion;

        return $this;
    }

    /**
     * Get the value of includeRetroCom
     */
    public function getIncludeRetroCom()
    {
        return $this->includeRetroCom;
    }

    /**
     * Set the value of includeRetroCom
     *
     * @return  self
     */
    public function setIncludeRetroCom($includeRetroCom)
    {
        $this->includeRetroCom = $includeRetroCom;

        return $this;
    }

    /**
     * Get the value of includeTaxeCourtier
     */
    public function getIncludeTaxeCourtier()
    {
        return $this->includeTaxeCourtier;
    }

    /**
     * Set the value of includeTaxeCourtier
     *
     * @return  self
     */
    public function setIncludeTaxeCourtier($includeTaxeCourtier)
    {
        $this->includeTaxeCourtier = $includeTaxeCourtier;

        return $this;
    }

    /**
     * Get the value of includeTaxeAssureur
     */
    public function getIncludeTaxeAssureur()
    {
        return $this->includeTaxeAssureur;
    }

    /**
     * Set the value of includeTaxeAssureur
     *
     * @return  self
     */
    public function setIncludeTaxeAssureur($includeTaxeAssureur)
    {
        $this->includeTaxeAssureur = $includeTaxeAssureur;

        return $this;
    }

    /**
     * Set the value of commissionLocale
     *
     * @return  self
     */
    public function setCommissionLocale($commissionLocale)
    {
        $this->commissionLocale = $commissionLocale;

        return $this;
    }

    /**
     * Set the value of commissionFronting
     *
     * @return  self
     */
    public function setCommissionFronting($commissionFronting)
    {
        $this->commissionFronting = $commissionFronting;

        return $this;
    }

    /**
     * Set the value of commissionReassurance
     *
     * @return  self
     */
    public function setCommissionReassurance($commissionReassurance)
    {
        $this->commissionReassurance = $commissionReassurance;

        return $this;
    }

    /**
     * Set the value of fraisGestionTotale
     *
     * @return  self
     */
    public function setFraisGestionTotale($fraisGestionTotale)
    {
        $this->fraisGestionTotale = $fraisGestionTotale;

        return $this;
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
     * Set the value of commissionTotale
     *
     * @return  self
     */
    public function setCommissionTotale($commissionTotale)
    {
        $this->commissionTotale = $commissionTotale;

        return $this;
    }

    /**
     * Set the value of retroCommissionTotale
     *
     * @return  self
     */
    public function setRetroCommissionTotale($retroCommissionTotale)
    {
        $this->retroCommissionTotale = $retroCommissionTotale;

        return $this;
    }

    /**
     * Set the value of taxeCourtierTotale
     *
     * @return  self
     */
    public function setTaxeCourtierTotale($taxeCourtierTotale)
    {
        $this->taxeCourtierTotale = $taxeCourtierTotale;

        return $this;
    }

    /**
     * Set the value of taxeAssureurTotale
     *
     * @return  self
     */
    public function setTaxeAssureurTotale($taxeAssureurTotale)
    {
        $this->taxeAssureurTotale = $taxeAssureurTotale;

        return $this;
    }

    public function transfererObservateur(?Observateur $observateur)
    {
        // dd("Cette fonction n'est pas encore définie");
    }
}
