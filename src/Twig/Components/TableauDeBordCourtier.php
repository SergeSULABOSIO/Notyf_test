<?php
namespace App\Twig\Components;

use DateTimeImmutable;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class TableauDeBordCourtier
{
    public function __construct()
    {

    }

    public function getNow(): DateTimeImmutable
    {
        return new DateTimeImmutable("now");
    }
}