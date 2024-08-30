<?php

namespace App\Controller\Admin;

use DateTimeImmutable;
//use Doctrine\ORM\EntityManager;
use Faker\Core\DateTime;
//use Doctrine\ORM\QueryBuilder;
use App\Entity\ActionCRM;
use App\Service\ServiceDates;
use App\Service\ServiceTaxes;
use Doctrine\ORM\QueryBuilder;
use App\Service\ServiceMonnaie;
use Doctrine\ORM\Query\Expr\Func;
use App\Service\ServiceCrossCanal;
use App\Service\ServiceEntreprise;
use Doctrine\ORM\EntityRepository;
use App\Service\ServicePreferences;
use App\Service\ServiceSuppression;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use App\Service\RefactoringJS\Commandes\Commande;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Dto\BatchActionDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use App\Service\RefactoringJS\Commandes\CommandeExecuteur;
use App\Service\RefactoringJS\Evenements\SuperviseurSujet;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use App\Service\RefactoringJS\JSUIComponents\Tache\TacheUIBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use App\Service\RefactoringJS\Commandes\ComDefinirObservateursEvenements;

class ActionCRMCrudController extends AbstractCrudController implements CommandeExecuteur
{
    public const ACTION_ACHEVER_MISSION = "Achever cette mission";
    public const MISSION_ACHEVEE = "Achevée";
    public const MISSION_ENCOURS = "Encours";
    public const STATUS_MISSION = [
        self::MISSION_ACHEVEE => 1,
        self::MISSION_ENCOURS => 0
    ];

    public ?Crud $crud = null;
    public ?TacheUIBuilder $uiBuilder = null;


