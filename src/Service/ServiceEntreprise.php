<?php

namespace App\Service;

use App\Entity\Entreprise;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\Boolean;
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
        //Chargement de l'utilisateur
        $this->utilisateur = $this->security->getUser();

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
        //dd($someRepository->findAll());
    }

    public function hasEntreprise(){
        return $this->hasEntreprise;
    }

    public function isAdministrateur(){
        return $this->isAdmin;
    }

    public function getEntreprise()
    {
        return $this->entreprise == null? null : $this->entreprise;
    }

    public function getUtilisateur(): Utilisateur
    {
        return $this->utilisateur;
    }
}