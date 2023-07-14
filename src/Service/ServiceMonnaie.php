<?php

namespace App\Service;

use App\Entity\Entreprise;
use App\Entity\Monnaie;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Bundle\SecurityBundle\Security;


class ServiceMonnaie
{
    private ?Utilisateur $utilisateur = null;
    private ?Entreprise $entreprise = null;
    private $monnaies = [];

    public function __construct(
        private EntityManagerInterface $entityManager,
        private ServiceEntreprise $serviceEntreprise,
        private Security $security
    ) {
        //Chargement de l'utilisateur et de l'entreprise
        $this->utilisateur = $this->serviceEntreprise->getUtilisateur();
        $this->entreprise = $this->serviceEntreprise->getEntreprise();

        //Chargement des monnaies
        $this->chargerMonnaies();
    }

    public function chargerMonnaies()
    {
        $this->monnaies = $this->entityManager->getRepository(Monnaie::class)->findBy(
            ['entreprise' => $this->entreprise]
        );
    }

}
