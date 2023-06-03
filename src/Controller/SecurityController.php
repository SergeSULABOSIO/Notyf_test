<?php

namespace App\Controller;

use Faker\Factory;
use App\Entity\Taxe;
use DateTimeImmutable;
use App\Entity\Monnaie;
use App\Entity\Produit;
use App\Entity\EtapeCrm;
use App\Entity\Entreprise;
use App\Entity\DocClasseur;
use App\Entity\Utilisateur;
use App\Entity\DocCategorie;

use App\Entity\EtapeSinistre;
use App\Service\ServiceMails;
use App\Form\RegistrationType;

use App\Service\ServiceEntreprise;
use App\Form\AdminRegistrationType;
use App\Service\ServiceSuppression;
use Doctrine\Persistence\ObjectManager;
use App\Form\EntrepriseRegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\Admin\UtilisateurCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;


class SecurityController extends AbstractDashboardController //AbstractController
{

    public function __construct(private AuthenticationUtils $authenticationUtils, private EntityManagerInterface $manager)
    {
    }


    #[Route('/destruction/{idEntreprise}', name: 'security.destroy', methods: ['GET', 'POST'])]
    public function destruction($idEntreprise, ServiceSuppression $serviceSuppression): Response
    {
        $entreprise = $this->manager->getRepository(Entreprise::class)->find($idEntreprise);
        //dd("Utilisateur = " . $idUtilisateur . " et Entreprise = " . $idEntreprise);
        $serviceSuppression->supprimer($entreprise, ServiceSuppression::PAREMETRE_ENTREPRISE);
        //dd("Oops!");
        return $this->redirectToRoute('security.logout'); //app_sweet_alert
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

            //on persiste l'entreprise
            $manager->persist($entreprise);

            //On modifie et persiste aussi l'objet Utilisateur
            $utilisateur->setEntreprise($entreprise);
            $utilisateur->setUtilisateur($utilisateur);
            $manager->persist($utilisateur);

            $manager->flush();

            $this->addFlash("success", "Félicitation " . $utilisateur->getNom() . ", " . $entreprise->getNom() . " vient d'être créée avec succès! Vous pouvez maintenant travailler.");

            //Creation des ingrédients / objets de base
            $this->creerIngredients($utilisateur, $entreprise);
            //envoie de l'email de confirmation
            //$serviceMails->sendEmailBienvenu($utilisateur);

            return $this->redirectToRoute('admin');
        }

