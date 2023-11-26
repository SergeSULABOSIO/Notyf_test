<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\TrancheRepository;
use App\Controller\Admin\MonnaieCrudController;
use DateInterval;

#[ORM\Entity(repositoryClass: TrancheRepository::class)]
class Tranche
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $taux = null;

    #[ORM\ManyToOne]
    private ?Utilisateur $utilisateur = null;

    #[ORM\ManyToOne]
    private ?Entreprise $entreprise = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    private ?float $montant = 0;

    #[ORM\ManyToOne(inversedBy: 'tranches', cascade:['remove', 'persist', 'refresh'])]
    private ?Cotation $cotation = null;

    //#[ORM\Column]
    private ?\DateTimeImmutable $startedAt = null;

    //#[ORM\Column]
    private ?\DateTimeImmutable $endedAt = null;

    #[ORM\Column]
    private ?int $duree = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTaux(): ?float
    {
        return $this->taux;
    }

    public function setTaux(float $taux): self
    {
        $this->taux = $taux;

        return $this;
    }

    public function getTotalDureeTranchesPrecedantes($indiceCourant){
        $totalDureesCumulees = 0;
        /** @var Tranche */
        foreach ($this->getPolice()->getTranches() as $tranche) {
            if($this->getPolice()->getTranches()->indexOf($tranche) < $indiceCourant){
                $totalDureesCumulees = $totalDureesCumulees + $tranche->getDuree();
            }
        }
        return $totalDureesCumulees;
    }

    public function getStartedAt(): ?\DateTimeInterface
    {
        /** @var Police */
        $police = $this->getPolice();
        //dd($indice);
        if($police != null){
            $indiceCourant = ($police->getTranches()->indexOf($this));
            $dureesPrecedantes = $this->getTotalDureeTranchesPrecedantes($indiceCourant);
            $this->startedAt = $police->getDateeffet()->add(new DateInterval("P". ($dureesPrecedantes) ."M"));
            $this->endedAt = $this->startedAt->add(new DateInterval("P". ($this->getDuree()) ."M"));
            $this->endedAt = $this->endedAt->modify("-1 day");
        }
        //dd($this->startedAt);
        return $this->startedAt;
    }

    public function getEndedAt(): ?\DateTimeInterface
    {
        return $this->endedAt;
    }

    private function getPolice(){
        /** @var Police */
        $police = null;
        if($this->getCotation()){
            if($this->getCotation()->isValidated()){
                if(count($this->getCotation()->getPolices()) != 0){
                    $police = $this->getCotation()->getPolices()[0];
                }
            }
        }
        return $police;
    }

    public function setStartedAt(\DateTimeInterface $startedAt): self
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    

    public function setEndedAt(\DateTimeInterface $endedAt): self
    {
        $this->endedAt = $endedAt;

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

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get the value of montant
     */ 
    public function getMontant()
    {
        $mont = 0;
        if($this->getCotation() != null){
            $mont = (($this->getCotation()->getPrimeTotale() / 100) * $this->getTaux());
        }
        $this->montant = $mont;
        return $this->montant;
    }

    /**
     * Set the value of montant
     *
     * @return  self
     */ 
    public function setMontant($montant)
    {
        $this->montant = $montant;

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
        $strPeriode = " pour durée de " . $this->getDuree() . " mois. ";
        //dd($this->getStartedAt());
        if($this->getStartedAt() != null & $this->getEndedAt() != null){
            $strPeriode = ". Cette tranche est valide du " . (($this->startedAt)->format('d-m-Y')) . " au " . (($this->endedAt)->format('d-m-Y')) . " (les deux dates comprises).";
        }
        $strMont = "la prime de cette tranche est de " . number_format($this->getMontant(), 2, ",", ".") . $strMonnaie . " soit " . ($this->getTaux() * 100) . "% de " . number_format(($this->getCotation()->getPrimeTotale() / 100), 2, ",", ".") . $strMonnaie . $strPeriode;
        return $this->getNom() . ": " . $strMont;
    }

    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(int $duree): self
    {
        $this->duree = $duree;

        return $this;
    }

    private function getCodeMonnaieAffichage(): string{
        $strMonnaie = "";
        $monnaieAff = $this->getMonnaie_Affichage();
        if($monnaieAff != null){
            $strMonnaie = " " . $this->getMonnaie_Affichage()->getCode();
        }
        return $strMonnaie;
    }

    private function getMonnaie_Affichage()
    {
        $monnaie = $this->getMonnaie(MonnaieCrudController::TAB_MONNAIE_FONCTIONS[MonnaieCrudController::FONCTION_SAISIE_ET_AFFICHAGE]);
        if($monnaie == null){
            $monnaie = $this->getMonnaie(MonnaieCrudController::TAB_MONNAIE_FONCTIONS[MonnaieCrudController::FONCTION_AFFICHAGE_UNIQUEMENT]);
        }
        return $monnaie;
    }

    private function getMonnaie($fonction)
    {
        $tabMonnaies = $this->getEntreprise()->getMonnaies();
        foreach ($tabMonnaies as $monnaie) {
            if($monnaie->getFonction() == $fonction){
                return $monnaie;
            }
        }
        return null;
    }
}
