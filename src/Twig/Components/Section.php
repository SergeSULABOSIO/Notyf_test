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
}
