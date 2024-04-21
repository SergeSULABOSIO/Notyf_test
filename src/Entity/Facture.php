<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\FactureRepository;
use Doctrine\Common\Collections\Collection;
use App\Controller\Admin\FactureCrudController;
use App\Entity\Traits\TraitEcouteurEvenements;
use App\Service\RefactoringJS\Evenements\Sujet;
use Doctrine\Common\Collections\ArrayCollection;
use App\Service\RefactoringJS\Commandes\Commande;
use App\Service\RefactoringJS\Evenements\Evenement;
use App\Service\RefactoringJS\Evenements\Observateur;
use App\Service\RefactoringJS\Commandes\CommandeExecuteur;
use App\Service\RefactoringJS\AutresClasses\JSAbstractFinances;
use App\Service\RefactoringJS\Commandes\Facture\CommandeProduireSyntheDgi;
use App\Service\RefactoringJS\Commandes\ComDetecterEvenementAttribut;
use App\Service\RefactoringJS\Commandes\Facture\CommandeProduireSyntheArca;
use App\Service\RefactoringJS\Commandes\Facture\CommandeProduireSyntheClient;
use App\Service\RefactoringJS\Commandes\Piste\CommandePisteNotifierEvenement;
use App\Service\RefactoringJS\Commandes\Facture\CommandeProduireSynthePartenaire;
use App\Service\RefactoringJS\Commandes\Facture\CommandeProduireSyntheClientOuAssureur;
use App\Service\RefactoringJS\Commandes\Facture\CommandeProduireArticlesClientOuAssureur;
use App\Service\RefactoringJS\Commandes\Facture\CommandeProduireArticlesGrouperSelonNotes;
use App\Service\RefactoringJS\Commandes\Facture\CommandeProduireBordereauClientOuAssureur;

