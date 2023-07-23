<?php

namespace App\Service;

use NumberFormatter;
use App\Entity\Piste;
use App\Entity\Police;
use App\Entity\Contact;
use App\Entity\Monnaie;
use App\Entity\Cotation;
use App\Entity\DocPiece;
use App\Entity\EtapeCrm;
use App\Entity\ActionCRM;
use App\Entity\Entreprise;
use App\Entity\FeedbackCRM;
use App\Entity\Utilisateur;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Bundle\SecurityBundle\Security;
use App\Controller\Admin\PisteCrudController;
use App\Controller\Admin\PoliceCrudController;
use App\Controller\Admin\ContactCrudController;
use App\Controller\Admin\MonnaieCrudController;
use App\Controller\Admin\DocPieceCrudController;
use App\Controller\Admin\EtapeCrmCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use App\Controller\Admin\ActionCRMCrudController;
use App\Controller\Admin\CotationCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use App\Controller\Admin\FeedbackCRMCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Form\Type\ComparisonType;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;


class ServiceCrossCanal
{
    public const ACTION_FEEDBACK_AJOUTER = "Ajouter un feedback";
    public const ACTION_FEEDBACK_LISTER = "Voire les feedbacks";
    public const COTATION_PIECE_AJOUTER = "Ajouter une pièce";
    public const COTATION_PIECE_LISTER = "Voire les pièces";
    public const COTATION_PISTE_AJOUTER = "Ajouter une piste";
    public const COTATION_PISTE_LISTER = "Voire les pistes";
    public const PISTE_AJOUTER_MISSION = "Ajouter une mission";
    public const PISTE_AJOUTER_CONTACT = "Ajouter un contact";
    public const PISTE_AJOUTER_COTATION = "Ajouter une cotation";
    public const POLICE_AJOUTER_PIECE = "Ajouter une pièce";
    public const PISTE_LISTER_MISSION = "Voire les missions";
    public const PISTE_LISTER_CONTACT = "Voire les contacts";
    public const PISTE_LISTER_COTATION = "Voire les cotations";
    public const POLICE_LISTER_PIECE = "Voire les pièces";

