<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class NotyfController extends AbstractController
{
    #[Route('/notyf/{notyf}', name: 'app_notyf')]
    //#[IsGranted('ROLE_USER')]
    public function index(?string $notyf = null): Response
    {
        if ($notyf != null) {
            $this->addFlash($notyf, "Ceci est un message " . $notyf);
        }
        return $this->render('notyf/index.html.twig', [
            'notyf' => $notyf,
        ]);
    }
}