#[ORM\Entity(repositoryClass: FactureRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Facture extends JSAbstractFinances implements Sujet, CommandeExecuteur
{
    use TraitEcouteurEvenements;

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
    private ?array $synthseNDClientOuAssureur = [];
    private ?array $articlesNDClientOuAssureur = [];
    //Partenaire | Notes de Crédit
    private ?array $synthseNCPartenaire = [];
    private ?array $notesElementsNCPartenaire = [];
    //Arca | Notes de Crédit
    private ?array $synthseNCArca = [];
    private ?array $notesElementsNCArca = [];
    //Dgi | Notes de Crédit
    private ?array $synthseNCDgi = [];
    private ?array $notesElementsNCDgi = [];

    public function __construct()
    {
        $this->elementFactures = new ArrayCollection();
        $this->paiements = new ArrayCollection();
        $this->compteBancaires = new ArrayCollection();
        $this->documents = new ArrayCollection();
        $this->listeObservateurs = new ArrayCollection();
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
        $oldValue = $this->getReference();
        $newValue = $reference;
        $this->reference = $reference;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Référence", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

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


    public function getPartenaire(): ?Partenaire
    {
        return $this->partenaire;
    }

    public function setPartenaire(?Partenaire $partenaire): self
    {
        $oldValue = $this->getPartenaire();
        $newValue = $partenaire;
        $this->partenaire = $partenaire;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Partenaire", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $oldValue = $this->getDescription();
        $newValue = $description;
        $this->description = $description;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Description", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

        return $this;
    }

    public function getPiece(): ?DocPiece
    {
        return $this->piece;
    }

    public function setPiece(?DocPiece $piece): self
    {
        $oldValue = $this->getPiece();
        $newValue = $piece;
        $this->piece = $piece;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Document", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));

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
        $oldValue = $this->getTotalRecu();
        $newValue = $totalRecu;
        $this->totalRecu = $totalRecu;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Total reçu", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

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
            $oldValue = null;
            $newValue = $elementFacture;

            $this->elementFactures->add($elementFacture);
            $elementFacture->setFacture($this);

            //Ecouteur d'action
            $this->executer(new ComDetecterEvenementAttribut($this, "Article de la facture", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
        }

        return $this;
    }

    public function removeElementFacture(ElementFacture $elementFacture): self
    {
        if ($this->elementFactures->removeElement($elementFacture)) {
            // set the owning side to null (unless already changed)
            if ($elementFacture->getFacture() === $this) {
                $oldValue = $elementFacture;
                $newValue = null;
                $elementFacture->setFacture(null);
                //Ecouteur d'action
                $this->executer(new ComDetecterEvenementAttribut($this, "Article de la facture", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
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
        $oldValue = $this->getAutreTiers();
        $newValue = $autreTiers;
        $this->autreTiers = $autreTiers;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Autre tiers", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

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
            $oldValue = null;
            $newValue = $paiement;
            $this->paiements->add($paiement);
            $paiement->setFacture($this);
            //Ecouteur d'action
            $this->executer(new ComDetecterEvenementAttribut($this, "Paiement", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
        }

        return $this;
    }

    public function removePaiement(Paiement $paiement): self
    {
        if ($this->paiements->removeElement($paiement)) {
            // set the owning side to null (unless already changed)
            if ($paiement->getFacture() === $this) {
                $oldValue = $paiement;
                $newValue = null;
                $paiement->setFacture(null);
                //Ecouteur d'action
                $this->executer(new ComDetecterEvenementAttribut($this, "Paiement", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
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
            $oldValue = null;
            $newValue = $compteBancaire;
            $this->compteBancaires->add($compteBancaire);
            //Ecouteur d'action
            $this->executer(new ComDetecterEvenementAttribut($this, "Compte Bancaire", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
        }

        return $this;
    }

    public function removeCompteBancaire(CompteBancaire $compteBancaire): self
    {
        $oldValue = $compteBancaire;
        $newValue = null;
        $this->compteBancaires->removeElement($compteBancaire);
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Compte Bancaire", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));

        return $this;
    }

    public function getSignedBy(): ?string
    {
        return $this->signedBy;
    }

    public function setSignedBy(?string $signedBy): self
    {
        $oldValue = $this->getSignedBy();
        $newValue = $signedBy;
        $this->signedBy = $signedBy;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Signataire", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

        return $this;
    }

    public function getPosteSignedBy(): ?string
    {
        return $this->posteSignedBy;
    }

    public function setPosteSignedBy(?string $posteSignedBy): self
    {
        $oldValue = $this->getPosteSignedBy();
        $newValue = $posteSignedBy;
        $this->posteSignedBy = $posteSignedBy;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Poste du signataire", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

        return $this;
    }

    public function getTotalSolde(): ?float
    {
        $this->totalSolde = $this->getTotalDu() - $this->getTotalRecu();
        return $this->totalSolde;
    }

    public function setTotalSolde(?float $totalSolde): self
    {
        $oldValue = $this->getTotalSolde();
        $newValue = $totalSolde;
        $this->totalSolde = $totalSolde;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Solde total", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

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
        $oldValue = $this->getStatus();
        $newValue = $status;
        $this->status = $status;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Status", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

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
            $document->setFacture($this);
            //Ecouteur d'action
            $this->executer(new ComDetecterEvenementAttribut($this, "Document", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
        }

        return $this;
    }

    public function removeDocument(DocPiece $document): self
    {
        if ($this->documents->removeElement($document)) {
            // set the owning side to null (unless already changed)
            if ($document->getFacture() === $this) {
                $oldValue = $document;
                $newValue = null;
                $document->setFacture(null);
                //Ecouteur d'action
                $this->executer(new ComDetecterEvenementAttribut($this, "Document", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
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
        $oldValue = $this->getDestination();
        $newValue = $destination;
        $this->destination = $destination;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Destination", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

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
    public function getArticlesNDClientOuAssureur(): ?array
    {
        $this->executer(new CommandeProduireArticlesClientOuAssureur($this));
        return $this->articlesNDClientOuAssureur;
    }

    /**
     * Set the value of notesElementsFacturesND
     *
     * @return  self
     */
    public function setArticlesNDClientOuAssureur($notesElementsFacturesND)
    {
        $oldValue = $this->getArticlesNDClientOuAssureur();
        $newValue = $notesElementsFacturesND;
        $this->articlesNDClientOuAssureur = $notesElementsFacturesND;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Notes des articles", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

        return $this;
    }

    /**
     * Get the value of synthseNCPartenaire
     */
    public function getSynthseNCPartenaire()
    {
        $this->executer(new CommandeProduireSynthePartenaire($this, CommandeProduireSynthePartenaire::MODE_SYNTHESE));
        return $this->synthseNCPartenaire;
    }



    /**
     * Set the value of synthseNCPartenaire
     *
     * @return  self
     */
    public function setSynthseNCPartenaire($synthseNCPartenaire)
    {
        $oldValue = $this->getSynthseNCPartenaire();
        $newValue = $synthseNCPartenaire;
        $this->synthseNCPartenaire = $synthseNCPartenaire;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Synthèse NC du Partenaire", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

        return $this;
    }

    /**
     * Get the value of synthseNDClientOuAssureur
     */
    public function getSynthseNDClientOuAssureur()
    {
        $this->executer(new CommandeProduireSyntheClientOuAssureur($this));
        return $this->synthseNDClientOuAssureur;
    }

    /**
     * Set the value of synthseNDClientOuAssureur
     *
     * @return  self
     */
    public function setSynthseNDClientOuAssureur($synthseNDClientOuAssureur)
    {
        $oldValue = $this->getSynthseNDClientOuAssureur();
        $newValue = $synthseNDClientOuAssureur;
        $this->synthseNDClientOuAssureur = $synthseNDClientOuAssureur;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Synthèse ND du Client ou de l'Assureur", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

        return $this;
    }

    /**
     * Get the value of synthseNCArca
     */
    public function getSynthseNCArca()
    {
        $this->executer(new CommandeProduireSyntheArca($this, CommandeProduireSyntheArca::MODE_SYNTHESE));
        return $this->synthseNCArca;
    }



    /**
     * Set the value of synthseNCPartenaire
     *
     * @return  self
     */
    public function setSynthseNCArca($synthseNCArca)
    {
        $oldValue = $this->getSynthseNCArca();
        $newValue = $synthseNCArca;
        $this->synthseNCArca = $synthseNCArca;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Synthèse NC pour taxe", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

        return $this;
    }

    /**
     * Get the value of synthseNCDgi
     */
    public function getSynthseNCDgi()
    {
        $this->executer(new CommandeProduireSyntheDgi($this, CommandeProduireSyntheDgi::MODE_SYNTHESE));
        return $this->synthseNCDgi;
    }



    /**
     * Set the value of synthseNCDgi
     *
     * @return  self
     */
    public function setSynthseNCDgi($synthseNCDgi)
    {
        $oldValue = $this->getSynthseNCDgi();
        $newValue = $synthseNCDgi;
        $this->synthseNCDgi = $synthseNCDgi;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Synthèse NC pour taxe", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

        return $this;
    }

    /**
     * Get the value of notesElementsNCDgi
     */
    public function getNotesElementsNCDgi()
    {
        $this->executer(new CommandeProduireSyntheDgi($this, CommandeProduireSyntheDgi::MODE_BORDEREAU));
        // dd($this->notesElementsNCDgi);
        return $this->notesElementsNCDgi;
    }

    /**
     * Set the value of notesElementsNCDgi
     *
     * @return  self
     */
    public function setNotesElementsNCDgi($notesElementsNCDgi)
    {
        $oldValue = $this->getNotesElementsNCDgi();
        $newValue = $notesElementsNCDgi;
        $this->notesElementsNCDgi = $notesElementsNCDgi;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Notes NC pour taxe", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

        return $this;
    }

    /**
     * Get the value of notesElementsNCArca
     */
    public function getNotesElementsNCArca()
    {
        $this->executer(new CommandeProduireSyntheArca($this, CommandeProduireSyntheArca::MODE_BORDEREAU));
        return $this->notesElementsNCArca;
    }

    /**
     * Set the value of notesElementsNCArca
     *
     * @return  self
     */
    public function setNotesElementsNCArca($notesElementsNCArca)
    {
        $oldValue = $this->getNotesElementsNCArca();
        $newValue = $notesElementsNCArca;
        $this->notesElementsNCArca = $notesElementsNCArca;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Notes NC pour taxe", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

        return $this;
    }

    /**
     * Get the value of notesElementsNCPartenaire
     */
    public function getNotesElementsNCPartenaire()
    {
        $this->executer(new CommandeProduireSynthePartenaire($this, CommandeProduireSynthePartenaire::MODE_BORDEREAU));
        return $this->notesElementsNCPartenaire;
    }

    /**
     * Set the value of notesElementsNCPartenaire
     *
     * @return  self
     */
    public function setNotesElementsNCPartenaire($notesElementsNCPartenaire)
    {
        $oldValue = $this->getNotesElementsNCPartenaire();
        $newValue = $notesElementsNCPartenaire;
        $this->notesElementsNCPartenaire = $notesElementsNCPartenaire;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Notes NC pour partenaire", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

        return $this;
    }


    public function transfererObservateur(?Observateur $observateur)
    {
        // dd("Cette fonction n'est pas encore définie");
    }
}
