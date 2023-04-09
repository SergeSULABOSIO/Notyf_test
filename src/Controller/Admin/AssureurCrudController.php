<?php

namespace App\Controller\Admin;

use App\Entity\Assureur;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class AssureurCrudController extends AbstractCrudController
{
    public const ACTION_DUPLICATE = "Dupliquer";
    public const ACTION_OPEN = "Ouvrir";
    
    public static function getEntityFqcn(): string
    {
        return Assureur::class;
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
            // ...
        ;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            //IdField::new('id'),
            TextField::new('nom', 'Nom'),
            TextField::new('adresse', 'Adresse physique'),
            TelephoneField::new('telephone', 'Téléphone'),
            EmailField::new('email', 'E-mail'),
            BooleanField::new('isreassureur', 'Réassureur'),
            UrlField::new('siteweb', 'Site Internet'),
            TextField::new('rccm', 'RCCM')->hideOnIndex(),
            TextField::new('idnat', 'Id. Nationale')->hideOnIndex(),
            TextField::new('licence', 'N° Licence'),
            TextField::new('numimpot', 'N° Impôt')->hideOnIndex(),
            DateTimeField::new('updated_at', 'Last update')->hideOnform(),
            AssociationField::new('entreprise', 'Entreprise')->hideOnindex()
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $duplicate = Action::new(self::ACTION_DUPLICATE)
            ->linkToCrudAction('dupliquerAssureur');//->setCssClass("btn btn-warning");

        $ouvrir = Action::new(self::ACTION_OPEN)
            ->linkToCrudAction('ouvrirAssureur');

        return $actions
        //Action ouvrir Assureur
        //->add(Crud::PAGE_DETAIL, $ouvrir)
        ->add(Crud::PAGE_EDIT, $ouvrir)
        ->add(Crud::PAGE_INDEX, $ouvrir)
        //action dupliquer Assureur
        ->add(Crud::PAGE_DETAIL, $duplicate)
        ->add(Crud::PAGE_EDIT, $duplicate)
        ->add(Crud::PAGE_INDEX, $duplicate)
        ->reorder(Crud::PAGE_INDEX, [self::ACTION_OPEN, self::ACTION_DUPLICATE])
        ->reorder(Crud::PAGE_EDIT, [self::ACTION_OPEN, self::ACTION_DUPLICATE]);
    }

    public function dupliquerAssureur(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
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

    public function ouvrirAssureur(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
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
