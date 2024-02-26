<?php

namespace App\Entity;

use DateInterval;
use App\Entity\Utilisateur;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\PoliceRepository;
use Doctrine\Common\Collections\Collection;
use App\Controller\Admin\PoliceCrudController;
use App\Controller\Admin\MonnaieCrudController;
use App\Service\RefactoringJS\AutresClasses\JSAbstractFinances;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PoliceRepository::class)]
class Police extends JSAbstractFinances
{
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

    private ?Monnaie $monnaie_Affichage;



    public function __construct()
    {
        $this->documents = new ArrayCollection();
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
        $this->reference = $reference;

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
        $this->gestionnaire = $gestionnaire;

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
        $this->piste = $piste;

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
        $this->dateexpiration = $dateexpiration;

        return $this;
    }

    public function getIdAvenant(): ?int
    {
        return $this->idAvenant;
    }

    public function setIdAvenant(int $idAvenant): self
    {
        $this->idAvenant = $idAvenant;

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
        $this->dateeffet = $dateeffet;

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
            $document->setPolice($this);
        }

        return $this;
    }

    public function removeDocument(DocPiece $document): self
    {
        if ($this->documents->removeElement($document)) {
            // set the owning side to null (unless already changed)
            if ($document->getPolice() === $this) {
                $document->setPolice(null);
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
}
