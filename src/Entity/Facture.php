<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\FactureRepository;
use Doctrine\Common\Collections\Collection;
use App\Controller\Admin\FactureCrudController;
use App\Service\RefactoringJS\AutresClasses\JSAbstractFinances;
use App\Service\RefactoringJS\Commandes\Facture\CommandeProduireArticlesGrouperSelonNotes;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity(repositoryClass: FactureRepository::class)]
class Facture extends JSAbstractFinances
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $reference = null;

    #[ORM\ManyToOne]
    private ?Utilisateur $utilisateur = null;

    #[ORM\ManyToOne]
    private ?Entreprise $entreprise = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(nullable: true)]
    private ?int $destination = null;

    #[ORM\ManyToOne(inversedBy: 'factures')]
    private ?Partenaire $partenaire = null;

    #[ORM\ManyToOne(inversedBy: 'factures')]
    private ?Assureur $assureur = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?DocPiece $piece = null;

    // #[ORM\Column(nullable: true)]
    private ?float $totalDu = null;

    #[ORM\Column(nullable: true)]
    private ?float $totalRecu = null;

    #[ORM\OneToMany(mappedBy: 'facture', targetEntity: ElementFacture::class, cascade: ['remove', 'persist', 'refresh'])]
    private Collection $elementFactures;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $autreTiers = null;

    #[ORM\OneToMany(mappedBy: 'facture', targetEntity: Paiement::class)]
    private Collection $paiements;

    #[ORM\ManyToMany(targetEntity: CompteBancaire::class, inversedBy: 'factures')]
    private Collection $compteBancaires;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $signedBy = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $posteSignedBy = null;

    #[ORM\Column(nullable: true)]
    private ?float $totalSolde = null;

    #[ORM\Column(nullable: true)]
    private ?int $status = null;

    #[ORM\OneToMany(mappedBy: 'facture', targetEntity: DocPiece::class, cascade: ['remove', 'persist', 'refresh'])]
    private Collection $documents;

    private ?float $montantTTC;
    //Tableaux utiles pour les PDF
    //Client et Assureur | Notes de Débit
    private ?array $synthseND = [];
    private ?array $notesElementsFacturesND = [];
    //Partenaire | Notes de Crédit
    private ?array $synthseNCPartenaire = [];
    private ?array $notesElementsNCPartenaire = [];


    public function __construct()
    {
        $this->elementFactures = new ArrayCollection();
        $this->paiements = new ArrayCollection();
        $this->compteBancaires = new ArrayCollection();
        $this->documents = new ArrayCollection();
    }

    public function initEntreprise(): ?Entreprise
    {
        return $this->getEntreprise();
    }

    private function initMontantsPayes()
    {
        //Init paiements
        $this->totalRecu = 0;
        /** @var Paiement */
        foreach ($this->paiements as $paiement) {
            if ($this->getDestination() == $paiement->getDestination()) {
                $tabPaiements[] = $paiement;
                $this->totalRecu = $this->totalRecu + round($paiement->getMontant(), 0);
            }
        }
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

    // public function getType(): ?int
    // {
    //     return $this->type;
    // }

    // public function setType(int $type): self
    // {
    //     $this->type = $type;

    //     return $this;
    // }

    public function getPartenaire(): ?Partenaire
    {
        return $this->partenaire;
    }

    public function setPartenaire(?Partenaire $partenaire): self
    {
        $this->partenaire = $partenaire;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPiece(): ?DocPiece
    {
        return $this->piece;
    }

    public function setPiece(?DocPiece $piece): self
    {
        $this->piece = $piece;

        return $this;
    }

    public function getTotalDu(): ?float
    {
        // dd($this->totalDu);
        $this->totalDu = 0;
        /** @var ElementFacture */
        foreach ($this->elementFactures as $ef) {
            $this->totalDu = $this->totalDu + $ef->getMontant();
        }
        // dd($this->elementFactures);
        return round($this->totalDu);
    }

    public function getTotalRecu(): ?float
    {
        $this->initMontantsPayes();
        // dd($this->totalRecu);
        return round($this->totalRecu, 0);
    }

    public function setTotalRecu(?float $totalRecu): self
    {
        $this->totalRecu = $totalRecu;
        return $this;
    }

    public function __toString()
    {
        $tiers = " à nous.";
        switch ($this->destination) {
            case FactureCrudController::TAB_DESTINATION[FactureCrudController::DESTINATION_CLIENT]:
                $tiers = ", dû à " . $this->assureur . " par " . $this->autreTiers;
                break;
            case FactureCrudController::TAB_DESTINATION[FactureCrudController::DESTINATION_ASSUREUR]:
                $tiers = ", dû par " . $this->assureur;
                break;
            case FactureCrudController::TAB_DESTINATION[FactureCrudController::DESTINATION_PARTENAIRE]:
                $tiers =  " dû à " . $this->partenaire;
                break;
            case FactureCrudController::TAB_DESTINATION[FactureCrudController::DESTINATION_ARCA]:
                $tiers = " venant de l'Autorité de régulation";
                break;
            case FactureCrudController::TAB_DESTINATION[FactureCrudController::DESTINATION_DGI]:
                $tiers = " venant de l'Autorité fiscale";
                break;
            default:
                //$tiers = ".";
                break;
        }
        return $this->reference . ($this->createdAt != null ? " du " . $this->createdAt->format('d-m-Y') : "") . "" . $tiers; // . $this->description;
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
            $this->elementFactures->add($elementFacture);
            $elementFacture->setFacture($this);
        }

        return $this;
    }

    public function removeElementFacture(ElementFacture $elementFacture): self
    {
        if ($this->elementFactures->removeElement($elementFacture)) {
            // set the owning side to null (unless already changed)
            if ($elementFacture->getFacture() === $this) {
                $elementFacture->setFacture(null);
            }
        }

        return $this;
    }

    public function getAutreTiers(): ?string
    {
        return $this->autreTiers;
    }

    public function setAutreTiers(?string $autreTiers): self
    {
        $this->autreTiers = $autreTiers;

        return $this;
    }


    /**
     * @return Collection<int, Paiement>
     */
    public function getPaiements(): Collection
    {
        return $this->paiements;
    }

    public function addPaiement(Paiement $paiement): self
    {
        if (!$this->paiements->contains($paiement)) {
            $this->paiements->add($paiement);
            $paiement->setFacture($this);
        }

        return $this;
    }

    public function removePaiement(Paiement $paiement): self
    {
        if ($this->paiements->removeElement($paiement)) {
            // set the owning side to null (unless already changed)
            if ($paiement->getFacture() === $this) {
                $paiement->setFacture(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CompteBancaire>
     */
    public function getCompteBancaires(): Collection
    {
        return $this->compteBancaires;
    }

    public function addCompteBancaire(CompteBancaire $compteBancaire): self
    {
        if (!$this->compteBancaires->contains($compteBancaire)) {
            $this->compteBancaires->add($compteBancaire);
        }

        return $this;
    }

    public function removeCompteBancaire(CompteBancaire $compteBancaire): self
    {
        $this->compteBancaires->removeElement($compteBancaire);

        return $this;
    }

    public function getSignedBy(): ?string
    {
        return $this->signedBy;
    }

    public function setSignedBy(?string $signedBy): self
    {
        $this->signedBy = $signedBy;

        return $this;
    }

    public function getPosteSignedBy(): ?string
    {
        return $this->posteSignedBy;
    }

    public function setPosteSignedBy(?string $posteSignedBy): self
    {
        $this->posteSignedBy = $posteSignedBy;

        return $this;
    }

    public function getTotalSolde(): ?float
    {
        $this->totalSolde = $this->getTotalDu() - $this->getTotalRecu();
        return $this->totalSolde;
    }

    public function setTotalSolde(?float $totalSolde): self
    {
        $this->totalSolde = $totalSolde;

        return $this;
    }

    public function getStatus(): ?int
    {
        $solde = $this->getTotalSolde();
        if ($solde == 0) {
            $this->status = FactureCrudController::TAB_STATUS_FACTURE[FactureCrudController::STATUS_FACTURE_SOLDEE];
        } else if ($this->getTotalRecu() == 0) {
            $this->status = FactureCrudController::TAB_STATUS_FACTURE[FactureCrudController::STATUS_FACTURE_IMPAYEE];
        } else {
            $this->status = FactureCrudController::TAB_STATUS_FACTURE[FactureCrudController::STATUS_FACTURE_ENCOURS];
        }
        return $this->status;
    }

    public function setStatus(?int $status): self
    {
        $this->status = $status;

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
            $document->setFacture($this);
        }

        return $this;
    }

    public function removeDocument(DocPiece $document): self
    {
        if ($this->documents->removeElement($document)) {
            // set the owning side to null (unless already changed)
            if ($document->getFacture() === $this) {
                $document->setFacture(null);
            }
        }

        return $this;
    }


    /**
     * Get the value of montantTTC
     */
    public function getMontantTTC()
    {
        $total = 0;
        foreach ($this->elementFactures as $ef) {
            $total = $total + $ef->getMontant();
        }
        $this->montantTTC = $total;
        return $this->montantTTC;
    }

    /**
     * Get the value of destination
     */
    public function getDestination()
    {
        return $this->destination;
    }

    /**
     * Set the value of destination
     *
     * @return  self
     */
    public function setDestination($destination)
    {
        $this->destination = $destination;

        return $this;
    }



    /**
     * Get the value of montantReceivedPerDestination
     */
    public function getMontantReceivedPerDestination(?int $destination)
    {
        /** @var ElementFacture */
        foreach ($this->elementFactures as $elementFacture) {
            $this->montantReceivedPerDestination = $this->montantReceivedPerDestination + $elementFacture->getMontantReceivedPerDestination($destination);
        }
        return round($this->montantReceivedPerDestination);
    }

    /**
     * Get the value of montantReceivedPerTypeNote
     */
    public function getMontantReceivedPerTypeNote(?int $typeNote)
    {
        /** @var ElementFacture */
        foreach ($this->elementFactures as $elementFacture) {
            $this->montantReceivedPerTypeNote = $this->montantReceivedPerTypeNote + $elementFacture->getMontantReceivedPerTypeNote($typeNote);
        }
        return round($this->montantReceivedPerTypeNote);
    }

    /**
     * Get the value of montantInvoicedPerDestination
     */
    public function getMontantInvoicedPerDestination(?int $destination)
    {
        /** @var ElementFacture */
        foreach ($this->elementFactures as $elementFacture) {
            $this->montantInvoicedPerDestination = $this->montantInvoicedPerDestination + $elementFacture->getMontantInvoicedPerDestination($destination);
        }
        return round($this->montantInvoicedPerDestination);
    }

    /**
     * Get the value of montantInvoicedPerTypeNote
     */
    public function getMontantInvoicedPerTypeNote(?int $typeNote)
    {
        /** @var ElementFacture */
        foreach ($this->elementFactures as $elementFacture) {
            $this->montantInvoicedPerTypeNote = $this->montantInvoicedPerTypeNote + $elementFacture->getMontantInvoicedPerTypeNote($typeNote);
        }
        return round($this->montantInvoicedPerTypeNote);
    }

    /**
     * Get the value of notesElementsFactures
     *
     * @return ?array
     */
    public function getNotesElementsFacturesND(): ?array
    {
        (new CommandeProduireArticlesGrouperSelonNotes($this))
            ->executer();
        return $this->notesElementsFacturesND;
    }

    /**
     * Set the value of notesElementsFactures
     *
     * @return  self
     */
    public function setNotesElementsFacturesND($notesElementsFacturesND)
    {
        $this->notesElementsFacturesND = $notesElementsFacturesND;

        return $this;
    }
}
