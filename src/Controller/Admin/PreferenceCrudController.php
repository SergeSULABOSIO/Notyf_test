<?php

namespace App\Controller\Admin;

use App\Entity\Preference;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class PreferenceCrudController extends AbstractCrudController
{
    //CRM
    public const PREF_CRM_MISSION_ID = 0;
    public const PREF_CRM_MISSION_MISSION = 1;
    public const PREF_CRM_MISSION_OBJECTIF = 2;
    public const PREF_CRM_MISSION_STARTED_AT = 3;
    public const PREF_CRM_MISSION_ENDED_AT = 4;
    public const PREF_CRM_MISSION_UTILISATEUR = 5;
    public const PREF_CRM_MISSION_ENTREPRISE = 6;
    public const PREF_CRM_MISSION_CREATED_AT = 7;
    public const PREF_CRM_MISSION_UPDATED_AT = 8;
    
    public const TAB_CRM_MISSION = [
        self::PREF_CRM_MISSION_ID => "Identifiant",
        self::PREF_CRM_MISSION_MISSION => 'Nom',
        self::PREF_CRM_MISSION_OBJECTIF => "Objectif",
        self::PREF_CRM_MISSION_STARTED_AT => 'Date de début',
        self::PREF_CRM_MISSION_ENDED_AT => 'Echéance',
        self::PREF_CRM_MISSION_UTILISATEUR => 'Utilisateur',
        self::PREF_CRM_MISSION_ENTREPRISE => 'Entreprise',
        self::PREF_CRM_MISSION_CREATED_AT => 'Date de création',
        self::PREF_CRM_MISSION_UPDATED_AT => 'Date de modification'
    ];

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
