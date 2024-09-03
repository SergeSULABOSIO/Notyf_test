<?php

namespace App\Service\RefactoringJS\JSUIComponents\Victime;

use App\Service\ServiceTaxes;
use App\Service\ServiceMonnaie;
use Doctrine\ORM\EntityManager;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use App\Controller\Admin\PreferenceCrudController;
use App\Entity\Victime;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSChamp;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSPanelRenderer;

class VictimeFormRenderer extends JSPanelRenderer
{
    public function __construct(
        private EntityManager $entityManager,
        private ServiceMonnaie $serviceMonnaie,
        private ServiceTaxes $serviceTaxes,
        private string $pageName,
        private $objetInstance,
        private $crud,
        private AdminUrlGenerator $adminUrlGenerator
    ) {
        parent::__construct(self::TYPE_FORMULAIRE, $pageName, $objetInstance, $crud, $adminUrlGenerator);
    }

    public function design()
    {
        $column = 12;
        if ($this->objetInstance instanceof Victime) {
            $column = 10;
        }
        //Section - Principale
        $this->addChamp(
            (new JSChamp())
                ->createSection("Informations générales")
                ->setIcon('fas fa-person-falling-burst') //<i class="fa-sharp fa-solid fa-address-book"></i>
                ->setHelp("Personne (morale ou physique) laisée ou ayant subi les dommages au cours du sinistre.")
                ->setColumns($column)
                ->getChamp()
        );
        //Nom
        $this->addChamp(
            (new JSChamp())
                ->createTexte("nom", PreferenceCrudController::PREF_SIN_VICTIME_NOM)
                ->setColumns($column)
                ->getChamp()
        );
        //Adresse
        $this->addChamp(
            (new JSChamp())
                ->createTexte('adresse', PreferenceCrudController::PREF_SIN_VICTIME_ADRESSE)
                ->setColumns($column)
                ->getChamp()
        );
        //Email
        $this->addChamp(
            (new JSChamp())
                ->createTexte('email', PreferenceCrudController::PREF_SIN_VICTIME_EMAIL)
                ->setColumns($column)
                ->getChamp()
        );
        //Telephone
        $this->addChamp(
            (new JSChamp())
                ->createTexte('telephone', PreferenceCrudController::PREF_SIN_VICTIME_TELEPHONE)
                ->setColumns($column)
                ->getChamp()
        );
        //Sinistre
        $this->addChamp(
            (new JSChamp())
                ->createAssociation('sinistre', PreferenceCrudController::PREF_SIN_VICTIME_SINISTRE)
                ->setColumns($column)
                ->getChamp()
        );
    }

    public function batchActions(?array $champs, ?string $type = null, ?string $pageName = null, $objetInstance = null, ?Crud $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array
    {
        return $champs;
    }
}
