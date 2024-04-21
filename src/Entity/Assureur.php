<?php

namespace App\Entity;

use App\Entity\Traits\TraitEcouteurEvenements;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\AssureurRepository;
use Doctrine\Common\Collections\Collection;
use App\Service\RefactoringJS\Evenements\Sujet;
use Doctrine\Common\Collections\ArrayCollection;
use App\Service\RefactoringJS\Commandes\Commande;
use App\Service\RefactoringJS\Evenements\Evenement;
use App\Service\RefactoringJS\Evenements\Observateur;
use Symfony\Component\Validator\Constraints as Assert;
use App\Service\RefactoringJS\Commandes\CommandeExecuteur;
use App\Service\RefactoringJS\Commandes\ComDetecterEvenementAttribut;
use App\Service\RefactoringJS\Commandes\Piste\CommandePisteNotifierEvenement;

use function PHPSTORM_META\override;

#[ORM\Entity(repositoryClass: AssureurRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Assureur implements Sujet, CommandeExecuteur
{
    use TraitEcouteurEvenements;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank(message: "Ce champ ne peut pas être vide.")]
    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[Assert\NotBlank(message: "Ce champ ne peut pas être vide.")]
    #[ORM\Column(length: 255)]
    private ?string $adresse = null;

    #[Assert\NotBlank(message: "Ce champ ne peut pas être vide.")]
    #[ORM\Column(length: 255)]
    private ?string $telephone = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $siteweb = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $rccm = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $idnat = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $licence = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $numimpot = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Entreprise $entreprise = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne]
    private ?Utilisateur $utilisateur = null;

    #[ORM\OneToMany(mappedBy: 'assureur', targetEntity: Cotation::class)]
    private Collection $cotations;

    #[ORM\OneToMany(mappedBy: 'assureur', targetEntity: Facture::class)]
    private Collection $factures;


    public function __construct()
    {
        $this->cotations = new ArrayCollection();
        $this->factures = new ArrayCollection();
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

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): self
    {
        $oldValue = $this->getAdresse();
        $newValue = $adresse;
        $this->adresse = $adresse;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Adresse", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));
        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): self
    {
        $oldValue = $this->getTelephone();
        $newValue = $telephone;
        $this->telephone = $telephone;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Numéro de téléphone", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $oldValue = $this->getEmail();
        $newValue = $email;
        $this->email = $email;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Adresse mail", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

        return $this;
    }

    public function getSiteweb(): ?string
    {
        return $this->siteweb;
    }

    public function setSiteweb(?string $siteweb): self
    {
        $oldValue = $this->getSiteweb();
        $newValue = $siteweb;
        $this->siteweb = $siteweb;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Site Internet", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

        return $this;
    }

    public function getRccm(): ?string
    {
        return $this->rccm;
    }

    public function setRccm(?string $rccm): self
    {
        $oldValue = $this->getRccm();
        $newValue = $rccm;
        $this->rccm = $rccm;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Registre de commerce (RCCM)", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

        return $this;
    }

    public function getIdnat(): ?string
    {
        return $this->idnat;
    }

    public function setIdnat(?string $idnat): self
    {
        $oldValue = $this->getIdnat();
        $newValue = $idnat;
        $this->idnat = $idnat;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Numéro d'Identification Nationale (idNat)", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

        return $this;
    }

    public function getLicence(): ?string
    {
        return $this->licence;
    }

    public function setLicence(?string $licence): self
    {
        $oldValue = $this->getLicence();
        $newValue = $licence;
        $this->licence = $licence;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Licence / Agrement du marché", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

        return $this;
    }

    public function getNumimpot(): ?string
    {
        return $this->numimpot;
    }

    public function setNumimpot(?string $numimpot): self
    {
        $oldValue = $this->getNumimpot();
        $newValue = $numimpot;
        $this->numimpot = $numimpot;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Numéro d'Intentité Fiscale", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

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
        return $this->nom != null ? $this->nom : "";
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
            $oldValue = null;
            $newValue = $cotation;
            $this->cotations->add($cotation);
            $cotation->setAssureur($this);
            //Ecouteur d'action
            $this->executer(new ComDetecterEvenementAttribut($this, "Cotation", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
        }

        return $this;
    }

    public function removeCotation(Cotation $cotation): self
    {
        if ($this->cotations->removeElement($cotation)) {
            // set the owning side to null (unless already changed)
            if ($cotation->getAssureur() === $this) {
                $oldValue = $cotation;
                $newValue = null;
                $cotation->setAssureur(null);
                //Ecouteur d'action
                $this->executer(new ComDetecterEvenementAttribut($this, "Cotation", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
            }
        }

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
            $facture->setAssureur($this);
            //Ecouteur d'action
            $this->executer(new ComDetecterEvenementAttribut($this, "Facture", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
        }

        return $this;
    }

    public function removeFacture(Facture $facture): self
    {
        if ($this->factures->removeElement($facture)) {
            // set the owning side to null (unless already changed)
            if ($facture->getAssureur() === $this) {
                $oldValue = $facture;
                $newValue = null;
                $facture->setAssureur(null);
                //Ecouteur d'action
                $this->executer(new ComDetecterEvenementAttribut($this, "Facture", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
            }
        }

        return $this;
    }

    
    public function transfererObservateur(?Observateur $observateur)
    {
        // dd("Fonction non encore définie");
    }
}
