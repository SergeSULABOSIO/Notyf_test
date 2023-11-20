<?php

namespace App\Entity;

use App\Entity\Utilisateur;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\PoliceRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PoliceRepository::class)]
class Police
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank(message:"Veuillez fournir la référence de la police.")]
    #[ORM\Column(length: 255)]
    private ?string $reference = null;
    
    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: false)]
    private ?\DateTimeInterface $dateoperation = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: false)]
    private ?\DateTimeInterface $dateemission = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: false)]
    private ?\DateTimeInterface $dateeffet = null;

    //#[ORM\Column(type: Types::DATE_MUTABLE)]
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

    private ?Utilisateur $gestionnaire = null;
    
    private ?Utilisateur $assistant = null;

    #[ORM\ManyToOne(inversedBy: 'polices', cascade:['remove', 'persist', 'refresh'])]
    private ?Piste $piste = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Cotation $cotation = null;

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

    public function getDateexpiration(): ?\DateTimeInterface
    {
        return $this->dateexpiration;
    }

    public function setDateexpiration(\DateTimeInterface $dateexpiration): self
    {
        $this->dateexpiration = $dateexpiration;

        return $this;
    }

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
        if($this->getPiste()){
            $this->gestionnaire = $this->getPiste()->getUtilisateur();
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
        return "Réf.: " . $this->getReference();// . " / " . $this->getPrimetotale()/100 . " / " . $this->client->getNom() . " / " . $this->getAssureur()->getNom() . " / " . $this->getProduit()->getNom();
    }

    /**
     * Get the value of assistant
     */ 
    public function getAssistant()
    {
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
}