        return $this->render('security/registration.entreprise.html.twig', [ //
            'form' => $form->createView(),
            'utilisateur' => $utilisateur
        ]);
    }

    public function creerIngredients(Utilisateur $utilisateur, Entreprise $entreprise)
    {
        $faker = Factory::create();

        $taMonnaies = [
            [
                "setCode" => "USD",
                "setNom" => "Dollar Américain",
                "setTauxusd" => 1,
                "setIslocale" => true
            ],
            [
                "setCode" => "EUR",
                "setNom" => "Euro",
                "setTauxusd" => 1.09,
                "setIslocale" => false
            ]
        ]; //array("USD", "CDF");

        $tabTaxes = [
            [
                "setNom" => "TVA",
                "setDescription" => "Taxe sur la Valeur Ajoutée",
                "setTaux" => 0.16,
                "setPayableparcourtier" => false,
                "setOrganisation" => "DGI - Direction Générale des Impôts."
            ],
            [
                "setNom" => "ARCA",
                "setDescription" => "Frais de surveillance",
                "setTaux" => 0.02,
                "setPayableparcourtier" => true,
                "setOrganisation" => "ARCA - Autorité de Régulation et de Contrôle des Assurances."
            ]
        ]; //array("TVA", "ARCA");

        $tabEtapesCRM = [
            "PROSPECTION",
            "PRODUCTION DE COTATION",
            "EMISSION DE LA POLICE",
            "RENOUVELLEMENT"
        ];

        $tabProduits = [
            [
                "setNom" => "VIE ET EPARGNE / LIFE",
                "setCode" => "VIE",
                "setDescription" => "L'assurance vie est un contrat par lequel l'assureur s'engage, en contrepartie du paiement de primes, à verser une rente ou un capital à l'assuré ou à ses bénéficiaires.",
                "setIsobligatoire" => false,
                "setTauxarca" => 0.10,
                "setIsabonnement" => false,
                "setCategorie" => 1
            ],
            [
                "setNom" => "INCENDIE ET RISQUES DIVERS / ASSET / FAP",
                "setCode" => "IMR",
                "setDescription" => "Une assurance incendie est avant tout une {assurance de choses}. Ce qui veut dire qu'elle indemnise les dommages causés à vos biens matériels, plus particulièrement à l'habitation et son contenu. Mais elle couvre dans certaines circonstances également votre responsabilité civile à l'égard d'autrui.",
                "setIsobligatoire" => true,
                "setTauxarca" => 0.10,
                "setIsabonnement" => false,
                "setCategorie" => 0
            ],
            [
                "setNom" => "RC AUTOMOBILE / MOTOR TPL",
                "setCode" => "RCA",
                "setDescription" => "La garantie responsabilité civile de votre assurance automobile couvre les dommages causés aux tiers par vous ou par les personnes vivant avec vous (enfants, concubin, époux....).",
                "setIsobligatoire" => true,
                "setTauxarca" => 0.10,
                "setIsabonnement" => false,
                "setCategorie" => 0
            ],
            [
                "setNom" => "TOUS RISQUES AUTOMOBILES / MOTOR COMP.",
                "setCode" => "TRA",
                "setDescription" => "La garantie tous risques vous permet d'être indemnisé pour tous les dommages subis par votre véhicule, quel que soit le type d'accident et quelle que soit votre responsabilité en tant que conducteur.",
                "setIsobligatoire" => false,
                "setTauxarca" => 0.15,
                "setIsabonnement" => false,
                "setCategorie" => 0
            ]
        ];

        $tabSinistres = [
            [
                "setNom" => "OUVERTURE",
                "setDescription" => "La toute prémière étape où l'assureur est notifié de la survénance d'un probable sinistre. La déclaration doit se faire dans le délais contractuel.",
                "setIndice" => 0
            ],
            [
                "setNom" => "COLLECTE DES DONNEES",
                "setDescription" => "L'assureur (ou l'espert désigné par celui-ci) éffectue la collecte d'information permettant de mieux comprendre les circonstances de l'incident et de quantifier les dégâts.",
                "setIndice" => 1
            ],
            [
                "setNom" => "EVALUATION DES DEGATS",
                "setDescription" => "Analyse de données, évaluation et détermination de la somme compensatoire éventuelle à verser à la victime.",
                "setIndice" => 2
            ],
            [
                "setNom" => "INDEMNISATION ET / OU CLOTURE",
                "setDescription" => "En cas de sinistre approvée conformément à la police, l'assuereur effectue le règlement compensatoire et clos le dossier. Au cas contraire, l'assureur informe à l'assuré les raisons du rejet et clos tout de même le dossier.",
                "setIndice" => 3
            ]
        ];

        $tabBiblioCategorie = [
            "POLICES D'ASSURANCE",
            "FORMULAIRES DE PROPOSITION",
            "MANDATS DE COURTAGE",
            "FACTURES"
        ];

        $tabBiblioClasseur = [
            "PRODUCTION",
            "SINISTRES",
            "FINANCES"
        ];

        //Construction des objets et persistance
        //MONNAIES
        foreach ($taMonnaies as $O_monnaie) {
            $monnaie = new Monnaie();
            $monnaie->setCode($O_monnaie['setCode']);
            $monnaie->setNom($O_monnaie['setNom']);
            $monnaie->setTauxusd($O_monnaie['setTauxusd']);
            $monnaie->setIslocale($O_monnaie['setIslocale']);

            $monnaie->setEntreprise($entreprise);
            $monnaie->setCreatedAt(new \DateTimeImmutable());
            $monnaie->setUpdatedAt(new \DateTimeImmutable());
            $monnaie->setUtilisateur($utilisateur);
            //persistance
            $this->manager->persist($monnaie);
        }

        //TAXES
        foreach ($tabTaxes as $O_taxes) {
            $taxe = new Taxe();
            $taxe->setNom($O_taxes['setNom']);
            $taxe->setDescription($O_taxes['setDescription']);
            $taxe->setTaux($O_taxes['setTaux']);
            $taxe->setPayableparcourtier($O_taxes['setPayableparcourtier']);
            $taxe->setOrganisation($O_taxes['setOrganisation']);

            $taxe->setEntreprise($entreprise);
            $taxe->setCreatedAt(new \DateTimeImmutable());
            $taxe->setUpdatedAt(new \DateTimeImmutable());
            $taxe->setUtilisateur($utilisateur);

            $this->manager->persist($taxe);
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
        }

        //PRODUIT
        foreach ($tabProduits as $O_produit) {
            $produit = new Produit();
            $produit->setNom($O_produit['setNom']);
            $produit->setCode($O_produit['setCode']);
            $produit->setDescription($O_produit['setDescription']);
            $produit->setIsobligatoire($O_produit['setIsobligatoire']);
            $produit->setTauxarca($O_produit['setTauxarca']);
            $produit->setIsabonnement($O_produit['setIsabonnement']);
            $produit->setCategorie($O_produit['setCategorie']);

            $produit->setEntreprise($entreprise);
            $produit->setCreatedAt(new \DateTimeImmutable());
            $produit->setUpdatedAt(new \DateTimeImmutable());
            $produit->setUtilisateur($utilisateur);
            //persistance
            $this->manager->persist($produit);
            //$this->manager->flush();
        }

        //ETAPE SINISTRE
        foreach ($tabSinistres as $O_etape) {
            $etapeSinistre = new EtapeSinistre();
            $etapeSinistre->setNom($O_etape['setNom']);
            $etapeSinistre->setDescription($O_etape['setDescription']);
            $etapeSinistre->setIndice($O_etape['setIndice']); //$indice

            $etapeSinistre->setEntreprise($entreprise);
            $etapeSinistre->setCreatedAt(new \DateTimeImmutable());
            $etapeSinistre->setUpdatedAt(new \DateTimeImmutable());
            $etapeSinistre->setUtilisateur($utilisateur);
            //persistance
            $this->manager->persist($etapeSinistre);
        }

        //CATEGORIE BIBBLIOTHEQUE
        foreach ($tabBiblioCategorie as $O_categorie) {
            $categorieBib = new DocCategorie();
            $categorieBib->setNom($O_categorie);

            $categorieBib->setEntreprise($entreprise);
            $categorieBib->setCreatedAt(new \DateTimeImmutable());
            $categorieBib->setUpdatedAt(new \DateTimeImmutable());
            $categorieBib->setUtilisateur($utilisateur);
            //persistance
            $this->manager->persist($categorieBib);
        }

        //CLASSEUR BIBBLIOTHEQUE
        foreach ($tabBiblioClasseur as $O_classeur) {
            $classeurBib = new DocClasseur();
            $classeurBib->setNom($O_classeur);

            $classeurBib->setEntreprise($entreprise);
            $classeurBib->setCreatedAt(new \DateTimeImmutable());
            $classeurBib->setUpdatedAt(new \DateTimeImmutable());
            $classeurBib->setUtilisateur($utilisateur);
            //persistance
            $this->manager->persist($classeurBib);
        }

        $this->manager->flush();
    }
}
