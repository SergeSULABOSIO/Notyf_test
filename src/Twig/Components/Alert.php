<?php
namespace App\Twig\Components;

use App\Repository\PisteRepository;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class Alert
{
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
}