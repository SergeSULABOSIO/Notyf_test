<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SweetAlertController extends AbstractController
{
    #[Route('/', name: 'app_sweet_alert')]
    //#[IsGranted('ROLE_USER')]
    public function index(): Response
    {
        
        return $this->render('sweet_alert/index.html.twig', [
            'controller_name' => 'SweetAlertController',
        ]);
    }
}
