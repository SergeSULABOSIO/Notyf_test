<?php
namespace App\Twig\Components;

use App\Repository\PisteRepository;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsLiveComponent]
class Alert
{
    use DefaultActionTrait;
    
    public function __construct(
        private PisteRepository $pisteRepository
    )
    {
        
    }
    public string $type = "success";
    public string $titre;
    public string $message;

    public function getPistes():array
    {
        // dd($this->pisteRepository->findAll());
        return $this->pisteRepository->findAll();
    }

    public function getNbPistes():int
    {
        return count($this->pisteRepository->findAll());
    }

    public function getRandomNumber(): int
    {
        return rand(0, 1000);
    }
}