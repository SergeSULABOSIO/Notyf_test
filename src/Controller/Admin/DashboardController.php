<?php

namespace App\Controller\Admin;

use App\Entity\Taxe;
use App\Entity\Piste;
use App\Entity\Client;
use App\Entity\Expert;
use App\Entity\Police;
use App\Entity\Article;
use App\Entity\Contact;
use App\Entity\Monnaie;
use App\Entity\Produit;
use App\Entity\Victime;
use App\Entity\Assureur;
use App\Entity\Cotation;
use App\Entity\DocPiece;
use App\Entity\EtapeCrm;
use App\Entity\Sinistre;
use App\Entity\ActionCRM;
use App\Entity\Automobile;
use App\Entity\Entreprise;
use App\Entity\Partenaire;
use App\Entity\DocClasseur;
use App\Entity\EntreeStock;
use App\Entity\FeedbackCRM;
use App\Entity\Utilisateur;
use App\Entity\DocCategorie;
use App\Entity\PaiementTaxe;
use App\Entity\EtapeSinistre;
use App\Entity\PaiementCommission;
use App\Entity\PaiementPartenaire;
use App\Entity\CommentaireSinistre;
use App\Entity\Preference;
use App\Service\ServiceEntreprise;
use App\Service\ServicePreferences;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;

class DashboardController extends AbstractDashboardController
{
    public const ACTION_AJOUTER = "Ajouter";
    public const ACTION_OPEN = "Ouvrir";
    public const ACTION_DESTROY_ENTREPRISE = "Détruire l'entreprie";
    public const ACTION_LISTE = "Revenir sur la liste";
    public const ACTION_DUPLICATE = "Dupliquer";
    public const ACTION_RESET = "Réinitialiser";
    public const ACTION_SUPPRIMER = "Supprimer";
    public const ACTION_MODIFIER = "Modifier";
    public const ACTION_ENREGISTRER = "Enregistrer et Retourner";
    public const ACTION_ENREGISTRER_ET_CONTINUER = "Enregistrer et Continuer";
    public const ACTION_EXPORTER_EXCELS = "Exporter via MS Excels";

    public function __construct(
        private ServicePreferences $servicePreferences,
        private EntityManagerInterface $entityManager,
        private AdminUrlGenerator $adminUrlGenerator, 
        private ServiceEntreprise $serviceEntreprise
        )
    {
    }

    #[Route('/admin', name: 'admin')]
    //#[IsGranted("ROLE_ADMIN")]
    public function index(): Response
    {
        //return parent::index();

        // Option 1. You can make your dashboard redirect to some common page of your backend
        //
        // $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        // return $this->redirect($adminUrlGenerator->setController(OneOfYourCrudController::class)->generateUrl());

        // Option 2. You can make your dashboard redirect to different pages depending on the user
        //
        // if ('jane' === $this->getUser()->getUsername()) {
        //     return $this->redirect('...');
        // }

        // Option 3. You can render some custom template to display a proper dashboard with widgets, etc.
        // (tip: it's easier if your template extends from @EasyAdmin/page/content.html.twig)
        //

        //dd($this->serviceEntreprise);

        $connected_entreprise = $this->serviceEntreprise->getEntreprise();
        $connected_utilisateur = $this->serviceEntreprise->getUtilisateur();

        if ($this->serviceEntreprise->hasEntreprise() == true) {
            $this->addFlash("success", "Bien venue " . $connected_utilisateur->getNom() . "! Vous êtes connecté à " . $connected_entreprise->getNom());
            return $this->render('admin/dashboard.html.twig');
        } else {
            if ($this->serviceEntreprise->isAdministrateur() == true) {
                //$this->addFlash("info", "Salut " . $connected_utilisateur->getNom() . ", vous devez maintenant créer votre entreprise (espace de travail).");
                return $this->redirectToRoute('security.register.entreprise');
            } else {
                return $this->redirectToRoute('security.login');
            }
        }
        //dd($connected_entreprise);
        //dd($connected_utilisateur);
        //dd($this->serviceEntreprise);

        //return $this->render('admin/dashboard.html.twig');
        //return $this->redirect($this->adminUrlGenerator->setController(ArticleCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        $dashboard = Dashboard::new();
        $nomEntreprise = "TBA";
        //Application de la préférence sur l'apparence
        if($this->serviceEntreprise->getEntreprise() != null){
            $this->servicePreferences->appliquerPreferenceApparence($dashboard, $this->serviceEntreprise->getUtilisateur(), $this->serviceEntreprise->getEntreprise());
            $nomEntreprise = $this->serviceEntreprise->getEntreprise();
        }
        
        return $dashboard
            ->setLocales(['fr'])    //Ne fonctionne pas - je ne sais pourquoi
            ->setTitle($nomEntreprise) //$this->serviceEntreprise->getEntreprise()
            ->setFaviconPath('icones/icon04.png') //Ne fonctionne pas - je ne sais pourquoi
        ;
    }

