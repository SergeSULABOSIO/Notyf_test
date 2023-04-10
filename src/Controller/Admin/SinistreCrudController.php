<?php

namespace App\Controller\Admin;

use App\Entity\Sinistre;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class SinistreCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Sinistre::class;
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
