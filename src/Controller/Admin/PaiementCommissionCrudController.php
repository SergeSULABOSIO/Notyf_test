<?php

namespace App\Controller\Admin;

use App\Entity\PaiementCommission;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class PaiementCommissionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PaiementCommission::class;
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
