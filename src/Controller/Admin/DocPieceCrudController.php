<?php

namespace App\Controller\Admin;

use App\Entity\DocPiece;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use Symfony\Component\DomCrawler\Field\FileFormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class DocPieceCrudController extends AbstractCrudController
{
    public const ACTION_DUPLICATE = "Dupliquer";
    public const ACTION_OPEN = "Ouvrir";

    public const ARTICLE_BASE_PATH = 'uploads/documents';
    public const ARTICLE_UPLOAD_DIR = 'public/uploads/documents';

    public static function getEntityFqcn(): string
    {
        return DocPiece::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDateTimeFormat ('dd/MM/yyyy HH:mm:ss')
            ->setDateFormat ('dd/MM/yyyy')
            ->setPaginatorPageSize(30)
            ->renderContentMaximized()
            ->setEntityLabelInSingular("Pièce")
            ->setEntityLabelInPlural("Pièces")
            ->setPageTitle("index", "Liste des pièces")
            // ...
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        $duplicate = Action::new(self::ACTION_DUPLICATE)
            ->linkToCrudAction('dupliquerEntite');//->setCssClass("btn btn-warning");

        $ouvrir = Action::new(self::ACTION_OPEN)
            ->linkToCrudAction('ouvrirEntite');

        return $actions
        //Action ouvrir
        ->add(Crud::PAGE_EDIT, $ouvrir)
        ->add(Crud::PAGE_INDEX, $ouvrir)
        //action dupliquer Assureur
        ->add(Crud::PAGE_DETAIL, $duplicate)
        ->add(Crud::PAGE_EDIT, $duplicate)
        ->add(Crud::PAGE_INDEX, $duplicate)
        ->reorder(Crud::PAGE_INDEX, [self::ACTION_OPEN, self::ACTION_DUPLICATE])
        ->reorder(Crud::PAGE_EDIT, [self::ACTION_OPEN, self::ACTION_DUPLICATE]);
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


    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('categorie')
            ->add('classeur')
            ->add('utilisateur')
            
        ;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('nom', "Nom"),
            TextField::new('description', "Description"),
            AssociationField::new('categorie', "Catégorie"),
            AssociationField::new('classeur', "Classeur"),
            ImageField::new('fichierA', 'Fichier A')
                ->setBasePath(self::ARTICLE_BASE_PATH)
                ->setUploadDir(self::ARTICLE_UPLOAD_DIR)
                ->setSortable(false)
                ->hideOnIndex(),
            ImageField::new('fichierB', 'Fichier B')
                ->setBasePath(self::ARTICLE_BASE_PATH)
                ->setUploadDir(self::ARTICLE_UPLOAD_DIR)
                ->setSortable(false)
                ->hideOnIndex(),
            ImageField::new('fichierC', 'Fichier C')
                ->setBasePath(self::ARTICLE_BASE_PATH)
                ->setUploadDir(self::ARTICLE_UPLOAD_DIR)
                ->setSortable(false)
                ->hideOnIndex(),
            ImageField::new('fichierD', 'Fichier D')
                ->setBasePath(self::ARTICLE_BASE_PATH)
                ->setUploadDir(self::ARTICLE_UPLOAD_DIR)
                ->setSortable(false)
                ->hideOnIndex(),
            AssociationField::new('utilisateur', "Utiliateur")->hideOnIndex(),
            DateTimeField::new('createdAt', "Created At")->hideOnIndex(),
            DateTimeField::new('updatedAt', "Dernière modification"),
            AssociationField::new('entreprise', "Entreprise")->hideOnIndex()
        ];
    }
    
}
