<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class Section
{
    public string $nom = "Section";
    public int $type;
    public const TYPE_DE_HAUT = 0;
    public const TYPE_DU_CENTRE = 1;
    public const TYPE_DU_BAS = 2;

    public function __construct() {}

    public function getDescriptionType(): string
    {
        switch ($this->type) {
            case self::TYPE_DE_HAUT:
                return "Section du haut de la page";
            case self::TYPE_DU_CENTRE:
                return "Section du centre de la page";
            case self::TYPE_DU_BAS:
                return "Section du base de la page";
        }
    }
}
