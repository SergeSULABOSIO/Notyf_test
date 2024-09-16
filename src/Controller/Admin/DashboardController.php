<?php

namespace App\Controller\Admin;

use App\Controller\TableauDeBordCourtierController;
use App\Entity\Taxe;
use App\Entity\Piste;
use App\Entity\Client;
use App\Entity\Expert;
use App\Entity\Police;
use App\Entity\Revenu;
use App\Entity\Contact;
use App\Entity\Facture;
use App\Entity\Monnaie;
use App\Entity\Produit;
use App\Entity\Tranche;
use App\Entity\Victime;
use App\Entity\Assureur;
use App\Entity\Cotation;
use App\Entity\DocPiece;
use App\Entity\Paiement;
use App\Entity\Sinistre;
use App\Entity\ActionCRM;
use App\Entity\Entreprise;
use App\Entity\Partenaire;
use App\Entity\Preference;
use App\Entity\FeedbackCRM;
use App\Entity\Utilisateur;
use App\Entity\EtapeSinistre;
use App\Entity\CompteBancaire;
use App\Service\ServiceCrossCanal;
use App\Service\ServiceEntreprise;
use App\Service\ServicePreferences;
use Symfony\UX\Chartjs\Model\Chart;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\RefactoringJS\Commandes\Commande;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use App\Service\RefactoringJS\Commandes\CommandeExecuteur;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use App\Service\RefactoringJS\TableauDeBord\Commandes\ComCreerTableauDeBord;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;

class DashboardController extends AbstractDashboardController implements CommandeExecuteur
{
    public const ACTION_AJOUTER = "Ajouter";
    public const ACTION_OPEN = "Voire les détails";
    public const ACTION_DESTROY_ENTREPRISE = "Détruire l'entreprie";
    public const ACTION_LISTE = "Revenir sur la liste";
    public const ACTION_DUPLICATE = "Dupliquer";
    public const ACTION_GENERER_BORDEREAU_PDF = "Produire la bordereau (pdf)";
    public const ACTION_GENERER_FACTURE_PDF = "Produire la note (pdf)";
    public const ACTION_AJOUTER_PAIEMENT = "Ajouter un paiement";
    public const ACTION_RESET = "Réinitialiser";
    public const ACTION_SUPPRIMER = "Supprimer";
    public const ACTION_MODIFIER = "Modifier";
    public const ACTION_ENREGISTRER = "Enregistrer";
    public const ACTION_ENREGISTRER_ET_CONTINUER = "Enregistrer et Continuer";
    public const ACTION_EXPORTER_EXCELS = "Exporter via MS Excels";
    public const ACTION_FONCTION_AFFICHAGE_ET_SAISIE = "Pour affichage et saisie";
    public const ACTION_FONCTION_AFFICHAGE_UNIQUEMENT = "Pour affichage uniquement";
    public const ACTION_FONCTION_SAISIE_UNIQUEMENT = "Pour saisie uniquement";



    public function __construct(
        private ChartBuilderInterface $chartBuilder,
        private ServicePreferences $servicePreferences,
        private EntityManagerInterface $entityManager,
        private AdminUrlGenerator $adminUrlGenerator,
        private ServiceEntreprise $serviceEntreprise,
        private ServiceCrossCanal $serviceCrossCanal
    ) {}

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



        $connected_entreprise = $this->serviceEntreprise->getEntreprise();
        $connected_utilisateur = $this->serviceEntreprise->getUtilisateur();

