<?php

namespace App\Controller\Admin;

use App\Entity\Assureur;
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
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class AssureurCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Assureur::class;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('isreassureur')
        ;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDateTimeFormat ('dd/MM/yyyy HH:mm:ss')
            ->setDateFormat ('dd/MM/yyyy')
            ->setPaginatorPageSize(30)
            ->renderContentMaximized()
            ->setEntityLabelInSingular("Assureur")
            ->setEntityLabelInPlural("Assureurs")
            ->setPageTitle("index", "Liste d'assureurs")
            ->setDefaultSort(['updatedAt' => 'DESC'])
            // ...
        ;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addPanel('Informations générales')
            ->setIcon('fas fa-umbrella') //<i class="fa-sharp fa-solid fa-address-book"></i>
            ->setHelp("Le preneur des risques en contre partie du versement d'une prime d'assurance et selon les condtions bien spécifiées dans la police."),

            //Ligne 01
            TextField::new('nom', 'Nom')->setColumns(6),
            TextField::new('adresse', 'Adresse physique')->setColumns(6),

            //Ligne 02
            TelephoneField::new('telephone', 'Téléphone')->setColumns(6),
            EmailField::new('email', 'E-mail')->setColumns(6),

            //Ligne 03
            ChoiceField::new('isreassureur', 'Réassureur?')->setColumns(6)
            ->setChoices([
                'Oui' => 1,
                'Non' => 0
            ]),
            UrlField::new('siteweb', 'Site Internet')->setColumns(6),

            //Ligne 04
            TextField::new('rccm', 'RCCM')->hideOnIndex()->setColumns(6),
            TextField::new('idnat', 'Id. Nationale')->hideOnIndex()->setColumns(6),

            //Ligne 05
            TextField::new('licence', 'N° Licence')->setColumns(6),
            TextField::new('numimpot', 'N° Impôt')->hideOnIndex()->setColumns(6),

            //Ligne 06
            DateTimeField::new('updatedAt', 'Dernière modification')->hideOnform()->setColumns(6),
            AssociationField::new('entreprise', 'Entreprise')->hideOnindex()->setColumns(6)
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
            ->setIcon('fa-solid fa-file-excel');//<i class="fa-solid fa-file-excel"></i>

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
            return $action->setIcon('fas fa-umbrella')->setCssClass('btn btn-primary')->setLabel(DashboardController::ACTION_AJOUTER);
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

        //->add(Crud::PAGE_DETAIL, $ouvrir)
        ->add(Crud::PAGE_EDIT, $ouvrir)
        ->add(Crud::PAGE_INDEX, $ouvrir)
        //action dupliquer Assureur
        ->add(Crud::PAGE_DETAIL, $duplicate)
        ->add(Crud::PAGE_EDIT, $duplicate)
        ->add(Crud::PAGE_INDEX, $duplicate)
        //Reorganisation des boutons
        ->reorder(Crud::PAGE_INDEX, [DashboardController::ACTION_OPEN, DashboardController::ACTION_DUPLICATE])
        ->reorder(Crud::PAGE_EDIT, [DashboardController::ACTION_OPEN, DashboardController::ACTION_DUPLICATE]);
    }

    public function dupliquerEntite(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        /**@var Assureur $assureur */
        $assureur = $context->getEntity()->getInstance();
        $assureurDuplique = clone $assureur;
        parent::persistEntity($em, $assureurDuplique);

        $url = $adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::DETAIL)
            ->setEntityId($assureurDuplique->getId())
            ->generateUrl();

        return $this->redirect($url);
    }

    public function ouvrirEntite(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        /**@var Assureur $assureur */
        $assureur = $context->getEntity()->getInstance();
        
        $url = $adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::DETAIL)
            ->setEntityId($assureur->getId())
            ->generateUrl();

        return $this->redirect($url);
    }
   
}