    public const CROSSED_ENTITY_ACTION = "action";
    public const CROSSED_ENTITY_COTATION = "cotation";
    public const CROSSED_ENTITY_ETAPE_CRM = "etape";
    public const CROSSED_ENTITY_PISTE = "piste";
    public const CROSSED_ENTITY_POLICE = "police";

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
            ->setEntityId(null)
            ->generateUrl();
        return $url;
    }

    public function crossCanal_Cotation_ajouterPiece(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        $entite = $context->getEntity()->getInstance();
        $url = $adminUrlGenerator
            ->setController(DocPieceCrudController::class)
            ->setAction(Action::NEW)
            ->set("titre", "NOUVELLE PIECE - [Cotation: " . $entite . "]")
            ->set(self::CROSSED_ENTITY_COTATION, $entite->getId())
            ->setEntityId(null)
            ->generateUrl();
        return $url;
    }

    public function crossCanal_Police_ajouterPiece(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        $entite = $context->getEntity()->getInstance();
        $url = $adminUrlGenerator
            ->setController(DocPieceCrudController::class)
            ->setAction(Action::NEW)
            ->set("titre", "NOUVELLE PIECE - [Police: " . $entite . "]")
            ->set(self::CROSSED_ENTITY_POLICE, $entite->getId())
            ->setEntityId(null)
            ->generateUrl();
        return $url;
    }

    public function crossCanal_EtapeCRM_ajouterPiste(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        $entite = $context->getEntity()->getInstance();
        //dd($entite);
        $url = $adminUrlGenerator
            ->setController(PisteCrudController::class)
            ->setAction(Action::NEW)
            ->set("titre", "NOUVELLE PISTE - [Etape: " . $entite . "]")
            ->set(self::CROSSED_ENTITY_ETAPE_CRM, $entite->getId())
            ->setEntityId(null)
            ->generateUrl();
        //dd($url);
        return $url;
    }

    public function crossCanal_Piste_ajouterMission(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        $entite = $context->getEntity()->getInstance();
        //dd($entite);
        $url = $adminUrlGenerator
            ->setController(ActionCRMCrudController::class)
            ->setAction(Action::NEW)
            ->set("titre", "NOUVELLE MISSION - [Piste: " . $entite . "]")
            ->set(self::CROSSED_ENTITY_PISTE, $entite->getId())
            ->setEntityId(null)
            ->generateUrl();
        //dd($url);
        return $url;
    }

    public function crossCanal_Piste_ajouterCotation(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        $entite = $context->getEntity()->getInstance();
        //dd($entite);
        $url = $adminUrlGenerator
            ->setController(CotationCrudController::class)
            ->setAction(Action::NEW)
            ->set("titre", "NOUVELLE COTATION - [Piste: " . $entite . "]")
            ->set(self::CROSSED_ENTITY_PISTE, $entite->getId())
            ->setEntityId(null)
            ->generateUrl();
        //dd($url);
        return $url;
    }

    public function crossCanal_Piste_ajouterContact(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        $entite = $context->getEntity()->getInstance();
        //dd($entite);
        $url = $adminUrlGenerator
            ->setController(ContactCrudController::class)
            ->setAction(Action::NEW)
            ->set("titre", "NOUVAU CONTACT - [Piste: " . $entite . "]")
            ->set(self::CROSSED_ENTITY_PISTE, $entite->getId())
            ->setEntityId(null)
            ->generateUrl();
        //dd($url);
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
            ->setEntityId(null)
            ->generateUrl();

        return $url;
    }

    public function crossCanal_Piste_listerMission(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        $entite = $context->getEntity()->getInstance();
        $url = $adminUrlGenerator
            ->setController(ActionCRMCrudController::class)
            ->setAction(Action::INDEX)
            ->set("titre", "LISTE DES MISSIONS - [Piste: " . $entite . "]")
            ->set('filters[' . self::CROSSED_ENTITY_PISTE . '][value]', $entite->getId()) //il faut juste passer son ID
            ->set('filters[' . self::CROSSED_ENTITY_PISTE . '][comparison]', '=')
            ->setEntityId(null)
            ->generateUrl();

        return $url;
    }

    public function crossCanal_Piste_listerContact(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        $entite = $context->getEntity()->getInstance();
        $url = $adminUrlGenerator
            ->setController(ContactCrudController::class)
            ->setAction(Action::INDEX)
            ->set("titre", "LISTE DES CONTACTS - [Piste: " . $entite . "]")
            ->set('filters[' . self::CROSSED_ENTITY_PISTE . '][value]', [$entite->getId()]) //il faut juste passer son ID
            ->set('filters[' . self::CROSSED_ENTITY_PISTE . '][comparison]', ComparisonType::EQ) //'='
            ->setEntityId(null)
            ->generateUrl();

        return $url;
    }

    public function crossCanal_Piste_listerCotation(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        $entite = $context->getEntity()->getInstance();
        $url = $adminUrlGenerator
            ->setController(CotationCrudController::class)
            ->setAction(Action::INDEX)
            ->set("titre", "LISTE DES COTATIONS - [Piste: " . $entite . "]")
            ->set('filters[' . self::CROSSED_ENTITY_PISTE . '][value]', $entite->getId()) //il faut juste passer son ID
            ->set('filters[' . self::CROSSED_ENTITY_PISTE . '][comparison]', ComparisonType::EQ) //'='
            ->setEntityId(null)
            ->generateUrl();

        return $url;
    }

    public function crossCanal_Cotation_listerPiece(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        $entite = $context->getEntity()->getInstance();
        $url = $adminUrlGenerator
            ->setController(DocPieceCrudController::class)
            ->setAction(Action::INDEX)
            ->set("titre", "LISTE DES PICES - [Cotation: " . $entite . "]")
            ->set('filters[' . self::CROSSED_ENTITY_COTATION . '][value]', $entite->getId()) //il faut juste passer son ID
            ->set('filters[' . self::CROSSED_ENTITY_COTATION . '][comparison]', '=')
            ->setEntityId(null)
            ->generateUrl();

        return $url;
    }

    public function crossCanal_Police_listerPiece(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        $entite = $context->getEntity()->getInstance();
        $url = $adminUrlGenerator
            ->setController(DocPieceCrudController::class)
            ->setAction(Action::INDEX)
            ->set("titre", "LISTE DES PICES - [Police: " . $entite . "]")
            ->set('filters[' . self::CROSSED_ENTITY_POLICE . '][value]', $entite->getId()) //il faut juste passer son ID
            ->set('filters[' . self::CROSSED_ENTITY_POLICE . '][comparison]', '=')
            ->setEntityId(null)
            ->generateUrl();

        return $url;
    }

    public function crossCanal_EtapeCRM_listerPiste(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        $entite = $context->getEntity()->getInstance();
        $url = $adminUrlGenerator
            ->setController(PisteCrudController::class)
            ->setAction(Action::INDEX)
            ->set("titre", "LISTE DES PISTES - [Etape: " . $entite . "]")
            ->set('filters[' . self::CROSSED_ENTITY_ETAPE_CRM . '][value]', $entite->getId()) //il faut juste passer son ID
            ->set('filters[' . self::CROSSED_ENTITY_ETAPE_CRM . '][comparison]', '=')
            ->setEntityId(null)
            ->generateUrl();
        //dd($entite);
        return $url;
    }

    public function crossCanal_Action_setAction(FeedbackCRM $feedbackCRM, AdminUrlGenerator $adminUrlGenerator): FeedbackCRM
    {
        $actionCRM = null;
        $paramIDAction = $adminUrlGenerator->get(self::CROSSED_ENTITY_ACTION);
        if ($paramIDAction != null) {
            $actionCRM = $this->entityManager->getRepository(ActionCRM::class)->find($paramIDAction);
            $feedbackCRM->setAction($actionCRM);
        }
        return $feedbackCRM;
    }

    public function crossCanal_Piece_setCotation(DocPiece $docPiece, AdminUrlGenerator $adminUrlGenerator): DocPiece
    {
        $objet = null;
        $paramIDAction = $adminUrlGenerator->get(self::CROSSED_ENTITY_COTATION);
        if ($paramIDAction != null) {
            $objet = $this->entityManager->getRepository(Cotation::class)->find($paramIDAction);
            $docPiece->setCotation($objet);
        }
        return $docPiece;
    }

    public function crossCanal_Piece_setPolice(DocPiece $docPiece, AdminUrlGenerator $adminUrlGenerator): DocPiece
    {
        $objet = null;
        $paramIDAction = $adminUrlGenerator->get(self::CROSSED_ENTITY_POLICE);
        if ($paramIDAction != null) {
            $objet = $this->entityManager->getRepository(Police::class)->find($paramIDAction);
            $docPiece->setPolice($objet);
        }
        return $docPiece;
    }

    public function crossCanal_Etape_setEtape(Piste $piste, AdminUrlGenerator $adminUrlGenerator): Piste
    {
        $objet = null;
        $paramID = $adminUrlGenerator->get(self::CROSSED_ENTITY_ETAPE_CRM);
        if ($paramID != null) {
            $objet = $this->entityManager->getRepository(EtapeCrm::class)->find($paramID);
            $piste->setEtape($objet);
        }
        return $piste;
    }

    public function crossCanal_Piste_setPiste(Contact $contact, AdminUrlGenerator $adminUrlGenerator): Contact
    {
        $objet = null;
        $paramID = $adminUrlGenerator->get(self::CROSSED_ENTITY_PISTE);
        if ($paramID != null) {
            $objet = $this->entityManager->getRepository(Piste::class)->find($paramID);
            $contact->addPiste($objet);
        }
        return $contact;
    }

    public function crossCanal_Cotation_setPiste(Cotation $contact, AdminUrlGenerator $adminUrlGenerator): Cotation
    {
        $objet = null;
        $paramID = $adminUrlGenerator->get(self::CROSSED_ENTITY_PISTE);
        if ($paramID != null) {
            $objet = $this->entityManager->getRepository(Piste::class)->find($paramID);
            $contact->setPiste($objet);
        }
        return $contact;
    }

    public function crossCanal_Mission_setPiste(ActionCRM $actionCRM, AdminUrlGenerator $adminUrlGenerator): ActionCRM
    {
        $objet = null;
        $paramID = $adminUrlGenerator->get(self::CROSSED_ENTITY_PISTE);
        if ($paramID != null) {
            $objet = $this->entityManager->getRepository(Piste::class)->find($paramID);
            $actionCRM->setPiste($objet);
        }
        return $actionCRM;
    }

    public function crossCanal_setTitrePage(Crud $crud, AdminUrlGenerator $adminUrlGenerator): Crud
    {
        $crud->setPageTitle(Crud::PAGE_INDEX, $adminUrlGenerator->get("titre"));
        $crud->setPageTitle(Crud::PAGE_DETAIL, $adminUrlGenerator->get("titre"));
        $crud->setPageTitle(Crud::PAGE_NEW, $adminUrlGenerator->get("titre"));
        return $crud;
    }
}
