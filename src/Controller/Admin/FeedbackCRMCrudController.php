<?php

namespace App\Controller\Admin;

use App\Entity\FeedbackCRM;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class FeedbackCRMCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return FeedbackCRM::class;
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