    public function configureMenuItems(): iterable
    {
        //Seules les admin ont le droit de voir le menu
        //if ($this->isGranted('ROLE_ADMIN')) {
        yield MenuItem::section("ACCEUIL");
        yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-chart-pie');
        yield MenuItem::section("DEPARTEMENTS");
        yield MenuItem::subMenu('COMMERCIAL / CRM', 'fas fa-bullseye')->setSubItems([ //<i class="fa-solid fa-bullseye"></i>
            MenuItem::linkToCrud('Contact', 'fas fa-address-book', Contact::class), //<i class="fa-sharp fa-solid fa-address-book"></i>
            MenuItem::linkToCrud('Missions', 'fas fa-paper-plane', ActionCRM::class), //<i class="fa-solid fa-paper-plane"></i>
            MenuItem::linkToCrud('Feedbacks', 'fas fa-comments', FeedbackCRM::class),
            MenuItem::linkToCrud('Cotations', 'fas fa-cash-register', Cotation::class), //<i class="fa-solid fa-cash-register"></i>
            MenuItem::linkToCrud('Etapes', 'fas fa-list-check', EtapeCrm::class), //<i class="fa-solid fa-list-check"></i>
            MenuItem::linkToCrud('Pistes', 'fas fa-location-crosshairs', Piste::class) //<i class="fa-solid fa-location-crosshairs"></i>
        ])
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACCES_COMMERCIAL]);

        yield MenuItem::subMenu('PRODUCTION', 'fas fa-bag-shopping')->setSubItems([ //<i class="fa-solid fa-bag-shopping"></i>
            MenuItem::linkToCrud('Assureurs', 'fas fa-umbrella', Assureur::class),
            MenuItem::linkToCrud('Engins', 'fas fa-car', Automobile::class),
            MenuItem::linkToCrud('Clients', 'fas fa-person-shelter', Client::class), //<i class="fa-solid fa-person-shelter"></i>
            MenuItem::linkToCrud('Partenaires', 'fas fa-handshake', Partenaire::class),
            MenuItem::linkToCrud('Polices', 'fas fa-file-shield', Police::class),
            MenuItem::linkToCrud('Produits', 'fas fa-gifts', Produit::class)
        ])
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACCES_PRODUCTION]);;

        yield MenuItem::subMenu('FINANCES', 'fas fa-sack-dollar')->setSubItems([ //<i class="fa-solid fa-sack-dollar"></i>
            MenuItem::linkToCrud('Taxes', 'fas fa-landmark-dome', Taxe::class), //<i class="fa-solid fa-landmark-dome"></i>
            MenuItem::linkToCrud('Monnaies', 'fas fa-money-bill-1', Monnaie::class), //<i class="fa-regular fa-money-bill-1"></i>
            MenuItem::linkToCrud('Commissions reçues', 'fas fa-person-arrow-down-to-line', PaiementCommission::class), //<i class="fa-solid fa-person-arrow-down-to-line"></i>
            MenuItem::linkToCrud('Retrocom. payées', 'fas fa-person-arrow-up-from-line', PaiementPartenaire::class), //<i class="fa-solid fa-person-arrow-up-from-line"></i>
            MenuItem::linkToCrud('Taxes payées', 'fas fa-person-chalkboard', PaiementTaxe::class) //<i class="fa-solid fa-person-chalkboard"></i>
        ])
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACCES_FINANCES]);

        yield MenuItem::subMenu('SINISTRE', 'fas fa-fire')->setSubItems([ //<i class="fa-solid fa-fire"></i>
            MenuItem::linkToCrud('Commentaires', 'fas fa-comments', CommentaireSinistre::class), //<i class="fa-solid fa-comments"></i>
            MenuItem::linkToCrud('Etapes', 'fas fa-arrow-down-short-wide', EtapeSinistre::class), //<i class="fa-solid fa-arrow-down-short-wide"></i>
            MenuItem::linkToCrud('Expert', 'fas fa-user-graduate', Expert::class), //<i class="fa-solid fa-user-graduate"></i>
            MenuItem::linkToCrud('Sinistre', 'fas fa-bell', Sinistre::class), //<i class="fa-regular fa-bell"></i>
            MenuItem::linkToCrud('Victime', 'fas fa-person-falling-burst', Victime::class) //<i class="fa-solid fa-person-falling-burst"></i>
        ])
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACCES_SINISTRES]);

        yield MenuItem::subMenu('BIBLIOTHEQUE', 'fas fa-book')->setSubItems([ //<i class="fa-solid fa-books"></i>
            MenuItem::linkToCrud('Catégories', 'fas fa-tags', DocCategorie::class), //<i class="fa-regular fa-tags"></i>
            MenuItem::linkToCrud('Classeurs', 'fas fa-folder-open', DocClasseur::class), //<i class="fa-solid fa-folder-open"></i>
            MenuItem::linkToCrud('Pièces', 'fas fa-file-word', DocPiece::class) //<i class="fa-regular fa-file-word"></i>
        ])
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACCES_BIBLIOTHE]);

        yield MenuItem::section("CONFIGURATIONS")
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACCES_PARAMETRES]);
        yield MenuItem::subMenu('PARAMETRES', 'fas fa-gears')->setSubItems([ //<i class="fa-solid fa-gears"></i>
            MenuItem::linkToCrud('Utilisateur', 'fas fa-user', Utilisateur::class),
            MenuItem::linkToCrud('Entreprise', 'fas fa-shop', Entreprise::class)
                ->setAction(Action::DETAIL)
                ->setEntityId($this->serviceEntreprise->getEntreprise()->getId()),
            MenuItem::linkToCrud('Affichage', 'fa-solid fa-solar-panel', Preference::class)
                ->setAction(Action::DETAIL)
                ->setEntityId($this->serviceEntreprise->getEntreprise()->getId()) //<i class="fa-solid fa-solar-panel"></i>
        ])
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACCES_PARAMETRES]);

        yield MenuItem::linkToCrud("MON PROFIL", "fa-solid fa-user", Utilisateur::class) //<i class="fa-solid fa-user"></i>
            ->setAction(Action::DETAIL)
            ->setEntityId($this->serviceEntreprise->getUtilisateur()->getId());
        yield MenuItem::linkToLogout("DECONNEXION", "fa-solid fa-right-from-bracket");
        //}
    }
}
