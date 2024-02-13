<?php

namespace App\Service\RefactoringJS\Initisateurs\Facture;

use App\Entity\Assureur;
use App\Entity\ElementFacture;
use App\Entity\Entreprise;
use App\Entity\Facture;
use App\Entity\Partenaire;
use App\Entity\Tranche;
use App\Entity\Utilisateur;
use DateTimeImmutable;

interface FactureInit
{
    public const PARAM_FINAL = "final";
    public const PARAM_DIFFERENCES = "differences";
    public const PARAM_SAME_MONTANT = "sameMontant";
    public const PARAM_SAME_PARTENAIRE = "samePartenaire";
    public const PARAM_SAME_CLIENT = "sameClient";
    public const PARAM_SAME_ASSUREUR = "sameAssureur";
    public const PARAM_SAME_TRANCHE = "sameTranche";
    //Fonctions de creation de la Facture
    public function setComptesBancaires();
    public function setTranche(?Tranche $tranche);
    public function setSignedBy(?string $signataire);
    public function setPosteSignedBy(?string $posteSignataire);
    public function setStatus(?int $status);
    public function setAutreTiers(?string $autreTiers);
    public function setPartenaire(?Partenaire $partenaire);
    public function setAssureur(?Assureur $assureur);
    public function setDescription(?string $description);
    public function setReference(?string $reference);
    public function setType(?int $typeFacture);
    public function setEntreprise(?Entreprise $entreprise);
    public function setUtilisateur(?Utilisateur $utilisateur);
    public function setCreatedAt(?DateTimeImmutable $dateCreation);
    public function setUpdatedAt(?DateTimeImmutable $dateModification);
    public function produireElementsFacture():array;
    public function produireElementFacture():?ElementFacture;
    public function setTotalDu(?float $montantDu);
    public function addElementFacture(?ElementFacture $elementFacture);
    public function addElementsFacture(?array $TabElementsFactures);
    public function generateDescriptionFacture(): string;
    public function generateInvoiceReference(?int $indice):?string;
    //Production de la facture
    public function buildFacture(?int $indice, ?Tranche $tranche):?Facture;
    public function loadSavedFactures(?Tranche $tranche):?array;
    public function areEqual(?array $anciennesFactures, ?Facture $nouvelleFacture);
    public function saveFacture();
    public function reset();
}
