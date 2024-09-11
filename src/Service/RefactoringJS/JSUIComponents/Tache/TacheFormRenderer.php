<?php
namespace App\Service\RefactoringJS\JSUIComponents\Tache;

use App\Entity\ActionCRM;
use App\Service\ServiceTaxes;
use App\Service\ServiceMonnaie;
use Doctrine\ORM\EntityManager;
use App\Service\ServiceEntreprise;
use Doctrine\ORM\EntityRepository;
use App\Controller\Admin\DocPieceCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use App\Controller\Admin\PreferenceCrudController;
use App\Controller\Admin\FeedbackCRMCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSChamp;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSPanelRenderer;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSCssHtmlDecoration;

class TacheFormRenderer extends JSPanelRenderer
{
    public function __construct(
        private ServiceEntreprise $serviceEntreprise,
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
        if ($this->objetInstance instanceof ActionCRM) {
            $column = 10;
        }

        //Section - Principale
        $this->addChamp(
            (new JSChamp())
                ->createSection("Informations générales")
                ->setIcon('fas fa-paper-plane') //<i class="fa-sharp fa-solid fa-address-book"></i>
                ->setHelp("Une mission est une ou un ensembles d'actions attribuée(s) à un ou plusieurs utilisateurs.")
                ->setColumns($column)
                ->getChamp()
        );
        //Closed?
        $this->addChamp(
            (new JSChamp())
                ->createBoolean('closed', "La tâche est cloturée avec succès.")
                ->setFormTypeOptions('disabled', 'disabled')
                ->renderAsSwitch(false)
                ->setColumns($column)
                ->getChamp()
        );
        //Mission
        $this->addChamp(
            (new JSChamp())
                ->createTexte('mission', PreferenceCrudController::PREF_CRM_MISSION_NOM)
                ->setColumns($column)
                ->getChamp()
        );
        //AttributedTo
        $this->addChamp(
            (new JSChamp())
                ->createAssociation('attributedTo', PreferenceCrudController::PREF_CRM_MISSION_ATTRIBUE_A)
                ->setFormTypeOptions('query_builder', function (EntityRepository $entityRepository) {
                    return $entityRepository
                        ->createQueryBuilder('e')
                        ->Where('e.entreprise = :ese')
                        ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
                })
                ->setRequired(false)
                ->setColumns($column)
                ->getChamp()
        );
        //Started At
        $this->addChamp(
            (new JSChamp())
                ->createDate('startedAt', PreferenceCrudController::PREF_CRM_MISSION_STARTED_AT)
                ->setColumns($column)
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
                ->setColumns($column)
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

        //Section - Documents
        $this->addChamp(
            (new JSChamp())
                ->createSection("Pièces jointes et objectif")
                ->setIcon("fa-solid fa-paperclip")
                ->getChamp()
        );
        //Documents
        $this->addChamp(
            (new JSChamp())
                ->createCollection('documents', PreferenceCrudController::PREF_CRM_COTATION_DOCUMENTS)
                ->useEntryCrudForm(DocPieceCrudController::class)
                ->allowAdd(true)
                ->allowDelete(true)
                ->setRequired(false)
                ->setColumns($column)
                ->getChamp()
        );
        //Objetcif
        $this->addChamp(
            (new JSChamp())
                ->createEditeurTexte('objectif', PreferenceCrudController::PREF_CRM_MISSION_OBJECTIF)
                ->setRequired(false)
                ->setColumns($column)
                ->getChamp()
        );
        //Section - Feedback de l'action
        $this->addChamp(
            (new JSChamp())
                ->createSection("Feedbacks / Comptes Rendus")
                ->setIcon("fas fa-comments")
                ->getChamp()
        );
        //Feedback
        $this->addChamp(
            (new JSChamp())
                ->createCollection('feedbacks', "Feedbacks")
                ->useEntryCrudForm(FeedbackCRMCrudController::class)
                ->allowAdd(true)
                ->allowDelete(true)
                ->setRequired(false)
                ->setColumns($column)
                ->getChamp()
        );
    }

    public function batchActions(?array $champs, ?string $type = null, ?string $pageName = null, $objetInstance = null, ?Crud $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array
    {
        return $champs;
    }
}
