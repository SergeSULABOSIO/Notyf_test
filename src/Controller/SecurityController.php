<?php

namespace App\Controller;

use Faker\Factory;
use App\Entity\Taxe;
use DateTimeImmutable;
use App\Entity\Monnaie;
use App\Entity\Produit;
use App\Entity\EtapeCrm;
use App\Entity\Entreprise;
use App\Entity\Utilisateur;
use App\Service\ServiceMails;
use App\Form\RegistrationType;

use App\Form\AdminRegistrationType;
use Doctrine\Persistence\ObjectManager;
use App\Form\EntrepriseRegistrationType;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\Admin\UtilisateurCrudController;
use App\Entity\DocCategorie;
use App\Entity\EtapeSinistre;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;


class SecurityController extends AbstractDashboardController//AbstractController
{

    public function __construct(private AuthenticationUtils $authenticationUtils, private EntityManagerInterface $manager)
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

            //Creation des ingrédients / objets de base
            $this->creerIngredients($utilisateur, $entreprise);
            //envoie de l'email de confirmation
            //$serviceMails->sendEmailBienvenu($utilisateur);

            return $this->redirectToRoute('admin');
        }

        return $this->render('security/registration.entreprise.html.twig', [//
            'form' => $form->createView(),
            'utilisateur' => $utilisateur
        ]);
    }

    public function creerIngredients(Utilisateur $utilisateur , Entreprise $entreprise)
    {
        $faker = Factory::create();

        $tabCodesMonnaies = array("USD", "CDF");
        $tabNomsTaxes = array("TVA", "ARCA");
        $tabEtapesCRM = array(
            "PROSPECTION", 
            "PRODUCTION DE COTAION",
            "EMISSION DE LA POLICE",
            "RENOUVELLEMENT"
        );
        $tabProduits = array(
            "VIE ET EPARGNE / LIFE", 
            "INCENDIE ET RISQUES DIVERS / ASSET / FAP",
            "RC AUTOMOBILE / MOTOR TPL",
            "TOUS RISQUES AUTOMOBILES / MOTOR COMP."
        );
        $tabEtapesSinistre = array(
            "OUVERTURE", 
            "COLLECTE DES DONNEES",
            "EVALUATION DES DEGATS",
            "INDEMNISATION ET / OU CLOTURE"
        );
        $tabBiblioCategorie = array(
            "POLICES D'ASSURANCES", 
            "FORMULAIRES DE PROPOSITION",
            "MANDATS DE COURTAGE",
            "FACTURES / NOTES DE DEBIT"
        );
        $tabBiblioClasseur = array(
            "PRODUCTION", 
            "SINISTRES"
        );

        //Construction des objets et persistance
        //MONNAIES
        $monnaieUSD = null;
        foreach ($tabCodesMonnaies as $codeMonnaie) {
            //Pour chaque element du tableau
            $monnaie = new Monnaie();
            if ($codeMonnaie == "CDF") {
                $monnaie->setNom("Franc");
                $monnaie->setTauxusd(1);
                $monnaie->setIslocale(true);
            } else {
                $monnaie->setNom("Dollars Américains");
                $monnaie->setTauxusd(2050);
                $monnaie->setIslocale(false);
                $monnaieUSD = $monnaie;
            }
            $monnaie->setCode($codeMonnaie);
            $monnaie->setEntreprise($entreprise);
            $monnaie->setCreatedAt(new \DateTimeImmutable());
            $monnaie->setUpdatedAt(new \DateTimeImmutable());
            $monnaie->setUtilisateur($utilisateur);

            //persistance
            $this->manager->persist($monnaie);
            //$this->manager->flush();
        }

        //TAXES
        foreach ($tabNomsTaxes as $nomTaxes) {
            //Pour chaque element du tableau
            $taxe = new Taxe();
            $taxe->setNom($nomTaxes);
            if ($nomTaxes == "TVA") {
                $taxe->setDescription("Taxe sur la Valeur Ajoutée");
                $taxe->setTaux(0.16);
                $taxe->setPayableparcourtier(false);
                $taxe->setOrganisation("DGI - Direction Générale des Impôts");
            } else {
                $taxe->setDescription("Frais de surveillance");
                $taxe->setTaux(0.02);
                $taxe->setPayableparcourtier(true);
                $taxe->setOrganisation("ARCA - Autorité de Régulation des Assurances");
            }
            $taxe->setEntreprise($entreprise);
            $taxe->setCreatedAt(new \DateTimeImmutable());
            $taxe->setUpdatedAt(new \DateTimeImmutable());
            $taxe->setUtilisateur($utilisateur);

            $this->manager->persist($taxe);
            //$this->manager->flush();
        }

        //ETAPE CRM
        foreach ($tabEtapesCRM as $nomEtape) {
            $etapeCRM = new EtapeCrm();
            $etapeCRM->setNom($nomEtape);
            $etapeCRM->setEntreprise($entreprise);
            $etapeCRM->setCreatedAt(new \DateTimeImmutable());
            $etapeCRM->setUpdatedAt(new \DateTimeImmutable());
            $etapeCRM->setUtilisateur($utilisateur);
            //persistance
            $this->manager->persist($etapeCRM);
            //$this->manager->flush();
        }

        //PRODUIT
        foreach ($tabProduits as $nomProduit) {
            $produit = new Produit();
            $produit->setNom($nomProduit);
            $produit->setCode("PRD" . $faker->randomNumber(5, true));
            $produit->setDescription($faker->sentence(5));
            $produit->setIsobligatoire(true);
            $produit->setTauxarca(0.10);
            $produit->setIsabonnement(false);
            $produit->setCategorie(0);
            $produit->setEntreprise($entreprise);
            $produit->setCreatedAt(new \DateTimeImmutable());
            $produit->setUpdatedAt(new \DateTimeImmutable());
            $produit->setUtilisateur($utilisateur);
            //persistance
            $this->manager->persist($etapeCRM);
            //$this->manager->flush();
        }

        //ETAPE SINISTRE
        $indice = 0;
        foreach ($tabEtapesSinistre as $nomEtape) {
            $etapeSinistre = new EtapeSinistre();
            $etapeSinistre->setNom($nomEtape);
            $etapeSinistre->setDescription($faker->sentence(10));
            $etapeSinistre->setIndice($indice);//$indice
            $etapeSinistre->setEntreprise($entreprise);
            $etapeSinistre->setCreatedAt(new \DateTimeImmutable());
            $etapeSinistre->setUpdatedAt(new \DateTimeImmutable());
            $etapeSinistre->setUtilisateur($utilisateur);
            $indice++;
            //persistance
            $this->manager->persist($etapeSinistre);
            //$this->manager->flush();
        }

        //CATEGORIE BIBBLIOTHEQUE
        foreach ($tabBiblioCategorie as $nomCategorie) {
            $categorieBib = new DocCategorie();
            $categorieBib->setNom($nomCategorie);
            $categorieBib->setEntreprise($entreprise);
            $categorieBib->setCreatedAt(new \DateTimeImmutable());
            $categorieBib->setUpdatedAt(new \DateTimeImmutable());
            $categorieBib->setUtilisateur($utilisateur);
            //persistance
            $this->manager->persist($categorieBib);
            //$this->manager->flush();
        }

        //CLASSEUR BIBBLIOTHEQUE
        foreach ($tabBiblioClasseur as $nomClasseur) {
            $classeurBib = new DocCategorie();
            $classeurBib->setNom($nomClasseur);
            $classeurBib->setEntreprise($entreprise);
            $classeurBib->setCreatedAt(new \DateTimeImmutable());
            $classeurBib->setUpdatedAt(new \DateTimeImmutable());
            $classeurBib->setUtilisateur($utilisateur);
            //persistance
            $this->manager->persist($classeurBib);
            //$this->manager->flush();
        }

        $this->manager->flush();
    }
}
