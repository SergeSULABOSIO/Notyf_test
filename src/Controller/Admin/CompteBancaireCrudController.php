<?php

namespace App\Controller\Admin;

use App\Entity\CompteBancaire;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class CompteBancaireCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CompteBancaire::class;
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
