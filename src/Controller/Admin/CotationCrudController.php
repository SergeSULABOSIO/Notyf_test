<?php

namespace App\Controller\Admin;

use App\Entity\Cotation;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class CotationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Cotation::class;
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
