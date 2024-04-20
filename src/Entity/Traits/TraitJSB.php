<?php
namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use App\Service\RefactoringJS\Evenements\Evenement;
use App\Service\RefactoringJS\Commandes\CommandeDetecterChangementAttribut;

trait TraitJSB
{


    /**
     * LES PRES
     */

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        //Avant modification
        dd("Pre persist est appellé !!!!!", $this);
        $oldValue = null;
        $newValue = $this;
        $this->executer(new CommandeDetecterChangementAttribut($this, "Piste", $oldValue, $newValue, Evenement::FORMAT_VALUE_ENTITY));
    }

    #[ORM\PreRemove]
    public function onPreRemove(): void
    {
        //Avant supprission
        dd("PreRemove est appellé !!!!!", $this);
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        //Avant mise à jour
        dd("PreUpdate est appellé !!!!!", $this);
    }

    /**
     * LES POSTS
     */

    #[ORM\PostLoad]
    public function onPostLoad(): void
    {
        //Après Chargement
        dd("PostLoad est appellé SERGE SULA BOSIO!!!!!", $this);
    }

    #[ORM\PostPersist]
    public function onPostPersist(): void
    {
        //Après enregistrement
        dd("PostPersist est appellé !!!!!", $this);
    }

    #[ORM\PostRemove]
    public function onPostRemove(): void
    {
        //Après suppression
        dd("PostRemove est appellé !!!!!", $this);
    }

    #[ORM\PostUpdate]
    public function onPostUpdate(): void
    {
        //Après mise à jour
        dd("PostUpdate est appellé !!!!!", $this);
    }
}
