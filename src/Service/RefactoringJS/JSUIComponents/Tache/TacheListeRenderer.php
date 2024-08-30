<?php

namespace App\Service\RefactoringJS\JSUIComponents\Tache;

use App\Entity\Contact;
use App\Service\ServiceTaxes;
use App\Service\ServiceMonnaie;
use Doctrine\ORM\EntityManager;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use App\Controller\Admin\ActionCRMCrudController;
use App\Controller\Admin\PreferenceCrudController;
use App\Controller\Admin\UtilisateurCrudController;
use App\Entity\ActionCRM;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSChamp;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSPanelRenderer;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSCssHtmlDecoration;

class TacheListeRenderer extends JSPanelRenderer
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
        parent::__construct(self::TYPE_LISTE, $pageName, $objetInstance, $crud, $adminUrlGenerator);
    }

    public function design()
    {
        //Closed?
        $this->addChamp(
            (new JSChamp())
                ->createChoix('closed', PreferenceCrudController::PREF_CRM_MISSION_STATUS)
                ->setChoices(ActionCRMCrudController::STATUS_MISSION)
                ->renderAsBadges([
                    ActionCRMCrudController::STATUS_MISSION[ActionCRMCrudController::MISSION_ACHEVEE] => 'success', //info
                    ActionCRMCrudController::STATUS_MISSION[ActionCRMCrudController::MISSION_ENCOURS] => 'warning',
                ])
                ->setColumns(10)
                ->getChamp()
        );
        //Mission
        $this->addChamp(
            (new JSChamp())
                ->createTexte('mission', PreferenceCrudController::PREF_CRM_MISSION_NOM)
                ->setColumns(10)
                ->getChamp()
        );
        //Objetcif
        $this->addChamp(
            (new JSChamp())
                ->createZonneTexte('objectif', PreferenceCrudController::PREF_CRM_MISSION_OBJECTIF)
                ->setColumns(10)
                ->getChamp()
        );
        //Started At
        $this->addChamp(
            (new JSChamp())
                ->createDate('startedAt', PreferenceCrudController::PREF_CRM_MISSION_STARTED_AT)
                ->setColumns(10)
                ->setFormatValue(
                    function ($value, ActionCRM $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $value))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );
        //Ended At
        $this->addChamp(
            (new JSChamp())
                ->createDate('endedAt', PreferenceCrudController::PREF_CRM_MISSION_ENDED_AT)
                ->setColumns(10)
                ->setFormatValue(
                    function ($value, ActionCRM $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $value))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );
        //Piste
        $this->addChamp(
            (new JSChamp())
                ->createAssociation('piste', PreferenceCrudController::PREF_CRM_MISSION_PISTE)
                ->setColumns(10)
                ->getChamp()
        );
        //Feedback
        $this->addChamp(
            (new JSChamp())
                ->createAssociation('feedbacks', PreferenceCrudController::PREF_CRM_MISSION_FEEDBACKS)
                ->setColumns(10)
                ->getChamp()
        );
        //AttributedTo
        $this->addChamp(
            (new JSChamp())
                ->createAssociation('attributedTo', PreferenceCrudController::PREF_CRM_MISSION_ATTRIBUE_A)
                ->setColumns(10)
                ->getChamp()
        );
        //Dernière modification
        $this->addChamp(
            (new JSChamp())
                ->createDate('updatedAt', PreferenceCrudController::PREF_CRM_MISSION_UPDATED_AT)
                ->setColumns(10)
                ->setFormatValue(
                    function ($value, ActionCRM $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $value))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );
    }

    public function batchActions(?array $champs, ?string $type = null, ?string $pageName = null, $objetInstance = null, ?Crud $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array
    {
        return $champs;
    }
}
