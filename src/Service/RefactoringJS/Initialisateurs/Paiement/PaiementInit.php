<?php

namespace App\Service\RefactoringJS\Initialisateurs\Paiement;

use App\Entity\CompteBancaire;
use App\Entity\DocPiece;
use App\Entity\Entreprise;
use App\Entity\Facture;
use App\Entity\Paiement;
use App\Entity\Utilisateur;
use DateTimeImmutable;

interface PaiementInit
{
    public const PARAM_FINAL = "final";
    public const PARAM_DIFFERENCES = "differences";
    public const PARAM_SAME_MONTANT = "sameMontant";
    public const PARAM_SAME_FACTURE = "sameFacture";
    //Fonctions de creation du paiement
    public function setFacture(?Facture $facture);
    public function setPaidAt(?DateTimeImmutable $paidAt);
    public function setMontant(?float $paidAmount);
    public function setType(?int $typePaiement);
    public function setDescription(?string $description);
    public function setEntreprise(?Entreprise $entreprise);
    public function setUtilisateur(?Utilisateur $utilisateur);
    public function setCreatedAt(?DateTimeImmutable $dateCreation);
    public function setUpdatedAt(?DateTimeImmutable $dateModification);
    public function setCompteBancaire(?CompteBancaire $compteBancaire);
    public function addDocument(?DocPiece $document);
    
    //Production du paiement
    public function buildPaiement(?Facture $facture, ?DateTimeImmutable $dateOfPayment, ?Utilisateur $utilisateur, ?float $paidAmount):?Paiement;
    public function loadSavedPaiements(?Facture $facture):?array;
    public function areEqual(?array $anciennesPaiements, ?Paiement $nouveauPaiement);
    public function savePaiement();
    public function reset();
}