    public function __construct(
        private ServiceDates $serviceDates,
        private ServiceMonnaie $serviceMonnaie,
        private ServiceTaxes $serviceTaxes,
        private SuperviseurSujet $superviseurSujet,
        private ServiceSuppression $serviceSuppression,
        private EntityManagerInterface $entityManager,
        private Security $security,
        private ServiceEntreprise $serviceEntreprise,
        private ServicePreferences $servicePreferences,
        private ServiceCrossCanal $serviceCrossCanal,
        private AdminUrlGenerator $adminUrlGenerator
    ) {
        $this->uiBuilder = new TacheUIBuilder($this->serviceEntreprise);
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $connected_entreprise = $this->serviceEntreprise->getEntreprise();
        //dd($connected_entreprise);
        //dd($this->getUser());
        $hasVisionGlobale = $this->isGranted(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE]);
        //dd($hasVisionGlobale);
        $defaultQueryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        if ($hasVisionGlobale == false) {
            $defaultQueryBuilder
                ->Where('entity.utilisateur = :user')
                ->orWhere('entity.attributedTo = :user')
                ->setParameter('user', $this->getUser());
        }
        return $defaultQueryBuilder
            ->andWhere('entity.entreprise = :ese')
            ->setParameter('ese', $connected_entreprise);
    }

    public static function getEntityFqcn(): string
    {
        return ActionCRM::class;
    }

    public function configureFilters(Filters $filters): Filters
    {
        if ($this->isGranted(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])) {
            $filters->add('utilisateur');
        }
        return $filters
            //->add('startedAt')
            //->add('endedAt')
            //->add('piste')
            //->add('police')
            //->add('cotation')
            //->add('sinistre')
            //->add('clos')
            //->add('attributedTo')
        ;
    }

    public function configureCrud(Crud $crud): Crud
    {
        //Application de la préférence sur la taille de la liste
        $this->servicePreferences->appliquerPreferenceTaille(new ActionCRM(), $crud);
        $this->crud = $crud
            ->setDateTimeFormat('dd/MM/yyyy à HH:mm:ss')
            ->setDateFormat('dd/MM/yyyy')
            //->setPaginatorPageSize(100)
            ->renderContentMaximized()
            ->setEntityLabelInSingular("Mission")
            ->setEntityLabelInPlural("Missions")
            ->setPageTitle("index", "Liste des missions")
            ->setDefaultSort(['updatedAt' => 'DESC'])
            ->setEntityPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACCES_COMMERCIAL])
            // ...
        ;
        return $crud;
    }

    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        /** @var ActionCRM */
        $actionToDelete = $entityInstance;
        //Exécuter - Ecouteurs d'évènements
        $this->executer(new ComDefinirObservateursEvenements(
            $this->superviseurSujet,
            $this->entityManager,
            $this->serviceEntreprise,
            $this->serviceDates,
            $actionToDelete
        ));
        //destruction des feedbacks
        foreach ($actionToDelete->getFeedbacks() as $feedback) {
            $actionToDelete->removeFeedback($feedback);
        }
        //destruction définitive de la piste
        $this->entityManager->remove($actionToDelete);
        $this->entityManager->flush();

        // $this->serviceSuppression->supprimer($entityInstance, ServiceSuppression::CRM_ACTION);
    }


    public function createEntity(string $entityFqcn)
    {
        $objet = new ActionCRM();
        $objet->setStartedAt(new DateTimeImmutable("+1 day"));
        $objet->setEndedAt(new DateTimeImmutable("+7 day"));
        $objet->setClosed(ActionCRMCrudController::STATUS_MISSION[ActionCRMCrudController::MISSION_ENCOURS]);
        $objet = $this->serviceCrossCanal->crossCanal_Mission_setPiste($objet, $this->adminUrlGenerator);
        $objet = $this->serviceCrossCanal->crossCanal_Mission_setPolice($objet, $this->adminUrlGenerator);
        $objet = $this->serviceCrossCanal->crossCanal_Mission_setCotation($objet, $this->adminUrlGenerator);
        $objet = $this->serviceCrossCanal->crossCanal_Mission_setSinistre($objet, $this->adminUrlGenerator);
        
        //Exécuter - Ecouteurs d'évènements
        $this->executer(new ComDefinirObservateursEvenements(
            $this->superviseurSujet,
            $this->entityManager,
            $this->serviceEntreprise,
            $this->serviceDates,
            $objet
        ));
        
        return $objet;
    }

    public function configureFields(string $pageName): iterable
    {
        $instance = $this->getContext()->getEntity()->getInstance();
        if ($this->crud != null) {
            $this->crud = $this->serviceCrossCanal->crossCanal_setTitrePage($this->crud, $this->adminUrlGenerator, $this->getContext()->getEntity()->getInstance());
        } else {
            $this->adminUrlGenerator->set("champsACacher", [
                PreferenceCrudController::PREF_CRM_MISSION_POLICE,
                PreferenceCrudController::PREF_CRM_MISSION_COTATION,
                PreferenceCrudController::PREF_CRM_MISSION_SINISTRE,
                //PreferenceCrudController::PREF_CRM_MISSION_STATUS,
                PreferenceCrudController::PREF_CRM_MISSION_PISTE,
            ]);
        }
        // return $this->servicePreferences->getChamps(new ActionCRM(), $this->crud, $this->adminUrlGenerator);
        //Exécuter - Ecouteurs d'évènements
        $this->executer(new ComDefinirObservateursEvenements(
            $this->superviseurSujet,
            $this->entityManager,
            $this->serviceEntreprise,
            $this->serviceDates,
            $instance
        ));

        //dd($this->adminUrlGenerator);
        // return $this->servicePreferences->getChamps(new Contact(), $this->crud, $this->adminUrlGenerator);
        return $this->uiBuilder->render(
            $this->entityManager,
            $this->serviceMonnaie,
            $this->serviceTaxes,
            $pageName,
            $instance,
            $this->crud,
            $this->adminUrlGenerator
        );
    }


    public function configureActions(Actions $actions): Actions
    {
        //Cross Canal
        $feedback_ajouter = Action::new(ServiceCrossCanal::OPTION_FEEDBACK_AJOUTER)
            ->setIcon('fas fa-comments')
            ->linkToCrudAction('cross_canal_ajouterFeedback');
        $feedback_lister = Action::new(ServiceCrossCanal::OPTION_FEEDBACK_LISTER)
            ->setIcon('fa-solid fa-rectangle-list') //<i class="fa-solid fa-rectangle-list"></i>
            ->linkToCrudAction('cross_canal_listerFeedback');


        $terminer = Action::new(self::ACTION_ACHEVER_MISSION)
            ->setIcon('fas fa-regular fa-circle-check')
            ->linkToCrudAction('terminerAction');
        $duplicate = Action::new(DashboardController::ACTION_DUPLICATE)->setIcon('fa-solid fa-copy')
            ->linkToCrudAction('dupliquerEntite'); //<i class="fa-solid fa-copy"></i>
        $ouvrir = Action::new(DashboardController::ACTION_OPEN)
            ->setIcon('fa-solid fa-eye')->linkToCrudAction('ouvrirEntite'); //<i class="fa-solid fa-eye"></i>
        $exporter_ms_excels = Action::new("exporter_ms_excels", DashboardController::ACTION_EXPORTER_EXCELS)
            ->linkToCrudAction('exporterMSExcels')
            ->addCssClass('btn btn-primary')
            ->setIcon('fa-solid fa-file-excel'); //<i class="fa-solid fa-file-excel"></i>

        return $actions
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
                return $action->setIcon('fas fa-paper-plane')->setCssClass('btn btn-primary')->setLabel(DashboardController::ACTION_AJOUTER);
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
            //Action terminer
            ->add(Crud::PAGE_DETAIL, $terminer)
            ->add(Crud::PAGE_EDIT, $terminer)
            ->add(Crud::PAGE_INDEX, $terminer)
            //Action terminer
            ->add(Crud::PAGE_DETAIL, $feedback_ajouter)
            ->add(Crud::PAGE_INDEX, $feedback_ajouter)

            ->add(Crud::PAGE_DETAIL, $feedback_lister)
            ->add(Crud::PAGE_INDEX, $feedback_lister)
            //Reorganisation des boutons
            ->reorder(Crud::PAGE_INDEX, [DashboardController::ACTION_OPEN, DashboardController::ACTION_DUPLICATE])
            ->reorder(Crud::PAGE_EDIT, [DashboardController::ACTION_OPEN, DashboardController::ACTION_DUPLICATE])

            ->remove(Crud::PAGE_INDEX, Action::NEW)

            //Application des roles
            ->setPermission(Action::NEW, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
            ->setPermission(Action::EDIT, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
            ->setPermission(Action::DELETE, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
            ->setPermission(Action::BATCH_DELETE, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
            ->setPermission(Action::SAVE_AND_ADD_ANOTHER, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
            ->setPermission(Action::SAVE_AND_CONTINUE, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
            ->setPermission(Action::SAVE_AND_RETURN, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
            ->setPermission(DashboardController::ACTION_DUPLICATE, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
            ->setPermission(self::ACTION_ACHEVER_MISSION, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
            ->setPermission(ServiceCrossCanal::OPTION_FEEDBACK_AJOUTER, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION]);
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


    public function cross_canal_ajouterFeedback(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Action_ajouterFeedback($context, $adminUrlGenerator));
    }

    public function cross_canal_listerFeedback(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Action_listerFeedback($context, $adminUrlGenerator));
    }

    public function terminerAction(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {

        $entite = $context->getEntity()->getInstance();
        $entite->setClos(true);
        parent::persistEntity($em, $entite);

        $url = $adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::INDEX)
            ->setEntityId($entite->getId())
            ->generateUrl();

        //dd($entite);

        return $this->redirect($url);
    }

    public function dupliquerEntite(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
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

    public function executer(?Commande $commande)
    {
        if ($commande != null) {
            $commande->executer();
        }
    }
}
