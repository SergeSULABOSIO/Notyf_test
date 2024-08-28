<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ChargementRepository;
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

#[ORM\Entity(repositoryClass: ChargementRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Chargement implements Sujet, CommandeExecuteur
{
    use TraitEcouteurEvenements;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $type = null;
    private ?string $typeText = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private ?float $montant = null;

    #[ORM\ManyToOne]
    private ?Utilisateur $Utilisateur = null;

    #[ORM\ManyToOne]
    private ?Entreprise $entreprise = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'chargements', cascade: ['remove', 'persist', 'refresh'])]
    private ?Cotation $cotation = null;

    private ?Monnaie $monnaie_Affichage;


    public function __construct()
    {
        $this->listeObservateurs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $oldValue = $this->getType();
        $newValue = $type;
        $this->type = $type;
        //Ecouteur d'action
        $this->executer(new ComDetecterEvenementAttribut($this, "Type", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

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

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->Utilisateur;
    }

    public function setUtilisateur(?Utilisateur $Utilisateur): self
    {
        $this->Utilisateur = $Utilisateur;

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

    public function __toString()
    {
        $strMonnaie = $this->getCodeMonnaieAffichage();
        $strType = "";
        foreach (ChargementCrudController::TAB_TYPE_CHARGEMENT_ORDINAIRE as $key => $value) {
            if ($value == $this->type) {
                $strType = $key;
            }
        }
        //On calcul la prime totale
        return $strType . " (" . number_format($this->getMontant() / 100, 2, ",", ".") . $strMonnaie . ")";
    }

    private function getCodeMonnaieAffichage(): string
    {
        $strMonnaie = "";
        $monnaieAff = $this->getMonnaie_Affichage();
        if ($monnaieAff != null) {
            $strMonnaie = " " . $this->getMonnaie_Affichage()->getCode();
        }
        return $strMonnaie;
    }

    private function getMonnaie($fonction)
    {
        /** @var Cotation */
        $quote = $this->getCotation();
        if ($quote) {
            /** @var Entreprise */
            $ese = $quote->getEntreprise();
            if ($ese) {
                /** @var Monnaie */
                $currencies = $ese->getMonnaies();
                foreach ($currencies as $monnaie) {
                    if ($monnaie->getFonction() == $fonction) {
                        return $monnaie;
                    }
                }
            }
        }

        // $tabMonnaies = $this->getCotation()->getEntreprise()->getMonnaies();
        // foreach ($tabMonnaies as $monnaie) {
        //     if ($monnaie->getFonction() == $fonction) {
        //         return $monnaie;
        //     }
        // }
        return null;
    }

    /**
     * Get the value of typeText
     */
    public function getTypeText()
    {
        foreach (ChargementCrudController::TAB_TYPE_CHARGEMENT_ORDINAIRE as $nom => $code) {
            if ($code == $this->getType()) {
                $this->typeText = $nom;
            }
        }
        return $this->typeText;
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
        //Rien à transférer
        // dd("Cette fonction n'est pas encore définie");
    }
}
