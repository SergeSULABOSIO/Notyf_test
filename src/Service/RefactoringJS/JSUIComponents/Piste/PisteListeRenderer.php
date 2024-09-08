<?php

namespace App\Service\RefactoringJS\JSUIComponents\Piste;

use App\Entity\Piste;
use App\Service\ServiceTaxes;
use App\Service\ServiceMonnaie;
use Doctrine\ORM\EntityManager;
use App\Controller\Admin\PisteCrudController;
use App\Controller\Admin\PoliceCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use App\Controller\Admin\PreferenceCrudController;
use App\Controller\Admin\UtilisateurCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSChamp;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSPanelRenderer;

class PisteListeRenderer extends JSPanelRenderer
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
        //Id
        $this->addChamp(
            (new JSChamp())
                ->createNombre('id', PreferenceCrudController::PREF_CRM_PISTE_ID, 0)
                ->getChamp()
        );
        //Etape
        $this->addChamp(
            (new JSChamp())
                ->createChoix('etape', PreferenceCrudController::PREF_CRM_PISTE_ETAPE)
                ->setChoices(PisteCrudController::TAB_ETAPES)
                ->getChamp()
        );
        //Nom
        $this->addChamp(
            (new JSChamp())
                ->createTexte('nom', PreferenceCrudController::PREF_CRM_PISTE_NOM)
                ->getChamp()
        );
        //Objectif
        $this->addChamp(
            (new JSChamp())
                ->createZonneTexte('objectif', PreferenceCrudController::PREF_CRM_PISTE_OBJECTIF)
                ->getChamp()
        );
        //Date opération
        $this->addChamp(
            (new JSChamp())
                ->createDate('expiredAt', PreferenceCrudController::PREF_CRM_PISTE_DATE_EXPIRATION)
                ->getChamp()
        );
        //Cotation
        $this->addChamp(
            (new JSChamp())
                ->createTableau('cotations', PreferenceCrudController::PREF_CRM_PISTE_COTATION)
                ->setTemplatePath('admin/segment/view_cotations.html.twig')
                ->getChamp()
        );
        //Type Avenant
        $this->addChamp(
            (new JSChamp())
                ->createChoix('typeavenant', PreferenceCrudController::PREF_CRM_PISTE_TYPE_AVENANT)
                ->setChoices(PoliceCrudController::TAB_POLICE_TYPE_AVENANT)
                ->getChamp()
        );
        //Police
        $this->addChamp(
            (new JSChamp())
                ->createTexte('police', "Police source")
                ->getChamp()
        );
        //Tâche / Action
        $this->addChamp(
            (new JSChamp())
                ->createTableau('actionsCRMs', PreferenceCrudController::PREF_CRM_PISTE_ACTIONS)
                ->setTemplatePath('admin/segment/view_taches.html.twig')
                ->getChamp()
        );
        //Client
        $this->addChamp(
            (new JSChamp())
                ->createTexte('client', PreferenceCrudController::PREF_CRM_PISTE_CLIENT)
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
        //Partenaire
        $this->addChamp(
            (new JSChamp())
                ->createAssociation('partenaire', PreferenceCrudController::PREF_CRM_PISTE_PARTENAIRE)
                ->getChamp()
        );
        //Gestionnaire
        $this->addChamp(
            (new JSChamp())
                ->createAssociation('gestionnaire', PreferenceCrudController::PREF_CRM_PISTE_GESTIONNAIRE)
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
                ->getChamp()
        );
        //Assistant
        $this->addChamp(
            (new JSChamp())
                ->createAssociation('assistant', PreferenceCrudController::PREF_CRM_PISTE_ASSISTANT)
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
                ->getChamp()
        );
        //Utilisateur
        $this->addChamp(
            (new JSChamp())
                ->createAssociation('utilisateur', PreferenceCrudController::PREF_CRM_PISTE_UTILISATEUR)
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
                ->getChamp()
        );
        //Date de création
        $this->addChamp(
            (new JSChamp())
                ->createDate('createdAt', PreferenceCrudController::PREF_CRM_PISTE_DATE_DE_CREATION)
                ->getChamp()
        );
        //Date de modification
        $this->addChamp(
            (new JSChamp())
                ->createDate('updatedAt', PreferenceCrudController::PREF_CRM_PISTE_DATE_DE_MODIFICATION)
                ->getChamp()
        );
        //Entreprise
        $this->addChamp(
            (new JSChamp())
                ->createAssociation('entreprise', PreferenceCrudController::PREF_CRM_PISTE_ENTREPRISE)
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
                ->getChamp()
        );

        //************************ Contacts ********************* */
        //Section - Contacts
        $this->addChamp(
            (new JSChamp())
                ->createSection(" Détails relatifs aux Contacts")
                ->setIcon("fas fa-address-book")
                ->getChamp()
        );
        //Contacts
        $this->addChamp(
            (new JSChamp())
                ->createTableau('contacts', "Détails")
                ->setTemplatePath('admin/segment/view_contacts.html.twig')
                ->getChamp()
        );
        //*********************** Police ********************** */
        //Section - Police
        $this->addChamp(
            (new JSChamp())
                ->createSection(' Couverture en place')
                ->setIcon('fas fa-file-shield')
                ->setHelp("Polices d'assurance et/ou avenant mis en place.")
                ->getChamp()
        );
        //Polices
        $this->addChamp(
            (new JSChamp())
                ->createTableau('polices', "Police en place")
                ->getChamp()
        );
        //Assureur
        $this->addChamp(
            (new JSChamp())
                ->createTexte('assureur', PreferenceCrudController::PREF_CRM_PISTE_ASSUREUR)
                ->getChamp()
        );
        //Produit
        $this->addChamp(
            (new JSChamp())
                ->createTexte('produit', PreferenceCrudController::PREF_CRM_PISTE_PRODUIT)
                ->getChamp()
        );
        //Date d'effet
        $this->addChamp(
            (new JSChamp())
                ->createDate('dateEffet', "Date d'effet")
                ->getChamp()
        );
        //Date d'expiration
        $this->addChamp(
            (new JSChamp())
                ->createDate('dateExpiration', "Date d'expiration")
                ->getChamp()
        );
        //Durée
        $this->addChamp(
            (new JSChamp())
                ->createDate('duree', "Durée")
                ->setFormatValue(function ($value, Piste $entity) {
                    return $value . " mois.";
                })
                ->getChamp()
        );
        //Revenus
        $this->addChamp(
            (new JSChamp())
                ->createTableau('revenus', "Structure du revenu")
                ->setTemplatePath('admin/segment/view_revenus.html.twig')
                ->getChamp()
        );
        //Chargements
        $this->addChamp(
            (new JSChamp())
                ->createTableau('chargements', "Structure de la prime")
                ->setTemplatePath('admin/segment/view_chargements.html.twig')
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
        //Tranches
        $this->addChamp(
            (new JSChamp())
                ->createTableau('tranches', "Termes de paiement")
                ->setTemplatePath('admin/segment/view_tranches.html.twig')
                ->getChamp()
        );
    }

    public function batchActions(?array $champs, ?string $type = null, ?string $pageName = null, $objetInstance = null, ?Crud $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array
    {
        return $champs;
    }
}
