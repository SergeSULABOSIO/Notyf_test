<?php

namespace App\Controller;

use function PHPSTORM_META\map;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/connexion', name: 'security.login', methods: ['GET', 'POST'])]
    public function index(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        if($error != null){
            $this->addFlash("error", "Vos identifiants sont incorrects");
        }
        return $this->render('security/login.html.twig', [
            'controller_name' => 'SecurityController',
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $error,
        ]);
    }

    #[Route('/deconnexion', name: 'security.logout', methods: ['GET', 'POST'])]
    public function logout()
    {
        // Rien à faire ici
    }
}
