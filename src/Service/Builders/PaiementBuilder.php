<?php

namespace App\Service\Builders;

use App\Entity\Assureur;
use App\Entity\CompteBancaire;
use App\Entity\DocPiece;
use App\Entity\ElementFacture;
use App\Entity\Entreprise;
use App\Entity\Facture;
use App\Entity\Paiement;
use App\Entity\Partenaire;
use App\Entity\Police;
use App\Entity\Tranche;
use App\Entity\Utilisateur;
use DateTimeImmutable;

interface PaiementBuilder
{
    //Fonctions de creation du paiement
    public function setFacture(?Facture $facture);
    public function setPaidAt(?DateTimeImmutable $paidAt);
    public function setMontant(?float $paidAmount);
    public function setType(?int $typePaiement);
    public function setTypeFacture(?int $typeFacture);
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
