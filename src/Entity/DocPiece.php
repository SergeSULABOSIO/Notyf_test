<?php

namespace App\Entity;

use App\Controller\Admin\DocPieceCrudController;
use App\Repository\DocPieceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

#[ORM\Entity(repositoryClass: DocPieceRepository::class)]
#[Vich\Uploadable]
class DocPiece
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Entreprise $entreprise = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    // NOTE: This is not a mapped field of entity metadata, just a simple property.
    #[Vich\UploadableField(mapping: 'DocPieces', fileNameProperty: 'nomfichier', size: 'taillefichier')]
    private ?File $document = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nomfichier = null;

    #[ORM\Column(nullable: true)]
    private ?int $taillefichier = null;

    #[ORM\Column(nullable: true)]
    private ?int $type = null;

    #[ORM\ManyToOne(inversedBy: 'documents')]
    private ?Cotation $cotation = null;

    public function __construct()
    {
        
    }

    public function setDocument(?File $document = null): void
    {
        $this->document = $document;

        if (null !== $document) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getDocument(): ?File
    {
        return $this->document;
    }

    public function setNomfichier(?string $nomfichier): void
    {
        $this->nomfichier = $nomfichier;
    }

    public function getNomfichier(): ?string
    {
        return $this->nomfichier;
    }


    public function setTaillefichier(?int $taillefichier): void
    {
        $this->taillefichier = $taillefichier;
    }

    public function getTaillefichier(): ?int
    {
        return $this->taillefichier;
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
        $txt = "";
        if($this->getType()){
            foreach (DocPieceCrudController::TAB_TYPES as $key => $value) {
                if($value == $this->getType()){
                    $txt = $key . ": ";
                    break;
                }
            }
        }
        if($this->getCreatedAt()){
            $txt = $txt . "[" . $this->nom . "], chargé le " . $this->getCreatedAt()->format("d-m-Y");
        }
        if($this->getUtilisateur()){
            $txt = $txt . " par " . $this->getUtilisateur()->getNom();
        }
        $txtFichier = "Fichier: \"" . $this->getNomfichier() . "\", taille: " . $this->convertirTailleFichier($this->getTaillefichier()) . " Kb | ";
        return $txtFichier . $txt;
    }

    private function convertirTailleFichier($tailleOctets):int{
        return ($tailleOctets / 1024);
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(?int $type): self
    {
        $this->type = $type;

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
