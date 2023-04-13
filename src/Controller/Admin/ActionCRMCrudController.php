<?php

namespace App\Controller\Admin;

use App\Entity\ActionCRM;
use App\Entity\FeedbackCRM;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class ActionCRMCrudController extends AbstractCrudController
{
    public const ACTION_DUPLICATE = "Dupliquer";
    public const ACTION_TERMINER = "Terminer";
    public const ACTION_FEEDBACK = "Ajouter un feedback";
    public const ACTION_OPEN = "Ouvrir";

    public static function getEntityFqcn(): string
    {
        return ActionCRM::class;
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
            // ...
        ;
    }


    
    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('mission', "Tâches"),
            BooleanField::new('clos', "Terminée"),
            AssociationField::new('piste', "Piste"),
            TextEditorField::new('objectif', "Objectif"),
            AssociationField::new('utilisateur', "Utilisateur"),
            AssociationField::new('attributedTo', "Attribuée à"),
            AssociationField::new('feedbacks', "Feedbacks"),
            DateTimeField::new('startedAt', "Date effet"),
            DateTimeField::new('endedAt', "Echéance"),
            AssociationField::new('entreprise', "Entreprise")->hideOnIndex(),
            DateTimeField::new('createdAt', "Created At")->hideOnIndex(),
            DateTimeField::new('updatedAt', "Dernière modification")
        ];
    }
    

    public function configureActions(Actions $actions): Actions
    {
        $duplicate = Action::new(self::ACTION_DUPLICATE)
            ->linkToCrudAction('dupliquerEntite');//->setCssClass("btn btn-warning");

        $ouvrir = Action::new(self::ACTION_OPEN)
            ->linkToCrudAction('ouvrirEntite');

        $feedback = Action::new(self::ACTION_FEEDBACK)
            ->linkToCrudAction('ajouterFeedback');

        $terminer = Action::new(self::ACTION_TERMINER)
            ->linkToCrudAction('terminerAction');

        return $actions
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

        ->reorder(Crud::PAGE_INDEX, [self::ACTION_OPEN, self::ACTION_DUPLICATE])
        ->reorder(Crud::PAGE_EDIT, [self::ACTION_OPEN, self::ACTION_DUPLICATE]);
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

        return $this->redirect($url);
    }
    
    public function dupliquerEntite(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        /**@var Assureur $assureur */
        $entite = $context->getEntity()->getInstance();
        $entiteDuplique = clone $entite;
        $this->parent::persistEntity($em, $entiteDuplique);

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
