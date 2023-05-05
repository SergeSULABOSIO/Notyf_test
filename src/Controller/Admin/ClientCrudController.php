<?php

namespace App\Controller\Admin;

use App\Entity\Client;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Dto\BatchActionDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class ClientCrudController extends AbstractCrudController
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


    public static function getEntityFqcn(): string
    {
        return Client::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDateTimeFormat ('dd/MM/yyyy à HH:mm:ss')
            ->setDateFormat ('dd/MM/yyyy')
            ->setPaginatorPageSize(100)
            ->renderContentMaximized()
            ->setEntityLabelInSingular("Client")
            ->setEntityLabelInPlural("Clients")
            ->setPageTitle("index", "Liste des clients")
            ->setDefaultSort(['updatedAt' => 'DESC'])
            ->setEntityPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACCES_PRODUCTION])
            // ...
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        if($this->isGranted(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])){
            $filters->add('utilisateur');
        }
        return $filters
            ->add(ChoiceFilter::new("ispersonnemorale", "Forme Juridique")->setChoices(self::TAB_CLIENT_IS_PERSONNE_MORALE))
            ->add(ChoiceFilter::new("secteur", "Secteur d'activité")->setChoices(self::TAB_CLIENT_SECTEUR))
        ;
    }
    
    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addPanel('Informations générales')
            ->setIcon('fas fa-person-shelter') //<i class="fa-sharp fa-solid fa-address-book"></i>
            ->setHelp("L'assuré ou le bénéficiaire de la couverture d'assurance."),

            //Ligne 01
            TextField::new('nom', "Nom")->setColumns(6),
            TextField::new('adresse', "Adresse")->setColumns(6),

            //Ligne 02
            TelephoneField::new('telephone', "Téléphone")->setColumns(6),
            EmailField::new('email', "Email")->setColumns(6),

            //Ligne 03
            UrlField::new('siteweb', "Site web")->hideOnIndex()->setColumns(6),
            ChoiceField::new('ispersonnemorale', "Forme Juridique")->setColumns(6)
                ->setChoices(self::TAB_CLIENT_IS_PERSONNE_MORALE),
            
            //Ligne 04
            TextField::new('rccm', "RCCM")->hideOnIndex()->setColumns(6),
            TextField::new('idnat', "Id. Nationale")->hideOnIndex()->setColumns(6),

            //Ligne 05
            TextField::new('numipot', "N°. Impôt")->hideOnIndex()->setColumns(6),
            ChoiceField::new('secteur', "Secteur")->setColumns(6)
            ->setChoices(self::TAB_CLIENT_SECTEUR),
            
            AssociationField::new('utilisateur', "Utilisateur")->setColumns(6)->hideOnForm()
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE]),


            //Ligne 06
            AssociationField::new('entreprise', "Entreprise")->hideOnIndex()->setColumns(6),
            DateTimeField::new('createdAt', "Date de création")->hideOnIndex()->hideOnForm(),
            DateTimeField::new('updatedAt', "Dernière modification")->hideOnForm()
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $duplicate = Action::new(DashboardController::ACTION_DUPLICATE)->setIcon('fa-solid fa-copy')
            ->linkToCrudAction('dupliquerEntite');//<i class="fa-solid fa-copy"></i>
        $ouvrir = Action::new(DashboardController::ACTION_OPEN)
            ->setIcon('fa-solid fa-eye')->linkToCrudAction('ouvrirEntite');//<i class="fa-solid fa-eye"></i>
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
            return $action->setIcon('fa-solid fa-pen-to-square')->setLabel(DashboardController::ACTION_MODIFIER);//<i class="fa-solid fa-pen-to-square"></i>
        })
        ->update(Crud::PAGE_DETAIL, Action::INDEX, function (Action $action) {
            return $action->setIcon('fa-regular fa-rectangle-list')->setLabel(DashboardController::ACTION_LISTE);//<i class="fa-regular fa-rectangle-list"></i>
        })
        //Updates sur la page Index
        ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
            return $action->setIcon('fas fa-person-shelter')->setCssClass('btn btn-primary')->setLabel(DashboardController::ACTION_AJOUTER);
        })
        ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
            return $action->setIcon('fa-solid fa-trash')->setLabel(DashboardController::ACTION_SUPPRIMER);//<i class="fa-solid fa-trash"></i>
        })
        ->update(Crud::PAGE_INDEX, Action::BATCH_DELETE, function (Action $action) {
            return $action->setIcon('fa-solid fa-trash')->setLabel(DashboardController::ACTION_SUPPRIMER);//<i class="fa-solid fa-trash"></i>
        })
        ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
            return $action->setIcon('fa-solid fa-pen-to-square')->setLabel(DashboardController::ACTION_MODIFIER);
        })
        //Updates Sur la page Edit
        ->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN, function (Action $action) {
            return $action->setIcon('fa-solid fa-floppy-disk')->setLabel(DashboardController::ACTION_ENREGISTRER);//<i class="fa-solid fa-floppy-disk"></i>
        })
        ->update(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE, function (Action $action) {
            return $action->setIcon('fa-solid fa-floppy-disk')->setLabel(DashboardController::ACTION_ENREGISTRER_ET_CONTINUER);
        })
        //Updates Sur la page NEW
        ->update(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER, function (Action $action) {
            return $action->setIcon('fa-solid fa-floppy-disk')->setLabel(DashboardController::ACTION_ENREGISTRER_ET_CONTINUER);
        })
        ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, function (Action $action) {
            return $action->setIcon('fa-solid fa-floppy-disk')->setLabel(DashboardController::ACTION_ENREGISTRER);//<i class="fa-solid fa-floppy-disk"></i>
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
    
}