        if ($this->serviceEntreprise->hasEntreprise() == true) {
            // $this->addFlash("success", "Bien venue " . $connected_utilisateur->getNom() . "! Vous êtes connecté à " . $connected_entreprise->getNom());




            //Construction du tableau de bord ici
            // $this->executer(new ComCreerTableauDeBord());

            // $chart = $this->chartBuilder->createChart(Chart::TYPE_LINE);

            // $chart->setData([
            //     'labels' => ['January', 'February', 'March', 'April', 'May', 'June', 'July'],
            //     'datasets' => [
            //         [
            //             'label' => 'My First dataset',
            //             'backgroundColor' => 'rgb(255, 99, 132)',
            //             'borderColor' => 'rgb(255, 99, 132)',
            //             'data' => [0, 10, 5, 2, 20, 30, 45],
            //         ],
            //     ],
            // ]);

            // $chart->setOptions([
            //     'scales' => [
            //         'y' => [
            //             'suggestedMin' => 0,
            //             'suggestedMax' => 100,
            //         ],
            //     ],
            // ]);

            // return $this->render('admin/TableauDeBord/Composants/tableaudebord.html.twig', [
            //     'controller_name' => 'SweetAlertController',
            //     'chart' => $chart,
            // ]);
            return $this->render('admin/dashboard.html.twig');

            // $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
            // return $this->redirect($this->adminUrlGenerator->setController(TableauDeBordCourtierController::class)->generateUrl());
            // return $this->redirectToRoute("app_tableau_de_bord_courtier");
        } else {
            if ($this->serviceEntreprise->isAdministrateur() == true) {
                //$this->addFlash("info", "Salut " . $connected_utilisateur->getNom() . ", vous devez maintenant créer votre entreprise (espace de travail).");
                return $this->redirectToRoute('security.register.entreprise');
            } else {
                return $this->redirectToRoute('security.login');
            }
        }

