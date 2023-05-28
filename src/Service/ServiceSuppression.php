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
use Symfony\Component\HttpFoundation\RequestStack;

class ServiceSuppression
{
    public const FINANCE_TAXE = 0;
    public const FINANCE_MONNAIE = 1;
    public const FINANCE_PAIEMENT_TAXE = 2;
    public const FINANCE_PAIEMENT_COMMISSION = 3;
    public const FINANCE_PAIEMENT_PARTENAIRE = 4;

    public const PRODUCTION_CONTACT = 5;
    public const PRODUCTION_ENGIN = 6;
    public const PRODUCTION_POLICE = 7;
    public const PRODUCTION_ASSUREUR = 8;
    public const PRODUCTION_PRODUIT = 9;
    public const PRODUCTION_CLIENT = 10;
    public const PRODUCTION_PARTENAIRE = 11;

    public const SINISTRE_COMMENTAIRE = 12;
    public const SINISTRE_ETAPE = 13;
    public const SINISTRE_VICTIME = 14;
    public const SINISTRE_EXPERT = 15;
    public const SINISTRE_SINISTRE = 16;

    public const CRM_COTATION = 17;
    public const CRM_ACTION = 18;
    public const CRM_FEEDBACK = 19;
    public const CRM_ETAPE = 20;
    public const CRM_PISTE = 21;

    public const BIBLIOTHEQUE_PIECE = 22;
    public const BIBLIOTHEQUE_CLASSE = 23;
    public const BIBLIOTHEQUE_CATEGORIE = 24;

    public const PAREMETRE_UTILISATEUR = 25;
    public const PAREMETRE_ENTREPRISE = 26;

    public function __construct(
        private RequestStack $requestStack,
        private ServiceServiceEntreprise $serviceEntreprise,
        private EntityManagerInterface $entityManager,
        private Security $security
    ) {
    }

    public function supprimer($entityObject, $entityIndex)
    {
        switch ($entityIndex) {
            case self::FINANCE_PAIEMENT_TAXE:
                $this->supprimerEntiteSingleton($entityObject);
                break;

            case self::FINANCE_PAIEMENT_COMMISSION:
                $this->supprimerEntiteSingleton($entityObject);
                break;

            case self::FINANCE_PAIEMENT_PARTENAIRE:
                $this->supprimerEntiteSingleton($entityObject);
                break;

            case self::FINANCE_MONNAIE: //Il faut supprimer les données filles
                $this->supprimerEntiteSingleton($entityObject);
                break;

            case self::PRODUCTION_CONTACT:
                $this->supprimerEntiteSingleton($entityObject);
                break;

            case self::PRODUCTION_ENGIN:
                $this->supprimerEntiteSingleton($entityObject);
                break;

            case self::FINANCE_TAXE: //Il faut supprimer les données filles
                $this->supprimerEntiteSingleton($entityObject);
                break;

            default:
                dd("Cette fonction n'est pas encore disponible.");
                break;
        }
    }

    public function supprimerEntiteSingleton($entityInstance)
    {
        try {
            $this->entityManager->remove($entityInstance);
            $this->entityManager->flush();
        } catch (\Throwable $th) {
            $flashBag = $this->requestStack->getMainRequest()->getSession()->getFlashBag();
            $message = $this->serviceEntreprise->getUtilisateur()->getNom() . ", Il n'est pas possible de supprimer '" . $entityInstance . "' car elle est déjà utilisée dans une ou plusières rubriques.";
            $flashBag->add('danger', $message);
        }
    }
}
