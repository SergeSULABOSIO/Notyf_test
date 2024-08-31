<?php

namespace App\Controller\Admin;

use App\Entity\Client;
use App\Service\ServiceDates;
use App\Service\ServiceTaxes;
use Doctrine\ORM\QueryBuilder;
use App\Service\ServiceMonnaie;
use App\Service\ServiceCrossCanal;
use App\Service\ServiceEntreprise;
use App\Service\ServiceCalculateur;
use App\Service\ServicePreferences;
use App\Service\ServiceSuppression;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use App\Service\RefactoringJS\Commandes\Commande;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Dto\BatchActionDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use App\Service\RefactoringJS\Commandes\CommandeExecuteur;
use App\Service\RefactoringJS\Evenements\SuperviseurSujet;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use App\Service\RefactoringJS\JSUIComponents\Client\ClientUIBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use App\Service\RefactoringJS\Commandes\ComDefinirObservateursEvenements;

class ClientCrudController extends AbstractCrudController implements CommandeExecuteur
{
    public const TAB_CLIENT_SECTEUR = [
        'Agroalimentaire' => 0,
        'Banque / Assurance' => 1,
        'Bois / Papier / Carton / Imprimerie' => 2,
        'BTP / Matériaux de construction' => 3,
        'Chimie / Parachimie' => 4,
        'Commerce / Négoce / Distribution' => 5,
        'Édition / Communication / Multimédia' => 6,
        'Électronique / Électricité' => 7,
        'Études et conseils' => 8,
        'Hôtelerie' => 9,
        'Industrie pharmaceutique' => 10,
        'Informatique / Télécoms' => 11,
        'Machines et équipements / Automobile' => 12,
        'Métallurgie / Travail du métal' => 13,
        'Mines' => 14,
        'Plastique / Caoutchouc' => 15,
        'Restauration' => 16,
        'Santé' => 17,
        'Services aux entreprises' => 18,
        'Textile / Habillement / Chaussure' => 19,
        'Transports / Logistique' => 20,
        'Autres Secteurs' => 21
    ];

    public const TAB_CLIENT_IS_PERSONNE_MORALE = [
        'Personne Morale' => 0,
        'Personne Physique' => 1
    ];

    public ?Crud $crud = null;
    public ?ClientUIBuilder $uiBuilder = null;

    public function __construct(
        private SuperviseurSujet $superviseurSujet,
        private ServiceDates $serviceDates,
        private ServiceMonnaie $serviceMonnaie,
        private ServiceTaxes $serviceTaxes,
        private ServiceSuppression $serviceSuppression,
        private EntityManagerInterface $entityManager,
        private ServiceEntreprise $serviceEntreprise,
        private ServicePreferences $servicePreferences,
        private ServiceCrossCanal $serviceCrossCanal,
        private AdminUrlGenerator $adminUrlGenerator
    ) {
        $this->uiBuilder = new ClientUIBuilder();
    }


