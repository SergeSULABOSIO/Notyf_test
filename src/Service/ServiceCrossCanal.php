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
use App\Controller\Admin\AutomobileCrudController;
use App\Controller\Admin\ExpertCrudController;
use Symfony\Component\Validator\Constraints\Date;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use App\Controller\Admin\FeedbackCRMCrudController;
use App\Controller\Admin\PaiementTaxeCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use App\Controller\Admin\PaiementCommissionCrudController;
use App\Controller\Admin\PaiementPartenaireCrudController;
use App\Controller\Admin\VictimeCrudController;
use App\Entity\Automobile;
use App\Entity\DocCategorie;
use App\Entity\DocClasseur;
use App\Entity\EtapeSinistre;
use App\Entity\Expert;
use App\Entity\Victime;
use EasyCorp\Bundle\EasyAdminBundle\Form\Type\ComparisonType;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

use function PHPUnit\Framework\containsEqual;

class ServiceCrossCanal
{
    //code reporting
    public const REPORTING_CODE_UNPAID_COM = 0;
    public const REPORTING_CODE_UNPAID_RETROCOM = 1;
    public const REPORTING_CODE_UNPAID_TAXE_COURTIER = 2;
    public const REPORTING_CODE_UNPAID_TAXE_ASSUREUR = 3;
    public const REPORTING_CODE_PAID_COM = 100;
    public const REPORTING_CODE_PAID_RETROCOM = 101;
    public const REPORTING_CODE_PAID_TAXE_COURTIER = 102;
    public const REPORTING_CODE_PAID_TAXE_ASSUREUR = 103;


    //Feedback
    public const OPTION_FEEDBACK_AJOUTER = "Ajouter un feedback";
    public const OPTION_FEEDBACK_LISTER = "Voire les feedbacks";
    //Piece
    public const OPTION_PIECE_AJOUTER = "Ajouter une pièce";
    public const OPTION_PIECE_LISTER = "Voire les pièces";
    public const OPTION_PIECE_ATTACHER = "Attacher une pièce";
    //Automobile
    public const OPTION_AUTOMOBILE_AJOUTER = "Ajouter un engin";
    public const OPTION_AUTOMOBILE_LISTER = "Voire les engins";
    //Piste
    public const OPTION_PISTE_AJOUTER = "Ajouter une piste";
    public const OPTION_PISTE_LISTER = "Voire les pistes";
    //Police
    public const OPTION_POLICE_LISTER = "Voire les polices";
    public const OPTION_POLICE_OUVRIR = "Voire la police";
    public const OPTION_POLICE_CREER = "Créer la police";
    //Client
    public const OPTION_CLIENT_OUVRIR = "Voire le client";
    public const OPTION_CLIENT_CREER = "Créer le client";
    //Mission
    public const OPTION_MISSION_AJOUTER = "Ajouter une mission";
    public const OPTION_MISSION_LISTER = "Voire les missions";
    //Contact
    public const OPTION_CONTACT_AJOUTER = "Ajouter un contact";
    public const OPTION_CONTACT_LISTER = "Voire les contacts";
    //Cotation
    public const OPTION_COTATION_AJOUTER = "Ajouter une cotation";
    public const OPTION_COTATION_LISTER = "Voire les cotations";
    //POP Commissions
    public const OPTION_POP_COMMISSION_LISTER = "Voire les Pdp Comm";
    public const OPTION_POP_COMMISSION_AJOUTER = "Encaisser la Comm";
    //POP Partenaire
    public const OPTION_POP_PARTENAIRE_LISTER = "Voir les Pdp Partenaire";
    public const OPTION_POP_PARTENAIRE_AJOUTER = "Payer Partenaire";
    //POP Taxe
    public const OPTION_POP_TAXE_LISTER = "Voir les Pdp Taxe";
    public const OPTION_POP_TAXE_AJOUTER = "Payer Taxe";
    //Sinistre
    public const OPTION_SINISTRE_LISTER = "Voir les sinistres";
    public const OPTION_SINISTRE_AJOUTER = "Ajouter un sinistre";
    //Expert Sinistre
    public const OPTION_EXPERT_AJOUTER = "Ajouter un expert";
    public const OPTION_EXPERT_LISTER = "Lister les experts";
    //Victime Sinistre
    public const OPTION_VICTIME_AJOUTER = "Ajouter une victime";
    public const OPTION_VICTIME_LISTER = "Lister les victimes";


