<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Service\ServiceCrossCanal;
use App\Service\ServiceEntreprise;
use App\Service\ServicePreferences;
use Symfony\UX\Chartjs\Model\Chart;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TableauDeBordCourtierController extends AbstractController
{
    public function __construct(
        private ChartBuilderInterface $chartBuilder,
        private ServicePreferences $servicePreferences,
        private EntityManagerInterface $entityManager,
        private AdminUrlGenerator $adminUrlGenerator,
        private ServiceEntreprise $serviceEntreprise,
        private ServiceCrossCanal $serviceCrossCanal
    ) {}


    #[Route('/tableau/de/bord/courtier', name: 'app_tableau_de_bord_courtier')]
    public function index(): Response
    {
        /** @var Utilisateur */
        $user = $this->serviceEntreprise->getUtilisateur();
        
        // dd($user);

        $chart = $this->chartBuilder->createChart(Chart::TYPE_LINE);

        $chart->setData([
            'labels' => ['January', 'February', 'March', 'April', 'May', 'June', 'July'],
            'datasets' => [
                [
                    'label' => 'My First dataset',
                    'backgroundColor' => 'rgb(255, 99, 132)',
                    'borderColor' => 'rgb(255, 99, 132)',
                    'data' => [0, 10, 5, 2, 20, 30, 45],
                ],
            ],
        ]);

        $chart->setOptions([
            'scales' => [
                'y' => [
                    'suggestedMin' => 0,
                    'suggestedMax' => 100,
                ],
            ],
        ]);


        return $this->render('tableau_de_bord_courtier/index.html.twig', [
            'controller_name' => 'Tableau De Bord Courtier',
            'chart' => $chart,
            'user' => $user,
        ]);
    }
}
