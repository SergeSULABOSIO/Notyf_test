<?php

namespace App\Controller\Admin;

use App\Entity\Article;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class ArticleCrudController extends AbstractCrudController
{
    public const ARTICLE_BASE_PATH = 'uploads/images/articles';
    public const ARTICLE_UPLOAD_DIR = 'public/uploads/images/articles';

    public static function getEntityFqcn(): string
    {
        return Article::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDateTimeFormat ('dd/MM/yyyy HH:mm:ss')
            ->setDateFormat ('dd/MM/yyyy')
            ->setPaginatorPageSize(30)
            ->renderContentMaximized()
            ->setEntityLabelInSingular("Article")
            ->setEntityLabelInPlural("Articles")
            ->setPageTitle("index", "Liste d'articles")
            ->addFormTheme('@FOSCKEditor/Form/ckeditor_widget.html.twig')
            // ...
        ;
    }


    
    public function configureFields(string $pageName): iterable
    {
        return [
            //IdField::new('id'),
            ImageField::new('image', 'Image')
                ->setBasePath(self::ARTICLE_BASE_PATH)
                ->setUploadDir(self::ARTICLE_UPLOAD_DIR)
                ->setSortable(false),
            TextField::new('code', 'Code'),
            TextField::new('nom', 'Nom'),
            MoneyField::new('prix', 'Prix')
                ->setCurrency('USD'),
            TextEditorField::new('description', 'Descsription')
                ->setFormType(CKEditorType::class),
            DateTimeField::new('updated_at', 'Last update')
            ->hideOnform()
        ];
    }
   
}
