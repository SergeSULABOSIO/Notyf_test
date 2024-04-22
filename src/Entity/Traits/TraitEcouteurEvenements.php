<?php

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use App\Service\RefactoringJS\Commandes\Commande;
use App\Service\RefactoringJS\Evenements\Evenement;
use App\Service\RefactoringJS\Evenements\Observateur;
use App\Service\RefactoringJS\Commandes\ComDetecterEvenementEntite;
use App\Service\RefactoringJS\Commandes\Piste\ComPisteNotifierEvenement;

trait TraitEcouteurEvenements
{

    //Evenements
    private ?ArrayCollection $listeObservateurs = null;



    /**
     * LES METHODES NECESSAIRES AUX ECOUTEURS D'ACTIONS
     */


    public abstract function transfererObservateur(?Observateur $observateur);

    public function ajouterObservateur(?Observateur $observateur)
    {
        // Ajout observateur
        $this->initListeObservateurs();
        if (!$this->listeObservateurs->contains($observateur)) {
            $this->listeObservateurs->add($observateur);
        }
        //On transfère les observateurs aux autres sous entités via une fonction abstraite à définir
        $this->transfererObservateur($observateur);
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

    public function notifierLesObservateurs(?Evenement $evenement)
    {
        $this->executer(new ComPisteNotifierEvenement($this->listeObservateurs, $evenement));
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

    public function setListeObservateurs(ArrayCollection $listeObservateurs)
    {
        $this->listeObservateurs = $listeObservateurs;
    }








    /**
     * LES PRES - ECOUTEUR DES LIFECYCLE CALLBACK
     */

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        //Avant modification
        // dd("Pre persist est appellé !!!!!", $this);
        $this->executer(new ComDetecterEvenementEntite($this, Evenement::TYPE_ENTITE_AVANT_ENREGISTREMENT));
    }

    #[ORM\PreRemove]
    public function onPreRemove(): void
    {
        //Avant supprission
        // dd("PreRemove est appellé !!!!!", $this);
        $this->executer(new ComDetecterEvenementEntite($this, Evenement::TYPE_ENTITE_AVANT_SUPPRESSION));
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        //Avant mise à jour
        // dd("PreUpdate est appellé !!!!!", $this);
        $this->executer(new ComDetecterEvenementEntite($this, Evenement::TYPE_ENTITE_AVANT_EDITION));
    }




    /**
     * LES POSTS - ECOUTEUR DES LIFECYCLE CALLBACK
     */

    #[ORM\PostLoad]
    public function onPostLoad(): void
    {
        //Après Chargement
        // dd("PostLoad est appellé SERGE SULA BOSIO!!!!!", $this);
        $this->executer(new ComDetecterEvenementEntite($this, Evenement::TYPE_ENTITE_APRES_CHARGEMENT));
    }

    #[ORM\PostPersist]
    public function onPostPersist(): void
    {
        //Après enregistrement
        // dd("PostPersist est appellé !!!!!", $this);
        $this->executer(new ComDetecterEvenementEntite($this, Evenement::TYPE_ENTITE_APRES_ENREGISTREMENT));
    }

    #[ORM\PostRemove]
    public function onPostRemove(): void
    {
        //Après suppression
        // dd("PostRemove est appellé !!!!!", $this);
        $this->executer(new ComDetecterEvenementEntite($this, Evenement::TYPE_ENTITE_APRES_SUPPRESSION));
    }

    #[ORM\PostUpdate]
    public function onPostUpdate(): void
    {
        //Après mise à jour
        // dd("PostUpdate est appellé !!!!!", $this);
        $this->executer(new ComDetecterEvenementEntite($this, Evenement::TYPE_ENTITE_APRES_EDITION));
    }
}
