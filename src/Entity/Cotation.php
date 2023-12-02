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

    #[ORM\ManyToOne(inversedBy: 'cotations', cascade: ['remove', 'persist', 'refresh'])]
    private ?Piste $piste = null;

    #[ORM\ManyToOne(inversedBy: 'cotations')]
    private ?Assureur $assureur = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;

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

    //Les champs calculables automatiquement sur base des données existantes
    private ?float $primeTotale;
    //parties partageable et non partageable tous confondues
    private ?float $revenuTotalHT;
    private ?float $taxeCourtierTotale;
    private ?float $revenuNetTotal;

    //partie partageable
    private ?float $revenuTotalHTPartageable;
    private ?Partenaire $partenaire;
    private ?float $taxeCourtierTotalePartageable;
    private ?float $revenuNetTotalPartageable;
    #[ORM\Column]
    private ?float $tauxretrocompartenaire = 0;
    private ?float $retroComPartenaire;

    private ?Taxe $taxeCourtier;
    private ?Taxe $taxeAssureur;
    private ?Collection $taxes;

    #[ORM\OneToMany(mappedBy: 'cotation', targetEntity: Police::class, cascade: ['remove', 'persist', 'refresh'])]
    private Collection $polices;

    #[ORM\OneToMany(mappedBy: 'cotation', targetEntity: DocPiece::class, cascade: ['remove', 'persist', 'refresh'])]
    private Collection $documents;

    private ?string $client;
    private ?string $gestionnaire;

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



    public function calc_getChargement($type)
    {
        $total = 0;
        if ($this->getChargements()) {
            foreach ($this->getChargements() as $chargement) {
                if ($type == $chargement->getType()) {
                    $total = $total + $chargement->getMontant();
                }
            }
        }
        return $total;
    }

    public function __toString()
    {
        $strMonnaie = $this->getCodeMonnaieAffichage();
        $strCommission = "";
        if ($this->getRevenus()) {
            $strCommission = " | Com. ht: " . number_format($this->getRevenuTotalHT() / 100, 2, ",", ".") . $strMonnaie . "";
        }
        $strNomAssureur = "";
        if ($this->assureur) {
            $strNomAssureur = $this->assureur->getNom();
        }
        $strNomProduit = "";
        if ($this->piste->getProduit()) {
            $strNomProduit = $this->piste->getProduit()->getNom();
        }
        $strValidation = ($this->isValidated() == true ? " (offre validée)" : "");        
        //return "" . $this->nom . " | " . $strNomAssureur . " | " . $strNomProduit . " | Prime ttc: " . number_format(($this->getPrimeTotale() / 100), 2, ",", ".") . $strMonnaie . $strCommission . ($this->isValidated() == true ? " (*validée*)." : ".");
        return "" . $this->nom . $strValidation;
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

    // public function getValidated(): ?int
    // {
    //     return $this->validated;
    // }

    // public function setValidated(?int $validated): self
    // {
    //     $this->validated = $validated;

    //     return $this;
    // }

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
        $tot = 0;
        if ($this->getChargements()) {
            foreach ($this->getChargements() as $chargement) {
                $tot = $tot + $chargement->getMontant();
            }
        }
        $this->primeTotale = $tot;
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
        $tot = 0;
        if ($this->getRevenus()) {
            foreach ($this->getRevenus() as $revenu) {
                $tot = $tot + $revenu->calc_getRevenuFinal();
            }
        }
        $this->revenuTotalHT = $tot * 100;
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

    private function getCodeMonnaieAffichage(): string
    {
        $strMonnaie = "";
        $monnaieAff = $this->getMonnaie_Affichage();
        if ($monnaieAff != null) {
            $strMonnaie = " " . $this->getMonnaie_Affichage()->getCode();
        }
        return $strMonnaie;
    }

    private function getMonnaie_Affichage()
    {
        $monnaie = $this->getMonnaie(MonnaieCrudController::TAB_MONNAIE_FONCTIONS[MonnaieCrudController::FONCTION_SAISIE_ET_AFFICHAGE]);
        if ($monnaie == null) {
            $monnaie = $this->getMonnaie(MonnaieCrudController::TAB_MONNAIE_FONCTIONS[MonnaieCrudController::FONCTION_AFFICHAGE_UNIQUEMENT]);
        }
        return $monnaie;
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
     * Get the value of taxes
     */
    public function getTaxes()
    {
        if ($this->getEntreprise()) {
            if ($this->getEntreprise()->getTaxes()) {
                $this->taxes = $this->getEntreprise()->getTaxes();
            }
        }
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
     * Get the value of partenaire
     */
    public function getPartenaire()
    {
        if ($this->getPiste()) {
            if ($this->getPiste()->getPartenaire()) {
                $this->partenaire = $this->getPiste()->getPartenaire();
            }
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
     * Get the value of taxeCourtier
     */
    public function getTaxeCourtier()
    {
        if ($this->getEntreprise()) {
            if ($this->getEntreprise()->getTaxes()) {
                /** @var Taxe */
                foreach ($this->getEntreprise()->getTaxes() as $taxe) {
                    if ($taxe->isPayableparcourtier() == true) {
                        $this->taxeCourtier = $taxe;
                    }
                }
            }
        }
        return $this->taxeCourtier;
    }

    /**
     * Get the value of retroComPartenaire
     */
    public function getRetroComPartenaire()
    {
        $taux = 0;
        if ($this->getTauxretrocompartenaire() == 0) {
            $taux = $this->getPartenaire()->getPart();
        } else {
            $taux = $this->getTauxretrocompartenaire();
        }
        $this->retroComPartenaire = $this->getRevenuNetTotalPartageable() * $taux;
        return $this->retroComPartenaire;
    }

    /**
     * Get the value of taxeCourtierTotale
     */
    public function getTaxeCourtierTotale()
    {
        $tot = 0;
        if ($this->getRevenus()) {
            /** @var Revenu */
            foreach ($this->getRevenus() as $revenu) {
                if ($revenu->getTaxable() == RevenuCrudController::TAB_TAXABLE[RevenuCrudController::TAXABLE_OUI]) {
                    $tauxTaxe = 0;
                    if ($this->getPiste()->getProduit()->isIard()) {
                        $tauxTaxe = $this->getTaxeCourtier()->getTauxIARD();
                    } else {
                        $tauxTaxe = $this->getTaxeCourtier()->getTauxVIE();
                    }
                    $tot = $tot + ($revenu->calc_getRevenuFinal() * $tauxTaxe);
                }
            }
        }
        $this->taxeCourtierTotale = $tot * 100;
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
        $tot = 0;
        if ($this->getRevenus()) {
            /** @var Revenu */
            foreach ($this->getRevenus() as $revenu) {
                if ($revenu->getPartageable() == RevenuCrudController::TAB_PARTAGEABLE[RevenuCrudController::PARTAGEABLE_OUI]) {
                    $tot = $tot + $revenu->calc_getRevenuFinal();
                }
            }
        }
        $this->revenuTotalHTPartageable = $tot * 100;
        return $this->revenuTotalHTPartageable;
    }

    /**
     * Get the value of revenuNetTotal
     */
    public function getRevenuNetTotal()
    {
        $this->revenuNetTotal = $this->getRevenuTotalHT() - $this->getTaxeCourtierTotale();
        return $this->revenuNetTotal;
    }

    /**
     * Get the value of taxeCourtierTotalePartageable
     */
    public function getTaxeCourtierTotalePartageable()
    {
        $tot = 0;
        if ($this->getRevenus()) {
            /** @var Revenu */
            foreach ($this->getRevenus() as $revenu) {
                if ($revenu->getTaxable() == RevenuCrudController::TAB_TAXABLE[RevenuCrudController::TAXABLE_OUI]) {
                    if ($revenu->getPartageable() == RevenuCrudController::TAB_PARTAGEABLE[RevenuCrudController::PARTAGEABLE_OUI]) {
                        $tauxTaxe = 0;
                        if ($this->getPiste()->getProduit()->isIard()) {
                            $tauxTaxe = $this->getTaxeCourtier()->getTauxIARD();
                        } else {
                            $tauxTaxe = $this->getTaxeCourtier()->getTauxVIE();
                        }
                        $tot = $tot + ($revenu->calc_getRevenuFinal() * $tauxTaxe);
                    }
                }
            }
        }
        $this->taxeCourtierTotalePartageable = $tot * 100;
        return $this->taxeCourtierTotalePartageable;
    }

    /**
     * Get the value of revenuNetTotalPartageable
     */
    public function getRevenuNetTotalPartageable()
    {
        $this->revenuNetTotalPartageable = $this->getRevenuTotalHTPartageable() - $this->getTaxeCourtierTotalePartageable();
        return $this->revenuNetTotalPartageable;
    }

    /**
     * Get the value of taxeAssureur
     */
    public function getTaxeAssureur()
    {
        if ($this->getEntreprise()) {
            if ($this->getEntreprise()->getTaxes()) {
                /** @var Taxe */
                foreach ($this->getEntreprise()->getTaxes() as $taxe) {
                    if ($taxe->isPayableparcourtier() == false) {
                        $this->taxeAssureur = $taxe;
                    }
                }
            }
        }
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
     * Get the value of client
     */ 
    public function getClient()
    {
        if($this->getPiste()){
            $this->client = $this->getPiste()->getClient();
        }
        return $this->client;
    }

    /**
     * Get the value of gestionnaire
     */ 
    public function getGestionnaire()
    {
        if($this->getPiste()){
            $this->gestionnaire = $this->getPiste()->getGestionnaire();
        }
        return $this->gestionnaire;
    }
}
