<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\PisteRepository;
use Sabberworm\CSS\CSSList\Document;
use Doctrine\Common\Collections\Collection;
use App\Controller\Admin\PisteCrudController;
use App\Controller\Admin\MonnaieCrudController;
use App\Service\RefactoringJS\Commandes\Commande;
use App\Service\RefactoringJS\Commandes\CommandeExecuteur;
use App\Service\RefactoringJS\Commandes\Piste\CommandePisteNotifierEvenement;
use App\Service\RefactoringJS\Evenements\Evenement;
use App\Service\RefactoringJS\Evenements\EvenementConcretAjout;
use App\Service\RefactoringJS\Evenements\EvenementConcretChargement;
use App\Service\RefactoringJS\Evenements\EvenementConcretEdition;
use App\Service\RefactoringJS\Evenements\EvenementConcretSuppression;
use App\Service\RefactoringJS\Evenements\Observateur;
use App\Service\RefactoringJS\Evenements\Sujet;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Contracts\EventDispatcher\Event;

#[ORM\Entity(repositoryClass: PisteRepository::class)]
class Piste implements Sujet, CommandeExecuteur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $objectif = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $expiredAt = null;

    #[ORM\OneToMany(mappedBy: 'piste', targetEntity: Contact::class, cascade: ['remove', 'persist', 'refresh'])]
    private Collection $contacts;

    #[ORM\OneToMany(mappedBy: 'piste', targetEntity: Cotation::class, cascade: ['remove', 'persist', 'refresh'])]
    private Collection $cotations;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $typeavenant = null;

    #[ORM\ManyToOne(inversedBy: 'pistes')]
    private ?Police $police = null;

    #[ORM\OneToMany(mappedBy: 'piste', targetEntity: ActionCRM::class, cascade: ['remove', 'persist', 'refresh'])]
    private Collection $actionsCRMs;

    #[ORM\ManyToOne(inversedBy: 'pistes')]
    private ?Client $client = null;

    #[ORM\OneToMany(mappedBy: 'piste', targetEntity: Client::class)]
    private Collection $prospect;

    #[ORM\ManyToOne(inversedBy: 'pistes')]
    private ?Produit $produit = null;

    #[ORM\ManyToOne(inversedBy: 'pistes')]
    private ?Partenaire $partenaire = null;

    #[ORM\OneToMany(mappedBy: 'piste', targetEntity: Partenaire::class)]
    private Collection $newpartenaire;

    #[ORM\ManyToOne(inversedBy: 'pistes')]
    private ?Utilisateur $gestionnaire = null;

    #[ORM\ManyToOne(inversedBy: 'pistes')]
    private ?Utilisateur $assistant = null;

    #[ORM\OneToMany(mappedBy: 'piste', targetEntity: Police::class, cascade: ['remove', 'persist', 'refresh'])]
    private Collection $polices;

    #[ORM\OneToMany(mappedBy: 'piste', targetEntity: DocPiece::class, cascade: ['remove', 'persist', 'refresh'])]
    private Collection $documents;

    #[ORM\Column(nullable: true)]
    private ?int $etape = null;
    private ?string $nomEtape = null;


    #[ORM\Column(nullable: true)]
    private ?float $montant = null;

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

    //Champs calculés
    private ?Assureur $assureur = null;
    private ?float $realisation = null;
    private Collection $chargements;
    private Collection $tranches;
    private Collection $revenus;
    private ?\DateTimeImmutable $dateEffet = null;
    private ?\DateTimeImmutable $dateExpiration = null;
    private ?float $duree = null;
    private ?Monnaie $monnaie_Affichage;

    //Evenements
    private ?ArrayCollection $listeObservateurs = null;
    private ?EvenementConcretAjout $eAjout = null;
    private ?EvenementConcretEdition $eEdition = null;
    private ?EvenementConcretChargement $eChargement = null;
    private ?EvenementConcretSuppression $eSuppression = null;



    public function __construct()
    {
        $this->cotations = new ArrayCollection();
        $this->contacts = new ArrayCollection();
        $this->actionsCRMs = new ArrayCollection();
        $this->prospect = new ArrayCollection();
        $this->newpartenaire = new ArrayCollection();
        $this->polices = new ArrayCollection();
        $this->documents = new ArrayCollection();
        $this->listeObservateurs = new ArrayCollection();

        //Evenemts
        $this->eAjout = new EvenementConcretAjout();
        $this->eEdition = new EvenementConcretEdition();
        $this->eChargement = new EvenementConcretChargement();
        $this->eSuppression = new EvenementConcretSuppression();
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

        //Ecouteur d'action
        $this->eAjout->setDonnees([
            Evenement::CHAMP_DONNEE => $this,
            Evenement::CHAMP_MESSAGE => "Initialisation du nom de la piste: " . $nom,
        ]);
        $this->notifierLesObservateurs($this->eAjout);

        return $this;
    }

    public function getObjectif(): ?string
    {
        return $this->objectif;
    }

    public function setObjectif(string $objectif): self
    {
        $this->objectif = $objectif;

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

    public function getMontant(): ?float
    {
        return $this->montant;
    }

    public function setMontant(?float $montant): self
    {
        $this->montant = $montant;

        return $this;
    }

    public function getExpiredAt(): ?\DateTimeImmutable
    {
        return $this->expiredAt;
    }

    public function setExpiredAt(\DateTimeImmutable $expiredAt): self
    {
        $this->expiredAt = $expiredAt;

        return $this;
    }

    public function __toString()
    {
        if ($this->nom == null) {
            $this->nom = "";
        }
        return $this->nom; // . ", ". ($this->updatedAt)->format('d/m/Y à H:m:s');
    }

    /**
     * @return Collection<int, Cotation>
     */
    public function getCotations(): Collection
    {
        return $this->cotations;
    }

    public function addCotation(Cotation $cotation): self
    {
        if (!$this->cotations->contains($cotation)) {
            $this->cotations->add($cotation);
            $cotation->setPiste($this);
        }

        return $this;
    }

    public function removeCotation(Cotation $cotation): self
    {
        if ($this->cotations->removeElement($cotation)) {
            // set the owning side to null (unless already changed)
            if ($cotation->getPiste() === $this) {
                $cotation->setPiste(null);
            }
        }

        return $this;
    }

    public function getTypeavenant(): ?string
    {
        return $this->typeavenant;
    }

    public function setTypeavenant(?string $typeavenant): self
    {
        $this->typeavenant = $typeavenant;

        return $this;
    }

    public function getPolice(): ?Police
    {
        return $this->police;
    }

    public function setPolice(?Police $police): self
    {
        $this->police = $police;

        return $this;
    }

    /**
     * @return Collection<int, Contact>
     */
    public function getContacts(): Collection
    {
        return $this->contacts;
    }

    public function addContact(Contact $contact): self
    {
        if (!$this->contacts->contains($contact)) {
            $this->contacts->add($contact);
            $contact->setPiste($this);
        }

        return $this;
    }

    public function removeContact(Contact $contact): self
    {
        if ($this->contacts->removeElement($contact)) {
            // set the owning side to null (unless already changed)
            if ($contact->getPiste() === $this) {
                $contact->setPiste(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ActionCRM>
     */
    public function getActionsCRMs(): Collection
    {
        return $this->actionsCRMs;
    }

    public function addActionsCRM(ActionCRM $actionsCRM): self
    {
        if (!$this->actionsCRMs->contains($actionsCRM)) {
            $this->actionsCRMs->add($actionsCRM);
            $actionsCRM->setPiste($this);
        }

        return $this;
    }

    public function removeActionsCRM(ActionCRM $actionsCRM): self
    {
        if ($this->actionsCRMs->removeElement($actionsCRM)) {
            // set the owning side to null (unless already changed)
            if ($actionsCRM->getPiste() === $this) {
                $actionsCRM->setPiste(null);
            }
        }

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): self
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return Collection<int, Client>
     */
    public function getProspect(): Collection
    {
        return $this->prospect;
    }

    public function addProspect(Client $prospect): self
    {
        if (!$this->prospect->contains($prospect)) {
            $this->prospect->add($prospect);
            $prospect->setPiste($this);
        }

        return $this;
    }

    public function removeProspect(Client $prospect): self
    {
        if ($this->prospect->removeElement($prospect)) {
            // set the owning side to null (unless already changed)
            if ($prospect->getPiste() === $this) {
                $prospect->setPiste(null);
            }
        }

        return $this;
    }

    public function getProduit(): ?Produit
    {
        return $this->produit;
    }

    public function setProduit(?Produit $produit): self
    {
        $this->produit = $produit;

        return $this;
    }

    public function getPartenaire(): ?Partenaire
    {
        return $this->partenaire;
    }

    public function setPartenaire(?Partenaire $partenaire): self
    {
        $this->partenaire = $partenaire;

        return $this;
    }

    /**
     * @return Collection<int, Partenaire>
     */
    public function getNewpartenaire(): Collection
    {
        return $this->newpartenaire;
    }

    public function addNewpartenaire(Partenaire $newpartenaire): self
    {
        if (!$this->newpartenaire->contains($newpartenaire)) {
            $this->newpartenaire->add($newpartenaire);
            $newpartenaire->setPiste($this);
        }

        return $this;
    }

    public function removeNewpartenaire(Partenaire $newpartenaire): self
    {
        if ($this->newpartenaire->removeElement($newpartenaire)) {
            // set the owning side to null (unless already changed)
            if ($newpartenaire->getPiste() === $this) {
                $newpartenaire->setPiste(null);
            }
        }

        return $this;
    }

    public function getGestionnaire(): ?Utilisateur
    {
        return $this->gestionnaire;
    }

    public function setGestionnaire(?Utilisateur $gestionnaire): self
    {
        $this->gestionnaire = $gestionnaire;

        return $this;
    }

    public function getAssistant(): ?Utilisateur
    {
        return $this->assistant;
    }

    public function setAssistant(?Utilisateur $assistant): self
    {
        $this->assistant = $assistant;

        return $this;
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
            $police->setPiste($this);
        }

        return $this;
    }

    public function removePolice(Police $police): self
    {
        if ($this->polices->removeElement($police)) {
            // set the owning side to null (unless already changed)
            if ($police->getPiste() === $this) {
                $police->setPiste(null);
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
            $document->setPiste($this);
        }

        return $this;
    }

    public function removeDocument(DocPiece $document): self
    {
        if ($this->documents->removeElement($document)) {
            // set the owning side to null (unless already changed)
            if ($document->getPiste() === $this) {
                $document->setPiste(null);
            }
        }

        return $this;
    }

    public function getEtape(): ?int
    {
        return $this->etape;
    }

    public function setEtape(?int $etape): self
    {
        $this->etape = $etape;

        return $this;
    }

    /**
     * Get the value of realisation
     */
    public function getRealisation()
    {
        if ($this->getPolices()) {
            if ($this->getPolices()[0]) {
                $this->realisation = $this->getPolices()[0]->getPrimeTotale();
            }
        }
        return $this->realisation;
    }

    /**
     * Get the value of assureur
     */
    public function getAssureur()
    {
        if ($this->getPolices()) {
            if ($this->getPolices()[0]) {
                $this->assureur = $this->getPolices()[0]->getAssureur();
            }
        }
        return $this->assureur;
    }

    /**
     * Get the value of chargements
     */
    public function getChargements()
    {
        if ($this->getPolices()) {
            if ($this->getPolices()[0]) {
                $this->chargements = $this->getPolices()[0]->getChargements();
            }
        }
        return $this->chargements;
    }

    /**
     * Get the value of tranches
     */
    public function getTranches()
    {
        if ($this->getPolices()) {
            if ($this->getPolices()[0]) {
                $this->tranches = $this->getPolices()[0]->getTranches();
            }
        }
        return $this->tranches;
    }

    /**
     * Get the value of revenus
     */
    public function getRevenus()
    {
        if ($this->getPolices()) {
            if ($this->getPolices()[0]) {
                $this->revenus = $this->getPolices()[0]->getRevenus();
            }
        }
        return $this->revenus;
    }

    /**
     * Get the value of dateEffet
     */
    public function getDateEffet()
    {
        if ($this->getPolices()) {
            if ($this->getPolices()[0]) {
                $this->dateEffet = $this->getPolices()[0]->getDateEffet();
            }
        }
        return $this->dateEffet;
    }

    /**
     * Get the value of dateExpiration
     */
    public function getDateExpiration()
    {
        if ($this->getPolices()) {
            if ($this->getPolices()[0]) {
                $this->dateExpiration = $this->getPolices()[0]->getDateExpiration();
            }
        }
        return $this->dateExpiration;
    }

    /**
     * Get the value of duree
     */
    public function getDuree()
    {
        if ($this->getPolices()) {
            if ($this->getPolices()[0]) {
                $this->duree = $this->getPolices()[0]->getCotation()->getDureeCouverture();
            }
        }
        return $this->duree;
    }

    /**
     * Get the value of nomEtape
     */
    public function getNomEtape()
    {
        $this->nomEtape = "Inconnu";
        foreach (PisteCrudController::TAB_ETAPES as $nomEtape => $codeEtape) {
            if ($this->getEtape() == $codeEtape) {
                $this->nomEtape = $nomEtape;
            }
        }
        return $this->nomEtape;
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
     * Get the value of monnaie_Affichage
     */
    public function getMonnaie_Affichage()
    {
        $this->monnaie_Affichage = $this->getMonnaie(MonnaieCrudController::TAB_MONNAIE_FONCTIONS[MonnaieCrudController::FONCTION_SAISIE_ET_AFFICHAGE]);
        if ($this->monnaie_Affichage == null) {
            $this->monnaie_Affichage = $this->getMonnaie(MonnaieCrudController::TAB_MONNAIE_FONCTIONS[MonnaieCrudController::FONCTION_AFFICHAGE_UNIQUEMENT]);
        }
        return $this->monnaie_Affichage;
    }

    public function ajouterObservateur(?Observateur $observateur)
    {
        // Ajout observateur
        if (!$this->listeObservateurs->contains($observateur)) {
            $this->listeObservateurs->add($observateur);
        }
    }

    public function retirerObservateur(?Observateur $observateur)
    {
        if ($this->listeObservateurs->contains($observateur)) {
            $this->listeObservateurs->removeElement($observateur);
        }
    }

    public function viderListeObservateurs()
    {
        if (!$this->listeObservateurs->isEmpty()) {
            $this->listeObservateurs = new ArrayCollection([]);
        }
    }

    public function getListeObservateurs(): ?ArrayCollection
    {
        return $this->listeObservateurs;
    }

    public function notifierLesObservateurs(?Evenement $evenement)
    {
        $this->executer(new CommandePisteNotifierEvenement($this->listeObservateurs, $evenement));
    }

    public function executer(?Commande $commande)
    {
        if ($commande != null) {
            $commande->executer();
        }
    }
}
