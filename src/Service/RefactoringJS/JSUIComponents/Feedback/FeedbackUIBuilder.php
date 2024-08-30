<?php
namespace App\Service\RefactoringJS\JSUIComponents\Feedback;

use App\Service\ServiceTaxes;
use App\Service\ServiceMonnaie;
use Doctrine\ORM\EntityManager;
use App\Service\ServiceEntreprise;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Service\RefactoringJS\JSUIComponents\Feedback\FeedbackFormRenderer;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSPanelBuilder;
use App\Service\RefactoringJS\JSUIComponents\Feedback\FeedbackListeRenderer;
use App\Service\RefactoringJS\JSUIComponents\Feedback\FeedbackDetailsRenderer;

class FeedbackUIBuilder extends JSPanelBuilder
{
    private ?FeedbackListeRenderer $listeRendere = null;
    private ?FeedbackDetailsRenderer $detailsRendere = null;
    private ?FeedbackFormRenderer $formRendere = null;

    public function __construct(
        private ?ServiceEntreprise $serviceEntreprise
    ) {
    }

    public function buildListPanel(?EntityManager $entityManager = null, ?ServiceMonnaie $serviceMonnaie = null, ?ServiceTaxes $serviceTaxes = null, ?string $pageName = null, $objetInstance = null, $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array
    {
        $this->listeRendere = new FeedbackListeRenderer(
            $entityManager,
            $serviceMonnaie,
            $serviceTaxes,
            $pageName,
            $objetInstance,
            $crud,
            $adminUrlGenerator
        );
        return $this->listeRendere->getChamps();
    }

    public function buildFormPanel(?EntityManager $entityManager = null, ?ServiceMonnaie $serviceMonnaie = null, ?ServiceTaxes $serviceTaxes = null, ?string $pageName = null, $objetInstance = null, $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array
    {
        $this->formRendere = new FeedbackFormRenderer(
            $entityManager,
            $serviceMonnaie,
            $serviceTaxes,
            $pageName,
            $objetInstance,
            $crud,
            $adminUrlGenerator
        );
        return $this->formRendere->getChamps();
    }

    public function buildDetailsPanel(?EntityManager $entityManager = null, ?ServiceMonnaie $serviceMonnaie = null, ?ServiceTaxes $serviceTaxes = null, ?string $pageName = null, $objetInstance = null, $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array
    {
        $this->detailsRendere = new FeedbackDetailsRenderer(
            $entityManager,
            $serviceMonnaie,
            $serviceTaxes,
            $pageName,
            $objetInstance,
            $crud,
            $adminUrlGenerator
        );
        return $this->detailsRendere->getChamps();
    }
}
