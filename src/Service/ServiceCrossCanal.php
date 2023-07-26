<?php

namespace App\Service;

use App\Entity\Taxe;
use NumberFormatter;
use App\Entity\Piste;
use App\Entity\Client;
use App\Entity\Police;
use DateTimeImmutable;
use App\Entity\Contact;
use App\Entity\Monnaie;
use App\Entity\Cotation;
use App\Entity\DocPiece;
use App\Entity\EtapeCrm;
use App\Entity\Sinistre;
use App\Entity\ActionCRM;
use App\Entity\Entreprise;
use App\Entity\FeedbackCRM;
use App\Entity\Utilisateur;
use App\Entity\PaiementTaxe;
use Doctrine\ORM\QueryBuilder;
use App\Entity\PaiementCommission;
use App\Entity\PaiementPartenaire;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Bundle\SecurityBundle\Security;
use App\Controller\Admin\PisteCrudController;
use App\Controller\Admin\ClientCrudController;
use App\Controller\Admin\PoliceCrudController;
use App\Controller\Admin\ContactCrudController;
use App\Controller\Admin\MonnaieCrudController;
use App\Controller\Admin\CotationCrudController;
use App\Controller\Admin\DocPieceCrudController;
use App\Controller\Admin\EtapeCrmCrudController;
use App\Controller\Admin\SinistreCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use App\Controller\Admin\ActionCRMCrudController;
use Symfony\Component\Validator\Constraints\Date;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use App\Controller\Admin\FeedbackCRMCrudController;
use App\Controller\Admin\PaiementTaxeCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use App\Controller\Admin\PaiementCommissionCrudController;
use App\Controller\Admin\PaiementPartenaireCrudController;
use App\Entity\EtapeSinistre;
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
    public const COTATION_POLICE_OUVRIR = "Voire la police";
    public const COTATION_CLIENT_OUVRIR = "Voire le client";
    public const COTATION_POLICE_CREER = "Créer la police";
    public const COTATION_CLIENT_CREER = "Créer le client";
    public const PISTE_AJOUTER_MISSION = "Ajouter une mission";
    public const PISTE_AJOUTER_CONTACT = "Ajouter un contact";
    public const PISTE_AJOUTER_COTATION = "Ajouter une cotation";
    public const POLICE_AJOUTER_PIECE = "Ajouter une pièce";
    public const PISTE_LISTER_MISSION = "Voire les missions";
    public const PISTE_LISTER_CONTACT = "Voire les contacts";
    public const PISTE_LISTER_COTATION = "Voire les cotations";
    public const POLICE_LISTER_PIECE = "Voire les pièces";
    public const POLICE_LISTER_POP_COMMISSIONS = "Voire les Pdp Comm";
    public const POLICE_LISTER_POP_PARTENAIRES = "Voir les Pdp Partenaire";
    public const POLICE_LISTER_POP_TAXES = "Voir les Pdp Taxe";
    public const POLICE_LISTER_SINISTRES = "Voir les sinistres";
    public const POLICE_AJOUTER_POP_COMMISSIONS = "Encaisser la Comm";
    public const POLICE_AJOUTER_POP_PARTENAIRES = "Payer Partenaire";
    public const POLICE_AJOUTER_POP_TAXES = "Payer Taxe";
    public const POLICE_AJOUTER_SINISTRE = "Ajouter un sinistre";
    public const CLIENT_LISTER_POLICES = "Voire les polices";
    public const CLIENT_LISTER_COTATIONS = "Voire les cotations";

    public const CROSSED_ENTITY_ACTION = "action";
    public const CROSSED_ENTITY_COTATION = "cotation";
    public const CROSSED_ENTITY_CLIENT = "client";
    public const CROSSED_ENTITY_ETAPE_CRM = "etape";
    public const CROSSED_ENTITY_ETAPE_SINISTRE = "etape";
    public const CROSSED_ENTITY_PISTE = "piste";
    public const CROSSED_ENTITY_POLICE = "police";
    public const CROSSED_ENTITY_POP_COMMISSIONS = "police";
    public const CROSSED_ENTITY_PRODUIT = "produit";
    public const CROSSED_ENTITY_PARTENAIRE = "partenaire";
    public const CROSSED_ENTITY_ASSUREUR = "assureur";
    public const CROSSED_ENTITY_TAXE = "taxe";

    public function __construct(
        private EntityManagerInterface $entityManager,
        private ServiceCalculateur $serviceCalculateur
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

    public function crossCanal_Cotation_creerPolice(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        $entite = $context->getEntity()->getInstance();
        $url = $adminUrlGenerator
            ->setController(PoliceCrudController::class)
            ->setAction(Action::NEW)
            ->set("titre", "NOUVELLE POLICE - [Cotation: " . $entite . "]")
            ->set(self::CROSSED_ENTITY_COTATION, $entite->getId())
            ->setEntityId(null)
            ->generateUrl();
        return $url;
    }

    public function crossCanal_Cotation_creerClient(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        $entite = $context->getEntity()->getInstance();
        $url = $adminUrlGenerator
            ->setController(ClientCrudController::class)
            ->setAction(Action::NEW)
            ->set("titre", "NOUVEAU CLIENT - [Cotation: " . $entite . "]")
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

    public function crossCanal_Police_ajouterPOPComm(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        $entite = $context->getEntity()->getInstance();
        $url = $adminUrlGenerator
            ->setController(PaiementCommissionCrudController::class)
            ->setAction(Action::NEW)
            ->set("titre", "NOUVELLE PDP COMMISSION - [Police: " . $entite . "]")
            ->set(self::CROSSED_ENTITY_POLICE, $entite->getId())
            ->setEntityId(null)
            ->generateUrl();
        return $url;
    }

    public function crossCanal_Police_ajouterPOPRetroComm(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        $entite = $context->getEntity()->getInstance();
        $url = $adminUrlGenerator
            ->setController(PaiementPartenaireCrudController::class)
            ->setAction(Action::NEW)
            ->set("titre", "NOUVELLE PDP PARTENAIRE - [Police: " . $entite . "]")
            ->set(self::CROSSED_ENTITY_POLICE, $entite->getId())
            ->setEntityId(null)
            ->generateUrl();
        return $url;
    }

    public function crossCanal_Police_ajouterPOPTaxe(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, Taxe $taxe)
    {
        $entite = $context->getEntity()->getInstance();
        $url = $adminUrlGenerator
            ->setController(PaiementTaxeCrudController::class)
            ->setAction(Action::NEW)
            ->set("titre", "NOUVELLE PDP TAXE - [Police: " . $entite . "]")
            ->set(self::CROSSED_ENTITY_POLICE, $entite->getId())
            ->set(self::CROSSED_ENTITY_TAXE, $taxe->getId())
            ->setEntityId(null)
            ->generateUrl();
        return $url;
    }

    public function crossCanal_Police_ajouterSinistre(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        $entite = $context->getEntity()->getInstance();
        $url = $adminUrlGenerator
            ->setController(SinistreCrudController::class)
            ->setAction(Action::NEW)
            ->set("titre", "NOUVEAU SINISTRE - [Police: " . $entite . "]")
            ->set(self::CROSSED_ENTITY_POLICE, $entite->getId())
            //->set(self::CROSSED_ENTITY_TAXE, $taxe->getId())
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
            ->set("titre", "LISTE DES PIECES - [Cotation: " . $entite . "]")
            ->set('filters[' . self::CROSSED_ENTITY_COTATION . '][value]', $entite->getId()) //il faut juste passer son ID
            ->set('filters[' . self::CROSSED_ENTITY_COTATION . '][comparison]', '=')
            ->setEntityId(null)
            ->generateUrl();

        return $url;
    }

    public function crossCanal_Client_listerPolice(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        $entite = $context->getEntity()->getInstance();
        $url = $adminUrlGenerator
            ->setController(PoliceCrudController::class)
            ->setAction(Action::INDEX)
            ->set("titre", "LISTE DES POLICES - [Cient: " . $entite . "]")
            ->set('filters[' . self::CROSSED_ENTITY_CLIENT . '][value]', $entite->getId()) //il faut juste passer son ID
            ->set('filters[' . self::CROSSED_ENTITY_CLIENT . '][comparison]', '=')
            ->setEntityId(null)
            ->generateUrl();

        return $url;
    }

    public function crossCanal_Produit_listerPolice(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        $entite = $context->getEntity()->getInstance();
        $url = $adminUrlGenerator
            ->setController(PoliceCrudController::class)
            ->setAction(Action::INDEX)
            ->set("titre", "LISTE DES POLICES - [Produit: " . $entite . "]")
            ->set('filters[' . self::CROSSED_ENTITY_PRODUIT . '][value]', $entite->getId()) //il faut juste passer son ID
            ->set('filters[' . self::CROSSED_ENTITY_PRODUIT . '][comparison]', '=')
            ->setEntityId(null)
            ->generateUrl();

        return $url;
    }

    public function crossCanal_Partenaire_listerPolice(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        $entite = $context->getEntity()->getInstance();
        $url = $adminUrlGenerator
            ->setController(PoliceCrudController::class)
            ->setAction(Action::INDEX)
            ->set("titre", "LISTE DES POLICES - [Partenaire: " . $entite . "]")
            ->set('filters[' . self::CROSSED_ENTITY_PARTENAIRE . '][value]', $entite->getId()) //il faut juste passer son ID
            ->set('filters[' . self::CROSSED_ENTITY_PARTENAIRE . '][comparison]', '=')
            ->setEntityId(null)
            ->generateUrl();

        return $url;
    }

    public function crossCanal_Assureur_listerPolice(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        $entite = $context->getEntity()->getInstance();
        $url = $adminUrlGenerator
            ->setController(PoliceCrudController::class)
            ->setAction(Action::INDEX)
            ->set("titre", "LISTE DES POLICES - [Assureur: " . $entite . "]")
            ->set('filters[' . self::CROSSED_ENTITY_ASSUREUR . '][value]', $entite->getId()) //il faut juste passer son ID
            ->set('filters[' . self::CROSSED_ENTITY_ASSUREUR . '][comparison]', '=')
            ->setEntityId(null)
            ->generateUrl();

        return $url;
    }

    public function crossCanal_Assureur_listerCotation(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        $entite = $context->getEntity()->getInstance();
        $url = $adminUrlGenerator
            ->setController(CotationCrudController::class)
            ->setAction(Action::INDEX)
            ->set("titre", "LISTE DES COTATIONS - [Assureur: " . $entite . "]")
            ->set('filters[' . self::CROSSED_ENTITY_ASSUREUR . '][value]', $entite->getId()) //il faut juste passer son ID
            ->set('filters[' . self::CROSSED_ENTITY_ASSUREUR . '][comparison]', '=')
            ->setEntityId(null)
            ->generateUrl();

        return $url;
    }

    public function crossCanal_Client_listerCotation(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        $entite = $context->getEntity()->getInstance();
        $url = $adminUrlGenerator
            ->setController(CotationCrudController::class)
            ->setAction(Action::INDEX)
            ->set("titre", "LISTE DES COTATIONS - [Cient: " . $entite . "]")
            ->set('filters[' . self::CROSSED_ENTITY_CLIENT . '][value]', $entite->getId()) //il faut juste passer son ID
            ->set('filters[' . self::CROSSED_ENTITY_CLIENT . '][comparison]', '=')
            ->setEntityId(null)
            ->generateUrl();

        return $url;
    }

    public function crossCanal_Produit_listerCotation(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        $entite = $context->getEntity()->getInstance();
        $url = $adminUrlGenerator
            ->setController(CotationCrudController::class)
            ->setAction(Action::INDEX)
            ->set("titre", "LISTE DES COTATIONS - [Produit: " . $entite . "]")
            ->set('filters[' . self::CROSSED_ENTITY_PRODUIT . '][value]', $entite->getId()) //il faut juste passer son ID
            ->set('filters[' . self::CROSSED_ENTITY_PRODUIT . '][comparison]', '=')
            ->setEntityId(null)
            ->generateUrl();

        return $url;
    }

    public function crossCanal_Cotation_ouvrirPolice(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        $entite = $context->getEntity()->getInstance();
        $url = $adminUrlGenerator
            ->setController(PoliceCrudController::class)
            ->setAction(Action::DETAIL)
            ->set("titre", $entite->getPolice())
            ->setEntityId($entite->getPolice()->getId())
            ->generateUrl();

        return $url;
    }

    public function crossCanal_Cotation_ouvrirClient(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        $entite = $context->getEntity()->getInstance();
        $url = $adminUrlGenerator
            ->setController(ClientCrudController::class)
            ->setAction(Action::DETAIL)
            ->set("titre", $entite->getClient())
            ->setEntityId($entite->getClient()->getId())
            ->generateUrl();

        return $url;
    }

    public function crossCanal_Police_listerPiece(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        $entite = $context->getEntity()->getInstance();
        $url = $adminUrlGenerator
            ->setController(DocPieceCrudController::class)
            ->setAction(Action::INDEX)
            ->set("titre", "LISTE DES PIECES - [Police: " . $entite . "]")
            ->set('filters[' . self::CROSSED_ENTITY_POLICE . '][value]', $entite->getId()) //il faut juste passer son ID
            ->set('filters[' . self::CROSSED_ENTITY_POLICE . '][comparison]', '=')
            ->setEntityId(null)
            ->generateUrl();

        return $url;
    }

    public function crossCanal_Police_listerPOPComm(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        $entite = $context->getEntity()->getInstance();
        $url = $adminUrlGenerator
            ->setController(PaiementCommissionCrudController::class)
            ->setAction(Action::INDEX)
            ->set("titre", "LISTE DES PDP COMMISSIONS - [Police: " . $entite . "]")
            ->set('filters[' . self::CROSSED_ENTITY_POLICE . '][value]', $entite->getId()) //il faut juste passer son ID
            ->set('filters[' . self::CROSSED_ENTITY_POLICE . '][comparison]', '=')
            ->setEntityId(null)
            ->generateUrl();

        return $url;
    }

    public function crossCanal_Police_listerPOPRetroComm(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        $entite = $context->getEntity()->getInstance();
        $url = $adminUrlGenerator
            ->setController(PaiementPartenaireCrudController::class)
            ->setAction(Action::INDEX)
            ->set("titre", "LISTE DES PDP PARTENAIRES - [Police: " . $entite . "]")
            ->set('filters[' . self::CROSSED_ENTITY_POLICE . '][value]', $entite->getId()) //il faut juste passer son ID
            ->set('filters[' . self::CROSSED_ENTITY_POLICE . '][comparison]', '=')
            ->setEntityId(null)
            ->generateUrl();

        return $url;
    }

    public function crossCanal_Police_listerSinistre(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        $entite = $context->getEntity()->getInstance();
        $url = $adminUrlGenerator
            ->setController(SinistreCrudController::class)
            ->setAction(Action::INDEX)
            ->set("titre", "LISTE DES SINISTRES - [Police: " . $entite . "]")
            ->set('filters[' . self::CROSSED_ENTITY_POLICE . '][value]', $entite->getId()) //il faut juste passer son ID
            ->set('filters[' . self::CROSSED_ENTITY_POLICE . '][comparison]', '=')
            ->setEntityId(null)
            ->generateUrl();

        return $url;
    }

    public function crossCanal_EtapeSinistre_listerSinistre(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        /** @var EtapeSinistre */
        $entite = $context->getEntity()->getInstance();
        $url = $adminUrlGenerator
            ->setController(SinistreCrudController::class)
            ->setAction(Action::INDEX)
            ->set("titre", "LISTE DES SINISTRES - [Etape: " . $entite . "]")
            ->set('filters[' . self::CROSSED_ENTITY_ETAPE_SINISTRE . '][value]', $entite->getId()) //il faut juste passer son ID
            ->set('filters[' . self::CROSSED_ENTITY_ETAPE_SINISTRE . '][comparison]', '=')
            ->setEntityId(null)
            ->generateUrl();

        return $url;
    }

    public function crossCanal_Police_listerPOPTaxe(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, Taxe $taxe)
    {
        $entite = $context->getEntity()->getInstance();
        $url = $adminUrlGenerator
            ->setController(PaiementTaxeCrudController::class)
            ->setAction(Action::INDEX)
            ->set("titre", "LISTE DES PDP TAXE - [Police: " . $entite . "] & [Taxe: " . $taxe . "]")
            ->set('filters[' . self::CROSSED_ENTITY_POLICE . '][value]', $entite->getId()) //il faut juste passer son ID
            ->set('filters[' . self::CROSSED_ENTITY_POLICE . '][comparison]', '=')
            ->set('filters[' . self::CROSSED_ENTITY_TAXE . '][value]', $taxe->getId()) //il faut juste passer son ID
            ->set('filters[' . self::CROSSED_ENTITY_TAXE . '][comparison]', '=')
            ->setEntityId(null)
            ->generateUrl();

        return $url;
    }

    public function crossCanal_Partenaire_listerPOPRetroComm(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        $entite = $context->getEntity()->getInstance();
        $url = $adminUrlGenerator
            ->setController(PaiementPartenaireCrudController::class)
            ->setAction(Action::INDEX)
            ->set("titre", "LISTE DES PDP PARTENAIRES - [Partenaire: " . $entite . "]")
            ->set('filters[' . self::CROSSED_ENTITY_PARTENAIRE . '][value]', $entite->getId()) //il faut juste passer son ID
            ->set('filters[' . self::CROSSED_ENTITY_PARTENAIRE . '][comparison]', '=')
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
            $docPiece->setCotation($objet->getCotation());
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

    public function crossCanal_Police_setCotation(Police $police, AdminUrlGenerator $adminUrlGenerator): Police
    {
        $objet = null;
        $paramID = $adminUrlGenerator->get(self::CROSSED_ENTITY_COTATION);
        if ($paramID != null) {
            $objet = $this->entityManager->getRepository(Cotation::class)->find($paramID);
            $police->setCotation($objet);
            $police->setProduit($objet->getProduit());
            $police->setAssureur($objet->getAssureur());
            $police->setClient($objet->getClient());
        }
        return $police;
    }

    public function crossCanal_POPComm_setPolice(PaiementCommission $paiementCommission, AdminUrlGenerator $adminUrlGenerator): PaiementCommission
    {
        $objet = null;
        $paramID = $adminUrlGenerator->get(self::CROSSED_ENTITY_POLICE);
        if ($paramID != null) {
            $objet = $this->entityManager->getRepository(Police::class)->find($paramID);
            //On calcule d'abord les champs calculables
            $this->serviceCalculateur->updatePoliceCalculableFileds($objet);
            $paiementCommission->setPolice($objet);
            $paiementCommission->setMontant($objet->calc_revenu_ttc_solde_restant_du);//calc_revenu_ttc_solde_restant_du
        }
        return $paiementCommission;
    }

    public function crossCanal_POPTaxe_setPolice(PaiementTaxe $paiementTaxe, AdminUrlGenerator $adminUrlGenerator): PaiementTaxe
    {
        $police = null;
        $paramPoliceID = $adminUrlGenerator->get(self::CROSSED_ENTITY_POLICE);
        $paramTaxeID = $adminUrlGenerator->get(self::CROSSED_ENTITY_TAXE);
        if ($paramPoliceID != null && $paramTaxeID != null) {
            /** @var Police */
            $police = $this->entityManager->getRepository(Police::class)->find($paramPoliceID);
            /** @var Taxe */
            $taxe = $this->entityManager->getRepository(Taxe::class)->find($paramTaxeID);
            //On calcule d'abord les champs calculables
            $this->serviceCalculateur->updatePoliceCalculableFileds($police);
            $paiementTaxe->setPolice($police);
            $paiementTaxe->setTaxe($taxe);
            $paiementTaxe->setExercice(Date("Y"));
            if($taxe->isPayableparcourtier() == true){
                $paiementTaxe->setMontant($police->calc_taxes_courtier_solde);
            }else{
                $paiementTaxe->setMontant($police->calc_taxes_assureurs_solde);
            }
        }
        return $paiementTaxe;
    }

    public function crossCanal_Sinistre_setPolice(Sinistre $sinistre, AdminUrlGenerator $adminUrlGenerator): Sinistre
    {
        $police = null;
        $paramPoliceID = $adminUrlGenerator->get(self::CROSSED_ENTITY_POLICE);
        //$paramTaxeID = $adminUrlGenerator->get(self::CROSSED_ENTITY_TAXE);
        if ($paramPoliceID != null) {
            /** @var Police */
            $police = $this->entityManager->getRepository(Police::class)->find($paramPoliceID);
            //On calcule d'abord les champs calculables
            $this->serviceCalculateur->updatePoliceCalculableFileds($police);
            $sinistre->setPolice($police);
            $sinistre->setOccuredAt(new \DateTimeImmutable("now"));
            $sinistre->setCout(0);
            $sinistre->setMontantPaye(0);
            $sinistre->setTitre("SIN" . Date("dmYHis") . " / " . $police);
            $sinistre->setNumero("TMPSIN" . Date("dmYHis"));
        }
        return $sinistre;
    }


    public function crossCanal_POPRetroComm_setPolice(PaiementPartenaire $paiementPartenaire, AdminUrlGenerator $adminUrlGenerator): PaiementPartenaire
    {
        $objet = null;
        $paramID = $adminUrlGenerator->get(self::CROSSED_ENTITY_POLICE);
        if ($paramID != null) {
            $objet = $this->entityManager->getRepository(Police::class)->find($paramID);
            //On calcule d'abord les champs calculables
            $this->serviceCalculateur->updatePoliceCalculableFileds($objet);
            $paiementPartenaire->setPolice($objet);
            $paiementPartenaire->setPartenaire($objet->getPartenaire());
            $paiementPartenaire->setMontant($objet->calc_retrocom_solde);
        }
        return $paiementPartenaire;
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

    public function crossCanal_Client_setCotation(Client $client, AdminUrlGenerator $adminUrlGenerator): Client
    {
        $objet = null;
        $paramID = $adminUrlGenerator->get(self::CROSSED_ENTITY_COTATION);
        if ($paramID != null) {
            $objet = $this->entityManager->getRepository(Cotation::class)->find($paramID);
            $client->addCotation($objet);
        }
        return $client;
    }

    public function crossCanal_setTitrePage(Crud $crud, AdminUrlGenerator $adminUrlGenerator): Crud
    {
        $crud->setPageTitle(Crud::PAGE_INDEX, $adminUrlGenerator->get("titre"));
        $crud->setPageTitle(Crud::PAGE_DETAIL, $adminUrlGenerator->get("titre"));
        $crud->setPageTitle(Crud::PAGE_NEW, $adminUrlGenerator->get("titre"));
        return $crud;
    }
}
