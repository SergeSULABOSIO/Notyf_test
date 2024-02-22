<?php

namespace App\Service\RefactoringJS\JSUIComponents\Tranche;

use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSChamp;
use App\Service\ServiceTaxes;
use App\Service\ServiceMonnaie;
use Doctrine\ORM\EntityManager;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSPanelRenderer;

class TrancheFormRenderer extends JSPanelRenderer
{
    public function __construct(
        private EntityManager $entityManager,
        private ServiceMonnaie $serviceMonnaie,
        private ServiceTaxes $serviceTaxes,
        string $pageName,
        $objetInstance,
        $crud,
        AdminUrlGenerator $adminUrlGenerator
    ) {
        parent::__construct(self::TYPE_FORMULAIRE, $pageName, $objetInstance, $crud, $adminUrlGenerator);
    }

    public function design()
    {
        //Nom
        $this->addChamp(
            (new JSChamp())
                ->createTexte("nom", "Intitulé")
                ->setRequired(true)
                ->setColumns(12)
                ->getChamp()
        );

        //Durée
        $this->addChamp(
            (new JSChamp())
            ->createNombre("duree", "Durée (en mois)")
            ->setColumns(12)
            ->setRequired(true)
            ->getChamp()
        );

        //Taux
        $this->addChamp(
            (new JSChamp())
                ->createPourcentage("taux", "Portion")
                ->setColumns(12)
                ->getChamp()
        );
    }

    public function batchActions(?array $champs, ?string $type = null, ?string $pageName = null, $objetInstance = null, ?Crud $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array
    {
        return $champs;
    }
}
