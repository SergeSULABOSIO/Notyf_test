<?php

namespace App\Entity;

use App\Repository\PreferenceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PreferenceRepository::class)]
class Preference
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Entreprise $entreprise = null;

    #[ORM\Column(nullable: true)]
    private ?int $crmTaille = null;

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private array $crmMissions = [];

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private array $crmFeedbacks = [];

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private array $crmCotations = [];

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private array $crmEtapes = [];

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private array $crmPistes = [];

    #[ORM\Column]
    private ?int $apparence = null;

    #[ORM\Column]
    private ?int $proTaille = null;

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private array $proAssureurs = [];

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private array $proAutomobiles = [];

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private array $proContacts = [];

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private array $proClients = [];

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private array $proPartenaires = [];

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private array $proPolices = [];

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private array $proProduits = [];

    #[ORM\Column]
    private ?int $finTaille = null;

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private array $finTaxes = [];

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private array $finMonnaies = [];

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private array $finCommissionsPayees = [];

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private array $finRetrocommissionsPayees = [];

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private array $finTaxesPayees = [];

    #[ORM\Column]
    private ?int $sinTaille = null;

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private array $sinCommentaires = [];

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private array $sinEtapes = [];

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private array $sinExperts = [];

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private array $sinSinistres = [];

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private array $sinVictimes = [];

    #[ORM\Column]
    private ?int $bibTaille = null;

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private array $bibCategories = [];

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private array $bibClasseurs = [];

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private array $bibPieces = [];

    #[ORM\Column]
    private ?int $parTaille = null;

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private array $parUtilisateurs = [];

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private array $finFactures = [];

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private array $finElementFactures = [];

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private array $finCompteBancaires = [];

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private array $finPaiement = [];

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private array $finRevenu = [];

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private array $prodChargement = [];

    #[ORM\Column(type: Types::ARRAY)]
    private array $proTranches = [];


    public function getId(): ?int
    {
        return $this->id;
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

    public function setUtilisateur(Utilisateur $utilisateur): self
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

    public function getCrmTaille(): ?int
    {
        return $this->crmTaille;
    }

    public function setCrmTaille(?int $crmTaille): self
    {
        $this->crmTaille = $crmTaille;

        return $this;
    }

    public function getCrmMissions(): array
    {
        return $this->crmMissions;
    }

    public function setCrmMissions(?array $crmMissions): self
    {
        $this->crmMissions = $crmMissions;

        return $this;
    }

    public function getCrmFeedbacks(): array
    {
        return $this->crmFeedbacks;
    }

    public function setCrmFeedbacks(?array $crmFeedbacks): self
    {
        $this->crmFeedbacks = $crmFeedbacks;

        return $this;
    }

    public function getCrmCotations(): array
    {
        return $this->crmCotations;
    }

    public function setCrmCotations(?array $crmCotations): self
    {
        $this->crmCotations = $crmCotations;

        return $this;
    }

    public function getCrmEtapes(): array
    {
        return $this->crmEtapes;
    }

    public function setCrmEtapes(?array $crmEtapes): self
    {
        $this->crmEtapes = $crmEtapes;

        return $this;
    }

    public function getCrmPistes(): array
    {
        return $this->crmPistes;
    }

    public function setCrmPistes(?array $crmPistes): self
    {
        $this->crmPistes = $crmPistes;

        return $this;
    }

    public function getApparence(): ?int
    {
        return $this->apparence;
    }

    public function setApparence(int $apparence): self
    {
        $this->apparence = $apparence;

        return $this;
    }

    public function getProTaille(): ?int
    {
        return $this->proTaille;
    }

    public function setProTaille(int $proTaille): self
    {
        $this->proTaille = $proTaille;

        return $this;
    }

    public function getProAssureurs(): array
    {
        return $this->proAssureurs;
    }

    public function setProAssureurs(?array $proAssureurs): self
    {
        $this->proAssureurs = $proAssureurs;

        return $this;
    }

    public function getProAutomobiles(): array
    {
        return $this->proAutomobiles;
    }

    public function setProAutomobiles(?array $proAutomobiles): self
    {
        $this->proAutomobiles = $proAutomobiles;

        return $this;
    }

    public function getProContacts(): array
    {
        return $this->proContacts;
    }

    public function setProContacts(?array $proContacts): self
    {
        $this->proContacts = $proContacts;

        return $this;
    }

    public function getProClients(): array
    {
        return $this->proClients;
    }

    public function setProClients(?array $proClients): self
    {
        $this->proClients = $proClients;

        return $this;
    }

    public function getProPartenaires(): array
    {
        return $this->proPartenaires;
    }

    public function setProPartenaires(?array $proPartenaires): self
    {
        $this->proPartenaires = $proPartenaires;

        return $this;
    }

    public function getProPolices(): array
    {
        return $this->proPolices;
    }

    public function setProPolices(?array $proPolices): self
    {
        $this->proPolices = $proPolices;

        return $this;
    }

    public function getProProduits(): array
    {
        return $this->proProduits;
    }

    public function setProProduits(?array $proProduits): self
    {
        $this->proProduits = $proProduits;

        return $this;
    }

    public function getFinTaille(): ?int
    {
        return $this->finTaille;
    }

    public function setFinTaille(int $finTaille): self
    {
        $this->finTaille = $finTaille;

        return $this;
    }

    public function getFinTaxes(): array
    {
        return $this->finTaxes;
    }

    public function setFinTaxes(?array $finTaxes): self
    {
        $this->finTaxes = $finTaxes;

        return $this;
    }

    public function getFinMonnaies(): array
    {
        return $this->finMonnaies;
    }

    public function setFinMonnaies(?array $finMonnaies): self
    {
        $this->finMonnaies = $finMonnaies;

        return $this;
    }

    public function getFinCommissionsPayees(): array
    {
        return $this->finCommissionsPayees;
    }

    public function setFinCommissionsPayees(?array $finCommissionsPayees): self
    {
        $this->finCommissionsPayees = $finCommissionsPayees;

        return $this;
    }

    public function getFinRetrocommissionsPayees(): array
    {
        return $this->finRetrocommissionsPayees;
    }

    public function setFinRetrocommissionsPayees(?array $finRetrocommissionsPayees): self
    {
        $this->finRetrocommissionsPayees = $finRetrocommissionsPayees;

        return $this;
    }

    public function getFinTaxesPayees(): array
    {
        return $this->finTaxesPayees;
    }

    public function setFinTaxesPayees(?array $finTaxesPayees): self
    {
        $this->finTaxesPayees = $finTaxesPayees;

        return $this;
    }

    public function getSinTaille(): ?int
    {
        return $this->sinTaille;
    }

    public function setSinTaille(int $sinTaille): self
    {
        $this->sinTaille = $sinTaille;

        return $this;
    }

    public function getSinCommentaires(): array
    {
        return $this->sinCommentaires;
    }

    public function setSinCommentaires(?array $sinCommentaires): self
    {
        $this->sinCommentaires = $sinCommentaires;

        return $this;
    }

    public function getSinEtapes(): array
    {
        return $this->sinEtapes;
    }

    public function setSinEtapes(?array $sinEtapes): self
    {
        $this->sinEtapes = $sinEtapes;

        return $this;
    }

    public function getSinExperts(): array
    {
        return $this->sinExperts;
    }

    public function setSinExperts(?array $sinExperts): self
    {
        $this->sinExperts = $sinExperts;

        return $this;
    }

    public function getSinSinistres(): array
    {
        return $this->sinSinistres;
    }

    public function setSinSinistres(?array $sinSinistres): self
    {
        $this->sinSinistres = $sinSinistres;

        return $this;
    }

    public function getSinVictimes(): array
    {
        return $this->sinVictimes;
    }

    public function setSinVictimes(?array $sinVictimes): self
    {
        $this->sinVictimes = $sinVictimes;

        return $this;
    }

    public function getBibTaille(): ?int
    {
        return $this->bibTaille;
    }

    public function setBibTaille(int $bibTaille): self
    {
        $this->bibTaille = $bibTaille;

        return $this;
    }

    public function getBibCategories(): array
    {
        return $this->bibCategories;
    }

    public function setBibCategories(?array $bibCategories): self
    {
        $this->bibCategories = $bibCategories;

        return $this;
    }

    public function getBibClasseurs(): array
    {
        return $this->bibClasseurs;
    }

    public function setBibClasseurs(?array $bibClasseurs): self
    {
        $this->bibClasseurs = $bibClasseurs;

        return $this;
    }

    public function getBibPieces(): array
    {
        return $this->bibPieces;
    }

    public function setBibPieces(?array $bibPieces): self
    {
        $this->bibPieces = $bibPieces;

        return $this;
    }

    public function getParTaille(): ?int
    {
        return $this->parTaille;
    }

    public function setParTaille(int $parTaille): self
    {
        $this->parTaille = $parTaille;

        return $this;
    }

    public function getParUtilisateurs(): array
    {
        return $this->parUtilisateurs;
    }

    public function setParUtilisateurs(?array $parUtilisateurs): self
    {
        $this->parUtilisateurs = $parUtilisateurs;

        return $this;
    }

    public function __toString()
    {
        return "Paramètres d'affichage / " . $this->utilisateur;
    }

    public function getFinFactures(): array
    {
        return $this->finFactures;
    }

    public function setFinFactures(?array $finFactures): self
    {
        $this->finFactures = $finFactures;

        return $this;
    }

    public function getFinElementFactures(): array
    {
        return $this->finElementFactures;
    }

    public function setFinElementFactures(?array $finElementFactures): self
    {
        $this->finElementFactures = $finElementFactures;

        return $this;
    }

    public function getFinCompteBancaires(): array
    {
        return $this->finCompteBancaires;
    }

    public function setFinCompteBancaires(?array $finCompteBancaires): self
    {
        $this->finCompteBancaires = $finCompteBancaires;

        return $this;
    }

    public function getFinPaiement(): array
    {
        return $this->finPaiement;
    }

    public function setFinPaiement(?array $finPaiement): self
    {
        $this->finPaiement = $finPaiement;

        return $this;
    }

    public function getFinRevenu(): array
    {
        return $this->finRevenu;
    }

    public function setFinRevenu(?array $finRevenu): self
    {
        $this->finRevenu = $finRevenu;

        return $this;
    }

    public function getProdChargement(): array
    {
        return $this->prodChargement;
    }

    public function setProdChargement(?array $prodChargement): self
    {
        $this->prodChargement = $prodChargement;

        return $this;
    }

    public function getProTranches(): array
    {
        return $this->proTranches;
    }

    public function setProTranches(array $proTranches): self
    {
        $this->proTranches = $proTranches;

        return $this;
    }
}
