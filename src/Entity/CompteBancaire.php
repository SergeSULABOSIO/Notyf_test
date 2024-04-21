<?php

namespace App\Entity;

use App\Entity\Traits\TraitEcouteurEvenements;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use App\Repository\CompteBancaireRepository;
use App\Service\RefactoringJS\Evenements\Sujet;
use Doctrine\Common\Collections\ArrayCollection;
use App\Service\RefactoringJS\Evenements\Evenement;
use App\Service\RefactoringJS\Evenements\Observateur;
use App\Service\RefactoringJS\Commandes\CommandeExecuteur;
use App\Service\RefactoringJS\Commandes\ComDetecterEvenementAttribut;

#[ORM\Entity(repositoryClass: CompteBancaireRepository::class)]
#[ORM\HasLifecycleCallbacks]
class CompteBancaire implements Sujet, CommandeExecuteur
{
    use TraitEcouteurEvenements;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $intitule = null;

    #[ORM\Column(length: 255)]
    private ?string $numero = null;

    #[ORM\Column(length: 255)]
    private ?string $banque = null;

    #[ORM\Column(length: 255)]
    private ?string $codeSwift = null;

    #[ORM\ManyToOne(inversedBy: 'compteBancaires')]
    private ?Entreprise $entreprise = null;

    #[ORM\ManyToOne(inversedBy: 'compteBancaires')]
    private ?Utilisateur $utilisateur = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 255)]
    private ?string $codeMonnaie = null;

    #[ORM\ManyToMany(targetEntity: Facture::class, mappedBy: 'compteBancaires')]
    private Collection $factures;

    #[ORM\OneToMany(mappedBy: 'compteBancaire', targetEntity: Paiement::class)]
    private Collection $paiements;


    public function __construct()
    {
        $this->factures = new ArrayCollection();
        $this->paiements = new ArrayCollection();
        $this->listeObservateurs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIntitule(): ?string
    {
        return $this->intitule;
    }

    public function setIntitule(string $intitule): self
    {
        $oldValue = $this->getIntitule();
        $newValue = $intitule;
        $this->intitule = $intitule;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Intitulé du compte", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

        return $this;
    }

    public function getNumero(): ?string
    {
        return $this->numero;
    }

    public function setNumero(string $numero): self
    {
        $oldValue = $this->getNumero();
        $newValue = $numero;
        $this->numero = $numero;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Numéro du compte", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

        return $this;
    }

    public function getBanque(): ?string
    {
        return $this->banque;
    }

    public function setBanque(string $banque): self
    {
        $oldValue = $this->getBanque();
        $newValue = $banque;
        $this->banque = $banque;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Nom de la banque", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

        return $this;
    }

    public function getCodeSwift(): ?string
    {
        return $this->codeSwift;
    }

    public function setCodeSwift(string $codeSwift): self
    {
        $oldValue = $this->getCodeSwift();
        $newValue = $codeSwift;
        $this->codeSwift = $codeSwift;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Code Swift de la banque", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

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

    public function __toString()
    {
        return $this->banque . " - " . $this->numero . " / " . $this->codeMonnaie;
    }

    public function getCodeMonnaie(): ?string
    {
        return $this->codeMonnaie;
    }

    public function setCodeMonnaie(string $codeMonnaie): self
    {
        $oldValue = $this->getCodeMonnaie();
        $newValue = $codeMonnaie;
        $this->codeMonnaie = $codeMonnaie;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Code de la monnaie", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

        return $this;
    }

    /**
     * @return Collection<int, Facture>
     */
    public function getFactures(): Collection
    {
        return $this->factures;
    }

    public function addFacture(Facture $facture): self
    {
        if (!$this->factures->contains($facture)) {
            $oldValue = null;
            $newValue = $facture;
            $this->factures->add($facture);
            $facture->addCompteBancaire($this);
            //Ecouteur d'action
            $this->executer(new ComDetecterEvenementAttribut($this, "Facture", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
        }

        return $this;
    }

    public function removeFacture(Facture $facture): self
    {
        if ($this->factures->removeElement($facture)) {
            $oldValue = $facture;
            $newValue = null;
            $facture->removeCompteBancaire($this);
            //Ecouteur d'action
            $this->executer(new ComDetecterEvenementAttribut($this, "Facture", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
        }

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
            $paiement->setCompteBancaire($this);
            //Ecouteur d'action
            $this->executer(new ComDetecterEvenementAttribut($this, "Paiement", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
        }

        return $this;
    }

    public function removePaiement(Paiement $paiement): self
    {
        if ($this->paiements->removeElement($paiement)) {
            // set the owning side to null (unless already changed).
            if ($paiement->getCompteBancaire() === $this) {
                $oldValue = $paiement;
                $newValue = null;
                $paiement->setCompteBancaire(null);
                //Ecouteur d'action
                $this->executer(new ComDetecterEvenementAttribut($this, "Paiement", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
            }
        }

        return $this;
    }

    public function transfererObservateur(?Observateur $observateur)
    {
        // dd("Cette fonction n'est pas encore définie");
    }
}
