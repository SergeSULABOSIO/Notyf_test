<?php

namespace App\Controller\Admin;

use App\Entity\Piste;
use App\Entity\Client;
use App\Entity\Police;
use App\Entity\Revenu;
use App\Entity\Produit;
use App\Entity\Assureur;
use App\Entity\Partenaire;
use App\Entity\Utilisateur;
use App\Service\ServiceDates;
use App\Service\ServiceTaxes;
use Doctrine\ORM\QueryBuilder;
use App\Service\ServiceMonnaie;
use Doctrine\ORM\Query\Expr\Func;
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
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use App\Service\RefactoringJS\JSUIComponents\Revenu\RevenuUIBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class RevenuCrudController extends AbstractCrudController
{
    //Type de revenu
    public const TYPE_COM_REA = "Commission de réassurance";
    public const TYPE_COM_LOCALE = "Commission locale";
    public const TYPE_COM_FRONTING = "Commission sur Fronting";
    public const TYPE_FRAIS_DE_GESTION = "Frais de gestion";
    public const TYPE_AUTRE_CHARGEMENT = "Autre chargement";

    public const TAB_TYPE = [
        self::TYPE_COM_REA              => 0,
        self::TYPE_COM_LOCALE           => 1,
        self::TYPE_COM_FRONTING         => 2,
        self::TYPE_FRAIS_DE_GESTION     => 3,
        self::TYPE_AUTRE_CHARGEMENT     => 4
    ];

    //Partageable?
    public const PARTAGEABLE_OUI = "Oui";
    public const PARTAGEABLE_NON = "Pas du tout";
    public const TAB_PARTAGEABLE = [
        self::PARTAGEABLE_NON   => 0,
        self::PARTAGEABLE_OUI   => 1
    ];

    //Taxable?
    public const TAXABLE_OUI = "Oui";
    public const TAXABLE_NON = "Pas du tout.";
    public const TAB_TAXABLE = [
        self::TAXABLE_NON   => 0,
        self::TAXABLE_OUI   => 1
    ];

    //Base des calculs
    public const BASE_FRONTING = "Un % du Fronting";
    public const BASE_PRIME_NETTE = "Un % de la prime nette";
    public const BASE_MONTANT_FIXE = "Une valeur fixe";
    public const TAB_BASE = [
        self::BASE_FRONTING   => 0,
        self::BASE_PRIME_NETTE   => 1,
        self::BASE_MONTANT_FIXE   => 2
    ];

    public ?Crud $crud = null;
    public $revenu = null;
    public ?RevenuUIBuilder $uiBuilder = null;

    public function __construct(
        private ServiceSuppression $serviceSuppression,
        private EntityManagerInterface $entityManager,
        private ServiceDates $serviceDates,
        private ServiceEntreprise $serviceEntreprise,
        private ServicePreferences $servicePreferences,
        private ServiceCrossCanal $serviceCrossCanal,
        private ServiceTaxes $serviceTaxes,
        private ServiceMonnaie $serviceMonnaie,
        private AdminUrlGenerator $adminUrlGenerator,
        private ServiceFiltresNonMappes $serviceFiltresNonMappes
    ) {
        $this->uiBuilder = new RevenuUIBuilder();
    }

    public static function getEntityFqcn(): string
    {
        return Revenu::class;
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

    public function configureFilters(Filters $filters): Filters
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

        return $filters
            ->add(
                ChoiceFilter::new('partageable', "Partageable?")
                    ->setChoices([
                        "Oui" => true,
                        "Non" => false
                    ])
            )
            ->add(
                ChoiceFilter::new('taxable', "Taxable?")
                    ->setChoices([
                        "Oui" => true,
                        "Non" => false
                    ])
            )
            ->add(
                ChoiceFilter::new('type', "Type")
                    ->setChoices(RevenuCrudController::TAB_TYPE)
            )
            ->add(DateTimeFilter::new('dateEffet', "Début de la police"))
            ->add(DateTimeFilter::new('dateExpiration', "Echéance de la police"));
    }

    public function configureCrud(Crud $crud): Crud
    {
        //Application de la préférence sur la taille de la liste
        $this->servicePreferences->appliquerPreferenceTaille(new Revenu(), $crud);

        $this->crud = $crud
            ->setDateTimeFormat('dd/MM/yyyy') // HH:mm:ss
            ->setDateFormat('dd/MM/yyyy')
            //->setPaginatorPageSize(100)
            ->renderContentMaximized()
            ->setEntityLabelInSingular("Revenu")
            ->setEntityLabelInPlural("Revenus")
            ->setPageTitle("index", "Liste des revenus")
            ->setDefaultSort(['updatedAt' => 'DESC'])
            ->setEntityPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACCES_FINANCES])
            // ...
        ;
        return $crud;
    }

    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->serviceSuppression->supprimer($entityInstance, ServiceSuppression::FINANCE_REVENU);
    }

    public function createEntity(string $entityFqcn)
    {
        $objet = new Revenu();
        $objet->setType(RevenuCrudController::TAB_TYPE[RevenuCrudController::TYPE_COM_LOCALE]);
        $objet->setPartageable(RevenuCrudController::TAB_PARTAGEABLE[RevenuCrudController::PARTAGEABLE_NON]);
        $objet->setTaxable(RevenuCrudController::TAB_TAXABLE[RevenuCrudController::TAXABLE_OUI]);
        $objet->setBase(RevenuCrudController::TAB_BASE[RevenuCrudController::BASE_PRIME_NETTE]);
        $objet->setTaux(10);
        $objet->setMontantFlat(0);
        $objet->setCreatedAt($this->serviceDates->aujourdhui());
        $objet->setUpdatedAt($this->serviceDates->aujourdhui());
        $objet->setUtilisateur($this->serviceEntreprise->getUtilisateur());
        $objet->setEntreprise($this->serviceEntreprise->getEntreprise());
        //dd($objet);
        return $objet;
    }

    public function configureFields(string $pageName): iterable
    {
        $this->revenu = $this->getContext()->getEntity()->getInstance();
        if ($this->crud != null) {
            $this->crud = $this->serviceCrossCanal->crossCanal_setTitrePage($this->crud, $this->adminUrlGenerator, $this->revenu);
        }

        return $this->uiBuilder->render(
            $this->entityManager,
            $this->serviceMonnaie,
            $this->serviceTaxes,
            $pageName,
            $this->revenu,
            $this->crud,
            $this->adminUrlGenerator
        );
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
            ->setPermission(DashboardController::ACTION_DUPLICATE, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION]);
    }

    public function ouvrirEntite(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        /** @var Revenu */
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
}
