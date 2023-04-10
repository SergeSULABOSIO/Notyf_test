<?php

namespace App\Controller\Admin;

use App\Entity\DocPiece;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class DocPieceCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return DocPiece::class;
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
