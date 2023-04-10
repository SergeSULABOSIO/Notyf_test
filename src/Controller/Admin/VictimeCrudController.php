<?php

namespace App\Controller\Admin;

use App\Entity\Victime;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class VictimeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Victime::class;
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
