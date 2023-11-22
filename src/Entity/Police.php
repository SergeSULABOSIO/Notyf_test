<?php

namespace App\Entity;

use App\Controller\Admin\PisteCrudController;
use App\Controller\Admin\PoliceCrudController;
use DateTime;
use DateInterval;
use DateTimeImmutable;
use App\Entity\Utilisateur;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\PoliceRepository;
use Doctrine\Common\Collections\Collection;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PoliceRepository::class)]
class Police
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank(message: "Veuillez fournir la référence de la police.")]
    #[ORM\Column(length: 255)]
    private ?string $reference = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: false)]
    private ?\DateTimeInterface $dateoperation = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: false)]
    private ?\DateTimeInterface $dateemission = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: false)]
    private ?\DateTimeInterface $dateeffet = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateexpiration = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Entreprise $entreprise = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne]
    private ?Utilisateur $utilisateur = null;

    #[ORM\ManyToOne(inversedBy: 'polices', cascade: ['remove', 'persist', 'refresh'])]
    private ?Piste $piste = null;

    #[ORM\OneToOne] //(cascade: ['persist', 'remove'])
    private ?Cotation $cotation = null;

    #[ORM\Column]
    private ?int $idAvenant = null;

    //Champs calculés sur base des données existantes dans la base
    private ?Utilisateur $gestionnaire = null;
    private ?Utilisateur $assistant = null;
    private ?string $typeavenant = null;
    private ?Collection $chargements = null;



    public function __construct()
    {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateoperation(): ?\DateTimeInterface
    {
        return $this->dateoperation;
    }

    public function setDateoperation(\DateTimeInterface $dateoperation): self
    {
        $this->dateoperation = $dateoperation;

        return $this;
    }

    public function getDateemission(): ?\DateTimeInterface
    {
        return $this->dateemission;
    }

    public function setDateemission(?\DateTimeInterface $dateemission): self
    {
        $this->dateemission = $dateemission;

        return $this;
    }

    public function getDateeffet(): ?\DateTimeInterface
    {
        return $this->dateeffet;
    }

    public function setDateeffet(?\DateTimeInterface $dateeffet): self
    {
        $this->dateeffet = $dateeffet;

        return $this;
    }

    private function ajouterJours(DateTimeImmutable $dateInitiale, $nbJours): DateTimeImmutable
    {
        $txt = "P" . $nbJours . "D";
        $copie = clone $dateInitiale;
        return $copie->add(new DateInterval($txt));
    }

    public function convertDuree($duree): int
    {
        if ($duree == 0) {
            $duree = 12;
        }
        return (($duree / 12) * 365) - 2;
    }

    // public function getDateexpiration(): ?\DateTimeInterface
    // {
    //     if($this->dateexpiration == null){
    //         $duree = $this->convertDuree($this->getCotation()->getDureeCouverture());
    //         $this->dateexpiration = $this->ajouterJours($this->getDateeffet(), $duree);
    //     }
    //     return $this->dateexpiration;
    // }

    // public function setDateexpiration(\DateTimeInterface $dateexpiration): self
    // {
    //     $this->dateexpiration = $dateexpiration;

    //     return $this;
    // }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): self
    {
        $this->reference = $reference;

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

    public function getGestionnaire(): ?Utilisateur
    {
        if ($this->getPiste()) {
            $this->gestionnaire = $this->getPiste()->getGestionnaire();
        }
        //dd($this->gestionnaire);
        return $this->gestionnaire;
    }

    public function setGestionnaire(?Utilisateur $gestionnaire): self
    {
        $this->gestionnaire = $gestionnaire;

        return $this;
    }

    public function __toString()
    {
        $strAutreData = "";
        if ($this->getCotation()) {
            $strAutreData = $this->getCotation()->getAssureur()->getNom();
        }
        return "Réf.: " . $this->getReference() . " / " . $strAutreData; // . " / " . $this->getPrimetotale()/100 . " / " . $this->client->getNom() . " / " . $this->getAssureur()->getNom() . " / " . $this->getProduit()->getNom();
    }

    /**
     * Get the value of assistant
     */
    public function getAssistant()
    {
        if ($this->getPiste()) {
            $this->assistant = $this->getPiste()->getAssistant();
        }
        return $this->assistant;
    }

    public function getPiste(): ?Piste
    {
        return $this->piste;
    }

    public function setPiste(?Piste $piste): self
    {
        $this->piste = $piste;

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

    public function getDateexpiration(): ?\DateTimeInterface
    {
        // if ($this->dateexpiration == null) {
        //     $duree = $this->convertDuree($this->getCotation()->getDureeCouverture());
        //     $this->dateexpiration = $this->ajouterJours($this->getDateeffet(), $duree);
        // }
        return $this->dateexpiration;
    }

    public function setDateexpiration(\DateTimeInterface $dateexpiration): self
    {
        $this->dateexpiration = $dateexpiration;

        return $this;
    }

    public function getIdAvenant(): ?int
    {
        return $this->idAvenant;
    }

    public function setIdAvenant(int $idAvenant): self
    {
        $this->idAvenant = $idAvenant;

        return $this;
    }

    /**
     * Get the value of typeavenant
     */ 
    public function getTypeavenant()
    {
        if($this->getPiste()){
            foreach (PoliceCrudController::TAB_POLICE_TYPE_AVENANT as $key => $value) {
                if($value == $this->typeavenant){
                    $this->typeavenant = $key;
                    break;
                }
            }
        }
        return $this->typeavenant;
    }

    /**
     * Get the value of chargements
     */ 
    public function getChargements()
    {
        return $this->chargements;
    }
}
