<?php

namespace App\Service\RefactoringJS\JSUIComponents\Piste;

use App\Entity\Piste;
use App\Service\ServiceTaxes;
use App\Service\ServiceMonnaie;
use Doctrine\ORM\EntityManager;
use App\Controller\Admin\PisteCrudController;
use App\Controller\Admin\PoliceCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use App\Service\RefactoringJS\Commandes\Commande;
use App\Controller\Admin\PreferenceCrudController;
use App\Controller\Admin\UtilisateurCrudController;
use App\Service\RefactoringJS\Commandes\CommandeExecuteur;
use App\Service\RefactoringJS\Commandes\Piste\ComGenerateTitreReportingCRM;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSChamp;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSPanelRenderer;

class PisteListeRenderer extends JSPanelRenderer implements CommandeExecuteur
{
    public function __construct(
        private EntityManager $entityManager,
        private ServiceMonnaie $serviceMonnaie,
        private ServiceTaxes $serviceTaxes,
        private string $pageName,
        private $objetInstance,
        private $crud,
        private AdminUrlGenerator $adminUrlGenerator
    ) {
        parent::__construct(self::TYPE_LISTE, $pageName, $objetInstance, $crud, $adminUrlGenerator);
    }


    public function design()
    {
        //Type Avenant
        $this->addChamp(
            (new JSChamp())
                ->createChoix('typeavenant', PreferenceCrudController::PREF_CRM_PISTE_TYPE_AVENANT)
                ->setChoices(PoliceCrudController::TAB_POLICE_TYPE_AVENANT)
                ->renderAsBadges([
                    // $value => $badgeStyleName
                    PoliceCrudController::TAB_POLICE_TYPE_AVENANT[PoliceCrudController::AVENANT_TYPE_ANNULATION] => 'dark', //info
                    PoliceCrudController::TAB_POLICE_TYPE_AVENANT[PoliceCrudController::AVENANT_TYPE_SOUSCRIPTION] => 'success', //info
                    PoliceCrudController::TAB_POLICE_TYPE_AVENANT[PoliceCrudController::AVENANT_TYPE_INCORPORATION] => 'info', //info
                    PoliceCrudController::TAB_POLICE_TYPE_AVENANT[PoliceCrudController::AVENANT_TYPE_PROROGATION] => 'success', //info
                    PoliceCrudController::TAB_POLICE_TYPE_AVENANT[PoliceCrudController::AVENANT_TYPE_RENOUVELLEMENT] => 'success',
                    PoliceCrudController::TAB_POLICE_TYPE_AVENANT[PoliceCrudController::AVENANT_TYPE_RESILIATION] => 'warning',
                    PoliceCrudController::TAB_POLICE_TYPE_AVENANT[PoliceCrudController::AVENANT_TYPE_RISTOURNE] => 'danger',
                    PoliceCrudController::TAB_POLICE_TYPE_AVENANT[PoliceCrudController::AVENANT_TYPE_AUTRE_MODIFICATION] => 'info'
                ])->getChamp()
        );
        //Nom
        $this->addChamp(
            (new JSChamp())
                ->createTexte('nom', PreferenceCrudController::PREF_CRM_PISTE_NOM)
                ->setFormatValue(function ($value, Piste $piste) {
                    $this->executer(new ComGenerateTitreReportingCRM(
                        $this->crud,
                        $this->serviceMonnaie,
                        $this->adminUrlGenerator,
                        $piste
                    ));
                    return $value;
                })
                ->getChamp()
        );
        //Etape
        $this->addChamp(
            (new JSChamp())
                ->createChoix('etape', PreferenceCrudController::PREF_CRM_PISTE_ETAPE)
                ->setChoices(PisteCrudController::TAB_ETAPES)
                ->getChamp()
        );
        //Client
        $this->addChamp(
            (new JSChamp())
                ->createAssociation('client', PreferenceCrudController::PREF_CRM_PISTE_CLIENT)
                ->getChamp()
        );
        //Produit
        $this->addChamp(
            (new JSChamp())
                ->createAssociation('produit', PreferenceCrudController::PREF_CRM_PISTE_PRODUIT)
                ->getChamp()
        );
        //Assureur
        $this->addChamp(
            (new JSChamp())
                ->createTexte('assureur', PreferenceCrudController::PREF_CRM_PISTE_ASSUREUR)
                ->getChamp()
        );
        //Potentiel - Caff espérés
        $this->addChamp(
            (new JSChamp())
                ->createArgent('montant', PreferenceCrudController::PREF_CRM_PISTE_MONTANT)
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setFormatValue(function ($value, Piste $entity) {
                    return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getMontant());
                })
                ->getChamp()
        );
        //Réalisation
        $this->addChamp(
            (new JSChamp())
                ->createArgent('realisation', PreferenceCrudController::PREF_CRM_PISTE_PRIME_TOTALE)
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setFormatValue(
                    function ($value, Piste $entity) {
                        return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getRealisation());
                    }
                )
                ->getChamp()
        );
        //Date opération
        $this->addChamp(
            (new JSChamp())
                ->createDate('expiredAt', PreferenceCrudController::PREF_CRM_PISTE_DATE_EXPIRATION)
                ->getChamp()
        );
        //Gestionnaire
        $this->addChamp(
            (new JSChamp())
                ->createAssociation('gestionnaire', PreferenceCrudController::PREF_CRM_PISTE_GESTIONNAIRE)
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
                ->getChamp()
        );
        //Date de modification
        $this->addChamp(
            (new JSChamp())
                ->createDate('updatedAt', PreferenceCrudController::PREF_CRM_PISTE_DATE_DE_MODIFICATION)
                ->getChamp()
        );
    }

    public function batchActions(?array $champs, ?string $type = null, ?string $pageName = null, $objetInstance = null, ?Crud $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array
    {
        return $champs;
    }

    public function executer(?Commande $commande)
    {
        if ($commande != null) {
            $commande->executer();
        }
    }
}
