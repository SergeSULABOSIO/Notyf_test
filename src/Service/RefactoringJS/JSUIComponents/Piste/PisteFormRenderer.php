<?php

namespace App\Service\RefactoringJS\JSUIComponents\Piste;

use App\Entity\Piste;
use App\Service\ServiceTaxes;
use App\Service\ServiceMonnaie;
use Doctrine\ORM\EntityManager;
use App\Service\ServiceEntreprise;
use Doctrine\ORM\EntityRepository;
use App\Controller\Admin\PisteCrudController;
use App\Controller\Admin\ClientCrudController;
use App\Controller\Admin\PoliceCrudController;
use App\Controller\Admin\ContactCrudController;
use App\Controller\Admin\CotationCrudController;
use App\Controller\Admin\DocPieceCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use App\Controller\Admin\ActionCRMCrudController;
use App\Controller\Admin\PartenaireCrudController;
use App\Controller\Admin\PreferenceCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSChamp;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSPanelRenderer;

class PisteFormRenderer extends JSPanelRenderer
{
    public function __construct(
        private ServiceEntreprise $serviceEntreprise,
        private EntityManager $entityManager,
        private ServiceMonnaie $serviceMonnaie,
        private ServiceTaxes $serviceTaxes,
        private string $pageName,
        private $objetInstance,
        private $crud,
        private AdminUrlGenerator $adminUrlGenerator
    ) {
        parent::__construct(self::TYPE_FORMULAIRE, $pageName, $objetInstance, $crud, $adminUrlGenerator);
    }

