<?php

namespace App\Controller\Admin;

use App\Entity\Taxe;
use App\Service\RefactoringJS\JSUIComponents\Paiement\TaxeUIBuilder;
use Doctrine\ORM\QueryBuilder;
use App\Service\ServiceEntreprise;
use App\Service\ServiceCalculateur;
use App\Service\ServiceCrossCanal;
use App\Service\ServiceMonnaie;
use App\Service\ServicePreferences;
use App\Service\ServiceSuppression;
use App\Service\ServiceTaxes;
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
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class TaxeCrudController extends AbstractCrudController
{
    public const TAXE_COURTIER = 1;
    public const TAXE_ASSUREUR = 0;
    public ?TaxeUIBuilder $uiBuilder = null;
    public ?Taxe $taxe = null;

    public const TAB_TAXE_PAYABLE_PAR_COURTIER = [
        'Non' => 0,
        'Oui' => 1
    ];
    
    private ?Crud $crud = null;

    public function __construct(
        private ServiceCrossCanal $serviceCrossCanal,
        private ServiceSuppression $serviceSuppression,
        private EntityManagerInterface $entityManager,
        private ServiceEntreprise $serviceEntreprise,
        private ServicePreferences $servicePreferences,
        private ServiceMonnaie $serviceMonnaie,
        private ServiceTaxes $serviceTaxes,
        private AdminUrlGenerator $adminUrlGenerator
    ) {
        $this->uiBuilder = new TaxeUIBuilder();
    }

    public static function getEntityFqcn(): string
    {
        return Taxe::class;
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

    public function configureFilters(Filters $filters): Filters
    {
        if ($this->isGranted(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])) {
            $filters->add('utilisateur');
        }
        return $filters
            ->add('tauxIARD')
            ->add('tauxVIE')
            ->add(ChoiceFilter::new('payableparcourtier', 'Payable par le courtier?')->setChoices(self::TAB_TAXE_PAYABLE_PAR_COURTIER));
    }

    public function configureCrud(Crud $crud): Crud
    {
        //Application de la préférence sur la taille de la liste
        $this->servicePreferences->appliquerPreferenceTaille(new Taxe(), $crud);
        $this->crud = $crud
            ->setDateTimeFormat('dd/MM/yyyy à HH:mm:ss')
            ->setDateFormat('dd/MM/yyyy')
            //->setPaginatorPageSize(100)
            ->renderContentMaximized()
            ->setEntityLabelInSingular("Taxe")
            ->setEntityLabelInPlural("Taxes")
            ->setPageTitle("index", "Liste des taxes")
            ->setDefaultSort(['updatedAt' => 'DESC'])
            ->setEntityPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACCES_FINANCES])
            // ...
        ;
        return $crud;
    }

    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->serviceSuppression->supprimer($entityInstance, ServiceSuppression::FINANCE_TAXE);
    }


    public function createEntity(string $entityFqcn)
    {
        $objet = new Taxe();
        $objet->setTauxIARD(0.16);
        $objet->setTauxVIE(0.16);
        $objet->setPayableparcourtier(true);
        //$objet->setStartedAt(new DateTimeImmutable("+1 day"));
        //$objet->setEndedAt(new DateTimeImmutable("+7 day"));
        //$objet->setClos(0);
        return $objet;
    }


    public function configureFields(string $pageName): iterable
    {
        // $this->crud = $this->serviceCrossCanal->crossCanal_setTitrePage($this->crud, $this->adminUrlGenerator, $this->getContext()->getEntity()->getInstance());
        // return $this->servicePreferences->getChamps(new Taxe(), $this->crud, $this->adminUrlGenerator);
        /** @var Taxe */
        $this->taxe = $this->getContext()->getEntity()->getInstance();
        $this->crud = $this->serviceCrossCanal->crossCanal_setTitrePage($this->crud, $this->adminUrlGenerator, $this->taxe);

        return $this->uiBuilder->render(
            $this->entityManager,
            $this->serviceMonnaie,
            $this->serviceTaxes,
            $pageName,
            $this->taxe,
            $this->crud,
            $this->adminUrlGenerator
        );
    }


    public function configureActions(Actions $actions): Actions
    {
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
                return $action->setIcon('fas fa-landmark-dome')->setCssClass('btn btn-primary')->setLabel(DashboardController::ACTION_AJOUTER);
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
}
