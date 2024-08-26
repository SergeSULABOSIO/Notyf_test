<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\PisteRepository;
use Doctrine\Common\Collections\Collection;
use App\Controller\Admin\PisteCrudController;
use App\Controller\Admin\ClientCrudController;
use App\Entity\Traits\TraitEcouteurEvenements;
use App\Controller\Admin\MonnaieCrudController;
use App\Service\RefactoringJS\Evenements\Sujet;
use Doctrine\Common\Collections\ArrayCollection;
use App\Service\RefactoringJS\Commandes\Commande;
use App\Controller\Admin\ChargementCrudController;
use App\Service\RefactoringJS\Evenements\Evenement;
use App\Service\RefactoringJS\Evenements\Observateur;
use App\Service\RefactoringJS\Commandes\CommandeExecuteur;
use App\Service\RefactoringJS\Commandes\ComDetecterEvenementAttribut;
use App\Service\RefactoringJS\Commandes\Piste\ComPisteAppliquerEntiteesParDefautCotation;
use App\Service\RefactoringJS\Commandes\Piste\ComPisteAppliquerEntiteesParDefautPourCotation;

use function PHPUnit\Framework\isEmpty;

#[ORM\Entity(repositoryClass: PisteRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Piste implements Sujet, CommandeExecuteur
{
    use TraitEcouteurEvenements;

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
        $this->nom = $nom;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Nom", $oldValue, $nom, Evenement::FORMAT_VALUE_PRIMITIVE));
        return $this;
    }

    public function getObjectif(): ?string
    {
        return $this->objectif;
    }

    public function setObjectif(string $objectif): self
    {
        $oldValue = $this->getObjectif();
        $this->objectif = $objectif;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Objectif", $oldValue, $objectif, Evenement::FORMAT_VALUE_PRIMITIVE));
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $oldValue = $this->getCreatedAt();
        $this->createdAt = $createdAt;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Date de création", $oldValue, $createdAt, Evenement::FORMAT_VALUE_PRIMITIVE));

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $oldValue = $this->getUpdatedAt();
        $this->updatedAt = $updatedAt;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Date de modification", $oldValue, $updatedAt, Evenement::FORMAT_VALUE_PRIMITIVE));

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
        $oldValue = $this->getMontant();
        $newValue = $montant;
        $this->montant = $montant;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Montant", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));
        return $this;
    }

    public function getExpiredAt(): ?\DateTimeImmutable
    {
        return $this->expiredAt;
    }

    public function setExpiredAt(\DateTimeImmutable $expiredAt): self
    {
        $oldValue = $this->getExpiredAt();
        $this->expiredAt = $expiredAt;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Date d'expiration", $oldValue, $expiredAt, Evenement::FORMAT_VALUE_PRIMITIVE));

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
        //Petit ajustement avant d'ajouter la cotation dans la piste
        $this->executer(new ComPisteAppliquerEntiteesParDefautPourCotation($this, $cotation));
        
        // dd("New Cotation", $cotation);

        if (!$this->cotations->contains($cotation)) {
            $oldValue = null;
            $this->cotations->add($cotation);
            //Ecouteur d'action
            $this->executer(new ComDetecterEvenementAttribut($this, "Liste des cotations", $oldValue, $cotation, Evenement::FORMAT_VALUE_ENTITY));
        }
        return $this;
    }

    public function removeCotation(Cotation $cotation): self
    {
        if ($this->cotations->removeElement($cotation)) {
            // set the owning side to null (unless already changed)
            if ($cotation->getPiste() === $this) {
                $oldValue = $cotation;
                $newValue = null;
                $cotation->setPiste(null);
                //Ecouteur d'action
                $this->executer(new ComDetecterEvenementAttribut($this, "Liste des cotations", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
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
        $oldValue = $this->getTypeavenant();
        $this->typeavenant = $typeavenant;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Type d'avenant", $oldValue, $typeavenant, Evenement::FORMAT_VALUE_PRIMITIVE));

        return $this;
    }

    public function getPolice(): ?Police
    {
        // dd("Je suis ici: ", $this->police);
        return $this->police;
    }

    public function setPolice(?Police $police): self
    {
        $oldValue = $this->getPolice();
        $this->police = $police;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Police", $oldValue, null, Evenement::FORMAT_VALUE_ENTITY));

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
            $oldValue = null;
            $this->contacts->add($contact);
            $contact->setPiste($this);
            //Ecouteur d'action
            $this->executer(new ComDetecterEvenementAttribut($this, "Liste des Contacts", $oldValue, $contact, Evenement::FORMAT_VALUE_ENTITY));
        }

        return $this;
    }

    public function removeContact(Contact $contact): self
    {
        if ($this->contacts->removeElement($contact)) {
            // set the owning side to null (unless already changed)
            if ($contact->getPiste() === $this) {
                $oldValue = $contact;
                $contact->setPiste(null);
                //Ecouteur d'action
                $this->executer(new ComDetecterEvenementAttribut($this, "Liste des Contacts", $oldValue, null, Evenement::FORMAT_VALUE_ENTITY));
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
            $oldValue = null;
            $newValue = $actionsCRM;
            $this->actionsCRMs->add($actionsCRM);
            $actionsCRM->setPiste($this);
            //Ecouteur d'action
            $this->executer(new ComDetecterEvenementAttribut($this, "Liste d'actions", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
        }

        return $this;
    }

    public function removeActionsCRM(ActionCRM $actionsCRM): self
    {
        if ($this->actionsCRMs->removeElement($actionsCRM)) {
            // set the owning side to null (unless already changed)
            if ($actionsCRM->getPiste() === $this) {
                $oldValue = $actionsCRM;
                $actionsCRM->setPiste(null);
                //Ecouteur d'action
                $this->executer(new ComDetecterEvenementAttribut($this, "Liste d'actions", $oldValue, null, Evenement::FORMAT_VALUE_ENTITY));
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
        //dd($client);
        //On ne tolère pas le secteur vide chez un client,
        //on doit en définir un par défaut
        if ($client->getSecteur() == null) {
            $client->setSecteur(ClientCrudController::TAB_CLIENT_SECTEUR["Autres Secteurs"]);
        }
        //On vide la liste des prospects car elle ne sert plus à rien
        //Le client est déjà connu/défini.
        $tabProspect = $this->getProspect();
        foreach ($tabProspect as $pros) {
            $this->removeProspect($pros);
        }

        //On exécute maintenane les codes de la gestion d'écouteurs
        $oldValue = $this->getClient();
        $newValue = $client;
        $this->client = $client;

        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Client", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));

        return $this;
    }

    /**
     * @return Collection<int, Client>
     */
    public function getProspect(): Collection
    {
        return $this->prospect;
    }

    public function addProspect(Client $newClient): self
    {
        if (!$this->prospect->contains($newClient)) {
            //Si le secteur n'est pas défini,
            //on doit en définir un par défaut
            if ($newClient->getSecteur() == null) {
                $newClient->setSecteur(ClientCrudController::TAB_CLIENT_SECTEUR["Autres Secteurs"]);
            }
            //Le prospect devient client
            $this->setClient($newClient);

            // dd("Ici", $prospect);

            $oldValue = null;
            $this->prospect->add($newClient);
            $newClient->setPiste($this);

            //On vide la liste des prospects
            $tabProspect = $this->getProspect();
            foreach ($tabProspect as $pros) {
                $this->removeProspect($pros);
            }

            //Ecouteur d'action
            $this->executer(new ComDetecterEvenementAttribut($this, "Liste des prospects", $oldValue, $newClient, Evenement::FORMAT_VALUE_ENTITY));
        }

        return $this;
    }

    public function removeProspect(Client $prospect): self
    {
        if ($this->prospect->removeElement($prospect)) {
            // set the owning side to null (unless already changed)
            if ($prospect->getPiste() === $this) {
                $oldValue = $prospect;
                $prospect->setPiste(null);
                //Ecouteur d'action
                $this->executer(new ComDetecterEvenementAttribut($this, "Liste des prospects", $oldValue, null, Evenement::FORMAT_VALUE_ENTITY));
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
        $oldValue = $this->getProduit();
        $newValue = $produit;
        $this->produit = $produit;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Produit", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));

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
            $this->setPartenaire($newpartenaire);
            // dd("Ici", $prospect);
            $oldValue = null;
            $newValue = $newpartenaire;
            $this->newpartenaire->add($newpartenaire);
            $newpartenaire->setPiste($this);

            //On vide la liste des new partenaires
            $newpartenaires = $this->getNewpartenaire();
            foreach ($newpartenaires as $partn) {
                $this->removeNewpartenaire($partn);
            }
            //Ecouteur d'action
            $this->executer(new ComDetecterEvenementAttribut($this, "Nouveau Partenaire", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
        }

        return $this;
    }

    public function removeNewpartenaire(Partenaire $newpartenaire): self
    {
        if ($this->newpartenaire->removeElement($newpartenaire)) {
            // set the owning side to null (unless already changed)
            if ($newpartenaire->getPiste() === $this) {
                $oldValue = $newpartenaire;
                $newValue = null;
                $newpartenaire->setPiste(null);
                //Ecouteur d'action
                $this->executer(new ComDetecterEvenementAttribut($this, "Nouveau Partenaire", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
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
        $oldValue = $this->getGestionnaire();
        $newValue = $gestionnaire;
        $this->gestionnaire = $gestionnaire;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Gestionnaire de compte", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));

        return $this;
    }

    public function getAssistant(): ?Utilisateur
    {
        return $this->assistant;
    }

    public function setAssistant(?Utilisateur $assistant): self
    {
        $oldValue = $this->getAssistant();
        $newValue = $assistant;
        $this->assistant = $assistant;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Assistant", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));

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
        if($police->getDateeffet() == null){
            $police->setDateeffet(new \DateTimeImmutable("now"));
        }
        if($police->getDateemission() == null){
            $police->setDateemission(new \DateTimeImmutable("now"));
        }
        if($police->getDateoperation() == null){
            $police->setDateoperation(new \DateTimeImmutable("now"));
        }
        $police->setIdAvenant(-1);
        // dd("Adding a policy", $police);

        if (!$this->polices->contains($police)) {
            $oldValue = null;
            $newValue = $police;
            $this->polices->add($police);
            $police->setPiste($this);
            //Ecouteur d'action
            $this->executer(new ComDetecterEvenementAttribut($this, "Liste des Polices", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
        }

        return $this;
    }

    public function removePolice(Police $police): self
    {
        if ($this->polices->removeElement($police)) {
            // set the owning side to null (unless already changed)
            if ($police->getPiste() === $this) {
                $oldValue = $police;
                $newValue = null;
                $police->setPiste(null);
                //Ecouteur d'action
                $this->executer(new ComDetecterEvenementAttribut($this, "Liste des Polices", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
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
            $document->setPiste($this);
            //Ecouteur d'action
            $this->executer(new ComDetecterEvenementAttribut($this, "Liste des Documents", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
        }

        return $this;
    }

    public function removeDocument(DocPiece $document): self
    {
        if ($this->documents->removeElement($document)) {
            // set the owning side to null (unless already changed)
            if ($document->getPiste() === $this) {
                $oldValue = $document;
                $newValue = null;
                $document->setPiste(null);
                //Ecouteur d'action
                $this->executer(new ComDetecterEvenementAttribut($this, "Liste des Documents", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
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
        $oldValue = $this->getEtape();
        $newValue = $etape;
        $this->etape = $etape;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Etape", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

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
        $this->chargements = new ArrayCollection();
        // dd("Liste des polices :", $this->getPolices());
        if (!isEmpty($this->getPolices())) {
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


    public function transfererObservateur(?Observateur $observateur)
    {
        //Transfer de l'observateur chez Tâche/ActionCRM
        if (count($this->getActionsCRMs()) != 0) {
            foreach ($this->getActionsCRMs() as $tache) {
                $tache->ajouterObservateur($observateur);
            }
        }
        //Transfère de l'observateur chez Chargement
        if (count($this->getChargements()) != 0) {
            foreach ($this->getChargements() as $chargement) {
                $chargement->ajouterObservateur($observateur);
            }
        }
        //Transfère de l'observateur chez Contact
        if (count($this->getContacts()) != 0) {
            foreach ($this->getContacts() as $contacts) {
                $contacts->ajouterObservateur($observateur);
            }
        }
        //Transfère de l'observateur chez Cotation
        if (count($this->getCotations()) != 0) {
            foreach ($this->getCotations() as $cotation) {
                $cotation->ajouterObservateur($observateur);
            }
        }
        //Transfère de l'observateur chez Documents
        if (count($this->getDocuments()) != 0) {
            foreach ($this->getDocuments() as $document) {
                $document->ajouterObservateur($observateur);
            }
        }
        //Transfère de l'observateur chez Police
        if (count($this->getPolices()) != 0) {
            foreach ($this->getPolices() as $police) {
                $police->ajouterObservateur($observateur);
            }
        }
    }
}
