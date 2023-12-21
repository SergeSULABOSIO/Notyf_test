<?php

namespace App\Controller\Admin;

use mapped;
use App\Entity\Client;
use App\Entity\Police;
use App\Entity\Produit;
use App\Entity\Tranche;
use App\Entity\Assureur;
use App\Entity\Partenaire;
use App\Entity\Piste;
use App\Entity\Utilisateur;
use App\Service\ServiceDates;
use Doctrine\ORM\QueryBuilder;
use App\Service\ServiceCrossCanal;
use App\Service\ServiceEntreprise;
use App\Service\ServiceCalculateur;
use App\Service\ServicePreferences;
use App\Service\ServiceSuppression;
use App\Service\ServiceFiltresNonMappes;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Dto\BatchActionDto;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;

class TrancheCrudController extends AbstractCrudController
{
    public ?Crud $crud = null;

    public function __construct(
        private ServiceSuppression $serviceSuppression,
        private ServiceCalculateur $serviceCalculateur,
        private EntityManagerInterface $entityManager,
        private ServiceDates $serviceDates,
        private ServiceEntreprise $serviceEntreprise,
        private ServicePreferences $servicePreferences,
        private ServiceCrossCanal $serviceCrossCanal,
        private AdminUrlGenerator $adminUrlGenerator,
        private ServiceFiltresNonMappes $serviceFiltresNonMappes

    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Tranche::class;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $connected_entreprise = $this->serviceEntreprise->getEntreprise();
        $hasVisionGlobale = $this->isGranted(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE]);

        //On applique le critère basé sur les attributs non mappés dans l'entité
        $defaultQueryBuilder = $this->serviceFiltresNonMappes->appliquerCriteresAttributsNonMappes(
            $searchDto,
            $entityDto,
            $fields,
            $filters,
            function (SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder {
                return parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
            }
        );

        //dd($defaultQueryBuilder);

        //Filtre standard pour Utilisateur et Entreprise
        if ($hasVisionGlobale == false) {
            $defaultQueryBuilder
                ->Where('entity.utilisateur = :user')
                ->setParameter('user', $this->getUser());
        }
        return $defaultQueryBuilder
            ->andWhere('entity.entreprise = :ese')
            ->setParameter('ese', $connected_entreprise);
    }

    function configureFilters(Filters $filters): Filters
    {
        if ($this->isGranted(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])) {
            $filters->add('utilisateur');
        }

