<?php

namespace App\Controller\Admin;

use App\Entity\ActionCRM;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
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
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class ActionCRMCrudController extends AbstractCrudController
{
    

    public const ACTION_ACHEVER_MISSION = "Achever cette mission";
    public const ACTION_AJOUTER_UN_FEEDBACK = "Ajouter un feedback";
    public const ACTION_AJOUTER_UNE_MISSION = "Ajouter une Mission";
    

    public static function getEntityFqcn(): string
    {
        return ActionCRM::class;
    }
    
    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('startedAt')
            ->add('endedAt')
            ->add('attributedTo')
            ->add('utilisateur')
        ;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDateTimeFormat ('dd/MM/yyyy HH:mm:ss')
            ->setDateFormat ('dd/MM/yyyy')
            ->setPaginatorPageSize(30)
            ->renderContentMaximized()
            ->setEntityLabelInSingular("Mission")
            ->setEntityLabelInPlural("Missions")
            ->setPageTitle("index", "Liste des missions")
            ->setDefaultSort(['updatedAt' => 'DESC'])
            // ...
        ;
    }


    
    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addPanel('Informations générales')
            ->setIcon('fas fa-paper-plane') //<i class="fa-sharp fa-solid fa-address-book"></i>
            ->setHelp("Une mission est une ou un ensembles d'actions attribuée(s) à un ou plusieurs utilisateurs."),

            //Ligne 01
            TextField::new('mission', "Tâches")->setColumns(12),
            TextareaField::new('objectif', "Objectif")->setColumns(12),

            //ligne 02
            ChoiceField::new('clos', "Status")->setColumns(6)
            ->setHelp("Précisez si cette mission/action est encore en vigueur ou pas.")
            ->setChoices([
                'Mission achevée avec succès' => 1,
                'Mission en cours...' => 0
            ]),
            AssociationField::new('piste', "Piste")->setColumns(6),
            //Ligne 03
            DateTimeField::new('startedAt', "Date effet")->setColumns(6),
            DateTimeField::new('endedAt', "Echéance")->setColumns(6),

            //Ligne 04
            AssociationField::new('utilisateur', "Utilisateur")->setColumns(6),

            AssociationField::new('attributedTo', "Attribuée à")->setColumns(6)->onlyOnForms(),
            CollectionField::new('attributedTo', "Attribuée à")->setColumns(6)->onlyOnIndex(),
            ArrayField::new('attributedTo', "Attribuée à")->setColumns(6)->onlyOnDetail(),

            //Ligne 05
            AssociationField::new('feedbacks', "Feedbacks")->setColumns(6)->onlyOnForms(),
            CollectionField::new('feedbacks', "Feedbacks")->setColumns(6)->onlyOnIndex(),
            ArrayField::new('feedbacks', "Feedbacks")->setColumns(6)->onlyOnDetail(),

            AssociationField::new('entreprise', "Entreprise")->hideOnIndex(),

            //Ligne 06
            DateTimeField::new('createdAt', "Date création")->hideOnIndex()->hideOnForm(),
            DateTimeField::new('updatedAt', "Dernière modification")->hideOnForm()
        ];
    }
    

    public function configureActions(Actions $actions): Actions
    {//<i class="fa-regular fa-circle-check"></i>
        $duplicate = Action::new(DashboardController::ACTION_DUPLICATE)->setIcon('fa-solid fa-copy')->linkToCrudAction('dupliquerEntite');//<i class="fa-solid fa-copy"></i>
        $ouvrir = Action::new(DashboardController::ACTION_OPEN)->setIcon('fa-solid fa-eye')->linkToCrudAction('ouvrirEntite');//<i class="fa-solid fa-eye"></i>
        $feedback = Action::new(self::ACTION_AJOUTER_UN_FEEDBACK)->setIcon('fas fa-comments')->linkToCrudAction('ajouterFeedback');
        $terminer = Action::new(self::ACTION_ACHEVER_MISSION)->setIcon('fas fa-regular fa-circle-check')->linkToCrudAction('terminerAction');
        $exporter_ms_excels = Action::new("exporter_ms_excels", DashboardController::ACTION_EXPORTER_EXCELS)->linkToCrudAction('exporterMSExcels')
            ->addCssClass('btn btn-primary')
            ->setIcon('fa-light fa-file-spreadsheet');//<i class="fa-light fa-file-spreadsheet"></i>

        return $actions
        //Sur la page Index - Selection
        ->addBatchAction($exporter_ms_excels)
        //ur la page détail
        ->update(Crud::PAGE_DETAIL, Action::DELETE, function (Action $action) {
            return $action->setIcon('fa-solid fa-trash')->setLabel(DashboardController::ACTION_SUPPRIMER);
        })
        ->update(Crud::PAGE_DETAIL, Action::EDIT, function (Action $action) {
            return $action->setIcon('fa-solid fa-pen-to-square')->setLabel(DashboardController::ACTION_MODIFIER);//<i class="fa-solid fa-pen-to-square"></i>
        })
        //Sur la page Index
        ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
            return $action->setIcon('fas fa-paper-plane')->setCssClass('btn btn-primary')->setLabel(self::ACTION_AJOUTER_UNE_MISSION);
        })
        ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
            return $action->setIcon('fa-solid fa-trash')->setLabel(DashboardController::ACTION_SUPPRIMER);//<i class="fa-solid fa-trash"></i>
        })
        ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
            return $action->setIcon('fa-solid fa-pen-to-square')->setLabel(DashboardController::ACTION_MODIFIER);
        })
        //Sur la page Edit
        ->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN, function (Action $action) {
            return $action->setIcon('fa-solid fa-floppy-disk')->setLabel(DashboardController::ACTION_ENREGISTRER);//<i class="fa-solid fa-floppy-disk"></i>
        })
        ->update(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE, function (Action $action) {
            return $action->setIcon('fa-solid fa-floppy-disk')->setLabel(DashboardController::ACTION_ENREGISTRER_ET_CONTINUER);
        })
        //Sur la page NEW
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
        //Action terminer
        ->add(Crud::PAGE_DETAIL, $terminer)
        ->add(Crud::PAGE_EDIT, $terminer)
        ->add(Crud::PAGE_INDEX, $terminer)
        //Action terminer
        ->add(Crud::PAGE_DETAIL, $feedback)
        ->add(Crud::PAGE_EDIT, $feedback)
        ->add(Crud::PAGE_INDEX, $feedback)

        ->reorder(Crud::PAGE_INDEX, [DashboardController::ACTION_OPEN, DashboardController::ACTION_DUPLICATE])
        ->reorder(Crud::PAGE_EDIT, [DashboardController::ACTION_OPEN, DashboardController::ACTION_DUPLICATE]);
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


    public function ajouterFeedback(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        $entite = $context->getEntity()->getInstance();
        //parent::persistEntity($em, $entite);

        $url = $adminUrlGenerator
            ->setController(FeedbackCRMCrudController::class)
            ->setAction(Action::NEW)
            ->setEntityId(null)
            ->generateUrl();

        return $this->redirect($url);
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
}
