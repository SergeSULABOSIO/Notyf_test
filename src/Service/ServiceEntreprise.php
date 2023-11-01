<?php

namespace App\Service;

use App\Entity\Entreprise;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class ServiceEntreprise
{
    private ?Utilisateur $utilisateur = null;
    private ?Entreprise $entreprise = null;
    private $isAdmin = false;
    private $hasEntreprise = false;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private Security $security
    ) {
        //dd($this->security->getUser());
        if ($this->security != null) {
            if ($this->security->getUser()) {
                //Chargement de l'utilisateur
                /** @var Utilisateur */
                $this->utilisateur = $this->security->getUser();
                if ($this->utilisateur != null) {
                    //Chargement de l'entreprise de cet utilisateur
                    //Si nous somme en face d'un administrateur
                    if ($this->utilisateur->getEntreprise() == null) {
                        $this->isAdmin = true;
                        $this->hasEntreprise = false;
                        foreach (($this->entityManager->getRepository(Entreprise::class))->findAll() as $entreprise) {
                            if ($entreprise->getUtilisateur() == $this->utilisateur) {
                                $this->entreprise = $entreprise;
                                $this->hasEntreprise = true;
                                break;
                            }
                        }
                    } else {
                        $this->entreprise = $this->utilisateur->getEntreprise();
                        $this->isAdmin = false;
                        $this->hasEntreprise = true;
                    }
                }
            }
        }
    }

    public function hasEntreprise()
    {
        return $this->hasEntreprise;
    }

    public function isAdministrateur()
    {
        return $this->isAdmin;
    }

    public function getEntreprise()
    {
        return $this->entreprise == null ? null : $this->entreprise;
    }

    public function getUtilisateur()
    {
        return $this->utilisateur;
    }
}
