<?php

namespace App\Entity;

use App\Entity\Chargement;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\CotationRepository;
use App\Repository\ChargementRepository;
use Doctrine\Common\Collections\Collection;
use App\Controller\Admin\RevenuCrudController;
use App\Controller\Admin\MonnaieCrudController;
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
use DateTimeImmutable;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

#[ORM\Entity(repositoryClass: CotationRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Cotation implements IndicateursJS, Sujet, CommandeExecuteur
{
    use TraitEcouteurEvenements;

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
    private ?bool $validated = false;
    private ?string $status;

    //Les champs calculables automatiquement sur base des données existantes
    private ?float $primeTotale;
    //parties partageable et non partageable tous confondues
    private ?float $revenuPureTotal;
    private ?float $revenuNetTotal;
    private ?float $revenuTotalTTC;
    private ?float $taxeAssureurTotale;
    private ?float $taxeCourtierTotale;
    private ?float $retroComPartenaire;
    private ?float $reserve;
    //partie partageable
    private ?float $taxeCourtierTotalePartageable;
    private ?float $revenuNetTotalPartageable;

    #[ORM\Column]
    private ?float $tauxretrocompartenaire = 0;

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
        $this->listeObservateurs = new ArrayCollection();
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
        $oldValue = $this->getNom();
        $newValue = $nom;
        $this->nom = $nom;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Nom", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

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
        $oldValue = $this->getPiste();
        $newValue = $piste;
        $this->piste = $piste;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Piste", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));

        return $this;
    }

    public function getAssureur(): ?Assureur
    {
        return $this->assureur;
    }

    public function setAssureur(?Assureur $assureur): self
    {
        $oldValue = $this->getAssureur();
        $newValue = $assureur;
        $this->assureur = $assureur;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Assureur", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));

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
            $oldValue = null;
            $newValue = $revenu;
            $this->revenus->add($revenu);
            $revenu->setCotation($this);
            //Ecouteur d'action
            $this->executer(new ComDetecterEvenementAttribut($this, "Revenu", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
        }

        return $this;
    }

    public function removeRevenu(Revenu $revenu): self
    {
        if ($this->revenus->removeElement($revenu)) {
            // set the owning side to null (unless already changed)
            if ($revenu->getCotation() === $this) {
                $oldValue = $revenu;
                $newValue = null;
                $revenu->setCotation(null);
                //Ecouteur d'action
                $this->executer(new ComDetecterEvenementAttribut($this, "Revenu", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
            }
        }

        return $this;
    }

    public function getChargement($type): ?float
    {
        $tot = 0;
        /** @var Chargement */
        foreach ($this->getChargements() as $chargement) {
            if ($type === $chargement->getType()) {
                $tot = $tot + $chargement->getMontant();
            }
        }
        return round($tot);
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
        //C'est très important de lui fournir l'entreprise car il en aura besoin pour trouver la monnaie utilisée ici
        $chargement->setCreatedAt(new DateTimeImmutable("now"));
        $chargement->setUpdatedAt(new DateTimeImmutable("now"));
        $chargement->setEntreprise($this->getEntreprise());
        // dd("New Chargement", $chargement);

        if (!$this->chargements->contains($chargement)) {
            $oldValue = null;
            $newValue = $chargement;
            $this->chargements->add($chargement);
            $chargement->setCotation($this);
            // dd("Nouveau chargement", $chargement);
            //Ecouteur d'action
            $this->executer(new ComDetecterEvenementAttribut($this, "Chargement", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
        }

        return $this;
    }

    public function removeChargement(Chargement $chargement): self
    {
        if ($this->chargements->removeElement($chargement)) {
            // set the owning side to null (unless already changed)
            if ($chargement->getCotation() === $this) {
                $oldValue = null;
                $newValue = $chargement;
                $chargement->setCotation(null);
                //Ecouteur d'action
                $this->executer(new ComDetecterEvenementAttribut($this, "Chargement", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
            }
        }

        return $this;
    }

    /**
     * Get the value of primeTotale
     */
    public function getPrimeTotale()
    {

        $primeTotale = 0;
        /** @var Tranche */
        foreach ($this->getTranches() as $tranche) {
            $primeTotale += $tranche->getPrimeTotaleTranche();
        }
        $this->primeTotale = round($primeTotale);
        // dd("Cotation", $this, $this->primeTotale);
        return $this->primeTotale;
    }

    /**
     * Set the value of primeTotale
     *
     * @return  self
     */
    public function setPrimeTotale($primeTotale)
    {
        $oldValue = $this->getPrimeTotale();
        $newValue = $primeTotale;
        $this->primeTotale = $primeTotale;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Prime", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

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
            $oldValue = null;
            $newValue = $tranch;
            $this->tranches->add($tranch);
            $tranch->setCotation($this);
            //Ecouteur d'action
            $this->executer(new ComDetecterEvenementAttribut($this, "Tranche", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
        }

        return $this;
    }

    public function removeTranch(Tranche $tranch): self
    {
        if ($this->tranches->removeElement($tranch)) {
            // set the owning side to null (unless already changed)
            if ($tranch->getCotation() === $this) {
                $oldValue = $tranch;
                $newValue = null;
                $tranch->setCotation(null);
                //Ecouteur d'action
                $this->executer(new ComDetecterEvenementAttribut($this, "Tranche", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
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
        $oldValue = $this->getDureeCouverture();
        $newValue = $dureeCouverture;
        $this->dureeCouverture = $dureeCouverture;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Duré de couverture", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

        return $this;
    }

    public function isValidated(): ?bool
    {
        //On ne tolère pas le secteur vide
        if ($this->validated == null) {
            $this->validated = false;
        }
        return $this->validated;
    }

    public function setValidated(bool $validated): self
    {
        $oldValue = $this->isValidated();
        $newValue = $validated;
        $this->validated = $validated;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Validé?", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

        return $this;
    }

    /**
     * Get the value of taxes
     */
    public function getTaxes()
    {
        $this->taxes = (new Calculateur())
            ->setCotation($this)
            ->getTaxes();
        return $this->taxes;
    }

    /**
     * Set the value of taxes
     *
     * @return  self
     */
    public function setTaxes($taxes)
    {
        $oldValue = null;
        $newValue = $taxes;
        $this->taxes = $taxes;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Taxe", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

        return $this;
    }


    /**
     * Get the value of taxeCourtier
     */
    public function getTaxeCourtier()
    {
        $this->taxeCourtier = (new Calculateur())
            ->setCotation($this)
            ->getTaxeCourtier();
        return $this->taxeCourtier;
    }

    /**
     * Get the value of retroComPartenaire
     */
    public function getRetroComPartenaire()
    {
        $this->retroComPartenaire = (new Calculateur())
            ->getRetrocommissionTotale(
                null,
                null,
                null,
                $this,
                null,
                Calculateur::Param_from_cotation
            ) * 100;
        return $this->retroComPartenaire;
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
                null,
                $this,
                null,
                Calculateur::Param_from_cotation
            ) * 100;
        return $this->taxeCourtierTotale;
    }

    public function getTauxretrocompartenaire(): ?float
    {
        if ($this->tauxretrocompartenaire == null) {
            $this->tauxretrocompartenaire = 0;
        }
        return $this->tauxretrocompartenaire;
    }

    public function setTauxretrocompartenaire(?float $tauxretrocompartenaire): self
    {
        $oldValue = $this->getTauxretrocompartenaire();
        $newValue = $tauxretrocompartenaire;
        $this->tauxretrocompartenaire = $tauxretrocompartenaire;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Taux retrocom", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

        return $this;
    }

    /**
     * Get the value of revenuNetTotal
     */
    public function getRevenuNetTotal()
    {
        $this->revenuNetTotal = (new Calculateur())
            ->getRevenuNet(
                null,
                null,
                null,
                $this,
                null,
                Calculateur::Param_from_cotation
            ) * 100;
        return $this->revenuNetTotal;
    }

    /**
     * Get the value of taxeCourtierTotalePartageable
     */
    public function getTaxeCourtierTotalePartageable()
    {
        $this->taxeCourtierTotalePartageable = (new Calculateur())
            ->getTaxePourCourtier(
                null,
                null,
                null,
                $this,
                true,
                Calculateur::Param_from_cotation
            ) * 100;
        return $this->taxeCourtierTotalePartageable;
    }

    /**
     * Get the value of revenuNetTotalPartageable
     */
    public function getRevenuNetTotalPartageable()
    {
        $this->revenuNetTotalPartageable = (new Calculateur())
            ->getRevenuPure(
                null,
                null,
                null,
                $this,
                true,
                Calculateur::Param_from_cotation
            ) * 100;
        return $this->revenuNetTotalPartageable;
    }

    /**
     * Get the value of taxeAssureur
     */
    public function getTaxeAssureur()
    {
        $this->taxeAssureur = (new Calculateur())
            ->setCotation($this)
            ->getTaxeAssureur();
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
            $oldValue = null;
            $newValue = $police;
            $this->polices->add($police);
            $police->setCotation($this);
            //Ecouteur d'action
            $this->executer(new ComDetecterEvenementAttribut($this, "Police", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
        }

        return $this;
    }

    public function removePolice(Police $police): self
    {
        if ($this->polices->removeElement($police)) {
            // set the owning side to null (unless already changed)
            if ($police->getCotation() === $this) {
                $oldValue = $police;
                $newValue = null;
                $police->setCotation(null);
                //Ecouteur d'action
                $this->executer(new ComDetecterEvenementAttribut($this, "Police", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
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
            $oldValue = null;
            $newValue = $document;
            $this->documents->add($document);
            $document->setCotation($this);
            //Ecouteur d'action
            $this->executer(new ComDetecterEvenementAttribut($this, "Document", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
        }

        return $this;
    }

    public function removeDocument(DocPiece $document): self
    {
        if ($this->documents->removeElement($document)) {
            // set the owning side to null (unless already changed)
            if ($document->getCotation() === $this) {
                $oldValue = $document;
                $newValue = null;
                $document->setCotation(null);
                //Ecouteur d'action
                $this->executer(new ComDetecterEvenementAttribut($this, "Document", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
            }
        }
        return $this;
    }

    /**
     * Get the value of monnaie_Affichage
     */
    public function getMonnaie_Affichage()
    {
        $this->monnaie_Affichage = (new Calculateur())
            ->setCotation($this)
            ->getMonnaie();
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
        $oldValue = $this->getPolice();
        $newValue = $police;
        $this->police = $police;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Police", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));

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
        $oldValue = $this->getPartenaire();
        $newValue = $partenaire;
        $this->partenaire = $partenaire;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Partenaire", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));

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
        $oldValue = $this->getClient();
        $newValue = $client;
        $this->client = $client;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Client", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));

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
        $oldValue = $this->getProduit();
        $newValue = $produit;
        $this->produit = $produit;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Produit", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));

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
        $this->executer(new ComDetecterEvenementAttribut($this, "Date d'édition", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

        return $this;
    }

    /**
     * Get the value of taxeAssureurTotal
     */
    public function getTaxeAssureurTotale()
    {
        $this->taxeAssureurTotale = (new Calculateur())
            ->getTaxePourAssureur(
                null,
                null,
                null,
                $this,
                null,
                Calculateur::Param_from_cotation
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
        $oldValue = $this->getGestionnaire();
        $newValue = $gestionnaire;
        $this->gestionnaire = $gestionnaire;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Gestionnaire de compte", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));

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
        $oldValue = $this->getAssistant();
        $newValue = $assistant;
        $this->assistant = $assistant;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Assistant", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));

        return $this;
    }

    /**
     * Get the value of revenuTotalTTC
     */
    public function getRevenuTotalTTC()
    {
        $this->revenuTotalTTC = (new Calculateur())
            ->getRevenuTotale(
                null,
                null,
                null,
                $this,
                null,
                Calculateur::Param_from_cotation
            ) * 100;
        return $this->revenuTotalTTC;
    }

    /**
     * Get the value of revenuPureTotal
     */
    public function getRevenuPureTotal()
    {
        $this->revenuPureTotal = (new Calculateur())
            ->getRevenuPure(
                null,
                null,
                null,
                $this,
                null,
                Calculateur::Param_from_cotation
            ) * 100;
        return $this->revenuPureTotal;
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
                null,
                $this,
                null,
                Calculateur::Param_from_cotation
            ) * 100;
        return $this->reserve;
    }


    /**
     * Les fonctions de l'interface
     */
    public function getIndicaRisquePolice(): ?Police
    {
        return $this->getPolice();
    }

    public function getIndicaRisqueCotation(): ?Cotation
    {
        return $this;
    }

    public function getIndicaRisqueClient(): ?Client
    {
        return $this->getPiste()->getClient();
    }

    public function getIndicaRisqueAssureur(): ?Assureur
    {
        return $this->getPiste()->getAssureur();
    }

    public function getIndicaRisque(): ?Produit
    {
        return $this->getPiste()->getProduit();
    }

    public function getIndicaRisqueContacts(): ?ArrayCollection
    {
        return $this->getPiste()->getContacts();
    }

    public function getIndicaRisqueReferencePolice(): ?string
    {
        return $this->getPolice()->getReference();
    }

    public function getIndicaRisquePrimeReassurance(): ?float
    {
        return 0;
    }

    public function getIndicaRisquePrimeTotale(): ?float
    {
        return $this->getPrimeTotale();
    }

    public function getIndicaRisquePrimeNette(): ?float
    {
        return round($this->getChargement(ChargementCrudController::TAB_TYPE_CHARGEMENT_ORDINAIRE[ChargementCrudController::TYPE_PRIME_NETTE]));
    }

    public function getIndicaRisqueAccessoires(): ?float
    {
        return round($this->getChargement(ChargementCrudController::TAB_TYPE_CHARGEMENT_ORDINAIRE[ChargementCrudController::TYPE_ACCESSOIRES]));
    }

    public function getIndicaRisqueTaxeRegulateur(): ?float
    {
        return round($this->getChargement(ChargementCrudController::TAB_TYPE_CHARGEMENT_ORDINAIRE[ChargementCrudController::TYPE_FRAIS_DE_SURVEILLANCE_ARCA]));
    }

    public function getIndicaRisqueTaxeAssureur(): ?float
    {
        return round($this->getChargement(ChargementCrudController::TAB_TYPE_CHARGEMENT_ORDINAIRE[ChargementCrudController::TYPE_TVA]));
    }

    public function getIndicaRisqueFronting(): ?float
    {
        return round($this->getChargement(ChargementCrudController::TAB_TYPE_CHARGEMENT_ORDINAIRE[ChargementCrudController::TYPE_FRONTING]));
    }

    public function getIndicaRevenuNet(?int $typeRevenu = null, ?int $partageable = null): ?float
    {
        $tot = 0;
        /** @var Revenu */
        foreach ($this->getRevenus() as $revenu) {
            if ($typeRevenu !== null) {
                if ($typeRevenu === $revenu->getType()) {
                    if ($partageable !== null) {
                        if ($partageable === $revenu->getPartageable()) {
                            $tot = $tot + $revenu->getIndicaRevenuNet();
                        }
                    } else {
                        $tot = $tot + $revenu->getIndicaRevenuNet();
                    }
                }
            } else {
                if ($partageable !== null) {
                    if ($partageable === $revenu->getPartageable()) {
                        $tot = $tot + $revenu->getIndicaRevenuNet();
                    }
                } else {
                    $tot = $tot + $revenu->getIndicaRevenuNet();
                }
            }
        }
        // return round($tot);
        return round($tot);
    }

    public function getIndicaRevenuTaxeAssureur(?int $typeRevenu = null, ?int $partageable = null): ?float
    {
        $tot = 0;
        /** @var Client */
        $client = $this->getIndicaRisqueClient();
        /** @var Produit */
        $produit = $this->getIndicaRisque();

        $tot = 0;
        /** @var Revenu */
        foreach ($this->getRevenus() as $revenu) {
            if ($typeRevenu !== null) {
                if ($typeRevenu === $revenu->getType()) {
                    if ($client->isExoneree() === false) {
                        if ($revenu->getTaxable() === RevenuCrudController::TAB_TAXABLE[RevenuCrudController::TAXABLE_OUI]) {
                            $tauxTaxe = ($produit->isIard() === true) ? $this->getTaxeAssureur()->getTauxIARD() : $this->getTaxeAssureur()->getTauxVIE();
                            $tot = $tauxTaxe * $this->getIndicaRevenuNet($typeRevenu, $partageable);
                        }
                    }
                }
            } else {
                if ($client->isExoneree() === false) {
                    if ($revenu->getTaxable() === RevenuCrudController::TAB_TAXABLE[RevenuCrudController::TAXABLE_OUI]) {
                        $tauxTaxe = ($produit->isIard() === true) ? $this->getTaxeAssureur()->getTauxIARD() : $this->getTaxeAssureur()->getTauxVIE();
                        $tot = $tauxTaxe * $this->getIndicaRevenuNet($typeRevenu, $partageable);
                    }
                }
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

        $tot = 0;
        /** @var Revenu */
        foreach ($this->getRevenus() as $revenu) {
            if ($typeRevenu !== null) {
                if ($typeRevenu === $revenu->getType()) {
                    if ($client->isExoneree() === false) {
                        if ($revenu->getTaxable() === RevenuCrudController::TAB_TAXABLE[RevenuCrudController::TAXABLE_OUI]) {
                            $tauxTaxe = ($produit->isIard() === true) ? $this->getTaxeCourtier()->getTauxIARD() : $this->getTaxeCourtier()->getTauxVIE();
                            //On vérifie la partageabilité
                            if ($partageable !== null) {
                                if ($partageable === RevenuCrudController::TAB_PARTAGEABLE[RevenuCrudController::PARTAGEABLE_OUI]) {
                                    $tot = $tauxTaxe * $this->getIndicaRevenuNet($typeRevenu, $partageable);
                                }
                            } else {
                                $tot = $tauxTaxe * $this->getIndicaRevenuNet($typeRevenu, $partageable);
                            }
                        }
                    }
                }
            } else {
                if ($client->isExoneree() === false) {
                    if ($revenu->getTaxable() === RevenuCrudController::TAB_TAXABLE[RevenuCrudController::TAXABLE_OUI]) {
                        $tauxTaxe = ($produit->isIard() === true) ? $this->getTaxeCourtier()->getTauxIARD() : $this->getTaxeCourtier()->getTauxVIE();
                        //On vérifie la partageabilité
                        if ($partageable !== null) {
                            if ($partageable === RevenuCrudController::TAB_PARTAGEABLE[RevenuCrudController::PARTAGEABLE_OUI]) {
                                $tot = $tauxTaxe * $this->getIndicaRevenuNet($typeRevenu, $partageable);
                            }
                        } else {
                            $tot = $tauxTaxe * $this->getIndicaRevenuNet($typeRevenu, $partageable);
                        }
                    }
                }
            }
        }
        return round($tot);
    }

    public function getIndicaRevenuPartageable(?int $typeRevenu = null, ?int $partageable = null): ?float
    {
        $partageable = RevenuCrudController::TAB_PARTAGEABLE[RevenuCrudController::PARTAGEABLE_OUI];
        return round($this->getIndicaRevenuTotal($typeRevenu, $partageable) - $this->getIndicaRevenuTaxeCourtier($typeRevenu, $partageable));
    }

    public function getIndicaRevenuTotal(?int $typeRevenu = null, ?int $partageable = null): ?float
    {
        return round($this->getIndicaRevenuNet($typeRevenu, $partageable) + $this->getIndicaRevenuTaxeAssureur($typeRevenu, $partageable));
    }

    public function getIndicaPartenaire(): ?Partenaire
    {
        return $this->getPiste()->getPartenaire();
    }

    public function getIndicaPartenaireRetrocom(?int $typeRevenu = null): ?float
    {
        $tauxPartenaire = 0;
        if ($this->getTauxretrocompartenaire() != 0) {
            $tauxPartenaire = $this->getTauxretrocompartenaire();
        } else {
            $tauxPartenaire = $this->getPiste()->getPartenaire()->getPart();
        }
        $partageable_oui = RevenuCrudController::TAB_PARTAGEABLE[RevenuCrudController::PARTAGEABLE_OUI];
        $revPartageable = $this->getIndicaRevenuNet($typeRevenu, $partageable_oui) - $this->getIndicaRevenuTaxeCourtier($typeRevenu, $partageable_oui);
        return round($tauxPartenaire * $revPartageable);
    }

    public function getIndicaRevenuReserve(?int $typeRevenu = null): ?float
    {
        return round($this->getIndicaRevenuPartageable($typeRevenu) - $this->getIndicaPartenaireRetrocom($typeRevenu));
    }

    public function transfererObservateur(?Observateur $observateur)
    {
        //Transfère de l'observateur chez Chargement
        if (count($this->getChargements()) != 0) {
            foreach ($this->getChargements() as $chargement) {
                $chargement->ajouterObservateur($observateur);
            }
        }
    }
}
