<?php

namespace App\Controller\Admin;

use App\Entity\Police;
use DateTimeImmutable;
use App\Service\ServiceTaxes;
use Doctrine\ORM\QueryBuilder;
use App\Service\ServiceCrossCanal;
use App\Service\ServiceEntreprise;
use App\Service\ServiceCalculateur;
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

class PoliceCrudController extends AbstractCrudController
{
    public ?Crud $crud = null;

    public const TAB_POLICE_REPONSES_OUI_NON = [
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

    public const TAB_POLICE_TYPE_AVENANT = [
        'Souscription' => 0,
        'Ristourne' => 1,
        'Résiliation' => 2,
        'Renouvellement' => 3,
        'Prorogation' => 4,
        'Incorporation' => 5,
        'Annulation' => 6
    ];

    public function __construct(
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
            ->add(ChoiceFilter::new('cansharefrontingcom', 'Com. fronting partageable?')->setChoices(self::TAB_POLICE_REPONSES_OUI_NON));
    }

    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->serviceSuppression->supprimer($entityInstance, ServiceSuppression::PRODUCTION_POLICE);
    }

    public function createEntity(string $entityFqcn)
    {
        $objet = new Police();
        $objet->setDateemission(new DateTimeImmutable("now"));
        $objet->setDateoperation(new DateTimeImmutable("now"));
        $objet->setDateeffet(new DateTimeImmutable("now"));
        $objet->setDateexpiration(new DateTimeImmutable("+365 day"));
        $objet->setPartExceptionnellePartenaire(0);
        $objet->setIdavenant(0);
        $objet->setTypeavenant(0);
        $objet->setReassureurs("Voir le traité de réassurance en place.");
        $objet->setCapital(1000000.00);
        $objet->setPrimenette(0);
        $objet->setFronting(0);
        $objet->setArca(0);
        $objet->setTva(0);
        $objet->setFraisadmin(0);
        $objet->setPrimetotale(0);
        $objet->setModepaiement(0);
        $objet->setDiscount(0);

        $objet->setRicom(0);
        $objet->setCansharericom(false);
        $objet->setRicompayableby(0);

        $objet->setFrontingcom(0);
        $objet->setCansharefrontingcom(false);
        $objet->setFrontingcompayableby(0);

        $objet->setLocalcom(0);
        $objet->setCansharelocalcom(false);
        $objet->setLocalcompayableby(0);

        $objet->setPartenaire(null);
        $objet->setGestionnaire($this->serviceEntreprise->getUtilisateur());
        $objet = $this->serviceCrossCanal->crossCanal_Police_setCotation($objet, $this->adminUrlGenerator);


        return $objet;
    }


    public function configureFields(string $pageName): iterable
    {
        $this->crud = $this->serviceCrossCanal->crossCanal_setTitrePage($this->crud, $this->adminUrlGenerator);
        //Actualisation des attributs calculables - Merci Seigneur Jésus !
        $this->serviceCalculateur->calculate($this->container, ServiceCalculateur::RUBRIQUE_POLICE);
        return $this->servicePreferences->getChamps(new Police());
    }


    public function configureActions(Actions $actions): Actions
    {
        //cross canal
        $piece_ajouter = Action::new(ServiceCrossCanal::POLICE_AJOUTER_PIECE)
            ->setIcon('fas fa-file-word')
            ->linkToCrudAction('cross_canal_ajouterPiece');
        $piece_lister = Action::new(ServiceCrossCanal::POLICE_LISTER_PIECE)
            ->displayIf(static function (?Police $entity) {
                return count($entity->getDocPieces()) != 0;
            })
            ->setIcon('fa-solid fa-rectangle-list')
            ->linkToCrudAction('cross_canal_listerPiece');

        $actions
            ->add(Crud::PAGE_DETAIL, $piece_ajouter)
            ->add(Crud::PAGE_INDEX, $piece_ajouter)
            ->add(Crud::PAGE_DETAIL, $piece_lister)
            ->add(Crud::PAGE_INDEX, $piece_lister);

        $paiementCommission_ajouter = Action::new(ServiceCrossCanal::POLICE_AJOUTER_POP_COMMISSIONS)
            ->displayIf(static function (?Police $entity) {
                return ($entity->calc_revenu_ttc_solde_restant_du != 0);
            })
            ->setIcon('fas fa-person-arrow-down-to-line')
            ->linkToCrudAction('cross_canal_ajouterPOPComm');
        $paiementCommission_lister = Action::new(ServiceCrossCanal::POLICE_LISTER_POP_COMMISSIONS)
            ->displayIf(static function (?Police $entity) {
                return ($entity->calc_revenu_ttc_encaisse != 0);
            })
            ->setIcon('fas fa-person-arrow-down-to-line')
            ->linkToCrudAction('cross_canal_listerPOPComm');

        $paiementPartenaire_ajouter = Action::new(ServiceCrossCanal::POLICE_AJOUTER_POP_PARTENAIRES)
            ->displayIf(static function (?Police $entity) {
                //Attention on ne paie pas le partenaire tant que la comm n a pas ete encaissée
                return ($entity->getPartenaire() != null) && ($entity->calc_retrocom_solde != 0) && $entity->calc_revenu_ttc_encaisse != 0;
            })
            ->setIcon('fas fa-person-arrow-up-from-line')
            ->linkToCrudAction('cross_canal_ajouterPOPRetroComm');
        $paiementPartenaire_lister = Action::new(ServiceCrossCanal::POLICE_LISTER_POP_PARTENAIRES)
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
        $sinistre_ajouter = Action::new(ServiceCrossCanal::POLICE_AJOUTER_SINISTRE)
            ->setIcon('fas fa-bell')
            ->linkToCrudAction('cross_canal_ajouterSinistre');

        $sinistre_lister = Action::new(ServiceCrossCanal::POLICE_LISTER_SINISTRES)
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


        $duplicate = Action::new(DashboardController::ACTION_DUPLICATE)
            ->setIcon('fa-solid fa-copy')
            ->linkToCrudAction('dupliquerEntite'); //<i class="fa-solid fa-copy"></i>
        $ouvrir = Action::new(DashboardController::ACTION_OPEN)
            ->setIcon('fa-solid fa-eye')
            ->linkToCrudAction('ouvrirEntite'); //<i class="fa-solid fa-eye"></i>
        $exporter_ms_excels = Action::new("exporter_ms_excels", DashboardController::ACTION_EXPORTER_EXCELS)
            ->linkToCrudAction('exporterMSExcels')
            ->addCssClass('btn btn-primary')
            ->setIcon('fa-solid fa-file-excel');

        $actions
            //Sur la page Index - Selection
            ->addBatchAction($exporter_ms_excels)
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
