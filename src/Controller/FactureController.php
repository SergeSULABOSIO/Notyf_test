<?php

namespace App\Controller;


use App\Entity\Facture;
use App\Service\ServiceFacture;
use App\Service\ServicePdf;
use App\Service\ServiceIngredients;
use App\Service\ServicePreferences;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;


class FactureController extends AbstractController //AbstractDashboardController //AbstractController
{

    public function __construct(
        private ServiceFacture $serviceFacture,
        private ServiceIngredients $serviceIngredients,
        private ServicePreferences $servicePreferences,
        private AuthenticationUtils $authenticationUtils,
        private EntityManagerInterface $manager
    ) {
    }


    #[Route('/ouvrir/{id}', name: 'facture.ouvrir', methods: ['GET', 'POST'])]
    public function ouvrirFacture(?Facture $facture) {
        //dd($facture);
        //return new Response();
        return new Response();
    }
}
