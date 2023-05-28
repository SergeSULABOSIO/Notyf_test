<?php

namespace App\Service;

use App\Entity\Entreprise;
use App\Entity\Utilisateur;
use App\Service\ServiceEntreprise as ServiceServiceEntreprise;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Bundle\SecurityBundle\Security;


class ServiceSuppression
{

    public const PAIEMENT_TAXE = 0;
    public const PAIEMENT_COMMISSION = 1;

    public function __construct(
        private ServiceServiceEntreprise $serviceEntreprise,
        private EntityManagerInterface $entityManager,
        private Security $security
    ) {
    }

    public function supprimer($entityObject, $entityIndex)
    {
        switch ($entityIndex) {
            case self::PAIEMENT_TAXE:
                $this->supprimerEntiteSingleton($entityObject);
                break;

            case self::PAIEMENT_COMMISSION:
                $this->supprimerEntiteSingleton($entityObject);
                break;
            default:
                # code...
                break;
        }
    }

    public function supprimerEntiteSingleton($entityInstance)
    {
        $this->entityManager->remove($entityInstance);
        $this->entityManager->flush();
    }
}
