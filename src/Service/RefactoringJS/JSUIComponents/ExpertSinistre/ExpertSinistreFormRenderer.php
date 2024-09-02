<?php

namespace App\Service\RefactoringJS\JSUIComponents\ExpertSinistre;

use App\Entity\Assureur;
use App\Service\ServiceTaxes;
use App\Service\ServiceMonnaie;
use Doctrine\ORM\EntityManager;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use App\Controller\Admin\PreferenceCrudController;
use App\Controller\Admin\UtilisateurCrudController;
use App\Entity\EtapeSinistre;
use App\Entity\Expert;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSChamp;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSPanelRenderer;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSCssHtmlDecoration;

class ExpertSinistreFormRenderer extends JSPanelRenderer
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
        if ($this->objetInstance instanceof Expert) {
            $column = 10;
        }

        //Section - Principale
        $this->addChamp(
            (new JSChamp())
                ->createSection("Informations générales")
                ->setIcon('fas fa-user-graduate') //<i class="fa-sharp fa-solid fa-address-book"></i>
                ->setHelp("L'expert est une personne morale ou physique qui a pour rôle d'aider l'assureur à mieux évaluer l'ampleur du dégât (évaluation chiffrée) afin de déterminer le montant réel de la compensation.")
                ->setColumns($column)
                ->getChamp()
        );

        //Nom
        $this->addChamp(
            (new JSChamp())
                ->createTexte("nom", PreferenceCrudController::PREF_SIN_EXPERT_NOM)
                ->setColumns($column)
                ->getChamp()
        );
        //Adresse
        $this->addChamp(
            (new JSChamp())
                ->createTexte('adresse', PreferenceCrudController::PREF_SIN_EXPERT_ADRESSE)
                ->setColumns($column)
                ->getChamp()
        );
        //Email
        $this->addChamp(
            (new JSChamp())
                ->createTexte('email', PreferenceCrudController::PREF_SIN_EXPERT_EMAIL)
                ->setColumns($column)
                ->getChamp()
        );
        //Site web
        $this->addChamp(
            (new JSChamp())
                ->createTexte('siteweb', PreferenceCrudController::PREF_SIN_EXPERT_SITE_INTERNET)
                ->setColumns($column)
                ->setColumns(10)
                ->getChamp()
        );
        //Téléphone
        $this->addChamp(
            (new JSChamp())
                ->createTexte('telephone', PreferenceCrudController::PREF_SIN_EXPERT_TELEPHONE)
                ->setColumns($column)
                ->getChamp()
        );
        //Description
        $this->addChamp(
            (new JSChamp())
                ->createEditeurTexte('description', PreferenceCrudController::PREF_SIN_EXPERT_DESCRIPTION)
                ->setColumns($column)
                ->getChamp()
        );
        //Sinistres
        $this->addChamp(
            (new JSChamp())
                ->createAssociation('sinistres', PreferenceCrudController::PREF_SIN_EXPERT_SINISTRES)
                ->setColumns($column)
                ->getChamp()
        );
    }

    public function batchActions(?array $champs, ?string $type = null, ?string $pageName = null, $objetInstance = null, ?Crud $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array
    {
        return $champs;
    }
}
