<?php

namespace App\Controller\Admin;

use App\Entity\Article;
use App\Entity\Assureur;
use App\Entity\Automobile;
use App\Entity\Client;
use App\Entity\CommentaireSinistre;
use App\Entity\Contact;
use App\Entity\EntreeStock;
use App\Entity\Entreprise;
use App\Entity\EtapeSinistre;
use App\Entity\Expert;
use App\Entity\Monnaie;
use App\Entity\PaiementCommission;
use App\Entity\PaiementPartenaire;
use App\Entity\PaiementTaxe;
use App\Entity\Partenaire;
use App\Entity\Police;
use App\Entity\Produit;
use App\Entity\Sinistre;
use App\Entity\Taxe;
use App\Entity\Utilisateur;
use App\Entity\Victime;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;

class DashboardController extends AbstractDashboardController
{
    public function __construct(private AdminUrlGenerator $adminUrlGenerator)
    {
    }

    #[Route('/admin', name: 'admin')]
    #[IsGranted("ROLE_ADMIN")]
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
        return $this->render('admin/dashboard.html.twig');
        //return $this->redirect($this->adminUrlGenerator->setController(ArticleCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('JS Brokers - Administration')
            ->renderContentMaximized();
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::section("ACCEUIL");
        yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-chart-pie');
        
        yield MenuItem::section("DEPARTEMENTS");
        yield MenuItem::subMenu('MARKETING / CRM', 'fas fa-bullseye')->setSubItems([//<i class="fa-solid fa-bullseye"></i>

        ]);
        yield MenuItem::subMenu('PRODUCTION', 'fas fa-bag-shopping')->setSubItems([//<i class="fa-solid fa-bag-shopping"></i>
            MenuItem::linkToCrud('Assureurs', 'fas fa-umbrella', Assureur::class),
            MenuItem::linkToCrud('Automobiles', 'fas fa-car', Automobile::class),
            MenuItem::linkToCrud('Clients', 'fas fa-person-shelter', Client::class),//<i class="fa-solid fa-person-shelter"></i>
            MenuItem::linkToCrud('Partenaires', 'fas fa-handshake', Partenaire::class),
            MenuItem::linkToCrud('Polices', 'fas fa-file-shield', Police::class),
            MenuItem::linkToCrud('Produits', 'fas fa-gifts', Produit::class)
        ]);

        yield MenuItem::subMenu('FINANCES', 'fas fa-sack-dollar')->setSubItems([//<i class="fa-solid fa-sack-dollar"></i>
            MenuItem::linkToCrud('Taxes', 'fas fa-landmark-dome', Taxe::class),//<i class="fa-solid fa-landmark-dome"></i>
            MenuItem::linkToCrud('Monnaies', 'fas fa-money-bill-1', Monnaie::class),//<i class="fa-regular fa-money-bill-1"></i>
            MenuItem::linkToCrud('Commissions reçues', 'fas fa-person-arrow-down-to-line', PaiementCommission::class),//<i class="fa-solid fa-person-arrow-down-to-line"></i>
            MenuItem::linkToCrud('Retrocom. payées', 'fas fa-person-arrow-up-from-line', PaiementPartenaire::class),//<i class="fa-solid fa-person-arrow-up-from-line"></i>
            MenuItem::linkToCrud('Taxes payées', 'fas fa-person-chalkboard', PaiementTaxe::class)//<i class="fa-solid fa-person-chalkboard"></i>
        ]);

        yield MenuItem::subMenu('SINISTRE', 'fas fa-fire')->setSubItems([//<i class="fa-solid fa-fire"></i>
            MenuItem::linkToCrud('Commentaires', 'fas fa-umbrella', CommentaireSinistre::class),
            MenuItem::linkToCrud('Etapes', 'fas fa-truck', EtapeSinistre::class),
            MenuItem::linkToCrud('Expert', 'fas fa-gift', Expert::class),
            MenuItem::linkToCrud('Sinistre', 'fas fa-gift', Sinistre::class),
            MenuItem::linkToCrud('Victime', 'fas fa-gift', Victime::class)
        ]);
        yield MenuItem::section("CONFIGURATIONS");
        yield MenuItem::subMenu('PARAMETRES', 'fas fa-gears')->setSubItems([//<i class="fa-solid fa-gears"></i>
            MenuItem::linkToCrud('Utilisateur', 'fas fa-user', Utilisateur::class),
            MenuItem::linkToCrud('Changer mot de passe', 'fas fa-key', Utilisateur::class),//<i class="fa-solid fa-key"></i>
            MenuItem::linkToCrud('Entreprises', 'fas fa-shop', Entreprise::class)
        ]);
    }
}
