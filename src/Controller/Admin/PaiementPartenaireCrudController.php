<?php

namespace App\Controller\Admin;

use App\Entity\PaiementPartenaire;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class PaiementPartenaireCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PaiementPartenaire::class;
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
