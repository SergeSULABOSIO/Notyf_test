<?php

namespace App\Service\RefactoringJS\JSUIComponents\Feedback;

use App\Service\ServiceTaxes;
use App\Service\ServiceMonnaie;
use Doctrine\ORM\EntityManager;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use App\Controller\Admin\PreferenceCrudController;
use App\Entity\FeedbackCRM;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSChamp;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSPanelRenderer;

class FeedbackFormRenderer extends JSPanelRenderer
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
        if ($this->objetInstance instanceof FeedbackCRM) {
            $column = 10;
        }

        //Section - Principale
        $this->addChamp(
            (new JSChamp())
                ->createSection("Informations générales")
                ->setIcon('fas fa-comments') //<i class="fa-sharp fa-solid fa-address-book"></i>
                ->setHelp("Un feedback est une réponse ou compte rendu attaché à une mission. Chaque mission doit avoir un ou plusieurs feedbacks.")
                ->setColumns($column)
                ->getChamp()
        );
        //Closed?
        $this->addChamp(
            (new JSChamp())
                ->createBoolean('closed', "La tâche est exécutée avec succès.")
                // ->setFormTypeOption('disabled', 'disabled')
                // ->renderAsSwitch(false)
                ->setColumns($column)
                ->getChamp()
        );
        //Message
        $this->addChamp(
            (new JSChamp())
                ->createEditeurTexte('message', PreferenceCrudController::PREF_CRM_FEEDBACK_MESAGE)
                ->setColumns($column)
                ->getChamp()
        );
    }

    public function batchActions(?array $champs, ?string $type = null, ?string $pageName = null, $objetInstance = null, ?Crud $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array
    {
        return $champs;
    }
}
