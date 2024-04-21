<?php

namespace App\Entity;

use App\Entity\Traits\TraitEcouteurEvenements;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\PaiementRepository;
use Doctrine\Common\Collections\Collection;
use App\Service\RefactoringJS\Evenements\Sujet;
use Doctrine\Common\Collections\ArrayCollection;
use App\Service\RefactoringJS\Commandes\Commande;
use App\Service\RefactoringJS\Evenements\Evenement;
use App\Service\RefactoringJS\Evenements\Observateur;
use App\Service\RefactoringJS\Commandes\CommandeExecuteur;
use App\Service\RefactoringJS\AutresClasses\JSAbstractFinances;
use App\Service\RefactoringJS\Commandes\ComDetecterEvenementAttribut;
use App\Service\RefactoringJS\Commandes\Piste\CommandePisteNotifierEvenement;

#[ORM\Entity(repositoryClass: PaiementRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Paiement extends JSAbstractFinances implements Sujet, CommandeExecuteur
{
    use TraitEcouteurEvenements;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $paidAt = null;

    #[ORM\Column]
    private ?float $montant = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'paiements')]
    private ?Facture $facture = null;

    #[ORM\ManyToOne(inversedBy: 'paiements')]
    private ?Entreprise $entreprise = null;

    #[ORM\ManyToOne(inversedBy: 'paiements')]
    private ?Utilisateur $utilisateur = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'paiements')]
    private ?CompteBancaire $compteBancaire = null;

    #[ORM\OneToMany(mappedBy: 'paiement', targetEntity: DocPiece::class, cascade: ['remove', 'persist', 'refresh'])]
    private Collection $documents;

    #[ORM\Column]
    private ?int $destination = null;

    #[ORM\Column(nullable: true)]
    private ?int $type = null;

    public function __construct()
    {
        $this->documents = new ArrayCollection();
        $this->listeObservateurs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPaidAt(): ?\DateTimeImmutable
    {
        return $this->paidAt;
    }

    public function getMontantInvoicedPerDestination(?int $destination)
    {
        return 0;
    }

    public function getMontantInvoicedPerTypeNote(?int $typeNote)
    {
        return 0;
    }

    public function getMontantReceivedPerTypeNote(?int $typeNote)
    {
        return 0;
    }

    public function getMontantReceivedPerDestination(?int $destination)
    {
        return 0;
    }

    public function setPaidAt(\DateTimeImmutable $paidAt): self
    {
        $oldValue = $this->getPaidAt();
        $newValue = $paidAt;
        $this->paidAt = $paidAt;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Date de paiement", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

        return $this;
    }

    public function getMontant(): ?float
    {
        return $this->montant;
    }

    public function setMontant(float $montant): self
    {
        $oldValue = $this->getMontant();
        $newValue = $montant;
        $this->montant = $montant;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Montant", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

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

    public function initEntreprise(): ?Entreprise
    {
        return $this->getEntreprise();
    }

    public function __toString()
    {
        $ref = "Null";
        if ($this->facture != null) {
            $ref = $this->facture->getReference();
        }
        return "Paiement du " . $this->paidAt->format('d-m-Y') . " | Mont.: " . $this->getMontantEnMonnaieAffichage($this->montant) . " | Réf. ND: " . $ref . " | Desc.: " . $this->description;
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

    public function getCompteBancaire(): ?CompteBancaire
    {
        return $this->compteBancaire;
    }

    public function setCompteBancaire(?CompteBancaire $compteBancaire): self
    {
        $oldValue = $this->getCompteBancaire();
        $newValue = $compteBancaire;
        $this->compteBancaire = $compteBancaire;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Compte bancaire", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));

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
            $document->setPaiement($this);
            //Ecouteur d'action
            $this->executer(new ComDetecterEvenementAttribut($this, "Document", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
        }

        return $this;
    }

    public function removeDocument(DocPiece $document): self
    {
        if ($this->documents->removeElement($document)) {
            // set the owning side to null (unless already changed)
            if ($document->getPaiement() === $this) {
                $oldValue = $document;
                $newValue = null;
                $document->setPaiement(null);
                //Ecouteur d'action
                $this->executer(new ComDetecterEvenementAttribut($this, "Document", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
            }
        }

        return $this;
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
     * Get the value of type.
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set the value of type
     *
     * @return  self
     */
    public function setType($type)
    {
        $oldValue = $this->getType();
        $newValue = $type;
        $this->type = $type;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Type", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

        return $this;
    }

    public function transfererObservateur(?Observateur $observateur)
    {
        // dd("Cette fonction n'est pas encore définie");
    }
}
