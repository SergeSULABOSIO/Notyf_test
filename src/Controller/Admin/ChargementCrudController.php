<?php

namespace App\Controller\Admin;

use App\Entity\Chargement;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class ChargementCrudController extends AbstractCrudController
{

    //Type de revenu
    public const TYPE_PRIME_NETTE = "Prime nette";
    public const TYPE_ACCESSOIRES = "Frais accéssoires / admin.";
    public const TYPE_FRONTING = "Fronting";
    public const TYPE_FRAIS_DE_SURVEILLANCE_ARCA = "Frais de surveillance";
    public const TYPE_TVA = "Tva (taxe sur la valeur ajoutée)";
    public const TYPE_AUTRE = "Autre chargement";

    public const TAB_TYPE = [
        self::TYPE_PRIME_NETTE                  => 0,
        self::TYPE_ACCESSOIRES                  => 1,
        self::TYPE_FRONTING                     => 2,
        self::TYPE_TVA                          => 3,
        self::TYPE_FRAIS_DE_SURVEILLANCE_ARCA   => 4,
        self::TYPE_AUTRE                        => 5
    ];


    public static function getEntityFqcn(): string
    {
        return Chargement::class;
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