    public static function getEntityFqcn(): string
    {
        return Client::class;
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
        $this->servicePreferences->appliquerPreferenceTaille(new Client(), $crud);
        $this->crud = $crud
            ->setDateTimeFormat('dd/MM/yyyy') // à HH:mm:ss
            ->setDateFormat('dd/MM/yyyy')
            //->setPaginatorPageSize(100)
            ->renderContentMaximized()
            ->setEntityLabelInSingular("Client")
            ->setEntityLabelInPlural("Clients")
            ->setPageTitle("index", "Liste des clients")
            ->setDefaultSort(['updatedAt' => 'DESC'])
            ->setEntityPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACCES_PRODUCTION])
            // ...
        ;
        return $this->crud;
    }

    public function configureFilters(Filters $filters): Filters
    {
        if ($this->isGranted(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])) {
            $filters->add('utilisateur');
        }
        return $filters
            ->add(ChoiceFilter::new("ispersonnemorale", "Forme Juridique")->setChoices(self::TAB_CLIENT_IS_PERSONNE_MORALE))
            ->add(ChoiceFilter::new("secteur", "Secteur d'activité")->setChoices(self::TAB_CLIENT_SECTEUR));
    }

    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        /** @var Client */
        $clientToDelete = $entityInstance;
        //Exécuter - Ecouteurs d'évènements
        $this->executer(new ComDefinirObservateursEvenements(
            $this->superviseurSujet,
            $this->entityManager,
            $this->serviceEntreprise,
            $this->serviceDates,
            $clientToDelete
        ));

        //destruction définitive de la piste
        $this->entityManager->remove($clientToDelete);
        $this->entityManager->flush();
        // $this->serviceSuppression->supprimer($entityInstance, ServiceSuppression::PRODUCTION_CLIENT);
    }


    public function createEntity(string $entityFqcn)
    {
        $objet = new Client();
        $objet = $this->serviceCrossCanal->crossCanal_Client_setCotation($objet, $this->adminUrlGenerator);
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
        if ($this->crud) {
            $this->crud = $this->serviceCrossCanal->crossCanal_setTitrePage($this->crud, $this->adminUrlGenerator, $this->getContext()->getEntity()->getInstance());
        } else {
            $this->adminUrlGenerator->set("champsACacher", [
                PreferenceCrudController::PREF_PRO_CLIENT_COTATIONS,
                PreferenceCrudController::PREF_PRO_CLIENT_DATE_DE_CREATION,
                PreferenceCrudController::PREF_PRO_CLIENT_DATE_DE_MODIFICATION,
                PreferenceCrudController::PREF_PRO_CLIENT_ENTREPRISE,
                PreferenceCrudController::PREF_PRO_CLIENT_UTILISATEUR,
                PreferenceCrudController::PREF_PRO_CLIENT_POLICES,
            ]);
        }

        //Exécuter - Ecouteurs d'évènements
        $this->executer(new ComDefinirObservateursEvenements(
            $this->superviseurSujet,
            $this->entityManager,
            $this->serviceEntreprise,
            $this->serviceDates,
            $instance
        ));

        // return $this->servicePreferences->getChamps(new Client(), $this->crud, $this->adminUrlGenerator);
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
        //cross canal
        // $police_lister = Action::new(ServiceCrossCanal::OPTION_POLICE_LISTER)
        //     ->displayIf(static function (?Client $entity) {
        //         return count($entity->getPolice()) != 0;
        //     })
        //     ->setIcon('fas fa-file-shield')
        //     ->linkToCrudAction('cross_canal_listerPolice');

        // $cotation_lister = Action::new(ServiceCrossCanal::OPTION_COTATION_LISTER)
        //     ->displayIf(static function (?Client $entity) {
        //         return count($entity->getCotations()) != 0;
        //     })
        //     ->setIcon('fas fa-cash-register')
        //     ->linkToCrudAction('cross_canal_listerCotation');

        $duplicate = Action::new(DashboardController::ACTION_DUPLICATE)->setIcon('fa-solid fa-copy')
            ->linkToCrudAction('dupliquerEntite'); //<i class="fa-solid fa-copy"></i>
        $ouvrir = Action::new(DashboardController::ACTION_OPEN)
            ->setIcon('fa-solid fa-eye')->linkToCrudAction('ouvrirEntite'); //<i class="fa-solid fa-eye"></i>
        $exporter_ms_excels = Action::new("exporter_ms_excels", DashboardController::ACTION_EXPORTER_EXCELS)
            ->linkToCrudAction('exporterMSExcels')
            ->addCssClass('btn btn-primary')
            ->setIcon('fa-solid fa-file-excel');

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
                return $action->setIcon('fas fa-person-shelter')->setCssClass('btn btn-primary')->setLabel(DashboardController::ACTION_AJOUTER);
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

            // ->add(Crud::PAGE_DETAIL, $police_lister)
            // ->add(Crud::PAGE_INDEX, $police_lister)

            // ->add(Crud::PAGE_DETAIL, $cotation_lister)
            // ->add(Crud::PAGE_INDEX, $cotation_lister)

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

    public function cross_canal_listerPolice(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Client_listerPolice($context, $adminUrlGenerator));
    }

    public function cross_canal_listerCotation(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Client_listerCotation($context, $adminUrlGenerator));
    }

    public function executer(?Commande $commande)
    {
        if ($commande != null) {
            $commande->executer();
        }
    }
}
