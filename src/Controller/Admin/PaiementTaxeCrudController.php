<?php

namespace App\Controller\Admin;

use App\Entity\PaiementTaxe;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class PaiementTaxeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PaiementTaxe::class;
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