        //FILTRES BASES SUR LES ATTRIBUTS NON MAPPES
        $criteresNonMappes = [
            "validated" => [
                "label" => "Validée?",
                "class" => null,
                "defaultValue" => true,
                "userValues" => null,
                "options" => [
                    "Oui" => true,
                    "Non" => false,
                ],
                "multipleChoices" => false,
                "joiningEntity" => "cotation",
            ],
            "piste" => [
                "label" => "Pistes",
                "class" => Piste::class,
                "defaultValue" => [],
                "userValues" => [],
                "options" => [],
                "multipleChoices" => true,
                "joiningEntity" => "cotation",
            ],
            "police" => [
                "label" => "Polices",
                "class" => Police::class,
                "defaultValue" => [],
                "userValues" => [],
                "options" => [],
                "multipleChoices" => true,
                "joiningEntity" => "cotation",
            ],
            "client" => [
                "label" => "Clients",
                "class" => Client::class,
                "defaultValue" => [],
                "userValues" => [],
                "options" => [],
                "multipleChoices" => true,
                "joiningEntity" => "cotation",
            ],
            "partenaire" => [
                "label" => "Partenaires",
                "class" => Partenaire::class,
                "defaultValue" => [],
                "userValues" => [],
                "options" => [],
                "multipleChoices" => true,
                "joiningEntity" => "cotation",
            ],
            "produit" => [
                "label" => "Produits",
                "class" => Produit::class,
                "defaultValue" => [],
                "userValues" => [],
                "options" => [],
                "multipleChoices" => true,
                "joiningEntity" => "cotation",
            ],
            "assureur" => [
                "label" => "Assureurs",
                "class" => Assureur::class,
                "defaultValue" => [],
                "userValues" => [],
                "options" => [],
                "multipleChoices" => true,
                "joiningEntity" => "cotation",
            ],
            "gestionnaire" => [
                "label" => "Gestionnaire",
                "class" => Utilisateur::class,
                "defaultValue" => [],
                "userValues" => [],
                "options" => [],
                "multipleChoices" => true,
                "joiningEntity" => "cotation",
            ],
            "assistant" => [
                "label" => "Assistant",
                "class" => Utilisateur::class,
                "defaultValue" => [],
                "userValues" => [],
                "options" => [],
                "multipleChoices" => true,
                "joiningEntity" => "cotation",
            ],
        ];
        $filters = $this->serviceFiltresNonMappes->definirFiltreNonMappe($criteresNonMappes, $filters);
        //dd($filters);
        return $filters
            ->add(DateTimeFilter::new('startedAt', "Début de la tranche"))
            ->add(DateTimeFilter::new('endedAt', "Echéance de la tranche"))
            ->add(DateTimeFilter::new('dateEffet', "Début de la police"))
            ->add(DateTimeFilter::new('dateExpiration', "Echéance de la police"))
            //->add('police')
            //->add('utilisateur')
            //->add('actionCRMs')
        ;
    }

    public function configureCrud(Crud $crud): Crud
    {
        //Application de la préférence sur la taille de la liste
        $this->servicePreferences->appliquerPreferenceTaille(new Tranche(), $crud);

        $this->crud = $crud
            ->setDateTimeFormat('dd/MM/yyyy') // HH:mm:ss
            ->setDateFormat('dd/MM/yyyy')
            //->setPaginatorPageSize(100)
            ->renderContentMaximized()
            ->setEntityLabelInSingular("Tranche")
            ->setEntityLabelInPlural("Tranches")
            ->setPageTitle("index", "Liste des tranches")
            ->setDefaultSort(['updatedAt' => 'ASC'])
            ->setEntityPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACCES_PRODUCTION])
            // ...
        ;
        return $crud;
    }


    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->serviceSuppression->supprimer($entityInstance, ServiceSuppression::PRODUCTION_TRANCHE);
    }

    public function createEntity(string $entityFqcn)
    {
        $objet = new Tranche();
        $objet->setNom("");
        $objet->setTaux(100);
        $objet->setCreatedAt($this->serviceDates->aujourdhui());
        $objet->setUpdatedAt($this->serviceDates->aujourdhui());
        $objet->setUtilisateur($this->serviceEntreprise->getUtilisateur());
        $objet->setEntreprise($this->serviceEntreprise->getEntreprise());
        //dd($objet);
        return $objet;
    }



    public function configureFields(string $pageName): iterable
    {
        if ($this->crud) {
            $this->crud = $this->serviceCrossCanal->crossCanal_setTitrePage($this->crud, $this->adminUrlGenerator, $this->getContext()->getEntity()->getInstance());
        }
        //Actualisation des attributs calculables - Merci Seigneur Jésus !
        return $this->servicePreferences->getChamps(new Tranche(), $this->crud, $this->adminUrlGenerator);
    }

    public function configureActions(Actions $actions): Actions
    {
        // $duplicate = Action::new(DashboardController::ACTION_DUPLICATE)
        //     ->setIcon('fa-solid fa-copy')
        //     ->linkToCrudAction('dupliquerEntite'); //<i class="fa-solid fa-copy"></i>
        $ouvrir = Action::new(DashboardController::ACTION_OPEN)
            ->setIcon('fa-solid fa-eye')
            ->linkToCrudAction('ouvrirEntite'); //<i class="fa-solid fa-eye"></i>
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
                return $action->setIcon('fas fa-location-crosshairs')->setCssClass('btn btn-primary')->setLabel(DashboardController::ACTION_AJOUTER);
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
            // ->add(Crud::PAGE_DETAIL, $duplicate)
            // ->add(Crud::PAGE_EDIT, $duplicate)
            // ->add(Crud::PAGE_INDEX, $duplicate)

            //Reorganisation des boutons
            // ->reorder(Crud::PAGE_INDEX, [DashboardController::ACTION_OPEN, DashboardController::ACTION_DUPLICATE])
            // ->reorder(Crud::PAGE_EDIT, [DashboardController::ACTION_OPEN, DashboardController::ACTION_DUPLICATE])


            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->remove(Crud::PAGE_DETAIL, Action::EDIT)
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->remove(Crud::PAGE_DETAIL, Action::DELETE)

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

    public function ouvrirEntite(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        /** @var Tranche */
        $entite = $context->getEntity()->getInstance();

        $url = $adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::DETAIL)
            ->setEntityId($entite->getId())
            ->generateUrl();

        return $this->redirect($url);
    }
}
