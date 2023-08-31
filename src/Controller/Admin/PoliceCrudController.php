<?php

namespace App\Controller\Admin;

use App\Entity\Police;
use App\Service\ServiceAvenant;
use DateTimeImmutable;
use App\Service\ServiceTaxes;
use Doctrine\ORM\QueryBuilder;
use App\Service\ServiceCrossCanal;
use App\Service\ServiceEntreprise;
use App\Service\ServiceCalculateur;
use App\Service\ServiceFacture;
use App\Service\ServicePreferences;
use App\Service\ServiceSuppression;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use Symfony\Component\HttpFoundation\RedirectResponse;
use EasyCorp\Bundle\EasyAdminBundle\Dto\BatchActionDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NumericFilter;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use PhpParser\Node\Expr\Cast\Array_;

class PoliceCrudController extends AbstractCrudController
{
    public ?Crud $crud = null;

    public const TAB_POLICE_REPONSES_OUI_NON = [
        'Non' => 0,
        'Oui' => 1
    ];

    public const TAB_POLICE_OUTSTANDING_RESPONSE = [
        'Non' => 0,
        'Oui' => 1
    ];

    public const TAB_POLICE_DEBITEUR = [
        "L'assureur" => 0,
        "Le client" => 1,
        "Le courtier de réassurance" => 2,
        "Le réassureur" => 3
    ];

    public const TAB_POLICE_MODE_PAIEMENT = [
        'Paiement Annuel' => 0,
        'Paiements Trimestriels' => 1,
        'Paiements Semestriels' => 2,
        'Paiements Mensuels' => 3
    ];


    public const AVENANT_TYPE_SOUSCRIPTION = "Souscription";
    public const AVENANT_TYPE_RISTOURNE = "Ristourne";
    public const AVENANT_TYPE_RESILIATION = "Résiliation";
    public const AVENANT_TYPE_RENOUVELLEMENT = "Renouvellement";
    public const AVENANT_TYPE_PROROGATION = "Prorogation";
    public const AVENANT_TYPE_INCORPORATION = "Incorporation";
    public const AVENANT_TYPE_ANNULATION = "Annulation";
    public const AVENANT_TYPE_AUTRE_MODIFICATION = "Autre modification";

    public const TAB_POLICE_TYPE_AVENANT = [
        self::AVENANT_TYPE_SOUSCRIPTION => 0,
        self::AVENANT_TYPE_RISTOURNE => 1,
        self::AVENANT_TYPE_RESILIATION => 2,
        self::AVENANT_TYPE_RENOUVELLEMENT => 3,
        self::AVENANT_TYPE_PROROGATION => 4,
        self::AVENANT_TYPE_INCORPORATION => 5,
        self::AVENANT_TYPE_ANNULATION => 6,
        self::AVENANT_TYPE_AUTRE_MODIFICATION => 7
    ];

    public function __construct(
        private ServiceFacture $serviceFacture,
        private ServiceAvenant $serviceAvenant,
        private ServiceSuppression $serviceSuppression,
        private ServiceCalculateur $serviceCalculateur,
        private EntityManagerInterface $entityManager,
        private ServiceEntreprise $serviceEntreprise,
        private ServicePreferences $servicePreferences,
        private ServiceCrossCanal $serviceCrossCanal,
        private AdminUrlGenerator $adminUrlGenerator,
        private ServiceTaxes $serviceTaxes
    ) {
        //AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em
    }

