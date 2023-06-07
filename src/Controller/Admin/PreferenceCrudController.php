<?php

namespace App\Controller\Admin;

use App\Entity\Preference;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class PreferenceCrudController extends AbstractCrudController
{
    public const PREF_APPARENCE_CLAIRE = 0;
    public const PREF_APPARENCE_SOMBRE = 1;

    public const TAB_APPARENCES = [
        self::PREF_APPARENCE_CLAIRE => 'Claire (par défaut)',
        self::PREF_APPARENCE_SOMBRE => 'Sombre'
    ];

    public static function getEntityFqcn(): string
    {
        return Preference::class;
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
