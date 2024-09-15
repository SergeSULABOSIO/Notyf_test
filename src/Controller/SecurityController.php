<?php

namespace App\Controller;


use DateTimeImmutable;
use App\Entity\Entreprise;
use App\Entity\Utilisateur;
use App\Service\ServiceMails;
use App\Form\AdminDestructionType;
use App\Form\AdminRegistrationType;
use App\Service\ServiceSuppression;
use App\Form\EntrepriseRegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\Admin\UtilisateurCrudController;
use App\Service\ServiceIngredients;
use App\Service\ServicePreferences;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;


class SecurityController extends AbstractDashboardController //AbstractController
{

    public function __construct(
        private ServiceIngredients $serviceIngredients,
        private ServicePreferences $servicePreferences,
        private AuthenticationUtils $authenticationUtils,
        private EntityManagerInterface $manager
    ) {
    }


    #[Route('/destruction/{idEntreprise}/{idUtilisateur}', name: 'security.destroy', methods: ['GET', 'POST'])]
    public function destruction(
        $idEntreprise,
        $idUtilisateur,
        ServiceSuppression $serviceSuppression,
        ServiceMails $serviceMails,
        Request $request,
        UserPasswordHasherInterface $hasher
    ): Response {
        $entreprise = $this->manager->getRepository(Entreprise::class)->find($idEntreprise);
        $utilisateur = $this->manager->getRepository(Utilisateur::class)->find($idUtilisateur);
        $messageErreur = "";

        $form = $this->createForm(AdminDestructionType::class, new Utilisateur());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            if ($hasher->isPasswordValid($utilisateur, $data->getPlainPassword())) {
                //dd("Mot de passe CORRECT ! Je supprime...");
                $serviceSuppression->supprimer($entreprise, ServiceSuppression::PAREMETRE_ENTREPRISE);

                //envoie de l'email de confirmation de suppression effectuée.
                //$serviceMails->sendEmailBienvenu($user);
                return $this->redirectToRoute('security.logout');
            } else {
                $messageErreur = "Le mot de passe saisi n'est pas correct. Impossible de détruire ce compte !";
            }
        }

        return $this->render('security/destruction.html.twig', [
            'form' => $form->createView(),
            'utilisateur' => $utilisateur,
            'entreprise' => $entreprise,
            'messageErreur' => $messageErreur
        ]);
    }



    #[Route('/connexion', name: 'security.login', methods: ['GET', 'POST'])]
    public function index(): Response
    {
        $last_username = $this->authenticationUtils->getLastUsername();

        $error = $this->authenticationUtils->getLastAuthenticationError();
        
        //dd($error);
        /* if ($error != null) {
            $this->addFlash("error", "Vos identifiants sont incorrects");
        } */
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
                UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACCES_PARAMETRES],
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

            return $this->redirectToRoute('security.login');
        }

        return $this->render('security/registration.admin.html.twig', [
            'form' => $form->createView()
        ]);
    }


    #[Route('/inscription_entreprise', name: 'security.register.entreprise', methods: ['GET', 'POST'])]
    public function registration_admin(Security $security, ServiceMails $serviceMails, Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $hasher): Response
    {
        /** @var Entreprise */
        $entreprise = new Entreprise();

        /** @var Utilisateur */
        $utilisateur = new Utilisateur();
        
        /** @var Utilisateur */
        $utilisateur = $security->getUser();

        //dd($utilisateur);

        $form = $this->createForm(EntrepriseRegistrationType::class, $entreprise);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entreprise = $form->getData();
            $entreprise->setCreatedAt(new DateTimeImmutable());
            $entreprise->setUpdatedAt(new DateTimeImmutable()); //$user_admin->
            $entreprise->setUtilisateur($utilisateur);

            //on persiste l'entreprise
            $manager->persist($entreprise);

            $utilisateur->setEntreprise($entreprise);
            $utilisateur->setUtilisateur($utilisateur);
            
            $manager->persist($utilisateur);

            $manager->flush();

            $this->addFlash("success", "Félicitation " . $utilisateur->getNom() . ", " . $entreprise->getNom() . " vient d'être créée avec succès! Vous pouvez maintenant travailler.");

            //Creation des ingrédients / objets de base
            $this->serviceIngredients->creerIngredients($utilisateur, $entreprise);
            //envoie de l'email de confirmation
            //$serviceMails->sendEmailBienvenu($utilisateur);
            return $this->redirectToRoute('admin');
        }

        return $this->render('security/registration.entreprise.html.twig', [ //
            'form' => $form->createView(),
            'utilisateur' => $utilisateur
        ]);
    }
}