    public function design()
    {
        $column = 12;
        if ($this->objetInstance instanceof Piste) {
            $column = 10;
        }
        //*********************** Principale ************************** */
        //Section - Principale
        $this->addChamp(
            (new JSChamp())
                ->createOnglet(' Informations générales')
                ->setIcon('fas fa-location-crosshairs') //<i class="fa-sharp fa-solid fa-address-book"></i>
                ->setHelp("Une piste est un prospect (ou client potientiel) à suivre stratégiquement afin de lui convertir en client.")
                ->setColumns($column)
                ->getChamp()
        );
        //Etape
        $this->addChamp(
            (new JSChamp())
                ->createChoix('etape', PreferenceCrudController::PREF_CRM_PISTE_ETAPE)
                ->setChoices(PisteCrudController::TAB_ETAPES)
                ->setColumns($column)
                ->getChamp()
        );
        //Nom
        $this->addChamp(
            (new JSChamp())
                ->createTexte('nom', PreferenceCrudController::PREF_CRM_PISTE_NOM)
                ->setColumns($column)
                ->getChamp()
        );
        //Produit
        $this->addChamp(
            (new JSChamp())
                ->createAssociation('produit', PreferenceCrudController::PREF_CRM_PISTE_PRODUIT)
                ->setColumns($column)
                ->setRequired(true)
                ->setFormTypeOptions('query_builder', function (EntityRepository $entityRepository) {
                    return $entityRepository
                        ->createQueryBuilder('e')
                        ->Where('e.entreprise = :ese')
                        ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
                })
                ->getChamp()
        );
        //Potentiel - Caff espérés
        $this->addChamp(
            (new JSChamp())
                ->createArgent('montant', PreferenceCrudController::PREF_CRM_PISTE_MONTANT)
                ->setCurrency($this->serviceMonnaie->getCodeSaisie())
                ->setColumns($column)
                ->getChamp()
        );
        //Type Avenant
        $this->addChamp(
            (new JSChamp())
                ->createChoix('typeavenant', PreferenceCrudController::PREF_CRM_PISTE_TYPE_AVENANT)
                ->setChoices(PoliceCrudController::TAB_POLICE_TYPE_AVENANT)
                ->setColumns($column)
                ->getChamp()
        );
        //Date opération
        $this->addChamp(
            (new JSChamp())
                ->createDate('expiredAt', PreferenceCrudController::PREF_CRM_PISTE_DATE_EXPIRATION)
                ->setColumns($column)
                ->getChamp()
        );
        //Police
        $this->addChamp(
            (new JSChamp())
                ->createAssociation('police', PreferenceCrudController::PREF_CRM_PISTE_POLICE)
                ->setFormTypeOptions('query_builder', function (EntityRepository $entityRepository) {
                    return $entityRepository
                        ->createQueryBuilder('e')
                        ->Where('e.entreprise = :ese')
                        ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
                })
                ->setColumns($column)
                ->setRequired(false)
                ->getChamp()
        );
        //Gestionnaire
        $this->addChamp(
            (new JSChamp())
                ->createAssociation('gestionnaire', PreferenceCrudController::PREF_CRM_PISTE_GESTIONNAIRE)
                ->setFormTypeOptions('query_builder', function (EntityRepository $entityRepository) {
                    return $entityRepository
                        ->createQueryBuilder('e')
                        ->Where('e.entreprise = :ese')
                        ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
                })
                ->setColumns($column)
                ->setRequired(false)
                ->getChamp()
        );
        //Assistant
        $this->addChamp(
            (new JSChamp())
                ->createAssociation('assistant', PreferenceCrudController::PREF_CRM_PISTE_ASSISTANT)
                ->setFormTypeOptions('query_builder', function (EntityRepository $entityRepository) {
                    return $entityRepository
                        ->createQueryBuilder('e')
                        ->Where('e.entreprise = :ese')
                        ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
                })
                ->setColumns($column)
                ->setRequired(false)
                ->getChamp()
        );

        //*********************** Document ************************** */
        //Section - Document
        $this->addChamp(
            (new JSChamp())
                ->createSection("Objectif à atteindre et pièces jointes éventuelles")
                ->setIcon("fa-solid fa-paperclip")
                ->setColumns($column)
                ->getChamp()
        );
        //Document
        $this->addChamp(
            (new JSChamp())
                ->createCollection('documents', PreferenceCrudController::PREF_CRM_COTATION_DOCUMENTS)
                ->setHelp("Vous avez la possibilité d'en ajouter des données à volonté.")
                ->useEntryCrudForm(DocPieceCrudController::class)
                ->setColumns($column)
                ->setRequired(false)
                ->allowDelete(true)
                ->allowAdd(true)
                ->getChamp()
        );
        //Objectif
        $this->addChamp(
            (new JSChamp())
                ->createEditeurTexte('objectif', PreferenceCrudController::PREF_CRM_PISTE_OBJECTIF)
                ->setColumns($column)
                ->getChamp()
        );

        //*********************** Partenaire ************************** */
        //Onglet - Partenaire
        $this->addChamp(
            (new JSChamp())
                ->createOnglet(' Partenaire')
                ->setIcon('fas fa-handshake')
                ->setHelp("Intermédiaire (parrain) à travers lequel vous êtes entrés en contact avec cette piste.")
                ->setColumns($column)
                ->getChamp()
        );
        //Partenaire
        $this->addChamp(
            (new JSChamp())
                ->createAssociation('partenaire', PreferenceCrudController::PREF_CRM_PISTE_PARTENAIRE)
                ->setHelp("Si le partenaire n'existe pas encore sur cette liste, vous pouvez l'ajouter. Pour cela, il faut allez sur le champ d'ajout du partenaire.")
                ->setFormTypeOptions('query_builder', function (EntityRepository $entityRepository) {
                    return $entityRepository
                        ->createQueryBuilder('e')
                        ->Where('e.entreprise = :ese')
                        ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
                })
                ->setColumns($column)
                ->setRequired(false)
                ->getChamp()
        );
        //New Partenaire
        $this->addChamp(
            (new JSChamp())
                ->createCollection('newpartenaire', PreferenceCrudController::PREF_CRM_PISTE_NEW_PARTENAIRE)
                ->setHelp("Vous avez la possibilité d'en ajouter des données à volonté, mais seul la première sera finalement prise en compte.")
                ->useEntryCrudForm(PartenaireCrudController::class)
                ->setColumns($column)
                ->setRequired(false)
                ->allowDelete(true)
                ->allowAdd(true)
                ->getChamp()
        );

        //*********************** Prospect ************************** */
        //Onglet - Prospect
        $this->addChamp(
            (new JSChamp())
                ->createOnglet(' Client')
                ->setIcon('fas fa-person-shelter')
                ->setHelp("Le client ou prospect concerné par cette piste.")
                ->setColumns($column)
                ->getChamp()
        );
        //Prospect
        $this->addChamp(
            (new JSChamp())
                ->createAssociation('client', PreferenceCrudController::PREF_CRM_PISTE_CLIENT)
                ->setHelp("Si le client n'existe pas encore sur cette liste, vous pouvez l'ajouter comme prospect. Pour cela, il faut allez sur le champ d'ajout de prospect.")
                ->setFormTypeOptions('query_builder', function (EntityRepository $entityRepository) {
                    return $entityRepository
                        ->createQueryBuilder('e')
                        ->Where('e.entreprise = :ese')
                        ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
                })
                ->setColumns($column)
                ->setRequired(false)
                ->getChamp()
        );
        //New Prospect
        $this->addChamp(
            (new JSChamp())
                ->createCollection('prospect', PreferenceCrudController::PREF_CRM_PISTE_PROSPECTS)
                ->setHelp("Vous avez la possibilité d'ajouter des données à volonté. Mais seul le premier sera pris en compte.")
                ->useEntryCrudForm(ClientCrudController::class)
                ->setColumns($column)
                ->setRequired(false)
                ->allowDelete(true)
                ->allowAdd(true)
                ->getChamp()
        );

        //*********************** Contact ************************** */
        //Onglet - Contact
        $this->addChamp(
            (new JSChamp())
                ->createOnglet(' Contacts')
                ->setIcon('fas fa-address-book')
                ->setHelp("Les contacts ou personnes impliquées dans les échanges pour cette piste.")
                ->setColumns($column)
                ->getChamp()
        );
        //New contact
        $this->addChamp(
            (new JSChamp())
                ->createCollection('contacts', PreferenceCrudController::PREF_CRM_PISTE_CONTACT)
                ->setHelp("Vous avez la possibilité d'en ajouter des données à volonté.")
                ->useEntryCrudForm(ContactCrudController::class)
                ->setColumns($column)
                ->setRequired(false)
                ->allowDelete(true)
                ->allowAdd(true)
                ->getChamp()
        );

        //*********************** Tache ************************** */
        //Onglet - Tache
        $this->addChamp(
            (new JSChamp())
                ->createOnglet(' Tâches')
                ->setIcon('fas fa-paper-plane')
                ->setHelp("Il s'agit des missions ou actions à exécuter qui ont été assignées aux utilisateur pour cette piste.")
                ->setColumns($column)
                ->getChamp()
        );
        //New Tache
        $this->addChamp(
            (new JSChamp())
                ->createCollection('actionsCRMs', PreferenceCrudController::PREF_CRM_PISTE_ACTIONS)
                ->setHelp("Vous avez la possibilité d'en ajouter des données à volonté.")
                ->useEntryCrudForm(ActionCRMCrudController::class)
                ->setColumns($column)
                ->setRequired(false)
                ->allowDelete(true)
                ->allowAdd(true)
                ->getChamp()
        );

        //*********************** Cotation ************************** */
        //Onglet - Cotation
        $this->addChamp(
            (new JSChamp())
                ->createOnglet(' Propositions')
                ->setIcon('fas fa-cash-register')
                ->setHelp("Offres de proposition pour le client / prospect.")
                ->setColumns($column)
                ->getChamp()
        );
        //New Cotation
        $this->addChamp(
            (new JSChamp())
                ->createCollection('cotations', PreferenceCrudController::PREF_CRM_PISTE_COTATION)
                ->setHelp("Vous avez la possibilité d'en ajouter des données à volonté.")
                ->useEntryCrudForm(CotationCrudController::class)
                ->setColumns($column)
                ->setRequired(false)
                ->allowDelete(true)
                ->allowAdd(true)
                ->getChamp()
        );

        //*********************** Police ************************** */
        //Onglet - Police
        $this->addChamp(
            (new JSChamp())
                ->createOnglet(' Couverture en place')
                ->setIcon('fas fa-file-shield')
                ->setHelp("Polices d'assurance et/ou avenant mis en place.")
                ->setColumns($column)
                ->getChamp()
        );
        //New Cotation
        $this->addChamp(
            (new JSChamp())
                ->createCollection('polices', PreferenceCrudController::PREF_CRM_PISTE_POLICE)
                ->setHelp("Vous ne pouvez ajouter qu'un seul avenant ou police. Si vous chargez plusieurs, seul le premier enregistrement sera pris en compte.")
                ->useEntryCrudForm(PoliceCrudController::class)
                ->setColumns($column)
                ->setRequired(false)
                ->allowDelete(true)
                ->allowAdd(true)
                ->getChamp()
        );
    }

    public function batchActions(?array $champs, ?string $type = null, ?string $pageName = null, $objetInstance = null, ?Crud $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array
    {
        return $champs;
    }
}
