<?php

namespace App\Controller\Admin;

use App\Entity\DocCategorie;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class DocCategorieCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return DocCategorie::class;
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
