<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SweetAlertController extends AbstractController
{
    #[Route('/', name: 'app_sweet_alert')]
    public function index(): Response
    {
        
        return $this->render('sweet_alert/index.html.twig', [
            'controller_name' => 'SweetAlertController',
        ]);
    }
}
