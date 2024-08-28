<?php

namespace App\Entity;

use App\Entity\Client;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\RevenuRepository;
use App\Controller\Admin\RevenuCrudController;
use App\Service\RefactoringJS\Evenements\Sujet;
use Doctrine\Common\Collections\ArrayCollection;
use App\Service\RefactoringJS\Commandes\Commande;
use App\Controller\Admin\ChargementCrudController;
use App\Entity\Traits\TraitEcouteurEvenements;
use App\Service\RefactoringJS\Evenements\Evenement;
use App\Service\RefactoringJS\Evenements\Observateur;
use App\Service\RefactoringJS\AutresClasses\IndicateursJS;
use App\Service\RefactoringJS\Commandes\CommandeExecuteur;
use App\Service\RefactoringJS\Commandes\ComDetecterEvenementAttribut;
use App\Service\RefactoringJS\Commandes\Piste\CommandePisteNotifierEvenement;
use SebastianBergmann\Complexity\Calculator;

#[ORM\Entity(repositoryClass: RevenuRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Revenu implements IndicateursJS, Sujet, CommandeExecuteur
{
    use TraitEcouteurEvenements;

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
    private ?float $retrocommissionTotale = 0;
    private ?float $reserve = 0;

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

    public function __construct()
    {
        $this->listeObservateurs = new ArrayCollection();
    }

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
        $oldValue = $this->getType();
        $newValue = $type;
        $this->type = $type;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Type", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

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
        $oldValue = $this->getPartageable();
        $newValue = $partageable;
        $this->partageable = $partageable;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Partageable? (O/N)", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

        return $this;
    }

    public function getTaxable(): ?int
    {
        return $this->taxable;
    }

    public function setTaxable(int $taxable): self
    {
        $oldValue = $this->getTaxable();
        $newValue = $taxable;
        $this->taxable = $taxable;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Taxable? (O/N)", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

        return $this;
    }

    public function getBase(): ?int
    {
        return $this->base;
    }

    public function setBase(int $base): self
    {
        $oldValue = $this->getBase();
        $newValue = $base;
        $this->base = $base;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Base", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

        return $this;
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


    /**
     * Get the value of monnaie_Affichage
     */
    public function getMonnaie_Affichage()
    {
        $this->monnaie_Affichage = (new Calculateur())->setCotation($this->getCotation())->getMonnaie();
        return $this->monnaie_Affichage;
    }

    private function getCodeMonnaieAffichage(): string
    {
        $strMonnaie = "Um";
        $calc = new Calculateur();
        if($this->getCotation() != null){
            $calc->setCotation($this->getCotation());
            $strMonnaie = $calc->getCodeMonnaie();
        }
        // $strMonnaie = (new Calculateur())->setCotation($this->getCotation())->getCodeMonnaie();
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
        $oldValue = $this->isIsparttranche();
        $newValue = $isparttranche;
        $this->isparttranche = $isparttranche;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Par tranches? (O/N)", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

        return $this;
    }

    public function isIspartclient(): ?bool
    {
        return $this->ispartclient;
    }

    public function setIspartclient(?bool $ispartclient): self
    {
        $oldValue = $this->isIspartclient();
        $newValue = $ispartclient;
        $this->ispartclient = $ispartclient;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Payabale par client? (O/N)", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

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
        $data = (new Calculateur())
            ->setCotation($this->getCotation())
            ->getRevenufinaleHT($this);

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
        $oldValue = $this->getMontantFlat();
        $newValue = $montantFlat;
        $this->montantFlat = $montantFlat;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Montant flat", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

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
        $this->client = (new Calculateur())
            ->setCotation($this->getCotation())
            ->getClient();
        return $this->client;
    }

    /**
     * Get the value of police
     */
    public function getPolice()
    {
        $this->police = (new Calculateur())
            ->setCotation($this->getCotation())
            ->getPolice();
        return $this->police;
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
        $this->executer(new ComDetecterEvenementAttribut($this, "Echéance", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

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
        $this->executer(new ComDetecterEvenementAttribut($this, "Date d'opération", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

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
     * Get the value of taxeAssureur
     */
    public function getTaxeAssureur()
    {
        $this->taxeAssureur = (new Calculateur())
            ->getTaxePourAssureur(
                null,
                $this,
                null,
                null,
                null,
                Calculateur::Param_from_revenu
            );
        return $this->taxeAssureur;
    }

    /**
     * Get the value of taxeCourtier
     */
    public function getTaxeCourtier()
    {
        $this->taxeCourtier = (new Calculateur())
            ->getTaxePourCourtier(
                null,
                $this,
                null,
                null,
                null,
                Calculateur::Param_from_revenu
            );
        return $this->taxeCourtier;
    }

    /**
     * Get the value of revenuPure
     */
    public function getRevenuPure()
    {
        $this->revenuPure = (new Calculateur())
            ->getRevenuPure(
                null,
                $this,
                null,
                null,
                null,
                Calculateur::Param_from_revenu
            );
        return $this->revenuPure;
    }

    /**
     * Get the value of revenuTotale
     */
    public function getRevenuTotale()
    {
        $this->revenuTotale = (new Calculateur())
            ->getRevenuTotale(
                null,
                $this,
                null,
                null,
                null,
                Calculateur::Param_from_revenu
            );
        return $this->revenuTotale;
    }

    /**
     * Get the value of revenuNet
     */
    public function getRevenuNet()
    {
        $this->revenuNet = (new Calculateur())
            ->getRevenuNet(
                null,
                $this,
                null,
                null,
                null,
                Calculateur::Param_from_revenu
            );
        return $this->revenuNet;
    }

    /**
     * Get the value of retrocommissionTotale
     */
    public function getRetrocommissionTotale()
    {
        $this->retrocommissionTotale = (new Calculateur())
            ->getRetrocommissionTotale(
                null,
                $this,
                null,
                null,
                null,
                Calculateur::Param_from_revenu
            );
        return $this->retrocommissionTotale;
    }

    /**
     * Get the value of reserve
     */
    public function getReserve()
    {
        $this->reserve = (new Calculateur())
            ->getReserve(
                null,
                $this,
                null,
                null,
                null,
                Calculateur::Param_from_revenu
            );
        return $this->reserve;
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
        return round($this->getCotation()->getChargement(ChargementCrudController::TAB_TYPE_CHARGEMENT_ORDINAIRE[ChargementCrudController::TYPE_PRIME_NETTE]));
    }

    public function getIndicaRisqueAccessoires(): ?float
    {
        return round($this->getCotation()->getChargement(ChargementCrudController::TAB_TYPE_CHARGEMENT_ORDINAIRE[ChargementCrudController::TYPE_ACCESSOIRES]));
    }

    public function getIndicaRisqueTaxeRegulateur(): ?float
    {
        return round($this->getCotation()->getChargement(ChargementCrudController::TAB_TYPE_CHARGEMENT_ORDINAIRE[ChargementCrudController::TYPE_FRAIS_DE_SURVEILLANCE_ARCA]));
    }

    public function getIndicaRisqueTaxeAssureur(): ?float
    {
        return round($this->getCotation()->getChargement(ChargementCrudController::TAB_TYPE_CHARGEMENT_ORDINAIRE[ChargementCrudController::TYPE_TVA]));
    }

    public function getIndicaRisqueFronting(): ?float
    {
        return round($this->getCotation()->getChargement(ChargementCrudController::TAB_TYPE_CHARGEMENT_ORDINAIRE[ChargementCrudController::TYPE_FRONTING]));
    }

    public function getIndicaRevenuNet(?int $typeRevenu = null, ?int $partageable = null): ?float
    {
        $tot = 0;
        // On récupère d'abord la base des calculs ou la formule.
        $strBase = "";
        foreach (RevenuCrudController::TAB_BASE as $key => $value) {
            if ($value == $this->getBase()) {
                $strBase = $key;
            }
        }
        switch ($strBase) {
            case RevenuCrudController::BASE_PRIME_NETTE:
                $tot = ($this->getTaux() * $this->getIndicaRisquePrimeNette());
                break;
            case RevenuCrudController::BASE_FRONTING:
                $tot = ($this->getTaux() * $this->getIndicaRisqueFronting());
                break;
            case RevenuCrudController::BASE_MONTANT_FIXE:
                $tot = ($this->getMontantFlat() / 100);
                break;
            default:
                # code...
                break;
        }
        return round($tot);
    }

    public function getIndicaRevenuTaxeAssureur(?int $typeRevenu = null, ?int $partageable = null): ?float
    {
        $tot = 0;
        /** @var Client */
        $client = $this->getIndicaRisqueClient();
        /** @var Produit */
        $produit = $this->getIndicaRisque();

        if ($client->isExoneree() === false) {
            if ($this->getTaxable() === RevenuCrudController::TAB_TAXABLE[RevenuCrudController::TAXABLE_OUI]) {
                $tauxTaxe = ($produit->isIard() === true) ? $this->getCotation()->getTaxeAssureur()->getTauxIARD() : $this->getCotation()->getTaxeAssureur()->getTauxVIE();
                $tot = $tauxTaxe * $this->getIndicaRevenuNet();
            }
        }
        return round($tot);
    }

    public function getIndicaRevenuTaxeCourtier(?int $typeRevenu = null, ?int $partageable = null): ?float
    {
        $tot = 0;
        /** @var Client */
        $client = $this->getIndicaRisqueClient();
        /** @var Produit */
        $produit = $this->getIndicaRisque();

        if ($client->isExoneree() === false) {
            if ($this->getTaxable() === RevenuCrudController::TAB_TAXABLE[RevenuCrudController::TAXABLE_OUI]) {
                $tauxTaxe = ($produit->isIard() === true) ? $this->getCotation()->getTaxeCourtier()->getTauxIARD() : $this->getCotation()->getTaxeCourtier()->getTauxVIE();
                $tot = $tauxTaxe * $this->getIndicaRevenuNet();
            }
        }
        return round($tot);
    }

    public function getIndicaRevenuPartageable(?int $typeRevenu = null, ?int $partageable = null): ?float
    {
        return round($this->getIndicaRevenuNet() - $this->getIndicaRevenuTaxeCourtier());
    }

    public function getIndicaRevenuTotal(?int $typeRevenu = null, ?int $partageable = null): ?float
    {
        return round($this->getIndicaRevenuNet() + $this->getIndicaRevenuTaxeAssureur());
    }

    public function getIndicaPartenaire(): ?Partenaire
    {
        return $this->getCotation()->getPiste()->getPartenaire();
    }

    public function getIndicaPartenaireRetrocom(?int $typeRevenu = null): ?float
    {
        $tauxPartenaire = 0;
        if ($this->getCotation()->getTauxretrocompartenaire() != 0) {
            $tauxPartenaire = $this->getCotation()->getTauxretrocompartenaire();
        } else {
            $tauxPartenaire = $this->getCotation()->getPiste()->getPartenaire()->getPart();
        }
        return round($tauxPartenaire * $this->getIndicaRevenuPartageable());
    }

    public function getIndicaRevenuReserve(?int $typeRevenu = null): ?float
    {
        return round($this->getIndicaRevenuPartageable() - $this->getIndicaPartenaireRetrocom());
    }

    public function transfererObservateur(?Observateur $observateur)
    {
        // dd("Fonction non encore définie");
    }
}
