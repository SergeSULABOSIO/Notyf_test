<?php
namespace App\Twig\Components;

use App\Repository\PisteRepository;
use DateTimeImmutable;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;

#[AsLiveComponent]
class DateActuelle
{
    use DefaultActionTrait;
    
    public function __construct()
    {

    }

    public function getNow(): DateTimeImmutable
    {
        return new DateTimeImmutable("now");
    }
}