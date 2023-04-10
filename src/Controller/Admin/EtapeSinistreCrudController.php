<?php

namespace App\Controller\Admin;

use App\Entity\EtapeSinistre;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class EtapeSinistreCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return EtapeSinistre::class;
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
