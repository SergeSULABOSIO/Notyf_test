<?php

namespace App\Controller\Admin;

use App\Entity\Preference;
use App\Service\ServiceEntreprise;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;

class PreferenceCrudController extends AbstractCrudController
{
    //CRM - ACTION / MISSION
    public const PREF_CRM_MISSION_ID = 0;
    public const PREF_CRM_MISSION_MISSION = 1;
    public const PREF_CRM_MISSION_OBJECTIF = 2;
    public const PREF_CRM_MISSION_STARTED_AT = 3;
    public const PREF_CRM_MISSION_ENDED_AT = 4;
    public const PREF_CRM_MISSION_UTILISATEUR = 5;
    public const PREF_CRM_MISSION_ENTREPRISE = 6;
    public const PREF_CRM_MISSION_CREATED_AT = 7;
    public const PREF_CRM_MISSION_UPDATED_AT = 8;
    public const TAB_CRM_MISSION = [
        self::PREF_CRM_MISSION_ID => "Id",
        self::PREF_CRM_MISSION_MISSION => "Nom",
        self::PREF_CRM_MISSION_OBJECTIF => "Objectif",
        self::PREF_CRM_MISSION_STARTED_AT => "Date d'effet",
        self::PREF_CRM_MISSION_ENDED_AT => "Echéance",
        self::PREF_CRM_MISSION_UTILISATEUR => "Utilisateur",
        self::PREF_CRM_MISSION_ENTREPRISE => "Entreprise",
        self::PREF_CRM_MISSION_CREATED_AT => "Date de création",
        self::PREF_CRM_MISSION_UPDATED_AT => "Date de modification"
    ];

    //CRM - FEEDBACK
    public const PREF_CRM_FEEDBACK_ID = 0;
    public const PREF_CRM_FEEDBACK_MESAGE = 1;
    public const PREF_CRM_FEEDBACK_PROCHAINE_ETAPE = 2;
    public const PREF_CRM_FEEDBACK_DATE_EFFET = 3;
    public const PREF_CRM_FEEDBACK_ACTION = 4;
    public const PREF_CRM_FEEDBACK_DATE_CREATION = 5;
    public const PREF_CRM_FEEDBACK_DATE_MODIFICATION = 6;
    public const PREF_CRM_FEEDBACK_UTILISATEUR = 7;
    public const PREF_CRM_FEEDBACK_ENTREPRISE = 8;
    public const TAB_CRM_FEEDBACK = [
        self::PREF_CRM_FEEDBACK_ID => "Id",
        self::PREF_CRM_FEEDBACK_MESAGE => "Message",
        self::PREF_CRM_FEEDBACK_PROCHAINE_ETAPE => "Mission suivante",
        self::PREF_CRM_FEEDBACK_DATE_EFFET => "Date d'effet",
        self::PREF_CRM_FEEDBACK_ACTION => "Mission",
        self::PREF_CRM_FEEDBACK_UTILISATEUR => "Utilisateur",
        self::PREF_CRM_FEEDBACK_ENTREPRISE => "Entreprise",
        self::PREF_CRM_FEEDBACK_DATE_CREATION => "Date de création",
        self::PREF_CRM_FEEDBACK_DATE_MODIFICATION => "Date de modification",
    ];


    public const PREF_APPARENCE_CLAIRE = 0;
    public const PREF_APPARENCE_SOMBRE = 1;
    public const TAB_APPARENCES = [
        'Désactiver le mode sombre' => self::PREF_APPARENCE_CLAIRE,
        'Permettre le mode sombre' => self::PREF_APPARENCE_SOMBRE
    ];
    public const PREF_UTILISATEUR = "Utilisateur";
    public const PREF_ENTREPRISE = "Entreprise";


    public function __construct(
        private EntityManagerInterface $entityManager,
        private ServiceEntreprise $serviceEntreprise
    ) {
        //AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em
        $preferences = $this->entityManager->getRepository(Preference::class)->findBy(
            [
                'entreprise' => $this->serviceEntreprise->getEntreprise(),
                'utilisateur' => $this->serviceEntreprise->getUtilisateur(),
            ]
        );
        //dd($preferences[0]->getApparence());
        if($preferences[0]->getApparence() == 1){
            
        }
    }

    public static function getEntityFqcn(): string
    {
        return Preference::class;
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
        return $crud
            ->setDateTimeFormat('dd/MM/yyyy à HH:mm:ss')
            ->setDateFormat('dd/MM/yyyy')
            ->setPaginatorPageSize(100)
            ->renderContentMaximized()
            ->setEntityLabelInSingular("Paramètres d'affichage")
            ->setEntityLabelInPlural("Paramètres d'affichage")
            ->setPageTitle("index", "Liste des préférences")
            ->setDefaultSort(['updatedAt' => 'DESC'])
            ->setEntityPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACCES_PRODUCTION])
            // ...
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        if ($this->isGranted(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])) {
            $filters->add('utilisateur');
        }
        return $filters;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addTab(' Généralité')
                ->setIcon('fas fa-file-shield'), //<i class="fa-sharp fa-solid fa-address-book"></i>
            //->setHelp("Le contrat d'assurance en place."),

            //Ligne 01
            ChoiceField::new('apparence', "Apparence")
                //->setColumns(4)
                ->setChoices(self::TAB_APPARENCES),

            AssociationField::new('utilisateur', self::PREF_UTILISATEUR)
                //->setColumns(6)
                ->onlyOnDetail()
                ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                    return $entityRepository
                        ->createQueryBuilder('e')
                        ->Where('e.entreprise = :ese')
                        ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
                }),

            AssociationField::new('entreprise', self::PREF_ENTREPRISE)->onlyOnDetail(),
            DateTimeField::new('createdAt', "Date de création")->onlyOnDetail(),//->setColumns(2),
            DateTimeField::new('updatedAt', "Date de modification")->onlyOnDetail(),//->setColumns(2),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            //les Updates sur la page détail
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action->setIcon('fa-solid fa-pen-to-square')->setLabel(DashboardController::ACTION_MODIFIER);
            })
            //Updates Sur la page Edit
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN, function (Action $action) {
                return $action->setIcon('fa-solid fa-floppy-disk')->setLabel(DashboardController::ACTION_ENREGISTRER); //<i class="fa-solid fa-floppy-disk"></i>
            })
            ->update(Crud::PAGE_DETAIL, Action::EDIT, function (Action $action) {
                return $action->setIcon('fa-solid fa-pen-to-square')->setLabel(DashboardController::ACTION_MODIFIER);
            })

            ->remove(Crud::PAGE_DETAIL, Action::DELETE)
            ->remove(Crud::PAGE_DETAIL, Action::INDEX)
            ->remove(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE)
        ;
    }
}
