<?php

namespace App\Service;

use App\Entity\ActionCRM;
use App\Entity\Entreprise;
use App\Entity\Sinistre;
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

            case self::PRODUCTION_POLICE: //Il faut supprimer les données filles
                $this->supprimerEntiteSingleton($entityObject);
                break;

            case self::PAREMETRE_UTILISATEUR: //Il faut supprimer les données filles
                $this->supprimerEntiteSingleton($entityObject);
                break;

            case self::PRODUCTION_ASSUREUR: //Il faut supprimer les données filles
                $this->supprimerEntiteSingleton($entityObject);
                break;

            case self::PRODUCTION_PARTENAIRE: //Il faut supprimer les données filles
                $this->supprimerEntiteSingleton($entityObject);
                break;

            case self::PRODUCTION_PRODUIT:
                $this->supprimerEntiteSingleton($entityObject);
                break;

            case self::PRODUCTION_CLIENT:
                $this->supprimerEntiteSingleton($entityObject);
                break;

            case self::SINISTRE_COMMENTAIRE:
                $this->supprimerEntiteSingleton($entityObject);
                break;

            case self::SINISTRE_ETAPE:
                $this->supprimerEntiteSingleton($entityObject);
                break;

            case self::SINISTRE_VICTIME:
                $this->supprimerEntiteSingleton($entityObject);
                break;

            case self::SINISTRE_SINISTRE:
                $this->supprimerEntiteSingleton($entityObject);
                break;

            case self::CRM_FEEDBACK:
                $this->supprimerEntiteSingleton($entityObject);
                break;

            case self::CRM_ACTION:
                $this->supprimerEntiteSingleton($entityObject);
                break;

            case self::CRM_ETAPE:
                $this->supprimerEntiteSingleton($entityObject);
                break;

            case self::CRM_PISTE:
                $this->supprimerEntiteSingleton($entityObject);
                break;

            case self::BIBLIOTHEQUE_PIECE:
                $this->supprimerEntiteSingleton($entityObject);
                break;

            case self::BIBLIOTHEQUE_CATEGORIE:
                $this->supprimerEntiteSingleton($entityObject);
                break;

            case self::BIBLIOTHEQUE_CLASSE:
                $this->supprimerEntiteSingleton($entityObject);
                break;

            case self::PAREMETRE_ENTREPRISE:
                $this->supprimerEntreprise($entityObject);
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
            $message = $this->serviceEntreprise->getUtilisateur()->getNom() . ", Il n'est pas possible de supprimer cet enregistrement car il est déjà utilisé dans une ou plusières rubriques. Cette suppression violeraît les restrictions relatives à la sécurité des données.";
            $this->afficherFlashMessage("danger", $message);
        }
    }


    public function supprimerEntreprise($entityInstance)
    {
        try {
            $isAdmin = $entityInstance->getUtilisateur() == $this->serviceEntreprise->getUtilisateur();
            //dd($isAdmin);
            if ($isAdmin == true) {
                $this->activerContrainteIntegrite(true);

                //Suppression des Missions / Actions dans CRM
                $this->detruireEntites($this->entityManager->getRepository(ActionCRM::class)->findBy(
                    ['entreprise' => $this->serviceEntreprise->getEntreprise()]
                ));


                $this->activerContrainteIntegrite(false);
                $this->afficherFlashMessage("success", "Suppression effectuée ave succès!");
            } else {
                $message = "Désolé " . $this->serviceEntreprise->getUtilisateur()->getNom() . ", seul l'administrateur peut supprimer cette entreprise.";
                $this->afficherFlashMessage("danger", $message);
            }
        } catch (\Throwable $th) {
            //dd($th);
            $message = $this->serviceEntreprise->getUtilisateur()->getNom() . ", Il n'est pas possible de supprimer cet enregistrement car il est déjà utilisé dans une ou plusières rubriques. Cette suppression violeraît les restrictions relatives à la sécurité des données.";
            $this->afficherFlashMessage("danger", $message);
        }
    }

    private function detruireEntites($liste)
    {
        foreach ($liste as $entite) {
            $this->detruireEntite($entite);
        }
    }

    private function detruireEntite($entityInstance): string
    {
        $deleted = false;
        try {
            //Delete here
            $this->entityManager->remove($entityInstance);
            $this->entityManager->flush();
            $deleted = true;
        } catch (\Throwable $th) {
            //throw $th;
            $deleted = false;
        }
        return $deleted;
    }



    public function afficherFlashMessage($type, $message)
    {
        $flashBag = $this->requestStack->getMainRequest()->getSession()->getFlashBag();
        $flashBag->add($type, $message);
    }

    public function activerContrainteIntegrite($activer)
    {
        if ($activer == true) {
            $this->entityManager->getConnection()->beginTransaction();
            $this->entityManager->getConnection()->executeStatement("SET FOREIGN_KEY_CHECKS = 0");
        } else {
            $this->entityManager->getConnection()->executeStatement("SET FOREIGN_KEY_CHECKS = 1");
            $this->entityManager->getConnection()->commit();
        }
    }
}
