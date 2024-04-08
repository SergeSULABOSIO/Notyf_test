<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ChargementRepository;
use App\Controller\Admin\MonnaieCrudController;
use App\Service\RefactoringJS\Evenements\Sujet;
use Doctrine\Common\Collections\ArrayCollection;
use App\Service\RefactoringJS\Commandes\Commande;
use App\Controller\Admin\ChargementCrudController;
use App\Service\RefactoringJS\Evenements\Evenement;
use App\Service\RefactoringJS\Evenements\Observateur;
use App\Service\RefactoringJS\Commandes\CommandeExecuteur;
use App\Service\RefactoringJS\Commandes\CommandeDetecterChangementAttribut;
use App\Service\RefactoringJS\Commandes\Piste\CommandePisteNotifierEvenement;

#[ORM\Entity(repositoryClass: ChargementRepository::class)]
class Chargement implements Sujet, CommandeExecuteur
{
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

    //Evenements
    private ?ArrayCollection $listeObservateurs = null;


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
        $this->executer(new CommandeDetecterChangementAttribut($this, "Type", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

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
        $this->executer(new CommandeDetecterChangementAttribut($this, "Description", $oldValue, $newValue, Evenement::FORMAT_VALUE_PRIMITIVE));

        return $this;
    }

    public function getMontant(): ?float
    {
        return $this->montant;
    }

    public function setMontant(float $montant): self
    {
        $this->montant = $montant;

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
        $this->cotation = $cotation;

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

    // private function getMonnaie_Affichage()
    // {
    //     $monnaieAffichage = $this->getMonnaie(MonnaieCrudController::TAB_MONNAIE_FONCTIONS[MonnaieCrudController::FONCTION_SAISIE_ET_AFFICHAGE]);
    //     if($monnaieAffichage == null){
    //         $monnaieAffichage = $this->getMonnaie(MonnaieCrudController::TAB_MONNAIE_FONCTIONS[MonnaieCrudController::FONCTION_AFFICHAGE_UNIQUEMENT]);
    //     }
    //     return $monnaieAffichage;
    // }

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

    /**
     * LES METHODES NECESSAIRES AUX ECOUTEURS D'ACTIONS
     */


    public function ajouterObservateur(?Observateur $observateur)
    {
        // Ajout observateur
        $this->initListeObservateurs();
        if (!$this->listeObservateurs->contains($observateur)) {
            $this->listeObservateurs->add($observateur);
        }
    }

    public function retirerObservateur(?Observateur $observateur)
    {
        $this->initListeObservateurs();
        if ($this->listeObservateurs->contains($observateur)) {
            $this->listeObservateurs->removeElement($observateur);
        }
    }

    public function viderListeObservateurs()
    {
        $this->initListeObservateurs();
        if (!$this->listeObservateurs->isEmpty()) {
            $this->listeObservateurs = new ArrayCollection([]);
        }
    }

    public function getListeObservateurs(): ?ArrayCollection
    {
        return $this->listeObservateurs;
    }

    public function setListeObservateurs(ArrayCollection $listeObservateurs)
    {
        $this->listeObservateurs = $listeObservateurs;
    }

    public function notifierLesObservateurs(?Evenement $evenement)
    {
        $this->executer(new CommandePisteNotifierEvenement($this->listeObservateurs, $evenement));
    }

    public function initListeObservateurs()
    {
        if ($this->listeObservateurs == null) {
            $this->listeObservateurs = new ArrayCollection();
        }
    }

    public function executer(?Commande $commande)
    {
        if ($commande != null) {
            $commande->executer();
        }
    }
}
