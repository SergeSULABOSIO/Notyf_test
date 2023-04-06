<?php

namespace App\Controller\Admin;

use App\Entity\Automobile;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class AutomobileCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Automobile::class;
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
