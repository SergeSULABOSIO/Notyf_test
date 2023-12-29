<?php

namespace App\Entity;

use App\Controller\Admin\ChargementCrudController;
use App\Controller\Admin\MonnaieCrudController;
use App\Controller\Admin\RevenuCrudController;
use App\Repository\RevenuRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RevenuRepository::class)]
class Revenu
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $type = null;

    #[ORM\ManyToOne]
    private ?Utilisateur $utilisateur = null;

    #[ORM\ManyToOne]
    private ?Entreprise $entreprise = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column]
    private ?int $partageable = null;

    #[ORM\Column]
    private ?int $taxable = null;

    #[ORM\Column]
    private ?int $base = null;

    #[ORM\Column]
    private ?float $taux = null;

    #[ORM\Column]
    private ?float $montantFlat = null;

    #[ORM\ManyToOne(inversedBy: 'revenus', cascade: ['remove', 'persist', 'refresh'])]
    private ?Cotation $cotation = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isparttranche = null;

    #[ORM\Column(nullable: true)]
    private ?bool $ispartclient = null;


    //Les Champs non mappés
    private ?string $description;
    private ?Monnaie $monnaie_Affichage;
    private ?float $revenuPure = null;
    private ?float $taxeCourtier = 0;
    private ?float $revenuNet = 0;
    private ?float $taxeAssureur = 0;
    private ?float $revenuTotale = 0;
    private ?float $retroCommission = 0;

    private ?bool $validated;
    private ?Client $client;
    private ?Police $police = null;
    private ?Assureur $assureur;
    private ?Produit $produit;
    private ?Partenaire $partenaire;
    private ?Partenaire $piste;

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

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

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

    public function getPartageable(): ?int
    {
        return $this->partageable;
    }

    public function setPartageable(int $partageable): self
    {
        $this->partageable = $partageable;

        return $this;
    }

    public function getTaxable(): ?int
    {
        return $this->taxable;
    }

    public function setTaxable(int $taxable): self
    {
        $this->taxable = $taxable;

        return $this;
    }

    public function getBase(): ?int
    {
        return $this->base;
    }

    public function setBase(int $base): self
    {
        $this->base = $base;

        return $this;
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

    public function getCotation(): ?Cotation
    {
        return $this->cotation;
    }

    public function setCotation(?Cotation $cotation): self
    {
        $this->cotation = $cotation;

        return $this;
    }


    /**
     * Get the value of monnaie_Affichage
     */
    public function getMonnaie_Affichage()
    {
        $this->monnaie_Affichage = (new Calculateur())->getMonnaie();
        return $this->monnaie_Affichage;
    }

    private function getCodeMonnaieAffichage(): string
    {
        $strMonnaie = (new Calculateur())->setCotation($this->getCotation())->getCodeMonnaie();
        return $strMonnaie;
    }

    public function __toString()
    {
        return $this->getDescription();
    }

    public function isIsparttranche(): ?bool
    {
        return $this->isparttranche;
    }

    public function setIsparttranche(bool $isparttranche): self
    {
        $this->isparttranche = $isparttranche;

        return $this;
    }

    public function isIspartclient(): ?bool
    {
        return $this->ispartclient;
    }

    public function setIspartclient(?bool $ispartclient): self
    {
        $this->ispartclient = $ispartclient;

        return $this;
    }

    /**
     * Get the value of description
     */
    public function getDescription()
    {
        $strMonnaie = $this->getCodeMonnaieAffichage();
        $strType = "";
        foreach (RevenuCrudController::TAB_TYPE as $key => $value) {
            if ($value == $this->type) {
                $strType = $key;
            }
        }

        //On calcul le revennu total
        $data = (new Calculateur())->setCotation($this->getCotation())->getComfinaleHT($this);

        $strRedevablePar = "";
        if ($this->isIspartclient() == true) {
            $strRedevablePar = "par le client";
            if ($this->getCotation()) {
                if ($this->getCotation()->getPiste()) {
                    if ($this->getCotation()->getPiste()->getClient()) {
                        $strRedevablePar = "par " . $this->getCotation()->getPiste()->getClient()->getNom();
                    }
                }
            }
        } else {
            $strRedevablePar = "par l'assureur";
            if ($this->getCotation()) {
                if ($this->getCotation()) {
                    if ($this->getCotation()->getAssureur()) {
                        $strRedevablePar = "par " . $this->getCotation()->getAssureur()->getNom();
                    }
                }
            }
        }

        //Décomposition en tranches
        $strTranches = ", payable " . $strRedevablePar . " en une tranche sans délai.";
        if ($this->isIsparttranche() == true) {
            if ($this->getCotation()) {
                if ($this->getCotation()->getTranches()) {
                    $tabTranches = $this->getCotation()->getTranches();
                    $portions = " ";
                    /** @var Tranche */
                    $i = 0;
                    foreach ($tabTranches as $tranche) {
                        $i = $i + 1;
                        $comTranche = (($tranche->getTaux() / 100) * $data[Calculateur::DATA_VALEUR]) * 100;
                        if ($i == 1) {
                            $portions =  number_format($comTranche, 2, ",", ".") . $strMonnaie;
                        } else {
                            if ($i == count($tabTranches)) {
                                $portions = $portions . " et " . $comTranche . $strMonnaie;
                            } else {
                                $portions = $portions . ", " . $comTranche . $strMonnaie;
                            }
                        }
                    }
                    $strTranches = ", payable " . $strRedevablePar . " en " . count($tabTranches) . " tranche(s) de " . $portions . " hors taxes.";
                }
            }
        }

        $strPartageable = " Non partageable.";
        if ($this->getCotation()) {
            if ($this->getCotation()->getPiste()) {
                if ($this->getCotation()->getPiste()) {
                    if ($this->getPartageable()) {
                        if ($this->getPartageable() == 1) {
                            $strPartageable = " Partageable avec " . $this->getCotation()->getPiste()->getPartenaire();
                        }
                    }
                }
            }
        }
        $this->description = $strType . " (" . $data[Calculateur::DATA_VALEUR] . ", soit " . $data[Calculateur::DATA_FORMULE] . ")" . $strTranches . $strPartageable;
        return $this->description;
    }

    /**
     * Get the value of montantFlat
     */
    public function getMontantFlat()
    {
        return $this->montantFlat;
    }

    /**
     * Set the value of montantFlat
     *
     * @return  self
     */
    public function setMontantFlat($montantFlat)
    {
        $this->montantFlat = $montantFlat;

        return $this;
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
     * Get the value of client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Get the value of police
     */
    public function getPolice()
    {
        $this->police = (new Calculateur())->setCotation($this->getCotation())->getPolice();
        return $this->police;
    }

    /**
     * Get the value of assureur
     */
    public function getAssureur()
    {
        $this->assureur = (new Calculateur())->setCotation($this->getCotation())->getAssureur();
        return $this->assureur;
    }

    /**
     * Get the value of produit
     */
    public function getProduit()
    {
        $this->produit = (new Calculateur())->setCotation($this->getCotation())->getProduit();
        return $this->produit;
    }

    /**
     * Get the value of partenaire
     */
    public function getPartenaire()
    {
        $this->partenaire = (new Calculateur())->setCotation($this->getCotation())->getPartenaire();
        return $this->partenaire;
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
        $this->piste = (new Calculateur())->setCotation($this->getCotation())->getPiste();
        return $this->piste;
    }

    /**
     * Get the value of revenuNet
     */ 
    public function getRevenuNet()
    {
        //On calcul le revennu total
        $this->revenuNet = (new Calculateur())->setCotation($this->getCotation())->getComfinaleHT_valeur($this);
        return $this->revenuNet;
    }

    /**
     * Get the value of taxeAssureur
     */ 
    public function getTaxeAssureur()
    {
        $this->taxeAssureur = (new Calculateur())->setCotation($this->getCotation())->getMontantTaxe($this, false);
        return $this->taxeAssureur;
    }

    /**
     * Get the value of taxeCourtier
     */ 
    public function getTaxeCourtier()
    {
        $this->taxeCourtier = (new Calculateur())->setCotation($this->getCotation())->getMontantTaxe($this, true);
        return $this->taxeCourtier;
    }

    /**
     * Get the value of revenuPure
     */ 
    public function getRevenuPure()
    {
        $this->revenuPure = (new Calculateur())->setCotation($this->getCotation())->getComPure($this);
        return $this->revenuPure;
    }

    /**
     * Get the value of revenuTotale
     */ 
    public function getRevenuTotale()
    {
        $this->revenuTotale = (new Calculateur())->setCotation($this->getCotation())->getComTTC($this);
        return $this->revenuTotale;
    }
}
