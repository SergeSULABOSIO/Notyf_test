<?php

namespace App\Controller\Admin;

use App\Entity\Article;
use App\Entity\Assureur;
use App\Entity\Automobile;
use App\Entity\Client;
use App\Entity\Contact;
use App\Entity\EntreeStock;
use App\Entity\Entreprise;
use App\Entity\Monnaie;
use App\Entity\PaiementCommission;
use App\Entity\PaiementPartenaire;
use App\Entity\PaiementTaxe;
use App\Entity\Partenaire;
use App\Entity\Police;
use App\Entity\Produit;
use App\Entity\Taxe;
use App\Entity\Utilisateur;
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
        yield MenuItem::subMenu('MARKETING / CRM', 'fas fa-bars')->setSubItems([]);
        yield MenuItem::subMenu('PRODUCTION', 'fas fa-bars')->setSubItems([
            MenuItem::linkToCrud('Assureurs', 'fas fa-gift', Assureur::class),
            MenuItem::linkToCrud('Automobiles', 'fas fa-gift', Automobile::class),
            MenuItem::linkToCrud('Clients', 'fas fa-gift', Client::class),
            MenuItem::linkToCrud('Partenaires', 'fas fa-gift', Partenaire::class),
            MenuItem::linkToCrud('Polices', 'fas fa-gift', Police::class),
            MenuItem::linkToCrud('Produits', 'fas fa-gift', Produit::class)
        ]);

        yield MenuItem::subMenu('FINANCES', 'fas fa-bars')->setSubItems([
            MenuItem::linkToCrud('Taxes', 'fas fa-gift', Taxe::class),
            MenuItem::linkToCrud('Monnaies', 'fas fa-gift', Monnaie::class),
            MenuItem::linkToCrud('Commissions reçues', 'fas fa-gift', PaiementCommission::class),
            MenuItem::linkToCrud('Retrocom. payées', 'fas fa-gift', PaiementPartenaire::class),
            MenuItem::linkToCrud('Taxes payées', 'fas fa-gift', PaiementTaxe::class)
        ]);

        yield MenuItem::subMenu('SINISTRE', 'fas fa-bars')->setSubItems([
            MenuItem::linkToCrud('Arrivages', 'fas fa-truck', EntreeStock::class),
            MenuItem::linkToCrud('Articles', 'fas fa-gift', Article::class)
        ]);
        yield MenuItem::section("CONFIGURATIONS");
        yield MenuItem::subMenu('PARAMETRES', 'fas fa-bars')->setSubItems([
            MenuItem::linkToCrud('Utilisateur', 'fas fa-user', Utilisateur::class),
            MenuItem::linkToCrud('Entreprises', 'fas fa-gift', Entreprise::class)
        ]);
    }
}
