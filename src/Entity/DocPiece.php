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

    #[ORM\ManyToOne(inversedBy: 'documents')]
    private ?Police $police = null;

    #[ORM\ManyToOne(inversedBy: 'documents')]
    private ?Piste $piste = null;

    #[ORM\ManyToOne(inversedBy: 'documents')]
    private ?ActionCRM $actionCRM = null;

    #[ORM\ManyToOne(inversedBy: 'documents')]
    private ?Paiement $paiement = null;

    private ?string $nomType;

    private ?int $codeFormatFichier;
    private ?string $logoFormatFichier;

    #[ORM\ManyToOne(inversedBy: 'documents')]
    private ?Facture $facture = null;

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
        if ($this->getType()) {
            foreach (DocPieceCrudController::TAB_TYPES as $key => $value) {
                if ($value == $this->getType()) {
                    $txt = $key . ": ";
                    break;
                }
            }
        }
        if ($this->getCreatedAt()) {
            $txt = $txt . "[" . $this->nom . "], chargé le " . $this->getCreatedAt()->format("d-m-Y");
        }
        if ($this->getUtilisateur()) {
            $txt = $txt . " par " . $this->getUtilisateur()->getNom();
        }
        $txtFichier = "Fichier: \"" . $this->getNomfichier() . "\", taille: " . $this->convertirTailleFichier($this->getTaillefichier()) . " Kb | ";
        return $txtFichier . $txt;
    }

    private function convertirTailleFichier($tailleOctets): int
    {
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
        if ($this->cotation == null) {
            if ($this->getPolice()) {
                $this->cotation = $this->getPolice()->getCotation();
            }
        }
        return $this->cotation;
    }

    public function setCotation(?Cotation $cotation): self
    {
        $this->cotation = $cotation;

        return $this;
    }

    public function getPolice(): ?Police
    {
        if ($this->police == null) {
            if ($this->piste != null) {
                foreach ($this->piste->getCotations() as $cotation) {
                    if ($cotation->isValidated()) {
                        $this->police = $cotation->getPolices()[0];
                        //dd($this->police);
                    }
                }
            } else if ($this->cotation != null) {
                if($this->cotation->isValidated()){
                    $this->police = $this->cotation->getPolices()[0];
                }
            }
        }
        return $this->police;
    }

    public function setPolice(?Police $police): self
    {
        $this->police = $police;

        return $this;
    }

    public function getPiste(): ?Piste
    {
        if ($this->piste == null) {
            if ($this->getPolice()) {
                $this->piste = $this->getPolice()->getPiste();
            } else if ($this->getCotation()) {
                $this->piste = $this->getCotation()->getPiste();
            }
        }
        return $this->piste;
    }

    public function setPiste(?Piste $piste): self
    {
        $this->piste = $piste;

        return $this;
    }

    public function getPaiement(): ?Paiement
    {
        return $this->paiement;
    }

    public function setPaiement(?Paiement $paiement): self
    {
        $this->paiement = $paiement;

        return $this;
    }

    public function getActionCRM(): ?ActionCRM
    {
        return $this->actionCRM;
    }

    public function setActionCRM(?ActionCRM $actionCRM): self
    {
        $this->actionCRM = $actionCRM;

        return $this;
    }

    /**
     * Get the value of nomType
     */
    public function getNomType()
    {
        foreach (DocPieceCrudController::TAB_TYPES as $nom => $code) {
            if ($code == $this->getType()) {
                $this->nomType = $nom;
            }
        }
        return $this->nomType;
    }

    /**
     * Get the value of codeFormatFichier
     */
    public function getCodeFormatFichier()
    {

        return $this->codeFormatFichier;
    }

    /**
     * Get the value of logoFormatFichier
     */
    public function getLogoFormatFichier()
    {
        $marque = "LOGOIMAGE";
        $couleur = "LOGOCOULEUR";
        $htmlLogo = "<h3 class=\"" . $couleur . "\"><i class=\"" . $marque . "\"></i></h3>";
        if (str_ends_with($this->nomfichier, ".pdf")) {
            $htmlLogo = str_replace($marque, "fa-solid fa-file-pdf", $htmlLogo);
            $htmlLogo = str_replace($couleur, "text-danger", $htmlLogo);
        } else if (str_ends_with($this->nomfichier, ".docx")) {
            $htmlLogo = str_replace($marque, "fa-solid fa-file-word", $htmlLogo);
            $htmlLogo = str_replace($couleur, "text-info", $htmlLogo);
        } else if (str_ends_with($this->nomfichier, ".doc")) {
            $htmlLogo = str_replace($marque, "fa-solid fa-file-word", $htmlLogo);
            $htmlLogo = str_replace($couleur, "text-info", $htmlLogo);
        } else if (str_ends_with($this->nomfichier, ".xls")) {
            $htmlLogo = str_replace($marque, "fa-solid fa-file-excel", $htmlLogo);
            $htmlLogo = str_replace($couleur, "text-success", $htmlLogo);
        } else if (str_ends_with($this->nomfichier, ".xlsx")) {
            $htmlLogo = str_replace($marque, "fa-solid fa-file-excel", $htmlLogo);
            $htmlLogo = str_replace($couleur, "text-success", $htmlLogo);
        } else if (str_ends_with($this->nomfichier, ".zip")) {
            $htmlLogo = str_replace($marque, "fa-solid fa-file-zipper", $htmlLogo);
            $htmlLogo = str_replace($couleur, "text-warning", $htmlLogo);
        } else if (str_ends_with($this->nomfichier, ".eml")) {
            $htmlLogo = str_replace($marque, "fa-solid fa-envelope-open-text", $htmlLogo);
            $htmlLogo = str_replace($couleur, "text-secondary", $htmlLogo);
        } else if (str_ends_with($this->nomfichier, ".jpg")) {
            $htmlLogo = str_replace($marque, "fa-regular fa-image", $htmlLogo);
            $htmlLogo = str_replace($couleur, "text-black", $htmlLogo);
        } else if (str_ends_with($this->nomfichier, ".png")) {
            $htmlLogo = str_replace($marque, "fa-regular fa-image", $htmlLogo);
            $htmlLogo = str_replace($couleur, "text-black", $htmlLogo);
        } else if (str_ends_with($this->nomfichier, ".gif")) {
            $htmlLogo = str_replace($marque, "fa-regular fa-image", $htmlLogo);
            $htmlLogo = str_replace($couleur, "text-black", $htmlLogo);
        } else {
            $htmlLogo = str_replace($marque, "fa-solid fa-file", $htmlLogo);
            $htmlLogo = str_replace($couleur, "text-secondary", $htmlLogo);
        }
        $this->logoFormatFichier = $htmlLogo;
        return $this->logoFormatFichier;
    }

    public function getFacture(): ?Facture
    {
        return $this->facture;
    }

    public function setFacture(?Facture $facture): self
    {
        $this->facture = $facture;

        return $this;
    }
}