        //return $this->render('admin/dashboard.html.twig');
        //return $this->redirect($this->adminUrlGenerator->setController(ArticleCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        $dashboard = Dashboard::new();
        $nomEntreprise = "TBA";
        //Application de la préférence sur l'apparence
        if ($this->serviceEntreprise->getEntreprise() != null) {
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
        yield MenuItem::linkToDashboard('TABLEAU DE BORD', 'fa fa-chart-pie');
        yield MenuItem::section("DEPARTEMENTS");
        yield MenuItem::subMenu('COMMERCIAL / CRM', 'fas fa-bullseye')->setSubItems([ //<i class="fa-solid fa-bullseye"></i>
            MenuItem::linkToCrud('Contact', 'fas fa-address-book', Contact::class), //<i class="fa-sharp fa-solid fa-address-book"></i>
            MenuItem::linkToCrud('Tâches', 'fas fa-paper-plane', ActionCRM::class), //<i class="fa-solid fa-paper-plane"></i>
            MenuItem::linkToCrud('Feedbacks', 'fas fa-comments', FeedbackCRM::class),
            MenuItem::linkToCrud('Cotations', 'fas fa-cash-register', Cotation::class), //<i class="fa-solid fa-cash-register"></i>
            //MenuItem::linkToCrud('Etapes', 'fas fa-list-check', EtapeCrm::class), //<i class="fa-solid fa-list-check"></i>
            MenuItem::linkToCrud('Pistes', 'fas fa-location-crosshairs', Piste::class) //<i class="fa-solid fa-location-crosshairs"></i>
        ])
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACCES_COMMERCIAL]);

        yield MenuItem::subMenu('PRODUCTION', 'fas fa-bag-shopping')->setSubItems([ //<i class="fa-solid fa-bag-shopping"></i>
            MenuItem::linkToCrud('Assureurs', 'fas fa-umbrella', Assureur::class),
            // MenuItem::linkToCrud('Engins', 'fas fa-car', Automobile::class),
            MenuItem::linkToCrud('Clients', 'fas fa-person-shelter', Client::class), //<i class="fa-solid fa-person-shelter"></i>
            MenuItem::linkToCrud('Partenaires', 'fas fa-handshake', Partenaire::class),
            MenuItem::linkToCrud('Polices', 'fas fa-file-shield', Police::class),
            MenuItem::linkToCrud('Produits', 'fas fa-gifts', Produit::class)
        ])
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACCES_PRODUCTION]);;

        yield MenuItem::subMenu('FINANCES', 'fas fa-sack-dollar')->setSubItems([ //<i class="fa-solid fa-sack-dollar"></i>
            MenuItem::linkToCrud('Comptes bancaires', 'fa-solid fa-piggy-bank', CompteBancaire::class),
            MenuItem::linkToCrud('Taxes', 'fas fa-landmark-dome', Taxe::class), //<i class="fa-solid fa-landmark-dome"></i>
            MenuItem::linkToCrud('Monnaies', 'fas fa-money-bill-1', Monnaie::class), //<i class="fa-regular fa-money-bill-1"></i>
            MenuItem::linkToCrud('Tranches', 'fa-solid fa-layer-group', Tranche::class), //<i class="fa-solid fa-receipt"></i>
            MenuItem::linkToCrud('Revenus', 'fa-solid fa-burger', Revenu::class), //<i class="fa-solid fa-receipt"></i>
            MenuItem::linkToCrud('Factures', 'fa-solid fa-receipt', Facture::class), //<i class="fa-solid fa-receipt"></i>
            MenuItem::linkToCrud('Paiements', 'fa-solid fa-cash-register', Paiement::class), //<i class="fa-solid fa-cash-register"></i>
        ])
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACCES_FINANCES]);

        yield MenuItem::subMenu('SINISTRE', 'fas fa-fire')->setSubItems([ //<i class="fa-solid fa-fire"></i>
            MenuItem::linkToCrud('Etapes', 'fas fa-arrow-down-short-wide', EtapeSinistre::class), //<i class="fa-solid fa-arrow-down-short-wide"></i>
            MenuItem::linkToCrud('Expert', 'fas fa-user-graduate', Expert::class), //<i class="fa-solid fa-user-graduate"></i>
            MenuItem::linkToCrud('Sinistre', 'fas fa-bell', Sinistre::class), //<i class="fa-regular fa-bell"></i>
            MenuItem::linkToCrud('Victime', 'fas fa-person-falling-burst', Victime::class) //<i class="fa-solid fa-person-falling-burst"></i>
        ])
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACCES_SINISTRES]);

        yield MenuItem::subMenu('BIBLIOTHEQUE', 'fas fa-book')->setSubItems([ //<i class="fa-solid fa-books"></i>
            //MenuItem::linkToCrud('Catégories', 'fas fa-tags', DocCategorie::class), //<i class="fa-regular fa-tags"></i>
            //MenuItem::linkToCrud('Classeurs', 'fas fa-folder-open', DocClasseur::class), //<i class="fa-solid fa-folder-open"></i>
            MenuItem::linkToCrud('Documents', 'fa-solid fa-paperclip', DocPiece::class) //<i class="fa-solid fa-paperclip"></i>
        ])
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACCES_BIBLIOTHE]);

        yield MenuItem::section("REPORTING - Commissions")
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACCES_REPORTING]);
        //COMMISSIONS
        yield MenuItem::subMenu('Impayées', 'fa-regular fa-newspaper')
            ->setSubItems($this->serviceCrossCanal->reporting_commission_assureur_generer_liens(true))
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACCES_REPORTING]);

        yield MenuItem::subMenu('Encaissées', 'fa-regular fa-newspaper')
            ->setSubItems($this->serviceCrossCanal->reporting_commission_assureur_generer_liens(false))
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACCES_REPORTING]);

        //RETROCOMMISSION
        yield MenuItem::section("LISTING - Retro-Commissions")
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACCES_REPORTING]);

        yield MenuItem::subMenu('Impayées', 'fa-regular fa-newspaper')
            ->setSubItems($this->serviceCrossCanal->reporting_commission_partenaire_generer_liens(true))
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACCES_REPORTING]);

        yield MenuItem::subMenu('Payées', 'fa-regular fa-newspaper')
            ->setSubItems($this->serviceCrossCanal->reporting_commission_partenaire_generer_liens(false))
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACCES_REPORTING]);

        yield MenuItem::section("LISTING - Taxes")
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACCES_REPORTING]);
        //TAXES DUES
        yield MenuItem::subMenu("Impayées", 'fa-regular fa-newspaper')
            ->setSubItems($this->serviceCrossCanal->reporting_taxe_generer_liens(true))
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACCES_REPORTING]);

        //TAXES PAYEES
        yield MenuItem::subMenu("Payées", 'fa-regular fa-newspaper')
            ->setSubItems($this->serviceCrossCanal->reporting_taxe_generer_liens(false))
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACCES_REPORTING]);

        yield MenuItem::section("LISTING - Production")
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACCES_REPORTING]);

        //PRODUCTION GESTIONNAIRE DE COMPTE
        yield MenuItem::subMenu('Par gestionnaire', 'fa-regular fa-newspaper')
            ->setSubItems($this->serviceCrossCanal->reporting_production_gestionnaire_generer_liens())
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACCES_REPORTING]);


        //PRODUCTION ASSUREUR
        yield MenuItem::subMenu('Par assureur', 'fa-regular fa-newspaper')
            ->setSubItems($this->serviceCrossCanal->reporting_production_assureur_generer_liens())
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACCES_REPORTING]);

        //PRODUCTION PARTENAIRE
        yield MenuItem::subMenu('Par partenaire', 'fa-regular fa-newspaper')
            ->setSubItems($this->serviceCrossCanal->reporting_production_partenaire_generer_liens())
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACCES_REPORTING]);

        //PRODUCTION PRODUIT
        yield MenuItem::subMenu('Par produit', 'fa-regular fa-newspaper')
            ->setSubItems($this->serviceCrossCanal->reporting_production_produit_generer_liens())
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACCES_REPORTING]);

        yield MenuItem::section("LISTING - Sinistre")
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACCES_REPORTING]);
        //SINISTRE
        yield MenuItem::subMenu('Par étapes', 'fa-regular fa-newspaper')
            ->setSubItems($this->serviceCrossCanal->reporting_sinistre_etape_generer_liens())
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACCES_REPORTING]);

        yield MenuItem::section("LISTING - PISTE (CRM)")
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACCES_REPORTING]);
        //PISTE PAR ETAPE
        yield MenuItem::subMenu('Par étape', 'fa-regular fa-newspaper')
            ->setSubItems($this->serviceCrossCanal->reporting_piste_etape_generer_liens())
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACCES_REPORTING]);

        //PISTE PAR UTILISATEUR
        yield MenuItem::subMenu('Par utilisateur', 'fa-regular fa-newspaper')
            ->setSubItems($this->serviceCrossCanal->reporting_piste_utilisateur_generer_liens())
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACCES_REPORTING]);


        yield MenuItem::section("CONFIGURATIONS")
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACCES_PARAMETRES]);
        yield MenuItem::subMenu('PARAMETRES', 'fas fa-gears')
            ->setSubItems([ //<i class="fa-solid fa-gears"></i>
                MenuItem::linkToCrud('Utilisateur', 'fas fa-user', Utilisateur::class),
                MenuItem::linkToCrud('Entreprise', 'fas fa-shop', Entreprise::class)
                    ->setAction(Action::DETAIL)
                    ->setEntityId($this->serviceEntreprise->getEntreprise()->getId()),
                MenuItem::linkToCrud('Affichage', 'fa-solid fa-solar-panel', Preference::class)
                    ->setAction(Action::EDIT)
                    ->setEntityId($this->serviceEntreprise->getEntreprise()->getId()) //<i class="fa-solid fa-solar-panel"></i>
            ])
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACCES_PARAMETRES]);

        yield MenuItem::linkToCrud("MON PROFIL", "fa-solid fa-user", Utilisateur::class) //<i class="fa-solid fa-user"></i>
            ->setAction(Action::DETAIL)
            ->setEntityId($this->serviceEntreprise->getUtilisateur()->getId());
        yield MenuItem::linkToLogout("DECONNEXION", "fa-solid fa-right-from-bracket");
        //}
    }

    public function executer(?Commande $commande)
    {
        if ($commande != null) {
            $commande->executer();
        }
    }

    // public function configureAssets(): Assets
    // {
    //     $assets = parent::configureAssets();

    //     $assets->addWebpackEncoreEntry('app');

    //     return $assets;
    // }
}
