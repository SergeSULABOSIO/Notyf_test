<?php

namespace App\Controller;

use DateTimeImmutable;
use App\Entity\Entreprise;

use App\Entity\Utilisateur;
use App\Service\ServiceMails;
use App\Form\RegistrationType;
use App\Form\AdminRegistrationType;
use App\Form\EntrepriseRegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Controller\Admin\UtilisateurCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;


class SecurityController extends AbstractDashboardController//AbstractController
{

    public function __construct(private AuthenticationUtils $authenticationUtils)
    {
        
    }


    #[Route('/connexion', name: 'security.login', methods: ['GET', 'POST'])]
    public function index(): Response
    {
        $last_username = $this->authenticationUtils->getLastUsername();

        $error = $this->authenticationUtils->getLastAuthenticationError();

        if ($error != null) {
            $this->addFlash("error", "Vos identifiants sont incorrects");
        }
        return $this->render('security/login.html.twig', [
            'controller_name' => 'SecurityController',
            'last_username' => $last_username,
            'error' => $error,
        ]);
    }

    #[Route('/deconnexion', name: 'security.logout', methods: ['GET', 'POST'])]
    public function logout()
    {
        // Rien à faire ici
    }


    #[Route('/inscription_admin', name: 'security.register.admin', methods: ['GET', 'POST'])]
    public function registration(ServiceMails $serviceMails, Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $hasher): Response
    {
        $user = new Utilisateur();
        $form = $this->createForm(AdminRegistrationType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $user->setRoles([
                //Accès aux fonctionnalités
                UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACCES_COMMERCIAL],
                UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACCES_PRODUCTION],
                UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACCES_FINANCES],
                UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACCES_SINISTRES],
                UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACCES_BIBLIOTHE],
                //Pouvoeir d'action
                UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION],
                //Visibilité
                UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE]    
            ]);
            $user->setUpdatedAt(new DateTimeImmutable());
            $user->setCreatedAt(new DateTimeImmutable());
            $hashedPassword = $hasher->hashPassword($user, $user->getPlainPassword());
            $user->setPassword($hashedPassword);

            $manager->persist($user);
            $manager->flush();

            //$this->addFlash("success", "Félicitation " . $user->getNom() . ", votre comptre vient d'être créé avec succès!");

            //envoie de l'email de confirmation
            $serviceMails->sendEmailBienvenu($user);


            //On doit créer ici les ingrédients du compte



            return $this->redirectToRoute('security.login');
        }

        return $this->render('security/registration.admin.html.twig', [
            'form' => $form->createView()
        ]);
    }


    #[Route('/inscription_entreprise', name: 'security.register.entreprise', methods: ['GET', 'POST'])]
    public function registration_admin(Security $security, ServiceMails $serviceMails, Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $hasher): Response
    {
        $entreprise = new Entreprise();
        $utilisateur = $security->getUser();

        //dd($utilisateur);

        $form = $this->createForm(EntrepriseRegistrationType::class, $entreprise);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entreprise = $form->getData();
            $entreprise->setCreatedAt(new DateTimeImmutable());
            $entreprise->setUpdatedAt(new DateTimeImmutable()); //$user_admin->
            $entreprise->setUtilisateur($utilisateur);
            $manager->persist($entreprise);
            $manager->flush();

            $this->addFlash("success", "Félicitation " . $utilisateur->getNom() . ", ". $entreprise->getNom() ." vient d'être créée avec succès! Vous pouvez maintenant travailler.");

            //envoie de l'email de confirmation
            //$serviceMails->sendEmailBienvenu($utilisateur);

            return $this->redirectToRoute('admin');
        }

        return $this->render('security/registration.entreprise.html.twig', [//
            'form' => $form->createView(),
            'utilisateur' => $utilisateur
        ]);
    }
}