    public const CROSSED_ENTITY_ACTION = "action";
    public const CROSSED_ENTITY_COTATION = "cotation";
    public const CROSSED_ENTITY_CLIENT = "client";
    public const CROSSED_ENTITY_ETAPE_CRM = "etape";
    public const CROSSED_ENTITY_ETAPE_SINISTRE = "etape";
    public const CROSSED_ENTITY_EXPERT = "expert";
    public const CROSSED_ENTITY_PISTE = "piste";
    public const CROSSED_ENTITY_POLICE = "police";
    public const CROSSED_ENTITY_CATEGORIE = "categorie";
    public const CROSSED_ENTITY_CLASSEUR = "classeur";
    public const CROSSED_ENTITY_DOC_PIECE = "piece";
    public const CROSSED_ENTITY_POP_COMMISSIONS = "paiementCommissions";
    public const CROSSED_ENTITY_POP_PARTENAIRE = "paiementPartenaires";
    public const CROSSED_ENTITY_POP_TAXE = "paiementTaxes";
    public const CROSSED_ENTITY_PRODUIT = "produit";
    public const CROSSED_ENTITY_PARTENAIRE = "partenaire";
    public const CROSSED_ENTITY_ASSUREUR = "assureur";
    public const CROSSED_ENTITY_TAXE = "taxe";
    public const CROSSED_ENTITY_SINISTRE = "sinistre";

    public function __construct(
        private EntityManagerInterface $entityManager,
        private ServiceCalculateur $serviceCalculateur,
        private ServiceEntreprise $serviceEntreprise,
        private ServiceMonnaie $serviceMonnaie
    ) {
    }

