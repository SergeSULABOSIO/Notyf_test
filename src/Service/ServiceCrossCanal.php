<?php

namespace App\Service;

use NumberFormatter;
use App\Entity\Monnaie;
use App\Entity\ActionCRM;
use App\Entity\Entreprise;
use App\Entity\FeedbackCRM;
use App\Entity\Utilisateur;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Bundle\SecurityBundle\Security;
use App\Controller\Admin\MonnaieCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use App\Controller\Admin\FeedbackCRMCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;


class ServiceCrossCanal
{
    public const ACTION_FEEDBACK_AJOUTER = "Ajouter un feedback";
    public const ACTION_FEEDBACK_LISTER = "Voire les feedbacks";
    public const CROSSED_ENTITY_ACTION = "action";

    public function __construct(
        private EntityManagerInterface $entityManager
    ) {

    }

    public function crossCanal_Action_ajouterFeedback(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        $entite = $context->getEntity()->getInstance();
        $url = $adminUrlGenerator
            ->setController(FeedbackCRMCrudController::class)
            ->setAction(Action::NEW)
            ->set("titre", "NOUVEAU FEEDBACK - [Mission: " . $entite->getMission() . "]")
            ->set(self::CROSSED_ENTITY_ACTION, $entite->getId())
            //->setEntityId(null)
            ->generateUrl();
        return $url;
    }

    public function crossCanal_Action_listerFeedback(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        $entite = $context->getEntity()->getInstance();
        $url = $adminUrlGenerator
            ->setController(FeedbackCRMCrudController::class)
            ->setAction(Action::INDEX)
            ->set("titre", "LISTE DES FEEDBACKS - [Mission: " . $entite->getMission() . "]")
            ->set('filters[' . self::CROSSED_ENTITY_ACTION . '][value]', $entite->getId()) //il faut juste passer son ID
            ->set('filters[' . self::CROSSED_ENTITY_ACTION . '][comparison]', '=')
            ->generateUrl();

        return $url;
    }

    public function crossCanal_Action_setAction(FeedbackCRM $feedbackCRM, AdminUrlGenerator $adminUrlGenerator): FeedbackCRM
    {
        if ($adminUrlGenerator->get(self::CROSSED_ENTITY_ACTION) != null) {
            $actionCRM = null;
            $paramIDAction = $adminUrlGenerator->get(self::CROSSED_ENTITY_ACTION);
            if ($paramIDAction != null) {
                $actionCRM = $this->entityManager->getRepository(ActionCRM::class)->find($paramIDAction);
            }
            $feedbackCRM->setAction($actionCRM);
        }
        return $feedbackCRM;
    }

    public function crossCanal_Action_setTitrePage(Crud $crud, AdminUrlGenerator $adminUrlGenerator): Crud
    {
        if ($adminUrlGenerator->get("titre") != null) {
            $crud->setPageTitle(Crud::PAGE_INDEX, $adminUrlGenerator->get("titre"));
            $crud->setPageTitle(Crud::PAGE_DETAIL, $adminUrlGenerator->get("titre"));
            $crud->setPageTitle(Crud::PAGE_NEW, $adminUrlGenerator->get("titre"));
        }
        return $crud;
    }
}
