<?php

namespace App\Controller\Admin;

use App\Entity\EtapeCrm;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class EtapeCrmCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return EtapeCrm::class;
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