    public function crossCanal_Action_ajouterFeedback(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        /** @var ActionCRM */
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
        /** @var Cotation */
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
        /** @var Cotation */
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
        /** @var Cotation */
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
        /** @var Police */
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

    public function crossCanal_POPCom_attacherPiece(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        /** @var PaiementCommission */
        $entite = $context->getEntity()->getInstance();
        $url = $adminUrlGenerator
            ->setController(DocPieceCrudController::class)
            ->setAction(Action::NEW)
            ->set("titre", "NOUVELLE PIECE (Pdp) - [Commissions encaissées: " . $entite . "]")
            ->set(self::CROSSED_ENTITY_POP_COMMISSIONS, $entite->getId())
            ->setEntityId(null)
            ->generateUrl();
        return $url;
    }

    public function crossCanal_POPPartenaire_attacherPiece(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        /** @var PaiementPartenaire */
        $entite = $context->getEntity()->getInstance();
        $url = $adminUrlGenerator
            ->setController(DocPieceCrudController::class)
            ->setAction(Action::NEW)
            ->set("titre", "NOUVELLE PIECE (Pdp) - [Retrocommission payée: " . $entite . "]")
            ->set(self::CROSSED_ENTITY_POP_PARTENAIRE, $entite->getId())
            ->setEntityId(null)
            ->generateUrl();
        return $url;
    }

    public function crossCanal_POPTaxe_attacherPiece(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        /** @var PaiementTaxe */
        $entite = $context->getEntity()->getInstance();
        $url = $adminUrlGenerator
            ->setController(DocPieceCrudController::class)
            ->setAction(Action::NEW)
            ->set("titre", "NOUVELLE PIECE (Pdp) - [Taxe payée: " . $entite . "]")
            ->set(self::CROSSED_ENTITY_POP_TAXE, $entite->getId())
            ->setEntityId(null)
            ->generateUrl();
        return $url;
    }

    public function crossCanal_Police_ajouterPOPComm(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        /** @var Police */
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

    public function crossCanal_Piece_ajouterPOPComm(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        /** @var Police */
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
        /** @var Police */
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
        /** @var Police */
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
        /** @var Police */
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

    public function crossCanal_Police_ajouterMission(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        /** @var Police */
        $entite = $context->getEntity()->getInstance();
        $url = $adminUrlGenerator
            ->setController(ActionCRMCrudController::class)
            ->setAction(Action::NEW)
            ->set("titre", "NOUVELLE MISSION - [Police: " . $entite . "]")
            ->set(self::CROSSED_ENTITY_POLICE, $entite->getId())
            //->set(self::CROSSED_ENTITY_TAXE, $taxe->getId())
            ->setEntityId(null)
            ->generateUrl();
        return $url;
    }

    public function crossCanal_Cotation_ajouterMission(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        /** @var Cotation */
        $entite = $context->getEntity()->getInstance();
        $url = $adminUrlGenerator
            ->setController(ActionCRMCrudController::class)
            ->setAction(Action::NEW)
            ->set("titre", "NOUVELLE MISSION - [Cotation: " . $entite . "]")
            ->set(self::CROSSED_ENTITY_COTATION, $entite->getId())
            //->set(self::CROSSED_ENTITY_TAXE, $taxe->getId())
            ->setEntityId(null)
            ->generateUrl();
        return $url;
    }

    public function crossCanal_Sinistre_ajouterMission(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        /** @var Sinistre */
        $entite = $context->getEntity()->getInstance();
        $url = $adminUrlGenerator
            ->setController(ActionCRMCrudController::class)
            ->setAction(Action::NEW)
            ->set("titre", "NOUVELLE MISSION - [Sinistre: " . $entite . "]")
            ->set(self::CROSSED_ENTITY_SINISTRE, $entite->getId())
            //->set(self::CROSSED_ENTITY_TAXE, $taxe->getId())
            ->setEntityId(null)
            ->generateUrl();
        return $url;
    }

    public function crossCanal_Sinistre_ajouterExpert(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        /** @var Sinistre */
        $sinistre = $context->getEntity()->getInstance();
        $url = $adminUrlGenerator
            ->setController(ExpertCrudController::class)
            ->setAction(Action::NEW)
            ->set("titre", "NOUVEL EXPERT - [Sinistre: " . $sinistre . "]")
            ->set(self::CROSSED_ENTITY_SINISTRE, $sinistre->getId())
            //->set(self::CROSSED_ENTITY_TAXE, $taxe->getId())
            ->setEntityId(null)
            ->generateUrl();
        return $url;
    }

    public function crossCanal_Sinistre_ajouterVictime(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        /** @var Sinistre */
        $sinistre = $context->getEntity()->getInstance();
        $url = $adminUrlGenerator
            ->setController(VictimeCrudController::class)
            ->setAction(Action::NEW)
            ->set("titre", "NOUVELLE VICTIME - [Sinistre: " . $sinistre . "]")
            ->set(self::CROSSED_ENTITY_SINISTRE, $sinistre->getId())
            //->set(self::CROSSED_ENTITY_TAXE, $taxe->getId())
            ->setEntityId(null)
            ->generateUrl();
        return $url;
    }

    public function crossCanal_Sinistre_ajouterDocument(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        /** @var Sinistre */
        $sinistre = $context->getEntity()->getInstance();
        $url = $adminUrlGenerator
            ->setController(DocPieceCrudController::class)
            ->setAction(Action::NEW)
            ->set("titre", "NOUVEAU DOCUMENT - [Sinistre: " . $sinistre . "]")
            ->set(self::CROSSED_ENTITY_SINISTRE, $sinistre->getId())
            //->set(self::CROSSED_ENTITY_TAXE, $taxe->getId())
            ->setEntityId(null)
            ->generateUrl();
        return $url;
    }

    public function crossCanal_EtapeCRM_ajouterPiste(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        /** @var EtapeCrm */
        $entite = $context->getEntity()->getInstance();
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
        /** @var Piste */
        $entite = $context->getEntity()->getInstance();
        $url = $adminUrlGenerator
            ->setController(ActionCRMCrudController::class)
            ->setAction(Action::NEW)
            ->set("titre", "NOUVELLE MISSION - [Piste: " . $entite . "]")
            ->set(self::CROSSED_ENTITY_PISTE, $entite->getId())
            ->setEntityId(null)
            ->generateUrl();
        return $url;
    }

    public function crossCanal_Piste_ajouterCotation(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        /** @var Piste */
        $entite = $context->getEntity()->getInstance();
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
        /** @var Piste */
        $entite = $context->getEntity()->getInstance();
        $url = $adminUrlGenerator
            ->setController(ContactCrudController::class)
            ->setAction(Action::NEW)
            ->set("titre", "NOUVAU CONTACT - [Piste: " . $entite . "]")
            ->set(self::CROSSED_ENTITY_PISTE, $entite->getId())
            ->setEntityId(null)
            ->generateUrl();
        return $url;
    }

    public function crossCanal_Action_listerFeedback(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        /** @var ActionCRM */
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
        /** @var Piste */
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
        /** @var Piste */
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
        /** @var Piste */
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
        /** @var Cotation */
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
        /** @var Client */
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

    public function crossCanal_Categorie_listerPiece(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        /** @var DocCategorie */
        $entite = $context->getEntity()->getInstance();
        $url = $adminUrlGenerator
            ->setController(DocPieceCrudController::class)
            ->setAction(Action::INDEX)
            ->set("titre", "LISTE DES PIECES - [Catégorie: " . $entite . "]")
            ->set('filters[' . self::CROSSED_ENTITY_CATEGORIE . '][value]', $entite->getId()) //il faut juste passer son ID
            ->set('filters[' . self::CROSSED_ENTITY_CATEGORIE . '][comparison]', '=')
            ->setEntityId(null)
            ->generateUrl();

        return $url;
    }

    public function crossCanal_Classeur_listerPiece(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        /** @var DocClasseur */
        $entite = $context->getEntity()->getInstance();
        $url = $adminUrlGenerator
            ->setController(DocPieceCrudController::class)
            ->setAction(Action::INDEX)
            ->set("titre", "LISTE DES PIECES - [Classeur: " . $entite . "]")
            ->set('filters[' . self::CROSSED_ENTITY_CLASSEUR . '][value]', $entite->getId()) //il faut juste passer son ID
            ->set('filters[' . self::CROSSED_ENTITY_CLASSEUR . '][comparison]', '=')
            ->setEntityId(null)
            ->generateUrl();
        return $url;
    }

    public function crossCanal_POPCom_listerPiece(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        /** @var PaiementCommission */
        $entite = $context->getEntity()->getInstance();
        $url = $adminUrlGenerator
            ->setController(DocPieceCrudController::class)
            ->setAction(Action::INDEX)
            ->set("titre", "LISTE DES PIECES - [Commissions encaissées: " . $entite . "]")
            ->set('filters[' . self::CROSSED_ENTITY_POP_COMMISSIONS . '][value]', [$entite->getId()]) //il faut juste passer son ID
            ->set('filters[' . self::CROSSED_ENTITY_POP_COMMISSIONS . '][comparison]', '=')
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

    public function crossCanal_Piece_listerPOPComm(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        /** @var DocPiece */
        $entite = $context->getEntity()->getInstance();
        $url = $adminUrlGenerator
            ->setController(PaiementCommissionCrudController::class)
            ->setAction(Action::INDEX)
            ->set("titre", "LISTE DES PDP COMMISSIONS - [Pièce justificative: " . $entite . "]")
            ->set('filters[' . self::CROSSED_ENTITY_DOC_PIECE . '][value]', $entite->getId()) //il faut juste passer son ID
            ->set('filters[' . self::CROSSED_ENTITY_DOC_PIECE . '][comparison]', '=')
            ->setEntityId(null)
            ->generateUrl();

        return $url;
    }

    public function crossCanal_Piece_listerPOPPartenaire(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        /** @var DocPiece */
        $entite = $context->getEntity()->getInstance();
        $url = $adminUrlGenerator
            ->setController(PaiementPartenaireCrudController::class)
            ->setAction(Action::INDEX)
            ->set("titre", "LISTE DES PDP RETO-COMMISSIONS - [Pièce justificative: " . $entite . "]")
            ->set('filters[' . self::CROSSED_ENTITY_DOC_PIECE . '][value]', $entite->getId()) //il faut juste passer son ID
            ->set('filters[' . self::CROSSED_ENTITY_DOC_PIECE . '][comparison]', '=')
            ->setEntityId(null)
            ->generateUrl();

        return $url;
    }

    public function crossCanal_Piece_listerPOPTaxe(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        /** @var DocPiece */
        $entite = $context->getEntity()->getInstance();
        $url = $adminUrlGenerator
            ->setController(PaiementTaxeCrudController::class)
            ->setAction(Action::INDEX)
            ->set("titre", "LISTE DES PDP TAXES - [Pièce justificative: " . $entite . "]")
            ->set('filters[' . self::CROSSED_ENTITY_DOC_PIECE . '][value]', $entite->getId()) //il faut juste passer son ID
            ->set('filters[' . self::CROSSED_ENTITY_DOC_PIECE . '][comparison]', '=')
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

    public function crossCanal_Police_listerMission(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        /** @var Police */
        $entite = $context->getEntity()->getInstance();
        $url = $adminUrlGenerator
            ->setController(ActionCRMCrudController::class)
            ->setAction(Action::INDEX)
            ->set("titre", "LISTE DES MISSIONS - [Police: " . $entite . "]")
            ->set('filters[' . self::CROSSED_ENTITY_POLICE . '][value]', $entite->getId()) //il faut juste passer son ID
            ->set('filters[' . self::CROSSED_ENTITY_POLICE . '][comparison]', '=')
            ->setEntityId(null)
            ->generateUrl();

        return $url;
    }

    public function crossCanal_Police_ajouterContact(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        /** @var Police */
        $police = $context->getEntity()->getInstance();

        /** @var Piste */
        $piste = null;
        if ($police->getCotation() != null) {
            if ($police->getCotation()->getPiste() != null) {
                $piste = $police->getCotation()->getPiste();
            }
        }

        $url = $adminUrlGenerator
            ->setController(ContactCrudController::class)
            ->setAction(Action::NEW)
            ->set("titre", "NOUVEAU CONTACT - [Police: " . $police . "]")
            ->set(self::CROSSED_ENTITY_PISTE, $piste->getId())
            //->set(self::CROSSED_ENTITY_TAXE, $taxe->getId())
            ->setEntityId(null)
            ->generateUrl();
        return $url;
    }

    public function crossCanal_Police_ajouterAutomobile(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        /** @var Police */
        $police = $context->getEntity()->getInstance();

        $url = $adminUrlGenerator
            ->setController(AutomobileCrudController::class)
            ->setAction(Action::NEW)
            ->set("titre", "NOUVEL ENGIN AUTOMOTEUR - [Police: " . $police . "]")
            ->set(self::CROSSED_ENTITY_POLICE, $police->getId())
            ->setEntityId(null)
            ->generateUrl();
        return $url;
    }

    public function crossCanal_Police_listerContact(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        /** @var Police */
        $police = $context->getEntity()->getInstance();

        /** @var Piste */
        $piste = null;
        if ($police->getCotation() != null) {
            if ($police->getCotation()->getPiste() != null) {
                $piste = $police->getCotation()->getPiste();
            }
        }

        $url = $adminUrlGenerator
            ->setController(ContactCrudController::class)
            ->setAction(Action::INDEX)
            ->set("titre", "LISTE DES CONTACT - [Police: " . $police . "]")
            ->set('filters[' . self::CROSSED_ENTITY_PISTE . '][value]', [$piste->getId()]) //il faut juste passer son ID
            ->set('filters[' . self::CROSSED_ENTITY_PISTE . '][comparison]', '=')
            ->setEntityId(null)
            ->generateUrl();

        return $url;
    }

    public function crossCanal_Police_listerAutomobile(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        /** @var Police */
        $police = $context->getEntity()->getInstance();

        $url = $adminUrlGenerator
            ->setController(AutomobileCrudController::class)
            ->setAction(Action::INDEX)
            ->set("titre", "LISTE D'ENGINS AUTOMOTEURS - [Police: " . $police . "]")
            ->set('filters[' . self::CROSSED_ENTITY_POLICE . '][value]', $police->getId()) //il faut juste passer son ID
            ->set('filters[' . self::CROSSED_ENTITY_POLICE . '][comparison]', '=')
            ->setEntityId(null)
            ->generateUrl();

        return $url;
    }

    public function crossCanal_Cotation_listerMission(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        /** @var Cotation */
        $entite = $context->getEntity()->getInstance();
        $url = $adminUrlGenerator
            ->setController(ActionCRMCrudController::class)
            ->setAction(Action::INDEX)
            ->set("titre", "LISTE DES MISSIONS - [Cotation: " . $entite . "]")
            ->set('filters[' . self::CROSSED_ENTITY_COTATION . '][value]', $entite->getId()) //il faut juste passer son ID
            ->set('filters[' . self::CROSSED_ENTITY_COTATION . '][comparison]', '=')
            ->setEntityId(null)
            ->generateUrl();

        return $url;
    }

    public function crossCanal_Sinistre_listerMission(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        /** @var Sinistre */
        $entite = $context->getEntity()->getInstance();
        $url = $adminUrlGenerator
            ->setController(ActionCRMCrudController::class)
            ->setAction(Action::INDEX)
            ->set("titre", "LISTE DES MISSIONS - [Sinistre: " . $entite . "]")
            ->set('filters[' . self::CROSSED_ENTITY_SINISTRE . '][value]', $entite->getId()) //il faut juste passer son ID
            ->set('filters[' . self::CROSSED_ENTITY_SINISTRE . '][comparison]', '=')
            ->setEntityId(null)
            ->generateUrl();

        return $url;
    }

    public function crossCanal_Sinistre_listerExpert(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        /** @var Sinistre */
        $entite = $context->getEntity()->getInstance();
        $url = $adminUrlGenerator
            ->setController(ExpertCrudController::class)
            ->setAction(Action::INDEX)
            ->set("titre", "LISTE D'EXPERTS - [Sinistre: " . $entite . "]")
            ->set('filters[' . self::CROSSED_ENTITY_ETAPE_SINISTRE . '][value]', $entite->getId()) //il faut juste passer son ID
            ->set('filters[' . self::CROSSED_ENTITY_ETAPE_SINISTRE . '][comparison]', '=')
            ->setEntityId(null)
            ->generateUrl();

        return $url;
    }

    public function crossCanal_Sinistre_listerVictime(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        /** @var Sinistre */
        $entite = $context->getEntity()->getInstance();
        $url = $adminUrlGenerator
            ->setController(VictimeCrudController::class)
            ->setAction(Action::INDEX)
            ->set("titre", "LISTE DES VICTIMES - [Sinistre: " . $entite . "]")
            ->set('filters[' . self::CROSSED_ENTITY_ETAPE_SINISTRE . '][value]', $entite->getId()) //il faut juste passer son ID
            ->set('filters[' . self::CROSSED_ENTITY_ETAPE_SINISTRE . '][comparison]', '=')
            ->setEntityId(null)
            ->generateUrl();

        return $url;
    }

    public function crossCanal_Sinistre_listerDocument(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        /** @var Sinistre */
        $entite = $context->getEntity()->getInstance();
        $url = $adminUrlGenerator
            ->setController(DocPieceCrudController::class)
            ->setAction(Action::INDEX)
            ->set("titre", "LISTE DES DOCUMENTS - [Sinistre: " . $entite . "]")
            ->set('filters[' . self::CROSSED_ENTITY_SINISTRE . '][value]', $entite->getId()) //il faut juste passer son ID
            ->set('filters[' . self::CROSSED_ENTITY_SINISTRE . '][comparison]', '=')
            ->setEntityId(null)
            ->generateUrl();

        return $url;
    }

    public function crossCanal_Expert_listerSinistre(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        /** @var Expert */
        $entite = $context->getEntity()->getInstance();
        $url = $adminUrlGenerator
            ->setController(SinistreCrudController::class)
            ->setAction(Action::INDEX)
            ->set("titre", "LISTE DES SINISTRES - [Expert: " . $entite . "]")
            ->set('filters[experts][value]', [$entite->getId()]) //il faut juste passer son ID
            ->set('filters[experts][comparison]', '=')
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

    public function crossCanal_Piece_setSinistre(DocPiece $docPiece, AdminUrlGenerator $adminUrlGenerator): DocPiece
    {
        /** @var Sinistre */
        $sinistre = null;
        $paramIDSinistre = $adminUrlGenerator->get(self::CROSSED_ENTITY_SINISTRE);
        if ($paramIDSinistre != null) {
            $sinistre = $this->entityManager->getRepository(Sinistre::class)->find($paramIDSinistre);
            $docPiece->setSinistre($sinistre);
            $docPiece->setPolice($sinistre->getPolice());
            $docPiece->setCotation($sinistre->getPolice()->getCotation());
        }
        return $docPiece;
    }

    public function crossCanal_Piece_setPOPCom(DocPiece $docPiece, AdminUrlGenerator $adminUrlGenerator): DocPiece
    {
        /** @var PaiementCommission */
        $paiementCommission = null;
        $paramIDPOPCom = $adminUrlGenerator->get(self::CROSSED_ENTITY_POP_COMMISSIONS);
        if ($paramIDPOPCom != null) {
            $paiementCommission = $this->entityManager->getRepository(PaiementCommission::class)->find($paramIDPOPCom);
            $docPiece->setPolice($paiementCommission->getPolice());
            $docPiece->setCotation($paiementCommission->getPolice()->getCotation());
            $docPiece->addPaiementCommission($paiementCommission);
        }
        return $docPiece;
    }

    public function crossCanal_Piece_setPOPPartenaire(DocPiece $docPiece, AdminUrlGenerator $adminUrlGenerator): DocPiece
    {
        /** @var PaiementPartenaire */
        $paiementPartenaire = null;
        $paramIDPOPPartenaire = $adminUrlGenerator->get(self::CROSSED_ENTITY_POP_PARTENAIRE);
        if ($paramIDPOPPartenaire != null) {
            $paiementPartenaire = $this->entityManager->getRepository(PaiementPartenaire::class)->find($paramIDPOPPartenaire);
            $docPiece->setPolice($paiementPartenaire->getPolice());
            $docPiece->setCotation($paiementPartenaire->getPolice()->getCotation());
            $docPiece->addPaiementPartenaire($paiementPartenaire);
        }
        return $docPiece;
    }

    public function crossCanal_Piece_setPOPTaxe(DocPiece $docPiece, AdminUrlGenerator $adminUrlGenerator): DocPiece
    {
        /** @var PaiementTaxe */
        $paiementTaxe = null;
        $paramIDPOPTaxe = $adminUrlGenerator->get(self::CROSSED_ENTITY_POP_TAXE);
        if ($paramIDPOPTaxe != null) {
            $paiementTaxe = $this->entityManager->getRepository(PaiementTaxe::class)->find($paramIDPOPTaxe);
            $docPiece->setPolice($paiementTaxe->getPolice());
            $docPiece->setCotation($paiementTaxe->getPolice()->getCotation());
            $docPiece->addPaiementTax($paiementTaxe);
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

    public function crossCanal_Cotation_setPiste(Cotation $cotation, AdminUrlGenerator $adminUrlGenerator): Cotation
    {
        $paramID = $adminUrlGenerator->get(self::CROSSED_ENTITY_PISTE);
        if ($paramID != null) {
            /** @var Piste */
            $objet = $this->entityManager->getRepository(Piste::class)->find($paramID);
            $cotation->setPiste($objet);
        }
        return $cotation;
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

    public function crossCanal_Automobile_setPolice(Automobile $automobile, AdminUrlGenerator $adminUrlGenerator): Automobile
    {
        $paramIdPolice = $adminUrlGenerator->get(self::CROSSED_ENTITY_POLICE);
        if ($paramIdPolice != null) {
            /** @var Police */
            $police = $this->entityManager->getRepository(Police::class)->find($paramIdPolice);
            $automobile->setPolice($police);
        }
        return $automobile;
    }

    public function crossCanal_Expert_setSinistre(Expert $expert, AdminUrlGenerator $adminUrlGenerator): Expert
    {
        /** @var Sinistre */
        $sinistre = null;
        $paramIDSinistre = $adminUrlGenerator->get(self::CROSSED_ENTITY_SINISTRE);
        if ($paramIDSinistre != null) {
            $sinistre = $this->entityManager->getRepository(Sinistre::class)->find($paramIDSinistre);
            if ($expert->getSinistres()->contains($sinistre) == false) {
                $expert->addSinistre($sinistre);
            }
        }
        return $expert;
    }

    public function crossCanal_Victime_setSinistre(Victime $victime, AdminUrlGenerator $adminUrlGenerator): Victime
    {
        /** @var Sinistre */
        $sinistre = null;
        $paramIDSinistre = $adminUrlGenerator->get(self::CROSSED_ENTITY_SINISTRE);
        if ($paramIDSinistre != null) {
            $sinistre = $this->entityManager->getRepository(Sinistre::class)->find($paramIDSinistre);
            $victime->setSinistre($sinistre);
        }
        return $victime;
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
            $paiementCommission->setMontant($objet->calc_revenu_ttc_solde_restant_du); //calc_revenu_ttc_solde_restant_du
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
            if ($taxe->isPayableparcourtier() == true) {
                $paiementTaxe->setMontant($police->calc_taxes_courtier_solde);
            } else {
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

    public function crossCanal_Mission_setPolice(ActionCRM $actionCRM, AdminUrlGenerator $adminUrlGenerator): ActionCRM
    {
        $objet = null;
        $paramID = $adminUrlGenerator->get(self::CROSSED_ENTITY_POLICE);
        if ($paramID != null) {
            /** @var Police */
            $objet = $this->entityManager->getRepository(Police::class)->find($paramID);
            $actionCRM->setPolice($objet);
            if ($objet->getCotation() != null) {
                $actionCRM->setCotation($objet->getCotation());
                if ($objet->getCotation()->getPiste() != null) {
                    $actionCRM->setPiste($objet->getCotation()->getPiste());
                }
            }
        }
        return $actionCRM;
    }

    public function crossCanal_Mission_setCotation(ActionCRM $actionCRM, AdminUrlGenerator $adminUrlGenerator): ActionCRM
    {
        $paramID = $adminUrlGenerator->get(self::CROSSED_ENTITY_COTATION);
        if ($paramID != null) {
            /** @var Cotation */
            $objet = $this->entityManager->getRepository(Cotation::class)->find($paramID);
            $actionCRM->setCotation($objet);
            if ($objet->getPiste() != null) {
                $actionCRM->setPiste($objet->getPiste());
            }
        }
        return $actionCRM;
    }

    public function crossCanal_Mission_setSinistre(ActionCRM $actionCRM, AdminUrlGenerator $adminUrlGenerator): ActionCRM
    {
        $paramID = $adminUrlGenerator->get(self::CROSSED_ENTITY_SINISTRE);
        if ($paramID != null) {
            /** @var Sinistre */
            $objet = $this->entityManager->getRepository(Sinistre::class)->find($paramID);
            $actionCRM->setSinistre($objet);
            //Définition de la police
            if ($objet->getPolice() != null) {
                $actionCRM->setPolice($objet->getPolice());
                //Définition de la cotation
                if ($objet->getPolice()->getCotation() != null) {
                    $actionCRM->setCotation($objet->getPolice()->getCotation());
                    //Définition de la piste
                    if ($objet->getPolice()->getCotation()->getPiste() != null) {
                        $actionCRM->setPiste($objet->getPolice()->getCotation()->getPiste());
                    }
                }
            }
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

    public function reporting_commission_tous(AdminUrlGenerator $adminUrlGenerator, bool $outstanding)
    {
        $url = "";
        if($outstanding == true){
            $url = $this->reporting_commission_unpaid($adminUrlGenerator);
        }else{
            $url = $this->reporting_commission_paid($adminUrlGenerator);
        }
        /* $url = $adminUrlGenerator
            ->setController(PoliceCrudController::class)
            ->setAction(Action::INDEX)
            ->set("titre", $titre)
            ->set('filters[isCommissionUnpaid][value]', $outstanding)
            ->setEntityId(null)
            ->generateUrl(); */

        return $url;
    }

    private function reporting_commission_paid(AdminUrlGenerator $adminUrlGenerator):string
    {
        $titre = "TOUTES COMMISSIONS ENCAISSEES";
        $url = $adminUrlGenerator
            ->setController(PoliceCrudController::class)
            ->setAction(Action::INDEX)
            ->set("titre", $titre)
            ->set("codeReporting", ServiceCrossCanal::REPORTING_CODE_PAID_COM)
            ->set('filters[paidcommission][value]', 0)
            ->set('filters[paidcommission][comparison]', '>')
            ->setEntityId(null)
            ->generateUrl();

        return $url;
    }

    private function reporting_commission_unpaid(AdminUrlGenerator $adminUrlGenerator):string
    {
        $titre = "TOUTES COMMISSIONS IMPAYEES";
        $url = $adminUrlGenerator
            ->setController(PoliceCrudController::class)
            ->setAction(Action::INDEX)
            ->set("titre", $titre)
            ->set("codeReporting", ServiceCrossCanal::REPORTING_CODE_UNPAID_COM)
            ->set('filters[unpaidcommission][value]', 0)
            ->set('filters[unpaidcommission][comparison]', '!=')
            ->setEntityId(null)
            ->generateUrl();
        return $url;
    }
}
