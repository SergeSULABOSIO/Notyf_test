<?php

namespace App\Controller\Admin;

use App\Entity\Expert;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class ExpertCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Expert::class;
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
