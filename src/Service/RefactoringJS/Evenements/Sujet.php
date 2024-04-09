<?php

namespace App\Service\RefactoringJS\Evenements;

use App\Entity\Entreprise;
use App\Entity\Utilisateur;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;

interface Sujet
{
    public function initListeObservateurs();
    public function ajouterObservateur(?Observateur $observateur);
    public function retirerObservateur(?Observateur $observateur);
    public function viderListeObservateurs();
    public function getListeObservateurs():?ArrayCollection;
    public function setListeObservateurs(ArrayCollection $listeObservateurs);
    public function notifierLesObservateurs(?Evenement $evenement);

    public function getId():?int;
    public function getUtilisateur():?Utilisateur;
    public function getEntreprise():?Entreprise;
    public function getUpdatedAt():?DateTimeImmutable;
    public function getCreatedAt():?DateTimeImmutable;
    public function setUtilisateur(?Utilisateur $utilisateur);
    public function setEntreprise(?Entreprise $entreprise);
    public function setUpdatedAt(\DateTimeImmutable $createAt);
    public function setCreatedAt(\DateTimeImmutable $createAt);
}