    public static function getEntityFqcn(): string
    {
        return Police::class;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $connected_entreprise = $this->serviceEntreprise->getEntreprise();
        $hasVisionGlobale = $this->isGranted(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE]);
        $defaultQueryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        if ($hasVisionGlobale == false) {
            $defaultQueryBuilder
                ->Where('entity.utilisateur = :user')
                ->setParameter('user', $this->getUser());
        }
        return $defaultQueryBuilder
            ->andWhere('entity.entreprise = :ese')
            ->setParameter('ese', $connected_entreprise);
    }

    public function configureCrud(Crud $crud): Crud
    {
        //Application de la préférence sur la taille de la liste
        $this->servicePreferences->appliquerPreferenceTaille(new Police(), $crud);
        $this->crud = $crud
            ->setDateTimeFormat('dd/MM/yyyy à HH:mm:ss')
            //->setDateTimeFormat('dd/MM/yyyy')
            ->setDateFormat('dd/MM/yyyy')
            //->setPaginatorPageSize(100)
            ->renderContentMaximized()
            ->setEntityLabelInSingular("POLICE")
            ->setEntityLabelInPlural("Police d'assurance")
            ->setPageTitle("index", "POLICES")
            ->setDefaultSort(['updatedAt' => 'DESC'])
            ->setEntityPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACCES_PRODUCTION])
            // ...
        ;
        return $crud;
    }


    public function configureFilters(Filters $filters): Filters
    {
        if ($this->isGranted(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])) {
            $filters->add('utilisateur');
        }
        return $filters
            ->add('gestionnaire')
            //->add('isCommissionUnpaid')
            ->add('dateeffet')
            ->add('dateexpiration')
            ->add('client')
            ->add('produit')
            ->add('assureur')
            ->add('partenaire')
            ->add('docPieces')
            ->add('idavenant')
            ->add('capital')
            ->add('primenette')
            ->add('fronting')
            ->add('primetotale')
            ->add(ChoiceFilter::new('cansharericom', 'Com. de réa. partageable?')->setChoices(self::TAB_POLICE_REPONSES_OUI_NON))
            ->add(ChoiceFilter::new('cansharelocalcom', 'Com. locale partageable?')->setChoices(self::TAB_POLICE_REPONSES_OUI_NON))
            ->add(ChoiceFilter::new('cansharefrontingcom', 'Com. fronting partageable?')->setChoices(self::TAB_POLICE_REPONSES_OUI_NON))
            ->add('paidcommission')
            ->add('paidretrocommission')
            ->add('paidtaxecourtier')
            ->add('paidtaxeassureur')
            ->add('unpaidcommission')
            ->add('unpaidretrocommission')
            ->add('unpaidtaxecourtier')
            ->add('unpaidtaxeassureur')
            ->add('paidtaxe')
            ->add('unpaidtaxe');
    }

    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->serviceSuppression->supprimer($entityInstance, ServiceSuppression::PRODUCTION_POLICE);
    }

    public function createEntity(string $entityFqcn)
    {
        $objet = new Police();
        $objet = $this->serviceAvenant->setAvenant($objet, $this->adminUrlGenerator);
        $objet = $this->serviceCrossCanal->crossCanal_Police_setCotation($objet, $this->adminUrlGenerator);

        return $objet;
    }



    public function configureFields(string $pageName): iterable
    {
        $this->crud = $this->serviceCrossCanal->crossCanal_setTitrePage($this->crud, $this->adminUrlGenerator);
        //Actualisation des attributs calculables - Merci Seigneur Jésus !
        $this->serviceCalculateur->calculate($this->container, ServiceCalculateur::RUBRIQUE_POLICE);
        return $this->servicePreferences->getChamps(new Police(), $this->crud, $this->adminUrlGenerator);
    }


    public function configureActions(Actions $actions): Actions
    {
        //cross canal
        $automobile_ajouter = Action::new(ServiceCrossCanal::OPTION_AUTOMOBILE_AJOUTER)
            ->setIcon('fas fa-car')
            ->linkToCrudAction('cross_canal_ajouterAutomobile');
        $automobile_lister = Action::new(ServiceCrossCanal::OPTION_AUTOMOBILE_LISTER)
            ->displayIf(static function (?Police $entity) {
                return count($entity->getAutomobiles()) != 0;
            })
            ->setIcon('fas fa-car')
            ->linkToCrudAction('cross_canal_listerAutomobile');

        $actions
            ->add(Crud::PAGE_DETAIL, $automobile_ajouter)
            ->add(Crud::PAGE_INDEX, $automobile_ajouter)
            ->add(Crud::PAGE_DETAIL, $automobile_lister)
            ->add(Crud::PAGE_INDEX, $automobile_lister);


        $contact_ajouter = Action::new(ServiceCrossCanal::OPTION_CONTACT_AJOUTER)
            ->setIcon('fas fa-address-book')
            ->linkToCrudAction('cross_canal_ajouterContact');
        $contact_lister = Action::new(ServiceCrossCanal::OPTION_CONTACT_LISTER)
            ->displayIf(static function (?Police $entity) {
                $isEmpty = false;
                if ($entity->getCotation() != null) {
                    if ($entity->getCotation()->getPiste() != null) {
                        $isEmpty = count($entity->getCotation()->getPiste()->getContacts()) != 0;
                    }
                }
                return $isEmpty;
            })
            ->setIcon('fas fa-address-book')
            ->linkToCrudAction('cross_canal_listerContact');

        $actions
            ->add(Crud::PAGE_DETAIL, $contact_ajouter)
            ->add(Crud::PAGE_INDEX, $contact_ajouter)
            ->add(Crud::PAGE_DETAIL, $contact_lister)
            ->add(Crud::PAGE_INDEX, $contact_lister);


        $piece_ajouter = Action::new(ServiceCrossCanal::OPTION_PIECE_AJOUTER)
            ->setIcon('fa-solid fa-paperclip')
            ->linkToCrudAction('cross_canal_ajouterPiece');
        $piece_lister = Action::new(ServiceCrossCanal::OPTION_PIECE_LISTER)
            ->displayIf(static function (?Police $entity) {
                return count($entity->getDocPieces()) != 0;
            })
            ->setIcon('fa-solid fa-paperclip')
            ->linkToCrudAction('cross_canal_listerPiece');

        $actions
            ->add(Crud::PAGE_DETAIL, $piece_ajouter)
            ->add(Crud::PAGE_INDEX, $piece_ajouter)
            ->add(Crud::PAGE_DETAIL, $piece_lister)
            ->add(Crud::PAGE_INDEX, $piece_lister);

        $paiementCommission_ajouter = Action::new(ServiceCrossCanal::OPTION_POP_COMMISSION_AJOUTER)
            ->displayIf(static function (?Police $entity) {
                return ($entity->calc_revenu_ttc_solde_restant_du != 0);
            })
            ->setIcon('fas fa-person-arrow-down-to-line')
            ->linkToCrudAction('cross_canal_ajouterPOPComm');
        $paiementCommission_lister = Action::new(ServiceCrossCanal::OPTION_POP_COMMISSION_LISTER)
            ->displayIf(static function (?Police $entity) {
                return ($entity->calc_revenu_ttc_encaisse != 0);
            })
            ->setIcon('fas fa-person-arrow-down-to-line')
            ->linkToCrudAction('cross_canal_listerPOPComm');

        $paiementPartenaire_ajouter = Action::new(ServiceCrossCanal::OPTION_POP_PARTENAIRE_AJOUTER)
            ->displayIf(static function (?Police $entity) {
                //Attention on ne paie pas le partenaire tant que la comm n a pas ete encaissée
                return ($entity->getPartenaire() != null) && ($entity->calc_retrocom_solde != 0) && $entity->calc_revenu_ttc_encaisse != 0;
            })
            ->setIcon('fas fa-person-arrow-up-from-line')
            ->linkToCrudAction('cross_canal_ajouterPOPRetroComm');
        $paiementPartenaire_lister = Action::new(ServiceCrossCanal::OPTION_POP_PARTENAIRE_LISTER)
            ->displayIf(static function (?Police $entity) {
                return ($entity->calc_retrocom_payees != 0);
            })
            ->setIcon('fas fa-person-arrow-up-from-line')
            ->linkToCrudAction('cross_canal_listerPOPRetroComm');

        $txtTaxeCourtier = $this->serviceTaxes->getTaxe(true) != null ? $this->serviceTaxes->getNomTaxeCourtier() : "";
        $txtTaxeAssureur = $this->serviceTaxes->getTaxe(false) != null ? $this->serviceTaxes->getNomTaxeAssureur() : "";
        $paiementTaxeCourtier_ajouter = Action::new("Payer " . $txtTaxeCourtier)
            ->displayIf(static function (?Police $entity) {
                return ($entity->calc_taxes_courtier_solde != 0 && $entity->calc_revenu_ttc_encaisse != 0);
            })
            ->setIcon('fas fa-person-chalkboard')
            ->linkToCrudAction('cross_canal_ajouterPOPTaxeCourtier');

        $paiementTaxeAssureur_ajouter = Action::new("Payer " . $txtTaxeAssureur)
            ->displayIf(static function (?Police $entity) {
                return ($entity->calc_taxes_assureurs_solde != 0 && $entity->calc_revenu_ttc_encaisse != 0);
            })
            ->setIcon('fas fa-person-chalkboard')
            ->linkToCrudAction('cross_canal_ajouterPOPTaxeAssureur');

        $paiementTaxeCourtier_lister = Action::new("Voir les Pdp " . $txtTaxeCourtier)
            ->displayIf(static function (?Police $entity) {
                return ($entity->calc_taxes_courtier_payees != 0);
            })
            ->setIcon('fas fa-person-chalkboard')
            ->linkToCrudAction('cross_canal_listerPOPTaxeCourtier');

        $paiementTaxeAssureur_lister = Action::new("Voir les Pdp " . $txtTaxeAssureur)
            ->displayIf(static function (?Police $entity) {
                return ($entity->calc_taxes_assureurs_payees != 0);
            })
            ->setIcon('fas fa-person-chalkboard')
            ->linkToCrudAction('cross_canal_listerPOPTaxeAssureur');

        if ($this->serviceTaxes->getTaxe(true) != null) {
            $actions
                ->add(Crud::PAGE_DETAIL, $paiementTaxeCourtier_ajouter)
                ->add(Crud::PAGE_INDEX, $paiementTaxeCourtier_ajouter)

                ->add(Crud::PAGE_DETAIL, $paiementTaxeCourtier_lister)
                ->add(Crud::PAGE_INDEX, $paiementTaxeCourtier_lister);
        }

        if ($this->serviceTaxes->getTaxe(false) != null) {
            $actions
                ->add(Crud::PAGE_DETAIL, $paiementTaxeAssureur_ajouter)
                ->add(Crud::PAGE_INDEX, $paiementTaxeAssureur_ajouter)

                ->add(Crud::PAGE_DETAIL, $paiementTaxeAssureur_lister)
                ->add(Crud::PAGE_INDEX, $paiementTaxeAssureur_lister);
        }

        //Sinistres
        $sinistre_ajouter = Action::new(ServiceCrossCanal::OPTION_SINISTRE_AJOUTER)
            ->setIcon('fas fa-bell')
            ->linkToCrudAction('cross_canal_ajouterSinistre');

        $sinistre_lister = Action::new(ServiceCrossCanal::OPTION_SINISTRE_LISTER)
            ->displayIf(static function (?Police $entity) {
                return count($entity->getSinistres()) != 0;
            })
            ->setIcon('fas fa-bell')
            ->linkToCrudAction('cross_canal_listerSinistre');

        $actions
            ->add(Crud::PAGE_DETAIL, $sinistre_lister)
            ->add(Crud::PAGE_INDEX, $sinistre_lister)
            ->add(Crud::PAGE_DETAIL, $sinistre_ajouter)
            ->add(Crud::PAGE_INDEX, $sinistre_ajouter);

        $mission_ajouter = Action::new(ServiceCrossCanal::OPTION_MISSION_AJOUTER)
            ->setIcon('fas fa-paper-plane')
            ->linkToCrudAction('cross_canal_ajouterMission');

        $mission_lister = Action::new(ServiceCrossCanal::OPTION_MISSION_LISTER)
            ->displayIf(static function (?Police $entity) {
                return count($entity->getActionCRMs()) != 0;
            })
            ->setIcon('fas fa-paper-plane')
            ->linkToCrudAction('cross_canal_listerMission');

        $actions
            ->add(Crud::PAGE_DETAIL, $mission_lister)
            ->add(Crud::PAGE_INDEX, $mission_lister)
            ->add(Crud::PAGE_DETAIL, $mission_ajouter)
            ->add(Crud::PAGE_INDEX, $mission_ajouter);


        $piste_ajouter = Action::new(ServiceCrossCanal::OPTION_PISTE_AJOUTER)
            ->setIcon('fas fa-location-crosshairs')
            ->linkToCrudAction('cross_canal_ajouterPiste');

        $piste_lister = Action::new(ServiceCrossCanal::OPTION_PISTE_LISTER)
            ->displayIf(static function (?Police $entity) {
                return count($entity->getPistes()) != 0;
            })
            ->setIcon('fas fa-location-crosshairs')
            ->linkToCrudAction('cross_canal_listerPiste');

        $actions
            ->add(Crud::PAGE_DETAIL, $piste_lister)
            ->add(Crud::PAGE_INDEX, $piste_lister)
            ->add(Crud::PAGE_DETAIL, $piste_ajouter)
            ->add(Crud::PAGE_INDEX, $piste_ajouter);


        $duplicate = Action::new(DashboardController::ACTION_DUPLICATE)
            ->setIcon('fa-solid fa-copy')
            ->linkToCrudAction('dupliquerEntite'); //<i class="fa-solid fa-copy"></i>
        $ouvrir = Action::new(DashboardController::ACTION_OPEN)
            ->setIcon('fa-solid fa-eye')
            ->linkToCrudAction('ouvrirEntite'); //<i class="fa-solid fa-eye"></i>


        //dd($this->adminUrlGenerator->get("codeReporting"));

        //LES ACTIONS BATCH
        $batch_creer_facture_commission = Action::new("facture_commissions", "Créer une facture pour Commissions")
            ->linkToCrudAction('facture_commissions')
            //->addCssClass('btn btn-primary')
            ->setIcon('fa-solid fa-receipt');
        $batch_creer_facture_retrocommission = Action::new("facture_retrocommissions", "Créer une facture pour Retrocommissions")
            ->linkToCrudAction('facture_retrocommissions')
            //->addCssClass('btn btn-primary')
            ->setIcon('fa-solid fa-receipt');
        $batch_creer_facture_tva = Action::new("facture_tva", "Créer une note de perception pour TVA")
            ->displayIf(static function (?Police $entity) {
                return $entity->calc_taxes_assureurs_solde != 0;
            })
            ->linkToCrudAction('facture_tva')
            //->addCssClass('btn btn-primary')
            ->setIcon('fa-solid fa-receipt');
        $batch_creer_facture_arca = Action::new("facture_arca", "Créer une note de perception pour le régulateur")
            ->displayIf(static function (?Police $entity) {
                return $entity->calc_taxes_courtier_solde != 0;
            })
            ->linkToCrudAction('facture_arca')
            //->addCssClass('btn btn-primary')
            ->setIcon('fa-solid fa-receipt');

        $batch_exporter_ms_excels = Action::new("exporter_ms_excels", DashboardController::ACTION_EXPORTER_EXCELS)
            ->linkToCrudAction('exporterMSExcels')
            //->addCssClass('btn btn-primary')
            ->setIcon('fa-solid fa-file-excel');

        $actions
            //Sur la page Index - Selection
            ->addBatchAction($batch_exporter_ms_excels)
            ->addBatchAction($batch_creer_facture_arca)
            ->addBatchAction($batch_creer_facture_tva)
            ->addBatchAction($batch_creer_facture_retrocommission)
            ->addBatchAction($batch_creer_facture_commission)

            //les Updates sur la page détail
            ->update(Crud::PAGE_DETAIL, Action::DELETE, function (Action $action) {
                return $action->setIcon('fa-solid fa-trash')->setLabel(DashboardController::ACTION_SUPPRIMER);
            })
            ->update(Crud::PAGE_DETAIL, Action::EDIT, function (Action $action) {
                return $action->setIcon('fa-solid fa-pen-to-square')->setLabel(DashboardController::ACTION_MODIFIER); //<i class="fa-solid fa-pen-to-square"></i>
            })
            ->update(Crud::PAGE_DETAIL, Action::INDEX, function (Action $action) {
                return $action->setIcon('fa-regular fa-rectangle-list')->setLabel(DashboardController::ACTION_LISTE); //<i class="fa-regular fa-rectangle-list"></i>
            })
            //Updates sur la page Index
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setIcon('fas fa-file-shield')->setCssClass('btn btn-primary')->setLabel(DashboardController::ACTION_AJOUTER);
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action->setIcon('fa-solid fa-trash')->setLabel(DashboardController::ACTION_SUPPRIMER); //<i class="fa-solid fa-trash"></i>
            })
            ->update(Crud::PAGE_INDEX, Action::BATCH_DELETE, function (Action $action) {
                return $action->setIcon('fa-solid fa-trash')->setLabel(DashboardController::ACTION_SUPPRIMER); //<i class="fa-solid fa-trash"></i>
            })
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action->setIcon('fa-solid fa-pen-to-square')->setLabel(DashboardController::ACTION_MODIFIER);
            })
            //Updates Sur la page Edit
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN, function (Action $action) {
                return $action->setIcon('fa-solid fa-floppy-disk')->setLabel(DashboardController::ACTION_ENREGISTRER); //<i class="fa-solid fa-floppy-disk"></i>
            })
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE, function (Action $action) {
                return $action->setIcon('fa-solid fa-floppy-disk')->setLabel(DashboardController::ACTION_ENREGISTRER_ET_CONTINUER);
            })
            //Updates Sur la page NEW
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER, function (Action $action) {
                return $action->setIcon('fa-solid fa-floppy-disk')->setLabel(DashboardController::ACTION_ENREGISTRER_ET_CONTINUER);
            })
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, function (Action $action) {
                return $action->setIcon('fa-solid fa-floppy-disk')->setLabel(DashboardController::ACTION_ENREGISTRER); //<i class="fa-solid fa-floppy-disk"></i>
            })

            //Action ouvrir
            ->add(Crud::PAGE_EDIT, $ouvrir)
            ->add(Crud::PAGE_INDEX, $ouvrir)
            //action dupliquer Assureur
            ->add(Crud::PAGE_DETAIL, $duplicate)
            ->add(Crud::PAGE_EDIT, $duplicate)
            ->add(Crud::PAGE_INDEX, $duplicate)

            //cross canal

            ->add(Crud::PAGE_DETAIL, $paiementCommission_ajouter)
            ->add(Crud::PAGE_INDEX, $paiementCommission_ajouter)
            ->add(Crud::PAGE_DETAIL, $paiementPartenaire_ajouter)
            ->add(Crud::PAGE_INDEX, $paiementPartenaire_ajouter)
            ->add(Crud::PAGE_DETAIL, $paiementCommission_lister)
            ->add(Crud::PAGE_INDEX, $paiementCommission_lister)
            ->add(Crud::PAGE_DETAIL, $paiementPartenaire_lister)
            ->add(Crud::PAGE_INDEX, $paiementPartenaire_lister)


            //Reorganisation des boutons
            ->reorder(Crud::PAGE_INDEX, [DashboardController::ACTION_OPEN, DashboardController::ACTION_DUPLICATE])
            ->reorder(Crud::PAGE_EDIT, [DashboardController::ACTION_OPEN, DashboardController::ACTION_DUPLICATE])

            //Application des roles
            ->setPermission(Action::NEW, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
            ->setPermission(Action::EDIT, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
            ->setPermission(Action::DELETE, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
            ->setPermission(Action::BATCH_DELETE, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
            ->setPermission(Action::SAVE_AND_ADD_ANOTHER, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
            ->setPermission(Action::SAVE_AND_CONTINUE, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
            ->setPermission(Action::SAVE_AND_RETURN, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
            ->setPermission(DashboardController::ACTION_DUPLICATE, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
            //->setPermission(self::ACTION_ACHEVER_MISSION, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
            //->setPermission(self::ACTION_AJOUTER_UN_FEEDBACK, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
        ;


        //Opérations ARCA
        $avenant_annulation = Action::new("Avenant d'annulation")
            ->setIcon('fa-regular fa-trash-can') //<i class="fa-regular fa-trash-can"></i>
            ->addCssClass("btn btn-primary")
            ->linkToCrudAction('avenant_annulation');
        $actions
            ->add(Crud::PAGE_DETAIL, $avenant_annulation)
            //->add(Crud::PAGE_INDEX, $operation_annulation)
        ;

        $avenant_renouvellement = Action::new("Avenant de renouvellement")
            ->setIcon('fa-solid fa-champagne-glasses') //<i class="fa-solid fa-champagne-glasses"></i>
            ->addCssClass("btn btn-primary")
            ->linkToCrudAction('avenant_renouvellement');
        $actions
            ->add(Crud::PAGE_DETAIL, $avenant_renouvellement)
            //->add(Crud::PAGE_INDEX, $operation_renouvellement)
        ;

        $avenant_prorogation = Action::new("Avenant de prorogation")
            ->setIcon('fa-solid fa-bridge') //<i class="fa-solid fa-bridge"></i>
            ->addCssClass("btn btn-primary")
            ->linkToCrudAction('avenant_prorogation');
        $actions
            ->add(Crud::PAGE_DETAIL, $avenant_prorogation)
            //->add(Crud::PAGE_INDEX, $operation_prorogation)
        ;

        $avenant_incorporation = Action::new("Avenant d'incorporation")
            ->setIcon('fa-solid fa-plus') //<i class="fa-solid fa-plus"></i>
            ->addCssClass("btn btn-primary")
            ->linkToCrudAction('avenant_incorporation');
        $actions
            ->add(Crud::PAGE_DETAIL, $avenant_incorporation)
            //->add(Crud::PAGE_INDEX, $operation_incorporation)
        ;

        $avenant_ristourne = Action::new("Avenant de ristourne")
            ->setIcon('fa-solid fa-person-walking-arrow-loop-left') //<i class="fa-solid fa-person-walking-arrow-loop-left"></i>
            ->addCssClass("btn btn-primary")
            ->linkToCrudAction('avenant_ristourne');
        $actions
            ->add(Crud::PAGE_DETAIL, $avenant_ristourne)
            //->add(Crud::PAGE_INDEX, $operation_ristourne)
        ;

        $avenant_resiliation = Action::new("Avenant de résiliation")
            ->setIcon('fa-solid fa-ban') //<i class="fa-solid fa-ban"></i>
            ->addCssClass("btn btn-primary")
            ->linkToCrudAction('avenant_resiliation');
        $actions
            ->add(Crud::PAGE_DETAIL, $avenant_resiliation)
            //->add(Crud::PAGE_INDEX, $operation_resiliation)
        ;

        $avenant_autre_modifications = Action::new("Avenant pour autres modifications")
            ->setIcon('fa-solid fa-pen') //<i class="fa-solid fa-pen"></i>
            ->addCssClass("btn btn-primary")
            ->linkToCrudAction('avenant_autres_modifications');
        $actions
            ->add(Crud::PAGE_DETAIL, $avenant_autre_modifications)
            //->add(Crud::PAGE_INDEX, $operation_autre_modifications)
        ;
        //Ajout des opérations arca
        return $actions;
    }

    public function dupliquerEntite(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        //dd($context);
        /**@var Assureur $assureur */
        $entite = $context->getEntity()->getInstance();
        $entiteDuplique = clone $entite;
        parent::persistEntity($em, $entiteDuplique);

        $url = $adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::DETAIL)
            ->setEntityId($entiteDuplique->getId())
            ->generateUrl();

        return $this->redirect($url);
    }

    public function ouvrirEntite(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        /**@var Assureur $assureur */
        $entite = $context->getEntity()->getInstance();

        $url = $adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::DETAIL)
            ->setEntityId($entite->getId())
            ->generateUrl();

        return $this->redirect($url);
    }


    public function facture_commissions(BatchActionDto $batchActionDto, AdminUrlGenerator $adminUrlGenerator)
    {
        $reponse = $this->serviceFacture->canIssueFacture($batchActionDto, FactureCrudController::TYPE_FACTURE_COMMISSIONS);
        dd($reponse);

        ici
        /* if ($this->serviceFacture->canIssueFactureComm($batchActionDto, FactureCrudController::TYPE_FACTURE_COMMISSIONS)) {
            return $this->creerFacture($batchActionDto, $adminUrlGenerator, FactureCrudController::TYPE_FACTURE_COMMISSIONS);
        } else {
            $this->addFlash("warning", "Salut " . $this->serviceEntreprise->getUtilisateur()->getNom() . ". 
            Impossible d'émettre la facture. Assurez-vous que le solde des commissions dues est différent du nul et que la sélection actuelle ne concerne qu'un même assureur.");
            return $this->redirect($batchActionDto->getReferrerUrl());
        } */


    }

    public function facture_retrocommissions(BatchActionDto $batchActionDto, AdminUrlGenerator $adminUrlGenerator)
    {
        if ($this->serviceFacture->canIssueFactureRetroComm($batchActionDto, FactureCrudController::TYPE_FACTURE_RETROCOMMISSIONS)) {
            return $this->creerFacture($batchActionDto, $adminUrlGenerator, FactureCrudController::TYPE_FACTURE_RETROCOMMISSIONS);
        } else {
            $this->addFlash("warning", "Salut " . $this->serviceEntreprise->getUtilisateur()->getNom() . ". 
            Impossible d'émettre la facture. Assurez-vous que le solde des retro-commissions est différent du nul et que la sélection actuelle ne concerne qu'un même partenaire.");
            return $this->redirect($batchActionDto->getReferrerUrl());
        }
    }

    public function facture_arca(BatchActionDto $batchActionDto, AdminUrlGenerator $adminUrlGenerator)
    {
        if ($this->serviceFacture->canIssueFactureTaxeArca($batchActionDto, FactureCrudController::TYPE_FACTURE_NOTE_DE_PERCEPTION_ARCA)) {
            return $this->creerFacture($batchActionDto, $adminUrlGenerator, FactureCrudController::TYPE_FACTURE_NOTE_DE_PERCEPTION_ARCA);
        } else {
            $this->addFlash("warning", "Salut " . $this->serviceEntreprise->getUtilisateur()->getNom() . ". 
            Impossible d'émettre la facture. Il semble que le solde des taxes dues au régulateur est nul.");
            return $this->redirect($batchActionDto->getReferrerUrl());
        }
    }

    public function facture_tva(BatchActionDto $batchActionDto, AdminUrlGenerator $adminUrlGenerator)
    {
        if ($this->serviceFacture->canIssueFactureTaxeTva($batchActionDto, FactureCrudController::TYPE_FACTURE_NOTE_DE_PERCEPTION_TVA)) {
            return $this->creerFacture($batchActionDto, $adminUrlGenerator, FactureCrudController::TYPE_FACTURE_NOTE_DE_PERCEPTION_TVA);
        } else {
            $this->addFlash("warning", "Salut " . $this->serviceEntreprise->getUtilisateur()->getNom() . ". 
            Impossible d'émettre la facture. Il semble que le solde des tva dues à l'autorité fiscale est nul.");
            return $this->redirect($batchActionDto->getReferrerUrl());
        }
    }

    public function creerFacture(BatchActionDto $batchActionDto, AdminUrlGenerator $adminUrlGenerator, $type)
    {
        $tabIdPolices = [];
        foreach ($batchActionDto->getEntityIds() as $id) {
            $tabIdPolices[] = $id;
        }
        return $this->redirect($this->serviceCrossCanal->crossCanal_creer_facture($adminUrlGenerator, $tabIdPolices, $type));
    }

    public function exporterMSExcels(BatchActionDto $batchActionDto)
    {
        $className = $batchActionDto->getEntityFqcn();
        $entityManager = $this->container->get('doctrine')->getManagerForClass($className);

        dd($batchActionDto->getEntityIds());

        foreach ($batchActionDto->getEntityIds() as $id) {
            $user = $entityManager->find($className, $id);
            $user->approve();
        }
        $entityManager->flush();
        return $this->redirect($batchActionDto->getReferrerUrl());
    }

    public function cross_canal_ajouterPiece(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Police_ajouterPiece($context, $adminUrlGenerator));
    }

    public function cross_canal_listerPiece(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Police_listerPiece($context, $adminUrlGenerator));
    }

    public function cross_canal_ajouterPOPComm(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Police_ajouterPOPComm($context, $adminUrlGenerator));
    }

    public function cross_canal_listerPOPComm(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Police_listerPOPComm($context, $adminUrlGenerator));
    }

    public function cross_canal_ajouterPOPRetroComm(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Police_ajouterPOPRetroComm($context, $adminUrlGenerator));
    }

    public function cross_canal_listerPOPRetroComm(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Police_listerPOPRetroComm($context, $adminUrlGenerator));
    }

    public function cross_canal_ajouterPOPTaxeCourtier(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Police_ajouterPOPTaxe($context, $adminUrlGenerator, $this->serviceTaxes->getTaxe(true)));
    }

    public function cross_canal_listerPOPTaxeCourtier(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Police_listerPOPTaxe($context, $adminUrlGenerator, $this->serviceTaxes->getTaxe(true)));
    }

    public function cross_canal_ajouterPOPTaxeAssureur(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Police_ajouterPOPTaxe($context, $adminUrlGenerator, $this->serviceTaxes->getTaxe(false)));
    }

    public function cross_canal_listerPOPTaxeAssureur(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Police_listerPOPTaxe($context, $adminUrlGenerator, $this->serviceTaxes->getTaxe(false)));
    }

    public function cross_canal_ajouterSinistre(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Police_ajouterSinistre($context, $adminUrlGenerator));
    }

    public function cross_canal_listerSinistre(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Police_listerSinistre($context, $adminUrlGenerator));
    }

    public function cross_canal_ajouterMission(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Police_ajouterMission($context, $adminUrlGenerator));
    }

    public function cross_canal_listerMission(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Police_listerMission($context, $adminUrlGenerator));
    }

    public function cross_canal_ajouterPiste(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Police_ajouterPiste($context, $adminUrlGenerator));
    }

    public function cross_canal_listerPiste(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Police_listerPiste($context, $adminUrlGenerator));
    }

    public function cross_canal_ajouterContact(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Police_ajouterContact($context, $adminUrlGenerator));
    }

    public function cross_canal_listerContact(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Police_listerContact($context, $adminUrlGenerator));
    }

    public function cross_canal_ajouterAutomobile(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Police_ajouterAutomobile($context, $adminUrlGenerator));
    }

    public function avenant_annulation(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Avenant_Annulation($context, $adminUrlGenerator));
    }

    public function avenant_incorporation(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Avenant_Incorporation($context, $adminUrlGenerator));
    }

    public function avenant_resiliation(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Avenant_Resiliation($context, $adminUrlGenerator));
    }

    public function avenant_autres_modifications(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Avenant_Autres_Modifications($context, $adminUrlGenerator));
    }

    public function avenant_prorogation(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Avenant_Prorogation($context, $adminUrlGenerator));
    }

    public function avenant_ristourne(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Avenant_Ristourne($context, $adminUrlGenerator));
    }

    public function avenant_renouvellement(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Avenant_Renouvellement($context, $adminUrlGenerator));
    }

    public function cross_canal_listerAutomobile(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Police_listerAutomobile($context, $adminUrlGenerator));
    }

    protected function getRedirectResponseAfterSave(AdminContext $context, string $action): RedirectResponse
    {
        /** @var Police */
        $police = $context->getEntity()->getInstance();
        $url = $this->adminUrlGenerator
            ->setController(PoliceCrudController::class)
            ->setAction(Action::DETAIL)
            ->setEntityId($police->getId())
            ->generateUrl();
        $this->addFlash("success", "Salut " . $this->serviceEntreprise->getUtilisateur()->getNom() . ". La police " . $police .  " vient d'être enregistrée avec succès. Vous pouvez maintenant y ajouter d'autres informations.");
        return $this->redirect($url);
    }
}
