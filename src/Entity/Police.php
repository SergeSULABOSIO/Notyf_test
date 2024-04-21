<?php

namespace App\Entity;

use DateInterval;
use App\Entity\Cotation;
use App\Entity\Utilisateur;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\PoliceRepository;
use Doctrine\Common\Collections\Collection;
use App\Controller\Admin\PoliceCrudController;
use App\Service\RefactoringJS\Evenements\Sujet;
use Doctrine\Common\Collections\ArrayCollection;
use App\Controller\Admin\ChargementCrudController;
use App\Entity\Traits\TraitEcouteurEvenements;
use App\Service\RefactoringJS\Evenements\Evenement;
use App\Service\RefactoringJS\Evenements\Observateur;
use Symfony\Component\Validator\Constraints as Assert;
use App\Service\RefactoringJS\AutresClasses\IndicateursJS;
use App\Service\RefactoringJS\Commandes\CommandeExecuteur;
use App\Service\RefactoringJS\AutresClasses\JSAbstractFinances;
use App\Service\RefactoringJS\Commandes\ComDetecterEvenementAttribut;

#[ORM\Entity(repositoryClass: PoliceRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Police extends JSAbstractFinances implements Sujet, IndicateursJS, CommandeExecuteur
{
    use TraitEcouteurEvenements;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank(message: "Veuillez fournir la référence de la police.")]
    #[ORM\Column(length: 255)]
    private ?string $reference = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Entreprise $entreprise = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne]
    private ?Utilisateur $utilisateur = null;

    #[ORM\ManyToOne(inversedBy: 'polices', cascade: ['remove', 'persist', 'refresh'])]
    private ?Piste $piste = null;

    #[ORM\OneToOne] //(cascade: ['persist', 'remove'])
    private ?Cotation $cotation = null;

    #[ORM\Column]
    private ?int $idAvenant = null;

    //Champs calculés sur base des données existantes dans la base
    private ?Utilisateur $gestionnaire = null;
    private ?Utilisateur $assistant = null;
    private ?string $typeavenant = null;
    private ?Collection $chargements = null;
    private ?float $primeTotale;
    private ?float $primeNetteTotale;
    private ?Collection $tranches = null;
    private ?Collection $revenus = null;
    private ?float $commissionTotaleHT;
    private ?float $taxeAssureur;
    private ?float $commissionTotaleTTC;
    private ?float $revenuNetTotal;
    private ?float $taxeCourtierTotale;
    private ?Produit $produit = null;
    private ?Client $client = null;
    private ?Partenaire $partenaire = null;
    private ?Assureur $assureur = null;
    private Collection $contacts;
    private ?float $tauxretrocompartenaire = 0;

    //partie partageable / retrocom
    private ?float $revenuTotalHTPartageable;
    private ?float $taxeCourtierTotalePartageable;
    private ?float $revenuNetTotalPartageable;
    private ?float $retroComPartenaire;
    private ?float $reserve;


    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $dateoperation = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $dateemission = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $dateeffet = null;

    //#[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $dateexpiration = null;

    #[ORM\OneToMany(mappedBy: 'police', targetEntity: DocPiece::class, cascade: ['remove', 'persist', 'refresh'])]
    private Collection $documents;

    public function __construct()
    {
        $this->documents = new ArrayCollection();
        $this->listeObservateurs = new ArrayCollection();
    }

    public function getMontantInvoicedPerDestination(?int $destination)
    {
        return 0;
    }

    public function getMontantInvoicedPerTypeNote(?int $typeNote)
    {
        return 0;
    }

    public function getMontantReceivedPerDestination(?int $destination)
    {
        return 0;
    }

    public function getMontantReceivedPerTypeNote(?int $typeNote)
    {
        return 0;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): self
    {
        $oldValue = $this->getReference();
        $newValue = $reference;
        $this->reference = $reference;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Référence", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

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


    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): self
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    public function getGestionnaire(): ?Utilisateur
    {
        if ($this->getPiste()) {
            $this->gestionnaire = $this->getPiste()->getGestionnaire();
        }
        //dd($this->gestionnaire);
        return $this->gestionnaire;
    }

    public function setGestionnaire(?Utilisateur $gestionnaire): self
    {
        $oldValue = $this->getGestionnaire();
        $newValue = $gestionnaire;
        $this->gestionnaire = $gestionnaire;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Gestionnaire de compte", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));

        return $this;
    }

    public function initEntreprise(): ?Entreprise
    {
        return $this->getEntreprise();
    }

    public function __toString()
    {
        $strAutreData = "";
        if ($this->getCotation()) {
            $strAutreData = $this->getCotation()->getAssureur()->getNom();
        }
        $txtPrime = "";
        if ($this->getMonnaie_Affichage()) {
            $txtPrime = " | Prime totale: " . $this->getMontantEnMonnaieAffichage($this->getPrimeTotale());
        }
        return "Réf.: " . $this->getReference() . " / " . $strAutreData . $txtPrime; // . " / " . $this->getPrimetotale()/100 . " / " . $this->client->getNom() . " / " . $this->getAssureur()->getNom() . " / " . $this->getProduit()->getNom();
    }


    /**
     * Get the value of assistant
     */
    public function getAssistant()
    {
        if ($this->getPiste()) {
            $this->assistant = $this->getPiste()->getAssistant();
        }
        return $this->assistant;
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

    public function getDateexpiration(): ?\DateTimeInterface
    {
        // if ($this->dateexpiration == null) {
        //     $duree = $this->convertDuree($this->getCotation()->getDureeCouverture());
        //     $this->dateexpiration = $this->ajouterJours($this->getDateeffet(), $duree);
        // }
        if ($this->getCotation()) {
            $this->dateexpiration = $this->getDateeffet()->add(new DateInterval("P" . ($this->getCotation()->getDureeCouverture()) . "M"));
            $this->dateexpiration = $this->dateexpiration->modify("-1 day");
        }
        return $this->dateexpiration;
    }

    public function setDateexpiration(\DateTimeInterface $dateexpiration): self
    {
        $oldValue = $this->getDateexpiration();
        $newValue = $dateexpiration;
        $this->dateexpiration = $dateexpiration;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Echéance", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

        return $this;
    }

    public function getIdAvenant(): ?int
    {
        return $this->idAvenant;
    }

    public function setIdAvenant(int $idAvenant): self
    {
        $oldValue = $this->getIdAvenant();
        $newValue = $idAvenant;
        $this->idAvenant = $idAvenant;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Avenant", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

        return $this;
    }

    /**
     * Get the value of typeavenant
     */
    public function getTypeavenant()
    {
        if ($this->getPiste()) {
            foreach (PoliceCrudController::TAB_POLICE_TYPE_AVENANT as $key => $value) {
                if ($value == $this->typeavenant) {
                    $this->typeavenant = $key;
                    break;
                }
            }
        }
        return $this->typeavenant . " (" . $this->getIdAvenant() . ")";
    }

    /**
     * Get the value of chargements
     */
    public function getChargements()
    {
        if ($this->getCotation()) {
            if ($this->getCotation()->isValidated()) {
                $this->chargements = $this->getCotation()->getChargements();
            }
        }
        //dd($quote->isValidated());
        return $this->chargements;
    }

    /**
     * Get the value of primeTotale
     */
    public function getPrimeTotale()
    {
        if ($this->getCotation()) {
            $this->primeTotale = $this->getCotation()->getPrimeTotale();
        } else {
            $this->primeTotale = 0;
        }
        return $this->primeTotale;
    }

    /**
     * Get the value of tranches
     */
    public function getTranches()
    {
        if ($this->getCotation()) {
            if ($this->getCotation()->isValidated()) {
                $this->tranches = $this->getCotation()->getTranches();
            }
        }
        return $this->tranches;
    }


    /**
     * Get the value of commissionTotaleHT
     */
    public function getCommissionTotaleHT()
    {
        if ($this->getCotation()) {
            if ($this->getCotation()->isValidated()) {
                $this->commissionTotaleHT = $this->getCotation()->getRevenuNetTotal();
            }
        }
        return $this->commissionTotaleHT;
    }

    /**
     * Get the value of revenus
     */
    public function getRevenus()
    {
        if ($this->getCotation()) {
            if ($this->getCotation()->isValidated()) {
                $this->revenus = $this->getCotation()->getRevenus();
            }
        }
        return $this->revenus;
    }

    /**
     * Get the value of taxeAssureur
     */
    public function getTaxeAssureur()
    {
        if ($this->getEntreprise()) {
            foreach ($this->getEntreprise()->getTaxes() as $taxe) {
                if ($taxe->isPayableparcourtier() == false) {
                    if ($this->getPiste()->getClient()->isExoneree()) {
                        $this->taxeAssureur = (0 * $this->getCommissionTotaleHT()) / 100;
                        break;
                    } else {
                        if ($this->getPiste()->getProduit()->isIard()) {
                            $this->taxeAssureur = ($taxe->getTauxIARD() * $this->getCommissionTotaleHT()) / 100;
                            break;
                        } else {
                            $this->taxeAssureur = ($taxe->getTauxVIE() * $this->getCommissionTotaleHT()) / 100;
                            break;
                        }
                    }
                }
            }
        }
        return $this->taxeAssureur * 100;
    }

    /**
     * Get the value of commissionTotaleTTC
     */
    public function getCommissionTotaleTTC()
    {
        $this->commissionTotaleTTC = $this->getTaxeAssureur() + $this->getCommissionTotaleHT();
        return $this->commissionTotaleTTC;
    }

    /**
     * Get the value of revenuNetTotal
     */
    public function getRevenuNetTotal()
    {
        if ($this->getCotation()) {
            $this->revenuNetTotal = $this->getCotation()->getRevenuNetTotal();
        }
        return $this->revenuNetTotal;
    }

    /**
     * Get the value of taxeCourtierTotale
     */
    public function getTaxeCourtierTotale()
    {
        if ($this->getCotation()) {
            $this->taxeCourtierTotale = $this->getCotation()->getTaxeCourtierTotale();
        }
        return $this->taxeCourtierTotale;
    }

    /**
     * Get the value of produit
     */
    public function getProduit()
    {
        if ($this->getPiste()) {
            $this->produit = $this->getPiste()->getProduit();
        }
        return $this->produit;
    }

    /**
     * Get the value of client
     */
    public function getClient()
    {
        if ($this->getPiste()) {
            $this->client = $this->getPiste()->getClient();
        }
        return $this->client;
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
     * Get the value of contacts
     */
    public function getContacts()
    {
        if ($this->getPiste()) {
            $this->contacts = $this->getPiste()->getContacts();
        }
        return $this->contacts;
    }

    /**
     * Get the value of tauxretrocompartenaire
     */
    public function getTauxretrocompartenaire()
    {
        if ($this->getCotation()) {
            $this->tauxretrocompartenaire = $this->getCotation()->getTauxretrocompartenaire();
        }
        return $this->tauxretrocompartenaire;
    }

    /**
     * Get the value of revenuTotalHTPartageable
     */
    public function getRevenuTotalHTPartageable()
    {
        if ($this->getCotation()) {
            $this->revenuTotalHTPartageable = $this->getCotation()->getRevenuNetTotalPartageable();
        }
        return $this->revenuTotalHTPartageable;
    }

    /**
     * Get the value of taxeCourtierTotalePartageable
     */
    public function getTaxeCourtierTotalePartageable()
    {
        if ($this->getCotation()) {
            $this->taxeCourtierTotalePartageable = $this->getCotation()->getTaxeCourtierTotalePartageable();
        }
        return $this->taxeCourtierTotalePartageable * -1;
    }

    /**
     * Get the value of revenuNetTotalPartageable
     */
    public function getRevenuNetTotalPartageable()
    {
        if ($this->getCotation()) {
            $this->revenuNetTotalPartageable = $this->getCotation()->getRevenuNetTotalPartageable();
        }
        return $this->revenuNetTotalPartageable;
    }

    /**
     * Get the value of retroComPartenaire
     */
    public function getRetroComPartenaire()
    {
        if ($this->getCotation()) {
            $this->retroComPartenaire = $this->getCotation()->getRetroComPartenaire();
        }
        return $this->retroComPartenaire;
    }

    public function getDateoperation(): ?\DateTimeImmutable
    {
        return $this->dateoperation;
    }

    public function setDateoperation(?\DateTimeImmutable $dateoperation): self
    {
        $this->dateoperation = $dateoperation;

        return $this;
    }

    public function getDateemission(): ?\DateTimeImmutable
    {
        return $this->dateemission;
    }

    public function setDateemission(?\DateTimeImmutable $dateemission): self
    {
        $this->dateemission = $dateemission;

        return $this;
    }

    public function getDateeffet(): ?\DateTimeImmutable
    {
        return $this->dateeffet;
    }

    public function setDateeffet(?\DateTimeImmutable $dateeffet): self
    {
        $oldValue = $this->getDateeffet();
        $newValue = $dateeffet;
        $this->dateeffet = $dateeffet;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Date d'effet", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

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
            $document->setPolice($this);
            //Ecouteur d'action
            $this->executer(new ComDetecterEvenementAttribut($this, "Document", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
        }

        return $this;
    }

    public function removeDocument(DocPiece $document): self
    {
        if ($this->documents->removeElement($document)) {
            // set the owning side to null (unless already changed)
            if ($document->getPolice() === $this) {
                $oldValue = $document;
                $newValue = null;
                $document->setPolice(null);
                //Ecouteur d'action
                $this->executer(new ComDetecterEvenementAttribut($this, "Document", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
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
                null,
                $this->getCotation(),
                null,
                Calculateur::Param_from_cotation
            ) * 100;
        return $this->reserve;
    }

    public function getPrimeNetteTotale(): ?float
    {
        $typeRecherche = ChargementCrudController::TAB_TYPE_CHARGEMENT_ORDINAIRE[ChargementCrudController::TYPE_PRIME_NETTE];
        $this->primeNetteTotale = 0;
        // dd("Les chargements de la police:", $this->getChargements());
        /** @var Chargement */
        foreach ($this->getChargements() as $chargement) {
            if ($chargement->getType() == $typeRecherche) {
                $this->primeNetteTotale = $this->primeNetteTotale + $chargement->getMontant();
            }
        }
        return round($this->primeNetteTotale);
    }





    /**
     * Les fonctions de l'interface
     */
    public function getIndicaRisquePolice(): ?Police
    {
        return $this;
    }

    public function getIndicaRisqueCotation(): ?Cotation
    {
        return $this->getCotation()->getIndicaRisqueCotation();
    }

    public function getIndicaRisqueClient(): ?Client
    {
        return $this->getCotation()->getIndicaRisqueClient();
    }

    public function getIndicaRisqueAssureur(): ?Assureur
    {
        return $this->getCotation()->getIndicaRisqueAssureur();
    }

    public function getIndicaRisque(): ?Produit
    {
        return $this->getCotation()->getIndicaRisque();
    }

    public function getIndicaRisqueContacts(): ?ArrayCollection
    {
        return $this->getCotation()->getIndicaRisqueContacts();
    }

    public function getIndicaRisqueReferencePolice(): ?string
    {
        return $this->getCotation()->getIndicaRisqueReferencePolice();
    }

    public function getIndicaRisquePrimeReassurance(): ?float
    {
        return $this->getCotation()->getIndicaRisquePrimeReassurance();
    }

    public function getIndicaRisquePrimeTotale(): ?float
    {
        return $this->getCotation()->getIndicaRisquePrimeNette();
    }

    public function getIndicaRisquePrimeNette(): ?float
    {
        return $this->getCotation()->getIndicaRisquePrimeNette();
    }

    public function getIndicaRisqueAccessoires(): ?float
    {
        return $this->getCotation()->getIndicaRisqueAccessoires();
    }

    public function getIndicaRisqueTaxeRegulateur(): ?float
    {
        return $this->getCotation()->getIndicaRisqueTaxeRegulateur();
    }

    public function getIndicaRisqueTaxeAssureur(): ?float
    {
        return $this->getCotation()->getIndicaRevenuTaxeAssureur();
    }

    public function getIndicaRisqueFronting(): ?float
    {
        return $this->getCotation()->getIndicaRisqueFronting();
    }

    public function getIndicaRevenuNet(?int $typeRevenu = null, ?int $patageable = null): ?float
    {
        return $this->getCotation()->getIndicaRevenuNet();
    }

    public function getIndicaRevenuTaxeAssureur(?int $typeRevenu = null, ?int $patageable = null): ?float
    {
        return $this->getCotation()->getIndicaRevenuTaxeAssureur();
    }

    public function getIndicaRevenuTaxeCourtier(?int $typeRevenu = null, ?int $patageable = null): ?float
    {
        return $this->getCotation()->getIndicaRevenuTaxeCourtier();
    }

    public function getIndicaRevenuPartageable(?int $typeRevenu = null, ?int $patageable = null): ?float
    {
        return $this->getCotation()->getIndicaRevenuPartageable();
    }

    public function getIndicaRevenuTotal(?int $typeRevenu = null, ?int $patageable = null): ?float
    {
        return $this->getCotation()->getIndicaRevenuTotal();
    }

    public function getIndicaPartenaire(): ?Partenaire
    {
        return $this->getCotation()->getIndicaPartenaire();
    }

    public function getIndicaPartenaireRetrocom(?int $typeRevenu = null): ?float
    {
        return $this->getCotation()->getIndicaPartenaireRetrocom();
    }

    public function getIndicaRevenuReserve(?int $typeRevenu = null): ?float
    {
        return $this->getCotation()->getIndicaRevenuReserve();
    }

    public function transfererObservateur(?Observateur $observateur)
    {
        // dd("Fonction non encore définie");
    }
}
