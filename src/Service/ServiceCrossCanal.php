<?php

namespace App\Service;


use App\Entity\Taxe;
use NumberFormatter;
use App\Entity\Piste;
use App\Entity\Client;
use App\Entity\Expert;
use App\Entity\Police;
use DateTimeImmutable;
use App\Entity\Contact;
use App\Entity\Monnaie;
use App\Entity\Produit;
use App\Entity\Victime;
use App\Entity\Assureur;
use App\Entity\Cotation;
use App\Entity\DocPiece;
use App\Entity\EtapeCrm;
use App\Entity\Sinistre;
use App\Entity\ActionCRM;
use App\Entity\Automobile;
use App\Entity\Entreprise;
use App\Entity\Partenaire;
use App\Entity\DocClasseur;
use App\Entity\FeedbackCRM;
use App\Entity\Utilisateur;
use App\Entity\DocCategorie;
use App\Entity\PaiementTaxe;
use App\Entity\EtapeSinistre;
use Doctrine\ORM\QueryBuilder;
use App\Entity\PaiementCommission;
use App\Entity\PaiementPartenaire;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Bundle\SecurityBundle\Security;
use App\Controller\Admin\PisteCrudController;
use function PHPUnit\Framework\containsEqual;
use App\Controller\Admin\ClientCrudController;
use App\Controller\Admin\ExpertCrudController;
use App\Controller\Admin\PoliceCrudController;
use App\Controller\Admin\ContactCrudController;
use App\Controller\Admin\MonnaieCrudController;
use App\Controller\Admin\VictimeCrudController;
use App\Controller\Admin\CotationCrudController;
use App\Controller\Admin\DocPieceCrudController;
use App\Controller\Admin\EtapeCrmCrudController;
use App\Controller\Admin\SinistreCrudController;
use Doctrine\Common\Collections\ArrayCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use App\Controller\Admin\ActionCRMCrudController;
use Symfony\Component\Validator\Constraints\Date;
use App\Controller\Admin\AutomobileCrudController;
use App\Controller\Admin\FactureCrudController;
use App\Controller\Admin\PreferenceCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use App\Controller\Admin\FeedbackCRMCrudController;
use App\Controller\Admin\PaiementTaxeCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use App\Controller\Admin\PaiementCommissionCrudController;
use App\Controller\Admin\PaiementCrudController;
use App\Controller\Admin\PaiementPartenaireCrudController;
use App\Entity\Facture;
use App\Entity\Paiement;
use DateInterval;
use Doctrine\ORM\EntityManager;
use EasyCorp\Bundle\EasyAdminBundle\Form\Type\ComparisonType;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class ServiceCrossCanal
{
    //code reporting
    public const REPORTING_CODE_UNPAID_COM = 0;
    public const REPORTING_CODE_UNPAID_RETROCOM = 1;
    public const REPORTING_CODE_UNPAID_TAXE_COURTIER = 2;
    public const REPORTING_CODE_UNPAID_TAXE_ASSUREUR = 3;
    public const REPORTING_CODE_UNPAID_TAXE = 4;
    public const REPORTING_CODE_PAID_COM = 100;
    public const REPORTING_CODE_PAID_RETROCOM = 101;
    public const REPORTING_CODE_PAID_TAXE_COURTIER = 102;
    public const REPORTING_CODE_PAID_TAXE_ASSUREUR = 103;
    public const REPORTING_CODE_PAID_TAXE = 104;
    public const REPORTING_CODE_PRODUCTION_TOUS = 200;
    public const REPORTING_CODE_PRODUCTION_ASSUREUR = 210;
    public const REPORTING_CODE_PRODUCTION_PRODUIT = 220;
    public const REPORTING_CODE_PRODUCTION_PARTENAIRE = 230;
    public const REPORTING_CODE_SINISTRE_TOUS = 300;
    public const REPORTING_CODE_PISTE_TOUS = 400;


    //Feedback
    public const OPTION_FEEDBACK_AJOUTER = "New feedback";
    public const OPTION_FEEDBACK_LISTER = "Feedbacks";
    //Piece
    public const OPTION_PIECE_AJOUTER = "New pièce";
    public const OPTION_PIECE_LISTER = "Pièces";
    public const OPTION_PIECE_ATTACHER = "New pièce";
    //Automobile
    public const OPTION_AUTOMOBILE_AJOUTER = "New engin";
    public const OPTION_AUTOMOBILE_LISTER = "Engins";
    //Piste
    public const OPTION_PISTE_AJOUTER = "New piste";
    public const OPTION_PISTE_LISTER = "Pistes";
    //Police
    public const OPTION_POLICE_LISTER = "Polices";
    public const OPTION_POLICE_OUVRIR = "Police";
    public const OPTION_POLICE_CREER = "New police";
    //Client
    public const OPTION_CLIENT_OUVRIR = "Client";
    public const OPTION_CLIENT_CREER = "New client";
    //Mission
    public const OPTION_MISSION_AJOUTER = "New mission";
    public const OPTION_MISSION_LISTER = "Missions";
    //Contact
    public const OPTION_CONTACT_AJOUTER = "New contact";
    public const OPTION_CONTACT_LISTER = "Contacts";
    //Cotation
    public const OPTION_COTATION_AJOUTER = "New cotation";
    public const OPTION_COTATION_LISTER = "Cotations";
    //Sinistre
    public const OPTION_SINISTRE_LISTER = "Sinistres";
    public const OPTION_SINISTRE_AJOUTER = "New sinistre";
    //Expert Sinistre
    public const OPTION_EXPERT_AJOUTER = "New expert";
    public const OPTION_EXPERT_LISTER = "Experts";
    //Victime Sinistre
    public const OPTION_VICTIME_AJOUTER = "New victime";
    public const OPTION_VICTIME_LISTER = "Victimes";


    public const CROSSED_ENTITY_ACTION = "action";
    public const CROSSED_ENTITY_COTATION = "cotation";
    public const CROSSED_ENTITY_FACTURE = "facture";
    public const CROSSED_ENTITY_CLIENT = "client";
    public const CROSSED_ENTITY_ETAPE_CRM = "etape";
    public const CROSSED_ENTITY_ETAPE_SINISTRE = "etape";
    public const CROSSED_ENTITY_EXPERT = "expert";
    public const CROSSED_ENTITY_PISTE = "piste";
    public const CROSSED_ENTITY_CONTACT = "contact";
    public const CROSSED_ENTITY_POLICE = "police";
    public const CROSSED_ENTITY_CATEGORIE = "categorie";
    public const CROSSED_ENTITY_CLASSEUR = "classeur";
    public const CROSSED_ENTITY_DOC_PIECE = "piece";
    public const CROSSED_ENTITY_PRODUIT = "produit";
    public const CROSSED_ENTITY_PARTENAIRE = "partenaire";
    public const CROSSED_ENTITY_ASSUREUR = "assureur";
    public const CROSSED_ENTITY_TAXE = "taxe";
    public const CROSSED_ENTITY_SINISTRE = "sinistre";




    public function __construct(
        private ServiceFacture $serviceFacture,
        private ServiceAvenant $serviceAvenant,
        private ServiceDates $serviceDates,
        private EntityManagerInterface $entityManager,
        private ServiceEntreprise $serviceEntreprise,
        private AdminUrlGenerator $adminUrlGenerator,
        private ServiceTaxes $serviceTaxes,
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
            ->set("champsACacher[0]", PreferenceCrudController::PREF_CRM_FEEDBACK_ACTION)
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
            // ->set("champsACacher[0]", PreferenceCrudController::PREF_BIB_DOCUMENT_POLICE)
            // ->set("champsACacher[1]", PreferenceCrudController::PREF_BIB_DOCUMENT_COTATION)
            // ->set("champsACacher[2]", PreferenceCrudController::PREF_BIB_DOCUMENT_SINISTRE)

            ->set(self::CROSSED_ENTITY_COTATION, $entite->getId())
            ->setEntityId(null)
            ->generateUrl();
        return $url;
    }

    // public function crossCanal_Cotation_creerPolice(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    // {
    //     /** @var Cotation */
    //     $cotation = $context->getEntity()->getInstance();
    //     $tabData = $this->serviceAvenant->getAvenantPRT($cotation);
    //     //dd($this->serviceAvenant->getNomAvenant($cotation->getTypeavenant()));
    //     $this->appliquerDesactivations($this->serviceAvenant->getNomAvenant($cotation->getTypeavenant()), $adminUrlGenerator);
    //     $url = $adminUrlGenerator
    //         ->setController(PoliceCrudController::class)
    //         ->setAction(Action::NEW)
    //         ->set("champsACacher[0]", PreferenceCrudController::PREF_PRO_POLICE_COTATION)
    //         ->set("champsACacher[1]", PreferenceCrudController::PREF_PRO_POLICE_PRODUIT)
    //         ->set("champsACacher[2]", PreferenceCrudController::PREF_PRO_POLICE_CLIENT)
    //         ->set("champsACacher[3]", PreferenceCrudController::PREF_PRO_POLICE_PISTES)
    //         ->set("titre", $tabData['titre'])
    //         ->set("avenant[type]", $tabData['nomAvenant'])
    //         ->set("avenant[reference]", $tabData['reference'])
    //         ->set("avenant[police]", $tabData['idPolice'])
    //         ->set(self::CROSSED_ENTITY_COTATION, $cotation->getId())
    //         ->setEntityId(null)
    //         ->generateUrl();
    //     return $url;
    // }

    private function appliquerDesactivations($typeAvenant, AdminUrlGenerator $adminUrlGenerator)
    {
        //dd($typeAvenant);
        switch ($typeAvenant) {
            case PoliceCrudController::AVENANT_TYPE_ANNULATION:
                $this->desactiverChampsAnnulation($adminUrlGenerator);
                break;
            case PoliceCrudController::AVENANT_TYPE_PROROGATION:
                $this->desactiverChampsProrogation($adminUrlGenerator);
                break;
            case PoliceCrudController::AVENANT_TYPE_INCORPORATION:
                $this->desactiverChampsIncorporation($adminUrlGenerator);
                break;
            case PoliceCrudController::AVENANT_TYPE_RISTOURNE:
                $this->desactiverChampsRistourne($adminUrlGenerator);
                break;
            case PoliceCrudController::AVENANT_TYPE_RENOUVELLEMENT:
                $this->desactiverChampsRenouvellement($adminUrlGenerator);
                break;
            case PoliceCrudController::AVENANT_TYPE_SOUSCRIPTION:
                $this->desactiverChampsSouscription($adminUrlGenerator);
                break;
            case PoliceCrudController::AVENANT_TYPE_AUTRE_MODIFICATION:
                $this->desactiverChampsAutresModifications($adminUrlGenerator);
                break;
            case PoliceCrudController::AVENANT_TYPE_RESILIATION:
                $this->desactiverChampsResiliation($adminUrlGenerator);
                break;

            default:
                # code...
                break;
        }
    }

    public function crossCanal_Avenant_Annulation(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        /** @var Police */
        $police = $context->getEntity()->getInstance();
        $this->appliquerDesactivations(PoliceCrudController::AVENANT_TYPE_ANNULATION, $adminUrlGenerator);
        $url = $adminUrlGenerator
            ->setController(PoliceCrudController::class)
            ->setAction(Action::NEW)
            ->set("titre", "Annulation de la police " . $police . "")
            ->set("avenant[type]", PoliceCrudController::AVENANT_TYPE_ANNULATION)
            ->set("avenant[police]", $police->getId())
            ->set("avenant[reference]", $police->getReference())
            ->setEntityId(null)
            //->set("champsACacher[0]", PreferenceCrudController::PREF_PRO_POLICE_COTATION)
            //->set("champsACacher[1]", PreferenceCrudController::PREF_PRO_POLICE_PRODUIT)
            //->set("champsACacher[2]", PreferenceCrudController::PREF_PRO_POLICE_CLIENT)
            //->set(self::CROSSED_ENTITY_POLICE, $police->getId())
            ->generateUrl();
        return $url;
    }


    public function crossCanal_Avenant_Incorporation(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        /** @var Police */
        $police = $context->getEntity()->getInstance();
        $this->appliquerDesactivations(PoliceCrudController::AVENANT_TYPE_INCORPORATION, $adminUrlGenerator);
        //$this->appliquerDesactivations($this->serviceAvenant->getNomAvenant($police->getTypeavenant()), $adminUrlGenerator);
        $url = $adminUrlGenerator
            ->setController(PoliceCrudController::class)
            ->setAction(Action::NEW)
            ->set("titre", "Incorporation à la police " . $police . "")
            ->set("avenant[type]", PoliceCrudController::AVENANT_TYPE_INCORPORATION)
            ->set("avenant[police]", $police->getId())
            ->set("avenant[reference]", $police->getReference())
            ->setEntityId(null)

            //->set("champsACacher[0]", PreferenceCrudController::PREF_PRO_POLICE_COTATION)
            //->set("champsACacher[0]", PreferenceCrudController::PREF_PRO_POLICE_COTATION)
            //->set("champsACacher[1]", PreferenceCrudController::PREF_PRO_POLICE_PRODUIT)
            //->set("champsACacher[2]", PreferenceCrudController::PREF_PRO_POLICE_CLIENT)
            //->set(self::CROSSED_ENTITY_POLICE, $police->getId())
            ->generateUrl();
        return $url;
    }

    public function crossCanal_Avenant_Autres_Modifications(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        /** @var Police */
        $police = $context->getEntity()->getInstance();
        $this->appliquerDesactivations(PoliceCrudController::AVENANT_TYPE_AUTRE_MODIFICATION, $adminUrlGenerator);
        //dd("ici");
        $url = $adminUrlGenerator
            ->setController(PoliceCrudController::class)
            ->setAction(Action::NEW)
            ->set("titre", "Autres modifications à la police " . $police . "")
            ->set("avenant[type]", PoliceCrudController::AVENANT_TYPE_AUTRE_MODIFICATION)
            ->set("avenant[police]", $police->getId())
            ->set("avenant[reference]", $police->getReference())
            ->setEntityId(null)

            //->set("champsACacher[0]", PreferenceCrudController::PREF_PRO_POLICE_COTATION)
            //->set("champsACacher[0]", PreferenceCrudController::PREF_PRO_POLICE_COTATION)
            //->set("champsACacher[1]", PreferenceCrudController::PREF_PRO_POLICE_PRODUIT)
            //->set("champsACacher[2]", PreferenceCrudController::PREF_PRO_POLICE_CLIENT)
            //->set(self::CROSSED_ENTITY_POLICE, $police->getId())
            ->generateUrl();
        return $url;
    }

    public function crossCanal_Avenant_Resiliation(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        /** @var Police */
        $police = $context->getEntity()->getInstance();
        $this->appliquerDesactivations(PoliceCrudController::AVENANT_TYPE_RESILIATION, $adminUrlGenerator);
        $url = $adminUrlGenerator
            ->setController(PoliceCrudController::class)
            ->setAction(Action::NEW)
            ->set("titre", "Résiliation de la police " . $police . "")
            ->set("avenant[type]", PoliceCrudController::AVENANT_TYPE_RESILIATION)
            ->set("avenant[police]", $police->getId())
            ->set("avenant[reference]", $police->getReference())
            ->setEntityId(null)

            //->set("champsACacher[0]", PreferenceCrudController::PREF_PRO_POLICE_COTATION)
            //->set("champsACacher[0]", PreferenceCrudController::PREF_PRO_POLICE_COTATION)
            //->set("champsACacher[1]", PreferenceCrudController::PREF_PRO_POLICE_PRODUIT)
            //->set("champsACacher[2]", PreferenceCrudController::PREF_PRO_POLICE_CLIENT)
            //->set(self::CROSSED_ENTITY_POLICE, $police->getId())
            ->generateUrl();
        return $url;
    }

    public function crossCanal_Avenant_Ristourne(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        /** @var Police */
        $police = $context->getEntity()->getInstance();
        $this->appliquerDesactivations(PoliceCrudController::AVENANT_TYPE_RISTOURNE, $adminUrlGenerator);
        $url = $adminUrlGenerator
            ->setController(PoliceCrudController::class)
            ->setAction(Action::NEW)
            ->set("titre", "Ristourne sur la police " . $police . "")
            ->set("avenant[type]", PoliceCrudController::AVENANT_TYPE_RISTOURNE)
            ->set("avenant[police]", $police->getId())
            ->set("avenant[reference]", $police->getReference())
            ->setEntityId(null)

            //->set("champsACacher[0]", PreferenceCrudController::PREF_PRO_POLICE_COTATION)
            //->set("champsACacher[0]", PreferenceCrudController::PREF_PRO_POLICE_COTATION)
            //->set("champsACacher[1]", PreferenceCrudController::PREF_PRO_POLICE_PRODUIT)
            //->set("champsACacher[2]", PreferenceCrudController::PREF_PRO_POLICE_CLIENT)
            //->set(self::CROSSED_ENTITY_POLICE, $police->getId())
            ->generateUrl();
        return $url;
    }

    public function crossCanal_Avenant_Prorogation(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        /** @var Police */
        $police = $context->getEntity()->getInstance();
        $this->appliquerDesactivations(PoliceCrudController::AVENANT_TYPE_PROROGATION, $adminUrlGenerator);
        $url = $adminUrlGenerator
            ->setController(PoliceCrudController::class)
            ->setAction(Action::NEW)
            ->set("titre", "Prorogation de la police " . $police . "")
            ->set("avenant[type]", PoliceCrudController::AVENANT_TYPE_PROROGATION)
            ->set("avenant[police]", $police->getId())
            ->set("avenant[reference]", $police->getReference())
            ->setEntityId(null)

            //->set("champsACacher[0]", PreferenceCrudController::PREF_PRO_POLICE_COTATION)
            //->set("champsACacher[0]", PreferenceCrudController::PREF_PRO_POLICE_COTATION)
            //->set("champsACacher[1]", PreferenceCrudController::PREF_PRO_POLICE_PRODUIT)
            //->set("champsACacher[2]", PreferenceCrudController::PREF_PRO_POLICE_CLIENT)
            //->set(self::CROSSED_ENTITY_POLICE, $police->getId())
            ->generateUrl();
        return $url;
    }

    private function desactiverChampsRistourne(AdminUrlGenerator $adminUrlGenerator)
    {
        $adminUrlGenerator
            ->set("champsADesactiver[0]", PreferenceCrudController::PREF_PRO_POLICE_ID_AVENANT)
            ->set("champsADesactiver[1]", PreferenceCrudController::PREF_PRO_POLICE_TYPE_AVENANT)
            // ->set("champsADesactiver[2]", PreferenceCrudController::PREF_PRO_POLICE_ASSUREURS)
            // ->set("champsADesactiver[3]", PreferenceCrudController::PREF_PRO_POLICE_CLIENT)
            ->set("champsADesactiver[4]", PreferenceCrudController::PREF_PRO_POLICE_REFERENCE)
            // ->set("champsADesactiver[5]", PreferenceCrudController::PREF_PRO_POLICE_PRODUIT)
            ->set("champsADesactiver[6]", PreferenceCrudController::PREF_PRO_POLICE_COTATION)
            // ->set("champsADesactiver[7]", PreferenceCrudController::PREF_PRO_POLICE_REASSUREURS)
            //->set("champsADesactiver[8]", PreferenceCrudController::PREF_PRO_POLICE_CAPITAL)
            //->set("champsADesactiver[9]", PreferenceCrudController::PREF_PRO_POLICE_MODE_PAIEMENT)
            //->set("champsADesactiver[10]", PreferenceCrudController::PREF_PRO_POLICE_PRIME_NETTE)
            //->set("champsADesactiver[11]", PreferenceCrudController::PREF_PRO_POLICE_FRONTING)
            //->set("champsADesactiver[12]", PreferenceCrudController::PREF_PRO_POLICE_ARCA)
            //->set("champsADesactiver[13]", PreferenceCrudController::PREF_PRO_POLICE_TVA)
            //->set("champsADesactiver[14]", PreferenceCrudController::PREF_PRO_POLICE_FRAIS_ADMIN)
            //->set("champsADesactiver[15]", PreferenceCrudController::PREF_PRO_POLICE_DISCOUNT)
            //->set("champsADesactiver[16]", PreferenceCrudController::PREF_PRO_POLICE_PRIME_TOTALE)
            //->set("champsADesactiver[17]", PreferenceCrudController::PREF_PRO_POLICE_PARTENAIRE)
            //->set("champsADesactiver[18]", PreferenceCrudController::PREF_PRO_POLICE_PART_EXCEPTIONNELLE)
            //->set("champsADesactiver[19]", PreferenceCrudController::PREF_PRO_POLICE_RI_COM)
            //->set("champsADesactiver[20]", PreferenceCrudController::PREF_PRO_POLICE_CANHSARE_RI_COM)
            //->set("champsADesactiver[21]", PreferenceCrudController::PREF_PRO_POLICE_RI_COM_PAYABLE_BY)
            //->set("champsADesactiver[22]", PreferenceCrudController::PREF_PRO_POLICE_LOCAL_COM)
            //->set("champsADesactiver[23]", PreferenceCrudController::PREF_PRO_POLICE_CANHSARE_LOCAL_COM)
            //->set("champsADesactiver[24]", PreferenceCrudController::PREF_PRO_POLICE_LOCAL_COM_PAYABLE_BY)
            //->set("champsADesactiver[25]", PreferenceCrudController::PREF_PRO_POLICE_FRONTIN_COM)
            //->set("champsADesactiver[26]", PreferenceCrudController::PREF_PRO_POLICE_CANHSARE_FRONTING_COM)
            //->set("champsADesactiver[27]", PreferenceCrudController::PREF_PRO_POLICE_FRONTING_COM_PAYABLE_BY)
            //->set("champsADesactiver[28]", PreferenceCrudController::PREF_PRO_POLICE_REMARQUE)
        ;
    }

    private function desactiverChampsIncorporation(AdminUrlGenerator $adminUrlGenerator)
    {
        $adminUrlGenerator
            ->set("champsADesactiver[0]", PreferenceCrudController::PREF_PRO_POLICE_ID_AVENANT)
            ->set("champsADesactiver[1]", PreferenceCrudController::PREF_PRO_POLICE_TYPE_AVENANT)
            // ->set("champsADesactiver[2]", PreferenceCrudController::PREF_PRO_POLICE_ASSUREURS)
            // ->set("champsADesactiver[3]", PreferenceCrudController::PREF_PRO_POLICE_CLIENT)
            ->set("champsADesactiver[4]", PreferenceCrudController::PREF_PRO_POLICE_REFERENCE)
            // ->set("champsADesactiver[5]", PreferenceCrudController::PREF_PRO_POLICE_PRODUIT)
            ->set("champsADesactiver[6]", PreferenceCrudController::PREF_PRO_POLICE_COTATION)
            // ->set("champsADesactiver[7]", PreferenceCrudController::PREF_PRO_POLICE_REASSUREURS)
            //->set("champsADesactiver[8]", PreferenceCrudController::PREF_PRO_POLICE_CAPITAL)
            //->set("champsADesactiver[9]", PreferenceCrudController::PREF_PRO_POLICE_MODE_PAIEMENT)
            //->set("champsADesactiver[10]", PreferenceCrudController::PREF_PRO_POLICE_PRIME_NETTE)
            //->set("champsADesactiver[11]", PreferenceCrudController::PREF_PRO_POLICE_FRONTING)
            //->set("champsADesactiver[12]", PreferenceCrudController::PREF_PRO_POLICE_ARCA)
            //->set("champsADesactiver[13]", PreferenceCrudController::PREF_PRO_POLICE_TVA)
            //->set("champsADesactiver[14]", PreferenceCrudController::PREF_PRO_POLICE_FRAIS_ADMIN)
            //->set("champsADesactiver[15]", PreferenceCrudController::PREF_PRO_POLICE_DISCOUNT)
            //->set("champsADesactiver[16]", PreferenceCrudController::PREF_PRO_POLICE_PRIME_TOTALE)
            //->set("champsADesactiver[17]", PreferenceCrudController::PREF_PRO_POLICE_PARTENAIRE)
            //->set("champsADesactiver[18]", PreferenceCrudController::PREF_PRO_POLICE_PART_EXCEPTIONNELLE)
            //->set("champsADesactiver[19]", PreferenceCrudController::PREF_PRO_POLICE_RI_COM)
            //->set("champsADesactiver[20]", PreferenceCrudController::PREF_PRO_POLICE_CANHSARE_RI_COM)
            //->set("champsADesactiver[21]", PreferenceCrudController::PREF_PRO_POLICE_RI_COM_PAYABLE_BY)
            //->set("champsADesactiver[22]", PreferenceCrudController::PREF_PRO_POLICE_LOCAL_COM)
            //->set("champsADesactiver[23]", PreferenceCrudController::PREF_PRO_POLICE_CANHSARE_LOCAL_COM)
            //->set("champsADesactiver[24]", PreferenceCrudController::PREF_PRO_POLICE_LOCAL_COM_PAYABLE_BY)
            //->set("champsADesactiver[25]", PreferenceCrudController::PREF_PRO_POLICE_FRONTIN_COM)
            //->set("champsADesactiver[26]", PreferenceCrudController::PREF_PRO_POLICE_CANHSARE_FRONTING_COM)
            //->set("champsADesactiver[27]", PreferenceCrudController::PREF_PRO_POLICE_FRONTING_COM_PAYABLE_BY)
            //->set("champsADesactiver[28]", PreferenceCrudController::PREF_PRO_POLICE_REMARQUE)
        ;
    }

    private function desactiverChampsProrogation(AdminUrlGenerator $adminUrlGenerator)
    {
        $adminUrlGenerator
            ->set("champsADesactiver[0]", PreferenceCrudController::PREF_PRO_POLICE_ID_AVENANT)
            ->set("champsADesactiver[1]", PreferenceCrudController::PREF_PRO_POLICE_TYPE_AVENANT)
            // ->set("champsADesactiver[2]", PreferenceCrudController::PREF_PRO_POLICE_ASSUREURS)
            // ->set("champsADesactiver[3]", PreferenceCrudController::PREF_PRO_POLICE_CLIENT)
            ->set("champsADesactiver[4]", PreferenceCrudController::PREF_PRO_POLICE_REFERENCE)
            // ->set("champsADesactiver[5]", PreferenceCrudController::PREF_PRO_POLICE_PRODUIT)
            ->set("champsADesactiver[6]", PreferenceCrudController::PREF_PRO_POLICE_COTATION)
            // ->set("champsADesactiver[7]", PreferenceCrudController::PREF_PRO_POLICE_REASSUREURS)
            // ->set("champsADesactiver[8]", PreferenceCrudController::PREF_PRO_POLICE_CAPITAL)
            // ->set("champsADesactiver[9]", PreferenceCrudController::PREF_PRO_POLICE_MODE_PAIEMENT)
            //->set("champsADesactiver[10]", PreferenceCrudController::PREF_PRO_POLICE_PRIME_NETTE)
            //->set("champsADesactiver[11]", PreferenceCrudController::PREF_PRO_POLICE_FRONTING)
            //->set("champsADesactiver[12]", PreferenceCrudController::PREF_PRO_POLICE_ARCA)
            //->set("champsADesactiver[13]", PreferenceCrudController::PREF_PRO_POLICE_TVA)
            //->set("champsADesactiver[14]", PreferenceCrudController::PREF_PRO_POLICE_FRAIS_ADMIN)
            //->set("champsADesactiver[15]", PreferenceCrudController::PREF_PRO_POLICE_DISCOUNT)
            //->set("champsADesactiver[16]", PreferenceCrudController::PREF_PRO_POLICE_PRIME_TOTALE)
            // ->set("champsADesactiver[17]", PreferenceCrudController::PREF_PRO_POLICE_PARTENAIRE)
            // ->set("champsADesactiver[18]", PreferenceCrudController::PREF_PRO_POLICE_PART_EXCEPTIONNELLE)
            //->set("champsADesactiver[19]", PreferenceCrudController::PREF_PRO_POLICE_RI_COM)
            //->set("champsADesactiver[20]", PreferenceCrudController::PREF_PRO_POLICE_CANHSARE_RI_COM)
            //->set("champsADesactiver[21]", PreferenceCrudController::PREF_PRO_POLICE_RI_COM_PAYABLE_BY)
            //->set("champsADesactiver[22]", PreferenceCrudController::PREF_PRO_POLICE_LOCAL_COM)
            //->set("champsADesactiver[23]", PreferenceCrudController::PREF_PRO_POLICE_CANHSARE_LOCAL_COM)
            //->set("champsADesactiver[24]", PreferenceCrudController::PREF_PRO_POLICE_LOCAL_COM_PAYABLE_BY)
            //->set("champsADesactiver[25]", PreferenceCrudController::PREF_PRO_POLICE_FRONTIN_COM)
            //->set("champsADesactiver[26]", PreferenceCrudController::PREF_PRO_POLICE_CANHSARE_FRONTING_COM)
            //->set("champsADesactiver[27]", PreferenceCrudController::PREF_PRO_POLICE_FRONTING_COM_PAYABLE_BY)
            //->set("champsADesactiver[28]", PreferenceCrudController::PREF_PRO_POLICE_REMARQUE)
        ;
    }

    private function desactiverChampsAnnulation(AdminUrlGenerator $adminUrlGenerator)
    {
        $adminUrlGenerator
            ->set("champsADesactiver[0]", PreferenceCrudController::PREF_PRO_POLICE_ID_AVENANT)
            ->set("champsADesactiver[1]", PreferenceCrudController::PREF_PRO_POLICE_TYPE_AVENANT)
            // ->set("champsADesactiver[2]", PreferenceCrudController::PREF_PRO_POLICE_ASSUREURS)
            // ->set("champsADesactiver[3]", PreferenceCrudController::PREF_PRO_POLICE_CLIENT)
            // ->set("champsADesactiver[4]", PreferenceCrudController::PREF_PRO_POLICE_REFERENCE)
            // ->set("champsADesactiver[5]", PreferenceCrudController::PREF_PRO_POLICE_PRODUIT)
            ->set("champsADesactiver[6]", PreferenceCrudController::PREF_PRO_POLICE_COTATION);
        // ->set("champsADesactiver[7]", PreferenceCrudController::PREF_PRO_POLICE_REASSUREURS)
        // ->set("champsADesactiver[8]", PreferenceCrudController::PREF_PRO_POLICE_CAPITAL)
        // ->set("champsADesactiver[9]", PreferenceCrudController::PREF_PRO_POLICE_MODE_PAIEMENT)
        // ->set("champsADesactiver[10]", PreferenceCrudController::PREF_PRO_POLICE_PRIME_NETTE)
        // ->set("champsADesactiver[11]", PreferenceCrudController::PREF_PRO_POLICE_FRONTING)
        // ->set("champsADesactiver[12]", PreferenceCrudController::PREF_PRO_POLICE_ARCA)
        // ->set("champsADesactiver[13]", PreferenceCrudController::PREF_PRO_POLICE_TVA)
        // ->set("champsADesactiver[14]", PreferenceCrudController::PREF_PRO_POLICE_FRAIS_ADMIN)
        // ->set("champsADesactiver[15]", PreferenceCrudController::PREF_PRO_POLICE_DISCOUNT)
        // ->set("champsADesactiver[16]", PreferenceCrudController::PREF_PRO_POLICE_PRIME_TOTALE)
        // ->set("champsADesactiver[17]", PreferenceCrudController::PREF_PRO_POLICE_PARTENAIRE)
        // ->set("champsADesactiver[18]", PreferenceCrudController::PREF_PRO_POLICE_PART_EXCEPTIONNELLE)
        // ->set("champsADesactiver[19]", PreferenceCrudController::PREF_PRO_POLICE_RI_COM)
        // ->set("champsADesactiver[20]", PreferenceCrudController::PREF_PRO_POLICE_CANHSARE_RI_COM)
        // ->set("champsADesactiver[21]", PreferenceCrudController::PREF_PRO_POLICE_RI_COM_PAYABLE_BY)
        // ->set("champsADesactiver[22]", PreferenceCrudController::PREF_PRO_POLICE_LOCAL_COM)
        // ->set("champsADesactiver[23]", PreferenceCrudController::PREF_PRO_POLICE_CANHSARE_LOCAL_COM)
        // ->set("champsADesactiver[24]", PreferenceCrudController::PREF_PRO_POLICE_LOCAL_COM_PAYABLE_BY)
        // ->set("champsADesactiver[25]", PreferenceCrudController::PREF_PRO_POLICE_FRONTIN_COM)
        // ->set("champsADesactiver[26]", PreferenceCrudController::PREF_PRO_POLICE_CANHSARE_FRONTING_COM)
        // ->set("champsADesactiver[27]", PreferenceCrudController::PREF_PRO_POLICE_FRONTING_COM_PAYABLE_BY)
        // ->set("champsADesactiver[28]", PreferenceCrudController::PREF_PRO_POLICE_REMARQUE);
    }

    private function desactiverChampsRenouvellement(AdminUrlGenerator $adminUrlGenerator)
    {
        $adminUrlGenerator
            ->set("champsADesactiver[0]", PreferenceCrudController::PREF_PRO_POLICE_ID_AVENANT)
            ->set("champsADesactiver[1]", PreferenceCrudController::PREF_PRO_POLICE_TYPE_AVENANT)
            //->set("champsADesactiver[2]", PreferenceCrudController::PREF_PRO_POLICE_ASSUREURS)
            // ->set("champsADesactiver[3]", PreferenceCrudController::PREF_PRO_POLICE_CLIENT)
            ->set("champsADesactiver[4]", PreferenceCrudController::PREF_PRO_POLICE_REFERENCE)
            // ->set("champsADesactiver[5]", PreferenceCrudController::PREF_PRO_POLICE_PRODUIT)
            ->set("champsADesactiver[6]", PreferenceCrudController::PREF_PRO_POLICE_COTATION)
            //->set("champsADesactiver[7]", PreferenceCrudController::PREF_PRO_POLICE_REASSUREURS)
            //->set("champsADesactiver[8]", PreferenceCrudController::PREF_PRO_POLICE_CAPITAL)
            //->set("champsADesactiver[9]", PreferenceCrudController::PREF_PRO_POLICE_MODE_PAIEMENT)
            //->set("champsADesactiver[10]", PreferenceCrudController::PREF_PRO_POLICE_PRIME_NETTE)
            //->set("champsADesactiver[11]", PreferenceCrudController::PREF_PRO_POLICE_FRONTING)
            //->set("champsADesactiver[12]", PreferenceCrudController::PREF_PRO_POLICE_ARCA)
            //->set("champsADesactiver[13]", PreferenceCrudController::PREF_PRO_POLICE_TVA)
            //->set("champsADesactiver[14]", PreferenceCrudController::PREF_PRO_POLICE_FRAIS_ADMIN)
            //->set("champsADesactiver[15]", PreferenceCrudController::PREF_PRO_POLICE_DISCOUNT)
            //->set("champsADesactiver[16]", PreferenceCrudController::PREF_PRO_POLICE_PRIME_TOTALE)
            //->set("champsADesactiver[17]", PreferenceCrudController::PREF_PRO_POLICE_PARTENAIRE)
            //->set("champsADesactiver[18]", PreferenceCrudController::PREF_PRO_POLICE_PART_EXCEPTIONNELLE)
            //->set("champsADesactiver[19]", PreferenceCrudController::PREF_PRO_POLICE_RI_COM)
            //->set("champsADesactiver[20]", PreferenceCrudController::PREF_PRO_POLICE_CANHSARE_RI_COM)
            //->set("champsADesactiver[21]", PreferenceCrudController::PREF_PRO_POLICE_RI_COM_PAYABLE_BY)
            //->set("champsADesactiver[22]", PreferenceCrudController::PREF_PRO_POLICE_LOCAL_COM)
            //->set("champsADesactiver[23]", PreferenceCrudController::PREF_PRO_POLICE_CANHSARE_LOCAL_COM)
            //->set("champsADesactiver[24]", PreferenceCrudController::PREF_PRO_POLICE_LOCAL_COM_PAYABLE_BY)
            //->set("champsADesactiver[25]", PreferenceCrudController::PREF_PRO_POLICE_FRONTIN_COM)
            //->set("champsADesactiver[26]", PreferenceCrudController::PREF_PRO_POLICE_CANHSARE_FRONTING_COM)
            //->set("champsADesactiver[27]", PreferenceCrudController::PREF_PRO_POLICE_FRONTING_COM_PAYABLE_BY)
            //->set("champsADesactiver[28]", PreferenceCrudController::PREF_PRO_POLICE_REMARQUE)
        ;
    }

    private function desactiverChampsSouscription(AdminUrlGenerator $adminUrlGenerator)
    {
        $adminUrlGenerator
            ->set("champsADesactiver[0]", PreferenceCrudController::PREF_PRO_POLICE_ID_AVENANT)
            ->set("champsADesactiver[1]", PreferenceCrudController::PREF_PRO_POLICE_TYPE_AVENANT)
            //->set("champsADesactiver[2]", PreferenceCrudController::PREF_PRO_POLICE_ASSUREURS)
            //->set("champsADesactiver[3]", PreferenceCrudController::PREF_PRO_POLICE_CLIENT)
            //->set("champsADesactiver[4]", PreferenceCrudController::PREF_PRO_POLICE_REFERENCE)
            //->set("champsADesactiver[5]", PreferenceCrudController::PREF_PRO_POLICE_PRODUIT)
            //->set("champsADesactiver[6]", PreferenceCrudController::PREF_PRO_POLICE_COTATION)
            //->set("champsADesactiver[7]", PreferenceCrudController::PREF_PRO_POLICE_REASSUREURS)
            //->set("champsADesactiver[8]", PreferenceCrudController::PREF_PRO_POLICE_CAPITAL)
            //->set("champsADesactiver[9]", PreferenceCrudController::PREF_PRO_POLICE_MODE_PAIEMENT)
            //->set("champsADesactiver[10]", PreferenceCrudController::PREF_PRO_POLICE_PRIME_NETTE)
            //->set("champsADesactiver[11]", PreferenceCrudController::PREF_PRO_POLICE_FRONTING)
            //->set("champsADesactiver[12]", PreferenceCrudController::PREF_PRO_POLICE_ARCA)
            //->set("champsADesactiver[13]", PreferenceCrudController::PREF_PRO_POLICE_TVA)
            //->set("champsADesactiver[14]", PreferenceCrudController::PREF_PRO_POLICE_FRAIS_ADMIN)
            //->set("champsADesactiver[15]", PreferenceCrudController::PREF_PRO_POLICE_DISCOUNT)
            //->set("champsADesactiver[16]", PreferenceCrudController::PREF_PRO_POLICE_PRIME_TOTALE)
            //->set("champsADesactiver[17]", PreferenceCrudController::PREF_PRO_POLICE_PARTENAIRE)
            //->set("champsADesactiver[18]", PreferenceCrudController::PREF_PRO_POLICE_PART_EXCEPTIONNELLE)
            //->set("champsADesactiver[19]", PreferenceCrudController::PREF_PRO_POLICE_RI_COM)
            //->set("champsADesactiver[20]", PreferenceCrudController::PREF_PRO_POLICE_CANHSARE_RI_COM)
            //->set("champsADesactiver[21]", PreferenceCrudController::PREF_PRO_POLICE_RI_COM_PAYABLE_BY)
            //->set("champsADesactiver[22]", PreferenceCrudController::PREF_PRO_POLICE_LOCAL_COM)
            //->set("champsADesactiver[23]", PreferenceCrudController::PREF_PRO_POLICE_CANHSARE_LOCAL_COM)
            //->set("champsADesactiver[24]", PreferenceCrudController::PREF_PRO_POLICE_LOCAL_COM_PAYABLE_BY)
            //->set("champsADesactiver[25]", PreferenceCrudController::PREF_PRO_POLICE_FRONTIN_COM)
            //->set("champsADesactiver[26]", PreferenceCrudController::PREF_PRO_POLICE_CANHSARE_FRONTING_COM)
            //->set("champsADesactiver[27]", PreferenceCrudController::PREF_PRO_POLICE_FRONTING_COM_PAYABLE_BY)
            //->set("champsADesactiver[28]", PreferenceCrudController::PREF_PRO_POLICE_REMARQUE)
        ;
    }

    private function desactiverChampsAutresModifications(AdminUrlGenerator $adminUrlGenerator)
    {
        $adminUrlGenerator
            ->set("champsADesactiver[0]", PreferenceCrudController::PREF_PRO_POLICE_ID_AVENANT)
            ->set("champsADesactiver[1]", PreferenceCrudController::PREF_PRO_POLICE_TYPE_AVENANT)
            //->set("champsADesactiver[2]", PreferenceCrudController::PREF_PRO_POLICE_ASSUREURS)
            //->set("champsADesactiver[3]", PreferenceCrudController::PREF_PRO_POLICE_CLIENT)
            ->set("champsADesactiver[4]", PreferenceCrudController::PREF_PRO_POLICE_REFERENCE)
            //->set("champsADesactiver[5]", PreferenceCrudController::PREF_PRO_POLICE_PRODUIT)
            //->set("champsADesactiver[6]", PreferenceCrudController::PREF_PRO_POLICE_COTATION)
            //->set("champsADesactiver[7]", PreferenceCrudController::PREF_PRO_POLICE_REASSUREURS)
            //->set("champsADesactiver[8]", PreferenceCrudController::PREF_PRO_POLICE_CAPITAL)
            //->set("champsADesactiver[9]", PreferenceCrudController::PREF_PRO_POLICE_MODE_PAIEMENT)
            //->set("champsADesactiver[10]", PreferenceCrudController::PREF_PRO_POLICE_PRIME_NETTE)
            //->set("champsADesactiver[11]", PreferenceCrudController::PREF_PRO_POLICE_FRONTING)
            //->set("champsADesactiver[12]", PreferenceCrudController::PREF_PRO_POLICE_ARCA)
            //->set("champsADesactiver[13]", PreferenceCrudController::PREF_PRO_POLICE_TVA)
            //->set("champsADesactiver[14]", PreferenceCrudController::PREF_PRO_POLICE_FRAIS_ADMIN)
            //->set("champsADesactiver[15]", PreferenceCrudController::PREF_PRO_POLICE_DISCOUNT)
            //->set("champsADesactiver[16]", PreferenceCrudController::PREF_PRO_POLICE_PRIME_TOTALE)
            //->set("champsADesactiver[17]", PreferenceCrudController::PREF_PRO_POLICE_PARTENAIRE)
            //->set("champsADesactiver[18]", PreferenceCrudController::PREF_PRO_POLICE_PART_EXCEPTIONNELLE)
            //->set("champsADesactiver[19]", PreferenceCrudController::PREF_PRO_POLICE_RI_COM)
            //->set("champsADesactiver[20]", PreferenceCrudController::PREF_PRO_POLICE_CANHSARE_RI_COM)
            //->set("champsADesactiver[21]", PreferenceCrudController::PREF_PRO_POLICE_RI_COM_PAYABLE_BY)
            //->set("champsADesactiver[22]", PreferenceCrudController::PREF_PRO_POLICE_LOCAL_COM)
            //->set("champsADesactiver[23]", PreferenceCrudController::PREF_PRO_POLICE_CANHSARE_LOCAL_COM)
            //->set("champsADesactiver[24]", PreferenceCrudController::PREF_PRO_POLICE_LOCAL_COM_PAYABLE_BY)
            //->set("champsADesactiver[25]", PreferenceCrudController::PREF_PRO_POLICE_FRONTIN_COM)
            //->set("champsADesactiver[26]", PreferenceCrudController::PREF_PRO_POLICE_CANHSARE_FRONTING_COM)
            //->set("champsADesactiver[27]", PreferenceCrudController::PREF_PRO_POLICE_FRONTING_COM_PAYABLE_BY)
            //->set("champsADesactiver[28]", PreferenceCrudController::PREF_PRO_POLICE_REMARQUE)
        ;
        //dd($adminUrlGenerator);
    }

    private function desactiverChampsResiliation(AdminUrlGenerator $adminUrlGenerator)
    {
        $adminUrlGenerator
            ->set("champsADesactiver[0]", PreferenceCrudController::PREF_PRO_POLICE_ID_AVENANT)
            ->set("champsADesactiver[1]", PreferenceCrudController::PREF_PRO_POLICE_TYPE_AVENANT)
            // ->set("champsADesactiver[2]", PreferenceCrudController::PREF_PRO_POLICE_ASSUREURS)
            // ->set("champsADesactiver[3]", PreferenceCrudController::PREF_PRO_POLICE_CLIENT)
            ->set("champsADesactiver[4]", PreferenceCrudController::PREF_PRO_POLICE_REFERENCE)
            // ->set("champsADesactiver[5]", PreferenceCrudController::PREF_PRO_POLICE_PRODUIT)
            ->set("champsADesactiver[6]", PreferenceCrudController::PREF_PRO_POLICE_COTATION)
            // ->set("champsADesactiver[7]", PreferenceCrudController::PREF_PRO_POLICE_REASSUREURS)
            //->set("champsADesactiver[8]", PreferenceCrudController::PREF_PRO_POLICE_CAPITAL)
            // ->set("champsADesactiver[9]", PreferenceCrudController::PREF_PRO_POLICE_MODE_PAIEMENT)
            //->set("champsADesactiver[10]", PreferenceCrudController::PREF_PRO_POLICE_PRIME_NETTE)
            //->set("champsADesactiver[11]", PreferenceCrudController::PREF_PRO_POLICE_FRONTING)
            //->set("champsADesactiver[12]", PreferenceCrudController::PREF_PRO_POLICE_ARCA)
            //->set("champsADesactiver[13]", PreferenceCrudController::PREF_PRO_POLICE_TVA)
            //->set("champsADesactiver[14]", PreferenceCrudController::PREF_PRO_POLICE_FRAIS_ADMIN)
            //->set("champsADesactiver[15]", PreferenceCrudController::PREF_PRO_POLICE_DISCOUNT)
            //->set("champsADesactiver[16]", PreferenceCrudController::PREF_PRO_POLICE_PRIME_TOTALE)
            //->set("champsADesactiver[17]", PreferenceCrudController::PREF_PRO_POLICE_PARTENAIRE)
            //->set("champsADesactiver[18]", PreferenceCrudController::PREF_PRO_POLICE_PART_EXCEPTIONNELLE)
            //->set("champsADesactiver[19]", PreferenceCrudController::PREF_PRO_POLICE_RI_COM)
            //->set("champsADesactiver[20]", PreferenceCrudController::PREF_PRO_POLICE_CANHSARE_RI_COM)
            //->set("champsADesactiver[21]", PreferenceCrudController::PREF_PRO_POLICE_RI_COM_PAYABLE_BY)
            //->set("champsADesactiver[22]", PreferenceCrudController::PREF_PRO_POLICE_LOCAL_COM)
            //->set("champsADesactiver[23]", PreferenceCrudController::PREF_PRO_POLICE_CANHSARE_LOCAL_COM)
            //->set("champsADesactiver[24]", PreferenceCrudController::PREF_PRO_POLICE_LOCAL_COM_PAYABLE_BY)
            //->set("champsADesactiver[25]", PreferenceCrudController::PREF_PRO_POLICE_FRONTIN_COM)
            //->set("champsADesactiver[26]", PreferenceCrudController::PREF_PRO_POLICE_CANHSARE_FRONTING_COM)
            //->set("champsADesactiver[27]", PreferenceCrudController::PREF_PRO_POLICE_FRONTING_COM_PAYABLE_BY)
            //->set("champsADesactiver[28]", PreferenceCrudController::PREF_PRO_POLICE_REMARQUE)
        ;
    }

    public function crossCanal_Avenant_Renouvellement(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        /** @var Police */
        $police = $context->getEntity()->getInstance();
        $this->appliquerDesactivations(PoliceCrudController::AVENANT_TYPE_RENOUVELLEMENT, $adminUrlGenerator);
        $url = $adminUrlGenerator
            ->setController(PoliceCrudController::class)
            ->setAction(Action::NEW)
            ->set("titre", "Renouvellement de la police " . $police . "")
            ->set("avenant[type]", PoliceCrudController::AVENANT_TYPE_RENOUVELLEMENT)
            ->set("avenant[police]", $police->getId())
            ->set("avenant[reference]", $police->getReference())
            ->setEntityId(null)
            //->set("champsACacher[0]", PreferenceCrudController::PREF_PRO_POLICE_COTATION)
            //->set("champsACacher[0]", PreferenceCrudController::PREF_PRO_POLICE_COTATION)
            //->set("champsACacher[1]", PreferenceCrudController::PREF_PRO_POLICE_PRODUIT)
            //->set("champsACacher[2]", PreferenceCrudController::PREF_PRO_POLICE_CLIENT)
            //->set(self::CROSSED_ENTITY_POLICE, $police->getId())
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
            // ->set("champsACacher[0]", PreferenceCrudController::PREF_BIB_DOCUMENT_COTATION)
            // ->set("champsACacher[1]", PreferenceCrudController::PREF_BIB_DOCUMENT_POLICE)
            // ->set("champsACacher[2]", PreferenceCrudController::PREF_BIB_DOCUMENT_SINISTRE)
            ->setEntityId(null)
            ->generateUrl();
        return $url;
    }

    public function crossCanal_Police_ajouterPOPComm(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        /** @var Police */
        $entite = $context->getEntity()->getInstance();
        $url = $adminUrlGenerator
            // ->setController(PaiementCommissionCrudController::class)
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
            // ->setController(PaiementCommissionCrudController::class)
            ->setAction(Action::NEW)
            ->set("titre", "NOUVELLE PDP COMMISSION - [Police: " . $entite . "]")
            ->set(self::CROSSED_ENTITY_POLICE, $entite->getId())
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
            ->set("champsACacher[0]", PreferenceCrudController::PREF_SIN_SINISTRE_POLICE)
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
            ->set("champsACacher[0]", PreferenceCrudController::PREF_CRM_MISSION_POLICE)
            ->set("champsACacher[1]", PreferenceCrudController::PREF_CRM_MISSION_COTATION)
            ->set("champsACacher[2]", PreferenceCrudController::PREF_CRM_MISSION_SINISTRE)
            ->set("champsACacher[3]", PreferenceCrudController::PREF_CRM_MISSION_PISTE)
            ->setEntityId(null)
            ->generateUrl();
        return $url;
    }

    public function crossCanal_Police_ajouterPiste(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        /** @var Police */
        $police = $context->getEntity()->getInstance();
        $url = $adminUrlGenerator
            ->setController(PisteCrudController::class)
            ->setAction(Action::NEW)
            ->set("titre", "NOUVELLE PISTE - [Police: " . $police . "]")
            ->set(self::CROSSED_ENTITY_POLICE, $police->getId())
            ->set("champsACacher[0]", PreferenceCrudController::PREF_CRM_MISSION_POLICE)
            ->set("champsACacher[1]", PreferenceCrudController::PREF_CRM_MISSION_COTATION)
            ->set("champsACacher[2]", PreferenceCrudController::PREF_CRM_MISSION_SINISTRE)
            ->set("champsACacher[3]", PreferenceCrudController::PREF_CRM_MISSION_PISTE)
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
            ->set("champsACacher[0]", PreferenceCrudController::PREF_CRM_MISSION_POLICE)
            ->set("champsACacher[1]", PreferenceCrudController::PREF_CRM_MISSION_COTATION)
            ->set("champsACacher[2]", PreferenceCrudController::PREF_CRM_MISSION_SINISTRE)
            ->set("champsACacher[3]", PreferenceCrudController::PREF_CRM_MISSION_PISTE)
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
            ->set("champsACacher[0]", PreferenceCrudController::PREF_CRM_MISSION_POLICE)
            ->set("champsACacher[1]", PreferenceCrudController::PREF_CRM_MISSION_COTATION)
            ->set("champsACacher[2]", PreferenceCrudController::PREF_CRM_MISSION_SINISTRE)
            ->set("champsACacher[3]", PreferenceCrudController::PREF_CRM_MISSION_PISTE)
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
            ->set("champsACacher[0]", PreferenceCrudController::PREF_SIN_EXPERT_SINISTRES)
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
            ->set("champsACacher[0]", PreferenceCrudController::PREF_SIN_VICTIME_SINISTRE)
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
            // ->set("champsACacher[0]", PreferenceCrudController::PREF_BIB_DOCUMENT_COTATION)
            // ->set("champsACacher[1]", PreferenceCrudController::PREF_BIB_DOCUMENT_POLICE)
            // ->set("champsACacher[2]", PreferenceCrudController::PREF_BIB_DOCUMENT_SINISTRE)
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
            ->set("champsACacher[0]", PreferenceCrudController::PREF_CRM_PISTE_ETAPE)
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
            ->set("champsACacher[0]", PreferenceCrudController::PREF_CRM_MISSION_POLICE)
            ->set("champsACacher[1]", PreferenceCrudController::PREF_CRM_MISSION_COTATION)
            ->set("champsACacher[2]", PreferenceCrudController::PREF_CRM_MISSION_PISTE)
            ->set("champsACacher[3]", PreferenceCrudController::PREF_CRM_MISSION_SINISTRE)
            ->setEntityId(null)
            ->generateUrl();
        return $url;
    }



    // public function crossCanal_Piste_ajouterCotation(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    // {
    //     /** @var Piste */
    //     $entite = $context->getEntity()->getInstance();
    //     $nomAvenant = $this->serviceAvenant->getNomavenant($entite->getTypeavenant());
    //     //dd($entite->getTypeavenant());
    //     $url = $adminUrlGenerator
    //         ->setController(CotationCrudController::class)
    //         ->setAction(Action::NEW)
    //         ->set("titre", $nomAvenant . " - Nouvelle Cotation - [Piste: " . $entite . "]")
    //         //Champs de saisie à cacher obligatoirement car inutiles
    //         ->set("champsACacher[0]", PreferenceCrudController::PREF_CRM_COTATION_POLICE)
    //         ->set("champsACacher[1]", PreferenceCrudController::PREF_CRM_COTATION_MISSIONS)
    //         ->set("champsACacher[2]", PreferenceCrudController::PREF_CRM_COTATION_PISTE)
    //         ->set("avenant[type]", $nomAvenant)
    //         ->set("avenant[police]", $nomAvenant)
    //         ->set(self::CROSSED_ENTITY_PISTE, $entite->getId())
    //         ->setEntityId(null)
    //         ->generateUrl();
    //     //dd($url);
    //     return $url;
    // }

    public function crossCanal_Piste_ajouterContact(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        /** @var Piste */
        $entite = $context->getEntity()->getInstance();
        $url = $adminUrlGenerator
            ->setController(ContactCrudController::class)
            ->setAction(Action::NEW)
            ->set("titre", "NOUVAU CONTACT - [Piste: " . $entite . "]")
            //Champs de saisie à cacher obligatoirement car inutiles
            ->set("champsACacher[0]", PreferenceCrudController::PREF_PRO_CONTACT_PISTE)
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
            ->set('filters[' . self::CROSSED_ENTITY_PISTE . '][value]', $entite->getId()) //il faut juste passer son ID
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

    public function crossCanal_Police_listerPOPComm(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        $entite = $context->getEntity()->getInstance();
        $url = $adminUrlGenerator
            // ->setController(PaiementCommissionCrudController::class)
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
            // ->setController(PaiementCommissionCrudController::class)
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
            // ->setController(PaiementPartenaireCrudController::class)
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
            // ->setController(PaiementTaxeCrudController::class)
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
            // ->setController(PaiementPartenaireCrudController::class)
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

    public function crossCanal_Police_listerPiste(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        /** @var Police */
        $police = $context->getEntity()->getInstance();
        $url = $adminUrlGenerator
            ->setController(PisteCrudController::class)
            ->setAction(Action::INDEX)
            ->set("titre", "LISTE DES PISTES - [Police: " . $police . "]")
            ->set('filters[' . self::CROSSED_ENTITY_POLICE . '][value]', $police->getId()) //il faut juste passer son ID
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
        // if ($police->getCotation() != null) {
        //     if ($police->getCotation()->getPiste() != null) {
        //         $piste = $police->getCotation()->getPiste();
        //     }
        // }

        $url = $adminUrlGenerator
            ->setController(ContactCrudController::class)
            ->setAction(Action::NEW)
            ->set("titre", "NOUVEAU CONTACT - [Police: " . $police . "]")
            ->set(self::CROSSED_ENTITY_PISTE, $piste->getId())
            ->set("champsACacher[0]", PreferenceCrudController::PREF_PRO_CONTACT_PISTE)
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
            ->set("champsACacher[0]", PreferenceCrudController::PREF_PRO_ENGIN_POLICE)
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
        // if ($police->getCotation() != null) {
        //     if ($police->getCotation()->getPiste() != null) {
        //         $piste = $police->getCotation()->getPiste();
        //     }
        // }

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
        $url = $this->resetFilters($adminUrlGenerator);
        $url = $adminUrlGenerator
            ->setController(VictimeCrudController::class)
            ->setAction(Action::INDEX)
            ->set("titre", "LISTE DES VICTIMES - [Sinistre: " . $entite . "]")
            ->set('filters[' . self::CROSSED_ENTITY_SINISTRE . '][value]', $entite->getId()) //il faut juste passer son ID
            ->set('filters[' . self::CROSSED_ENTITY_SINISTRE . '][comparison]', '=')
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
            // ->setController(PaiementTaxeCrudController::class)
            // ->setAction(Action::INDEX)
            // ->set("titre", "LISTE DES PDP TAXE - [Police: " . $entite . "] & [Taxe: " . $taxe . "]")
            // ->set('filters[' . self::CROSSED_ENTITY_POLICE . '][value]', $entite->getId()) //il faut juste passer son ID
            // ->set('filters[' . self::CROSSED_ENTITY_POLICE . '][comparison]', '=')
            // ->set('filters[' . self::CROSSED_ENTITY_TAXE . '][value]', $taxe->getId()) //il faut juste passer son ID
            // ->set('filters[' . self::CROSSED_ENTITY_TAXE . '][comparison]', '=')
            // ->setEntityId(null)
            ->generateUrl();

        return $url;
    }


    public function initChampsFacture(AdminUrlGenerator $adminUrlGenerator, ?string $destinationFacture)
    {
        // dd("ici:", $typeFacture);
        if ($destinationFacture == FactureCrudController::DESTINATION_CLIENT) {
            $adminUrlGenerator
                ->set("titre", "EDITION: FACTURE - " . $destinationFacture)
                ->set("champsACacher[0]", PreferenceCrudController::PREF_FIN_FACTURE_PARTENAIRE)
                ->set("champsACacher[1]", PreferenceCrudController::PREF_FIN_FACTURE_PIECE)
                ->set("champsACacher[2]", PreferenceCrudController::PREF_FIN_FACTURE_AUTRE_TIERS)
                ->set("champsACacher[3]", PreferenceCrudController::PREF_FIN_FACTURE_COMPTES_BANCIARES)
                ->set("champsACacher[4]", PreferenceCrudController::PREF_FIN_FACTURE_TYPE)
                ->set("champsADesactiver[0]", PreferenceCrudController::PREF_FIN_FACTURE_REFERENCE)
                ->set("champsADesactiver[1]", PreferenceCrudController::PREF_FIN_FACTURE_ASSUREUR);
        } else if ($destinationFacture == FactureCrudController::DESTINATION_ASSUREUR) {
            $adminUrlGenerator
                ->set("titre", "EDITION: NOTE DE DEBIT - " . $destinationFacture)
                ->set("champsACacher[0]", PreferenceCrudController::PREF_FIN_FACTURE_PARTENAIRE)
                ->set("champsACacher[1]", PreferenceCrudController::PREF_FIN_FACTURE_PIECE)
                ->set("champsACacher[2]", PreferenceCrudController::PREF_FIN_FACTURE_AUTRE_TIERS)
                ->set("champsACacher[3]", PreferenceCrudController::PREF_FIN_FACTURE_TYPE)
                ->set("champsADesactiver[0]", PreferenceCrudController::PREF_FIN_FACTURE_REFERENCE)
                ->set("champsADesactiver[1]", PreferenceCrudController::PREF_FIN_FACTURE_ASSUREUR);
        } else if ($destinationFacture == FactureCrudController::DESTINATION_PARTENAIRE) {
            $adminUrlGenerator
                ->set("titre", "EDITION: NOTE DE CREDIT - " . $destinationFacture)
                ->set("champsACacher[0]", PreferenceCrudController::PREF_FIN_FACTURE_AUTRE_TIERS)
                ->set("champsACacher[1]", PreferenceCrudController::PREF_FIN_FACTURE_ASSUREUR)
                ->set("champsACacher[2]", PreferenceCrudController::PREF_FIN_FACTURE_PIECE)
                ->set("champsACacher[3]", PreferenceCrudController::PREF_FIN_FACTURE_TYPE)
                ->set("champsACacher[4]", PreferenceCrudController::PREF_FIN_FACTURE_COMPTES_BANCIARES)
                ->set("champsADesactiver[0]", PreferenceCrudController::PREF_FIN_FACTURE_REFERENCE)
                ->set("champsADesactiver[1]", PreferenceCrudController::PREF_FIN_FACTURE_PARTENAIRE);
        } else if ($destinationFacture == FactureCrudController::DESTINATION_ARCA) {
            $adminUrlGenerator
                ->set("titre", "EDITION: NOTE DE CREDIT - " . $destinationFacture)
                ->set("champsACacher[0]", PreferenceCrudController::PREF_FIN_FACTURE_ASSUREUR)
                ->set("champsACacher[1]", PreferenceCrudController::PREF_FIN_FACTURE_PARTENAIRE)
                ->set("champsACacher[2]", PreferenceCrudController::PREF_FIN_FACTURE_PIECE)
                ->set("champsACacher[3]", PreferenceCrudController::PREF_FIN_FACTURE_COMPTES_BANCIARES)
                ->set("champsADesactiver[0]", PreferenceCrudController::PREF_FIN_FACTURE_REFERENCE)
                ->set("champsADesactiver[1]", PreferenceCrudController::PREF_FIN_FACTURE_TYPE)
                ->set("champsADesactiver[2]", PreferenceCrudController::PREF_FIN_FACTURE_AUTRE_TIERS);
        } else if ($destinationFacture == FactureCrudController::DESTINATION_DGI) {
            $adminUrlGenerator
                ->set("titre", "EDITION: NOTE DE CREDIT - " . $destinationFacture)
                ->set("champsACacher[0]", PreferenceCrudController::PREF_FIN_FACTURE_ASSUREUR)
                ->set("champsACacher[1]", PreferenceCrudController::PREF_FIN_FACTURE_PARTENAIRE)
                ->set("champsACacher[2]", PreferenceCrudController::PREF_FIN_FACTURE_PIECE)
                ->set("champsACacher[3]", PreferenceCrudController::PREF_FIN_FACTURE_COMPTES_BANCIARES)
                ->set("champsADesactiver[0]", PreferenceCrudController::PREF_FIN_FACTURE_REFERENCE)
                ->set("champsADesactiver[1]", PreferenceCrudController::PREF_FIN_FACTURE_TYPE)
                ->set("champsADesactiver[2]", PreferenceCrudController::PREF_FIN_FACTURE_AUTRE_TIERS);
        } else {
            dd("Type de facture non reconnu!");
        }
        // dd($typeFacture);
        return $adminUrlGenerator;
    }

    public function crossCanal_creer_facture(AdminUrlGenerator $adminUrlGenerator, array $tabIdTranches, ?string $destination)
    {
        // dd("Ici");
        //$entite = $context->getEntity()->getInstance();
        // $adminUrlGenerator = $this->initChampsFacture($adminUrlGenerator, $typeFacture);
        $url = $adminUrlGenerator
            ->setController(FactureCrudController::class)
            ->setAction(Action::NEW)
            ->set("titre", "Note - Mode édition - " . $destination)
            ->set("donnees[destination]", $destination)
            ->set("donnees[action]", "facture")
            ->set("donnees[tabTranches]", $tabIdTranches)
            ->setEntityId(null)
            ->generateUrl();

        return $url;
    }

    public function crossCanal_payerFacture(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        /** @var Facture */
        $facture = $context->getEntity()->getInstance();
        // dd($facture);
        // $this->paiement_definirChampsCollectionPieces($adminUrlGenerator);
        $url = $adminUrlGenerator
            ->setController(PaiementCrudController::class)
            ->setAction(Action::NEW)
            ->set("titre", "Paiement de la facture " . $facture)
            ->set(self::CROSSED_ENTITY_FACTURE, $facture->getId())
            ->set("donnees[facture]", $facture->getId())
            ->setEntityId(null)
            ->generateUrl();

        return $url;
    }

    public function crossCanal_modifier_facture(AdminUrlGenerator $adminUrlGenerator, Facture $facture)
    {
        //$entite = $context->getEntity()->getInstance();
        $destinationFacture = $this->serviceFacture->getDestination($facture->getDestination());

        $adminUrlGenerator = $this->initChampsFacture($adminUrlGenerator, $destinationFacture);
        $url = $adminUrlGenerator
            ->setController(FactureCrudController::class)
            ->setAction(Action::EDIT)
            //->set("titre", "Modification de " . $typeFacture . " [" . $facture->getReference()."]")
            //->set("donnees[type]", $typeFacture)
            //->set("donnees[action]", "facture")
            //->set("donnees[tabPolices]", $tabIdPolice)
            ->setEntityId($facture->getId())
            ->generateUrl();

        return $url;
    }

    public function paiement_definirChampsCollectionPieces(AdminUrlGenerator $adminUrlGenerator)
    {
        $adminUrlGenerator
            // ->set("champsACacher[0]", PreferenceCrudController::PREF_BIB_DOCUMENT_POLICE)
            // ->set("champsACacher[1]", PreferenceCrudController::PREF_BIB_DOCUMENT_SINISTRE)
            // ->set("champsACacher[5]", PreferenceCrudController::PREF_BIB_DOCUMENT_COTATION)
            ->set("champsADesactiver[0]", PreferenceCrudController::PREF_FIN_PAIEMENT_FACTURE)
            ->set("champsADesactiver[1]", PreferenceCrudController::PREF_FIN_PAIEMENT_TYPE);
    }

    public function crossCanal_modifier_paiement(AdminUrlGenerator $adminUrlGenerator, Paiement $paiement)
    {
        //$entite = $context->getEntity()->getInstance();
        //$typeFacture = $this->serviceFacture->getType($facture->getType());
        //$adminUrlGenerator = $this->initChampsFacture($adminUrlGenerator, $typeFacture);
        $this->paiement_definirChampsCollectionPieces($adminUrlGenerator);
        $url = $adminUrlGenerator
            ->setController(PaiementCrudController::class)
            ->setAction(Action::EDIT)
            ->set("titre", "Modification de " . $paiement)
            //->set("donnees[type]", $typeFacture)
            //->set("donnees[action]", "facture")
            //->set("donnees[tabPolices]", $tabIdPolice)
            ->setEntityId($paiement->getId())
            ->generateUrl();

        return $url;
    }

    public function crossCanal_ouvrir_facture(AdminUrlGenerator $adminUrlGenerator, Facture $facture)
    {
        $destinationFacture = $this->serviceFacture->getDestination($facture->getDestination());
        $adminUrlGenerator = $this->initChampsFacture($adminUrlGenerator, $destinationFacture);
        $url = $adminUrlGenerator
            ->setController(FactureCrudController::class)
            ->setAction(Action::DETAIL)
            ->setEntityId($facture->getId())
            ->generateUrl();
        //dd($url);
        return $url;
    }

    public function crossCanal_ouvrir_paiement(AdminUrlGenerator $adminUrlGenerator, Paiement $paiement)
    {
        //$typeFacture = $this->serviceFacture->getType($facture->getType());
        //$adminUrlGenerator = $this->initChampsFacture($adminUrlGenerator, $typeFacture);
        $url = $adminUrlGenerator
            ->setController(PaiementCrudController::class)
            ->setAction(Action::DETAIL)
            ->setEntityId($paiement->getId())
            ->generateUrl();
        return $url;
    }

    public function crossCanal_ouvrir_facture_pdf(AdminUrlGenerator $adminUrlGenerator, Facture $facture)
    {
        $destinationFacture = $this->serviceFacture->getDestination($facture->getDestination());

        //dd("ici");

        /* $adminUrlGenerator = $this->initChampsFacture($adminUrlGenerator, $typeFacture);
        $url = $adminUrlGenerator
            ->setController(FactureCrudController::class)
            ->setAction(Action::DETAIL)
            ->setEntityId($facture->getId())
            ->generateUrl(); 
            return $url; */
    }

    public function crossCanal_Partenaire_listerPOPRetroComm(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        $entite = $context->getEntity()->getInstance();
        $url = $adminUrlGenerator
            // ->setController(PaiementPartenaireCrudController::class)
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
            // $feedbackCRM->setAction($actionCRM);
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



    public function crossCanal_Paiement_setFacture($container, Paiement $paiement, AdminUrlGenerator $adminUrlGenerator): Paiement
    {
        $paramIDFacture = $adminUrlGenerator->get(self::CROSSED_ENTITY_FACTURE);
        if ($paramIDFacture != null) {
            /** @var Facture  */
            $objetFacture = $this->entityManager->getRepository(Facture::class)->find($paramIDFacture);
            //dd($objetFacture);
            if ($objetFacture != null) {
                $paiement->setFacture($objetFacture);
                $paiement->setMontant($objetFacture->getTotalDu() - $objetFacture->getTotalRecu());

                switch ($objetFacture->getDestination()) {
                    case FactureCrudController::TAB_DESTINATION[FactureCrudController::DESTINATION_ASSUREUR]:
                        $paiement->setDestination(PaiementCrudController::TAB_TYPE_PAIEMENT[PaiementCrudController::TYPE_PAIEMENT_ENTREE]);
                        break;
                    case FactureCrudController::TAB_DESTINATION[FactureCrudController::DESTINATION_CLIENT]:
                        $paiement->setDestination(PaiementCrudController::TAB_TYPE_PAIEMENT[PaiementCrudController::TYPE_PAIEMENT_ENTREE]);
                        break;
                    case FactureCrudController::TAB_DESTINATION[FactureCrudController::DESTINATION_PARTENAIRE]:
                        $paiement->setDestination(PaiementCrudController::TAB_TYPE_PAIEMENT[PaiementCrudController::TYPE_PAIEMENT_SORTIE]);
                        break;
                    case FactureCrudController::TAB_TYPE_NOTE[FactureCrudController::DESTINATION_ARCA]:
                        $paiement->setDestination(PaiementCrudController::TAB_TYPE_PAIEMENT[PaiementCrudController::TYPE_PAIEMENT_SORTIE]);
                        break;
                    case FactureCrudController::TAB_TYPE_NOTE[FactureCrudController::DESTINATION_DGI]:
                        $paiement->setDestination(PaiementCrudController::TAB_TYPE_PAIEMENT[PaiementCrudController::TYPE_PAIEMENT_SORTIE]);
                        break;
                    default:
                        # code...
                        break;
                }
            }
        }
        return $paiement;
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
            // $docPiece->setSinistre($sinistre);
            $docPiece->setPolice($sinistre->getPolice());
            $docPiece->setCotation($sinistre->getPolice()->getCotation());
        }
        return $docPiece;
    }

    public function crossCanal_Piece_setPOPCom(DocPiece $docPiece, AdminUrlGenerator $adminUrlGenerator): DocPiece
    {
        /** @var PaiementCommission */
        //$paiementCommission = null;
        //$paramIDPOPCom = $adminUrlGenerator->get(self::CROSSED_ENTITY_POP_COMMISSIONS);
        //if ($paramIDPOPCom != null) {
        //    $paiementCommission = $this->entityManager->getRepository(PaiementCommission::class)->find($paramIDPOPCom);
        //    $docPiece->setPolice($paiementCommission->getPolice());
        //    $docPiece->setCotation($paiementCommission->getPolice()->getCotation());
        //    $docPiece->addPaiementCommission($paiementCommission);
        //}
        return $docPiece;
    }

    public function crossCanal_Piece_setPOPPartenaire(DocPiece $docPiece, AdminUrlGenerator $adminUrlGenerator): DocPiece
    {
        /** @var PaiementPartenaire */
        //$paiementPartenaire = null;
        //$paramIDPOPPartenaire = $adminUrlGenerator->get(self::CROSSED_ENTITY_POP_PARTENAIRE);
        //if ($paramIDPOPPartenaire != null) {
        //    $paiementPartenaire = $this->entityManager->getRepository(PaiementPartenaire::class)->find($paramIDPOPPartenaire);
        //    $docPiece->setPolice($paiementPartenaire->getPolice());
        //    $docPiece->setCotation($paiementPartenaire->getPolice()->getCotation());
        //    $docPiece->addPaiementPartenaire($paiementPartenaire);
        //}
        return $docPiece;
    }

    public function crossCanal_Piece_setPOPTaxe(DocPiece $docPiece, AdminUrlGenerator $adminUrlGenerator): DocPiece
    {
        /** @var PaiementTaxe */
        //$paiementTaxe = null;
        //$paramIDPOPTaxe = $adminUrlGenerator->get(self::CROSSED_ENTITY_POP_TAXE);
        //if ($paramIDPOPTaxe != null) {
        //    $paiementTaxe = $this->entityManager->getRepository(PaiementTaxe::class)->find($paramIDPOPTaxe);
        //    $docPiece->setPolice($paiementTaxe->getPolice());
        //    $docPiece->setCotation($paiementTaxe->getPolice()->getCotation());
        //    $docPiece->addPaiementTax($paiementTaxe);
        //}
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

    public function crossCanal_Piste_setPolice(Piste $piste, AdminUrlGenerator $adminUrlGenerator): Piste
    {
        $paramID = $adminUrlGenerator->get(self::CROSSED_ENTITY_POLICE);
        if ($paramID != null) {
            /** @var Police */
            $police = $this->entityManager->getRepository(Police::class)->find($paramID);
            $piste->setPolice($police);
            $piste->setTypeavenant(PoliceCrudController::TAB_POLICE_TYPE_AVENANT[PoliceCrudController::AVENANT_TYPE_INCORPORATION]);
        }
        return $piste;
    }

    public function crossCanal_Contact_setPiste(Contact $contact, AdminUrlGenerator $adminUrlGenerator): Contact
    {
        $paramID = $adminUrlGenerator->get(self::CROSSED_ENTITY_PISTE);
        if ($paramID != null) {
            /** @var Piste */
            $piste = $this->entityManager->getRepository(Piste::class)->find($paramID);
            $contact->setPiste($piste);
        }
        return $contact;
    }

    public function crossCanal_Cotation_setPiste($cotation, AdminUrlGenerator $adminUrlGenerator): Cotation
    {
        $paramID = $adminUrlGenerator->get(self::CROSSED_ENTITY_PISTE);
        if ($paramID != null) {
            /** @var Piste */
            $objet = $this->entityManager->getRepository(Piste::class)->find($paramID);
            /** @var Cotation */
            $cotation->setPiste($objet);
        }
        return $cotation;
    }

    public function crossCanal_Police_setCotation($police, AdminUrlGenerator $adminUrlGenerator): Police
    {
        $objet = null;
        $paramID = $adminUrlGenerator->get(self::CROSSED_ENTITY_COTATION);
        if ($paramID != null) {
            $objet = $this->entityManager->getRepository(Cotation::class)->find($paramID);
            /** @var Police */
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

    public function crossCanal_POPComm_setPolice($paiementCommission, AdminUrlGenerator $adminUrlGenerator)
    {
        $objet = null;
        $paramID = $adminUrlGenerator->get(self::CROSSED_ENTITY_POLICE);
        if ($paramID != null) {
            $objet = $this->entityManager->getRepository(Police::class)->find($paramID);
            $paiementCommission->setPolice($objet);
            $paiementCommission->setMontant($objet->calc_revenu_ttc_solde_restant_du); //calc_revenu_ttc_solde_restant_du
        }
        return $paiementCommission;
    }

    public function crossCanal_POPTaxe_setPolice($paiementTaxe, AdminUrlGenerator $adminUrlGenerator)
    {
        $police = null;
        $paramPoliceID = $adminUrlGenerator->get(self::CROSSED_ENTITY_POLICE);
        $paramTaxeID = $adminUrlGenerator->get(self::CROSSED_ENTITY_TAXE);
        if ($paramPoliceID != null && $paramTaxeID != null) {
            /** @var Police */
            $police = $this->entityManager->getRepository(Police::class)->find($paramPoliceID);
            /** @var Taxe */
            $taxe = $this->entityManager->getRepository(Taxe::class)->find($paramTaxeID);
            $paiementTaxe->setPolice($police);
            $paiementTaxe->setTaxe($taxe);
            $paiementTaxe->setExercice(Date("Y"));
            if ($taxe->isPayableparcourtier() == true) {
                //$paiementTaxe->setMontant($police->calc_taxes_courtier_solde);
            } else {
                //$paiementTaxe->setMontant($police->calc_taxes_assureurs_solde);
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
            $sinistre->setPolice($police);
            $sinistre->setOccuredAt(new \DateTimeImmutable("now"));
            $sinistre->setCout(0);
            $sinistre->setMontantPaye(0);
            $sinistre->setTitre("SIN" . Date("dmYHis") . " / " . $police);
            $sinistre->setNumero("TMPSIN" . Date("dmYHis"));
        }
        return $sinistre;
    }


    public function crossCanal_POPRetroComm_setPolice($paiementPartenaire, AdminUrlGenerator $adminUrlGenerator)
    {
        $objet = null;
        $paramID = $adminUrlGenerator->get(self::CROSSED_ENTITY_POLICE);
        if ($paramID != null) {
            $objet = $this->entityManager->getRepository(Police::class)->find($paramID);
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
            // $actionCRM->setPolice($objet);
            // if ($objet->getCotation() != null) {
            //     $actionCRM->setCotation($objet->getCotation());
            //     if ($objet->getCotation()->getPiste() != null) {
            //         $actionCRM->setPiste($objet->getCotation()->getPiste());
            //     }
            // }
        }
        return $actionCRM;
    }

    public function crossCanal_Mission_setCotation(ActionCRM $actionCRM, AdminUrlGenerator $adminUrlGenerator): ActionCRM
    {
        $paramID = $adminUrlGenerator->get(self::CROSSED_ENTITY_COTATION);
        if ($paramID != null) {
            /** @var Cotation */
            $objet = $this->entityManager->getRepository(Cotation::class)->find($paramID);
            // $actionCRM->setCotation($objet);
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
            // $actionCRM->setSinistre($objet);
            //Définition de la police
            if ($objet->getPolice() != null) {
                // $actionCRM->setPolice($objet->getPolice());
                //Définition de la cotation
                // if ($objet->getPolice()->getCotation() != null) {
                //     $actionCRM->setCotation($objet->getPolice()->getCotation());
                //     //Définition de la piste
                //     if ($objet->getPolice()->getCotation()->getPiste() != null) {
                //         $actionCRM->setPiste($objet->getPolice()->getCotation()->getPiste());
                //     }
                // }
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
            //$client->addCotation($objet);
        }
        return $client;
    }

    public function crossCanal_setTitrePage(Crud $crud, AdminUrlGenerator $adminUrlGenerator, $entite): Crud
    {
        // dd($entite);
        if ($adminUrlGenerator->get("titre")) {
            $crud->setPageTitle(Crud::PAGE_INDEX, $adminUrlGenerator->get("titre"));
            $crud->setPageTitle(Crud::PAGE_DETAIL, $adminUrlGenerator->get("titre"));
            $crud->setPageTitle(Crud::PAGE_NEW, $adminUrlGenerator->get("titre"));
        } else {
            $crud->setPageTitle(Crud::PAGE_NEW, "Nouveau - " . ucfirst(strtolower($crud->getAsDto()->getEntityLabelInSingular())));
            $crud->setPageTitle(Crud::PAGE_DETAIL, "Détails sur " . $entite);
        }
        if ($entite) {
            $crud->setPageTitle(Crud::PAGE_EDIT, "Edition de " . $entite);
        }
        return $crud;
    }

    public function reporting_commission_tous(AdminUrlGenerator $adminUrlGenerator, bool $outstanding)
    {
        $url = "";
        if ($outstanding == true) {
            $url = $this->reporting_commission_unpaid($adminUrlGenerator);
        } else {
            $url = $this->reporting_commission_paid($adminUrlGenerator);
        }
        return $url;
    }

    public function reporting_retrocommission_tous(AdminUrlGenerator $adminUrlGenerator, bool $outstanding)
    {
        $url = "";
        if ($outstanding == true) {
            $url = $this->reporting_retrocommission_unpaid($adminUrlGenerator);
        } else {
            $url = $this->reporting_retrocommission_paid($adminUrlGenerator);
        }
        return $url;
    }

    public function reporting_commission_assureur(AdminUrlGenerator $adminUrlGenerator, bool $outstanding, Assureur $assureur)
    {
        $url = "";
        if ($outstanding == true) {
            $url = $this->reporting_commission_unpaid_assureur($adminUrlGenerator, $assureur);
        } else {
            $url = $this->reporting_commission_paid_assureur($adminUrlGenerator, $assureur);
        }
        return $url;
    }

    public function reporting_retrocommission_unpaid_parteniare(AdminUrlGenerator $adminUrlGenerator, bool $outstanding, Partenaire $partenaire)
    {
        $url = "";
        if ($outstanding == true) {
            $url = $this->reporting_retrocommission_unpaid_partenaire($adminUrlGenerator, $partenaire);
        } else {
            $url = $this->reporting_retrocommission_paid_partenaire($adminUrlGenerator, $partenaire);
        }
        return $url;
    }

    private function reporting_commission_paid(AdminUrlGenerator $adminUrlGenerator): string
    {
        $titre = "TOUTES COMMISSIONS ENCAISSEES";
        $adminUrlGenerator = $this->resetFilters($adminUrlGenerator);
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

    private function reporting_retrocommission_paid(AdminUrlGenerator $adminUrlGenerator): string
    {
        $titre = "TOUTES RETRO-COMMISSIONS PAYEES";
        $adminUrlGenerator = $this->resetFilters($adminUrlGenerator);
        $url = $adminUrlGenerator
            ->setController(PoliceCrudController::class)
            ->setAction(Action::INDEX)
            ->set("titre", $titre)
            ->set("codeReporting", ServiceCrossCanal::REPORTING_CODE_PAID_RETROCOM)
            ->set('filters[paidretrocommission][value]', 0)
            ->set('filters[paidretrocommission][comparison]', '>')
            ->setEntityId(null)
            ->generateUrl();

        return $url;
    }

    private function reporting_taxe_paid(AdminUrlGenerator $adminUrlGenerator): string
    {
        $titre = "TOUTES TAXE PAYEES";
        $adminUrlGenerator = $this->resetFilters($adminUrlGenerator);
        $url = $adminUrlGenerator
            ->setController(PoliceCrudController::class)
            ->setAction(Action::INDEX)
            ->set("titre", $titre)
            ->set("codeReporting", ServiceCrossCanal::REPORTING_CODE_PAID_TAXE)
            ->set('filters[paidtaxe][value]', 0)
            ->set('filters[paidtaxe][comparison]', '>')
            ->setEntityId(null)
            ->generateUrl();

        return $url;
    }

    private function reporting_taxe_unpaid(AdminUrlGenerator $adminUrlGenerator): string
    {
        $titre = "TOUTES TAXES IMPAYEES";
        $adminUrlGenerator = $this->resetFilters($adminUrlGenerator);
        $url = $adminUrlGenerator
            ->setController(PoliceCrudController::class)
            ->setAction(Action::INDEX)
            ->set("titre", $titre)
            ->set("codeReporting", ServiceCrossCanal::REPORTING_CODE_UNPAID_TAXE)
            ->set('filters[unpaidtaxe][value]', 0)
            ->set('filters[unpaidtaxe][comparison]', '>')
            ->setEntityId(null)
            ->generateUrl();

        return $url;
    }


    private function reporting_commission_paid_assureur(AdminUrlGenerator $adminUrlGenerator, Assureur $assureur): string
    {
        $titre = "TOUTES COMMISSIONS ENCAISSEES VIA " . strtoupper($assureur->getNom());
        $adminUrlGenerator = $this->resetFilters($adminUrlGenerator);
        $url = $adminUrlGenerator
            ->setController(PoliceCrudController::class)
            ->setAction(Action::INDEX)
            ->set("titre", $titre)
            ->set("codeReporting", ServiceCrossCanal::REPORTING_CODE_PAID_COM)
            ->set('filters[paidcommission][value]', 0)
            ->set('filters[paidcommission][comparison]', '>')
            ->set('filters[assureur][value]', $assureur->getId())
            ->set('filters[assureur][comparison]', '=')
            ->setEntityId(null)
            ->generateUrl();

        return $url;
    }

    private function reporting_retrocommission_paid_partenaire(AdminUrlGenerator $adminUrlGenerator, Partenaire $partenaire): string
    {
        $titre = "TOUTES RETRO-COMMISSIONS PAYEES A " . strtoupper($partenaire->getNom());
        $adminUrlGenerator = $this->resetFilters($adminUrlGenerator);
        $url = $adminUrlGenerator
            ->setController(PoliceCrudController::class)
            ->setAction(Action::INDEX)
            ->set("titre", $titre)
            ->set("codeReporting", ServiceCrossCanal::REPORTING_CODE_PAID_RETROCOM)
            ->set('filters[paidretrocommission][value]', 0)
            ->set('filters[paidretrocommission][comparison]', '>')
            ->set('filters[partenaire][value]', $partenaire->getId())
            ->set('filters[partenaire][comparison]', '=')
            ->setEntityId(null)
            ->generateUrl();

        return $url;
    }

    private function reporting_commission_unpaid(AdminUrlGenerator $adminUrlGenerator): string
    {
        $titre = "TOUTES COMMISSIONS IMPAYEES";
        $adminUrlGenerator = $this->resetFilters($adminUrlGenerator);
        $url = $adminUrlGenerator
            ->setController(PoliceCrudController::class)
            ->setAction(Action::INDEX)
            ->set("titre", $titre)
            ->set("codeReporting", ServiceCrossCanal::REPORTING_CODE_UNPAID_COM)
            ->set('filters[unpaidcommission][value]', 0)
            ->set('filters[unpaidcommission][comparison]', '!=') //
            ->setEntityId(null)
            ->generateUrl();
        return $url;
    }

    private function reporting_retrocommission_unpaid(AdminUrlGenerator $adminUrlGenerator): string
    {
        $titre = "TOUTES RETRO-COMMISSIONS IMPAYEES";
        $adminUrlGenerator = $this->resetFilters($adminUrlGenerator);
        $url = $adminUrlGenerator
            ->setController(PoliceCrudController::class)
            ->setAction(Action::INDEX)
            ->set("titre", $titre)
            ->set("codeReporting", ServiceCrossCanal::REPORTING_CODE_UNPAID_RETROCOM)
            ->set('filters[unpaidretrocommission][value]', 0)
            ->set('filters[unpaidretrocommission][comparison]', '>') //!=
            ->setEntityId(null)
            ->generateUrl();

        return $url;
    }

    private function reporting_commission_unpaid_assureur(AdminUrlGenerator $adminUrlGenerator, Assureur $assureur): string
    {
        $titre = "TOUTES COMMISSIONS IMPAYEES PAR " . strtoupper($assureur->getNom());
        $adminUrlGenerator = $this->resetFilters($adminUrlGenerator);
        $url = $adminUrlGenerator
            ->setController(PoliceCrudController::class)
            ->setAction(Action::INDEX)
            ->set("titre", $titre)
            ->set("codeReporting", ServiceCrossCanal::REPORTING_CODE_UNPAID_COM)
            ->set('filters[unpaidcommission][value]', 0)
            ->set('filters[unpaidcommission][comparison]', '!=') //>
            ->set('filters[assureur][value]', $assureur->getId())
            ->set('filters[assureur][comparison]', '=')
            ->setEntityId(null)
            ->generateUrl();
        return $url;
    }

    private function reporting_retrocommission_unpaid_partenaire(AdminUrlGenerator $adminUrlGenerator, Partenaire $partenaire): string
    {
        $titre = "TOUTES RETRO-COMMISSIONS DUES A " . strtoupper($partenaire->getNom());
        $adminUrlGenerator = $this->resetFilters($adminUrlGenerator);
        $url = $adminUrlGenerator
            ->setController(PoliceCrudController::class)
            ->setAction(Action::INDEX)
            ->set("titre", $titre)
            ->set("codeReporting", ServiceCrossCanal::REPORTING_CODE_UNPAID_RETROCOM)
            ->set('filters[unpaidretrocommission][value]', 0)
            ->set('filters[unpaidretrocommission][comparison]', '>')
            ->set('filters[partenaire][value]', $partenaire->getId())
            ->set('filters[partenaire][comparison]', '=')
            ->setEntityId(null)
            ->generateUrl();
        return $url;
    }

    private function resetFilters(AdminUrlGenerator $adminUrlGenerator): AdminUrlGenerator
    {
        return $adminUrlGenerator
            ->unset("titre")
            ->unset("filters")
            ->unset("donnees")
            ->unset("champsACacher")
            ->unset("codeReporting")
            ->unset("avenant");
    }

    public function reporting_commission_assureur_generer_liens(bool $unpaid)
    {
        $assureurs = $this->entityManager->getRepository(Assureur::class)->findBy([
            'entreprise' => $this->serviceEntreprise->getEntreprise()
        ]);
        $subItemsComm = [];
        $subItemsComm[] = MenuItem::linkToUrl('Tout', 'fas fa-umbrella', $this->reporting_commission_tous($this->adminUrlGenerator, $unpaid));
        //dd($subItemsCommPayee);
        foreach ($assureurs as $assureur) {
            $subItemsComm[] = MenuItem::linkToUrl('' . strtoupper($assureur->getNom()), 'fas fa-umbrella', $this->reporting_commission_assureur($this->adminUrlGenerator, $unpaid, $assureur));
        }
        return $subItemsComm;
    }

    public function reporting_commission_partenaire_generer_liens(bool $unpaid)
    {
        $partenaires = $this->entityManager->getRepository(Partenaire::class)->findBy([
            'entreprise' => $this->serviceEntreprise->getEntreprise()
        ]);
        $subItemsRetroComm = [];
        $subItemsRetroComm[] = MenuItem::linkToUrl('Tout', 'fas fa-handshake', $this->reporting_retrocommission_tous($this->adminUrlGenerator, $unpaid));
        //dd($subItemsCommPayee);
        foreach ($partenaires as $partenaire) {
            $subItemsRetroComm[] = MenuItem::linkToUrl('' . strtoupper($partenaire->getNom()), 'fas fa-handshake', $this->reporting_retrocommission_unpaid_parteniare($this->adminUrlGenerator, $unpaid, $partenaire));
        }
        return $subItemsRetroComm;
    }

    public function reporting_taxe_generer_liens(bool $unpaid)
    {
        $taxes = $this->entityManager->getRepository(Taxe::class)->findBy([
            'entreprise' => $this->serviceEntreprise->getEntreprise()
        ]);
        $subItemsTaxes = [];
        if ($unpaid == true) {
            $subItemsTaxes[] = MenuItem::linkToUrl('Tout', 'fas fa-landmark-dome', $this->reporting_taxe_unpaid($this->adminUrlGenerator));
        } else {
            $subItemsTaxes[] = MenuItem::linkToUrl('Tout', 'fas fa-landmark-dome', $this->reporting_taxe_paid($this->adminUrlGenerator));
        }
        //dd($subItemsCommPayee);
        //Courtier
        foreach ($taxes as $taxe) {
            if ($taxe->isPayableparcourtier() == true) {
                $nomTaxe = "" . $this->serviceTaxes->getNomTaxeCourtier();
                if ($unpaid == true) {
                    $subItemsTaxes[] = MenuItem::linkToUrl(strtoupper($nomTaxe), 'fas fa-landmark-dome', $this->reporting_taxe_unpaid_courtier($this->adminUrlGenerator));
                } else {
                    $subItemsTaxes[] = MenuItem::linkToUrl(strtoupper($nomTaxe), 'fas fa-landmark-dome', $this->reporting_taxe_paid_courtier($this->adminUrlGenerator));
                }
            }
        }
        foreach ($taxes as $taxe) {
            if ($taxe->isPayableparcourtier() == false) {
                $nomTaxe = "" . $this->serviceTaxes->getNomTaxeAssureur();
                if ($unpaid == true) {
                    $subItemsTaxes[] = MenuItem::linkToUrl(strtoupper($nomTaxe), 'fas fa-landmark-dome', $this->reporting_taxe_unpaid_assureur($this->adminUrlGenerator));
                } else {
                    $subItemsTaxes[] = MenuItem::linkToUrl(strtoupper($nomTaxe), 'fas fa-landmark-dome', $this->reporting_taxe_paid_assureur($this->adminUrlGenerator));
                }
            }
        }
        return $subItemsTaxes;
    }

    public function reporting_production_assureur_generer_liens()
    {
        $subItemsTaxes = [];
        $assureurs = $this->entityManager->getRepository(Assureur::class)->findBy([
            'entreprise' => $this->serviceEntreprise->getEntreprise()
        ]);
        //TOUS
        $subItemsTaxes[] = MenuItem::linkToUrl('Tout', 'fas fa-umbrella', $this->reporting_production_assureur_tous($this->adminUrlGenerator));
        //PART ASSUREURS
        foreach ($assureurs as $assureur) {
            $subItemsTaxes[] = MenuItem::linkToUrl(strtoupper($assureur), 'fas fa-umbrella', $this->reporting_production_assureur($this->adminUrlGenerator, $assureur));
        }
        return $subItemsTaxes;
    }

    public function reporting_production_gestionnaire_generer_liens()
    {
        $subItemsTaxes = [];
        $gestionnaires = $this->entityManager->getRepository(Utilisateur::class)->findBy([
            'entreprise' => $this->serviceEntreprise->getEntreprise()
        ]);
        //TOUS
        //$subItemsTaxes[] = MenuItem::linkToUrl('TOUS', 'fa-solid fa-user', $this->reporting_production_assureur_tous($this->adminUrlGenerator));
        //PART ASSUREURS
        foreach ($gestionnaires as $gestionnaire) {
            $subItemsTaxes[] = MenuItem::linkToUrl(strtoupper($gestionnaire), 'fa-solid fa-user', $this->reporting_production_gestionnaire($this->adminUrlGenerator, $gestionnaire));
        }
        return $subItemsTaxes;
    }

    public function reporting_production_partenaire_generer_liens()
    {
        $subItemsTaxes = [];
        $partenaires = $this->entityManager->getRepository(Partenaire::class)->findBy([
            'entreprise' => $this->serviceEntreprise->getEntreprise()
        ]);
        //PART ASSUREURS
        foreach ($partenaires as $partenaire) {
            $subItemsTaxes[] = MenuItem::linkToUrl(strtoupper($partenaire), 'fas fa-handshake', $this->reporting_production_partenaire($this->adminUrlGenerator, $partenaire));
        }
        return $subItemsTaxes;
    }

    public function reporting_production_produit_generer_liens()
    {
        $subItemsTaxes = [];
        $produits = $this->entityManager->getRepository(Produit::class)->findBy([
            'entreprise' => $this->serviceEntreprise->getEntreprise()
        ]);
        //PRODUITS
        foreach ($produits as $produit) {
            $subItemsTaxes[] = MenuItem::linkToUrl(strtoupper($produit), 'fas fa-gifts', $this->reporting_production_produit($this->adminUrlGenerator, $produit));
        }
        return $subItemsTaxes;
    }

    public function reporting_piste_etape_generer_liens() //fas fa-location-crosshairs
    {
        $subItemsTaxes = [];
        $etapes = $this->entityManager->getRepository(EtapeCrm::class)->findBy([
            'entreprise' => $this->serviceEntreprise->getEntreprise()
        ]);
        //TOUS
        $subItemsTaxes[] = MenuItem::linkToUrl('Tout', 'fas fa-location-crosshairs', $this->reporting_piste_tous($this->adminUrlGenerator));

        //ETAPES
        foreach ($etapes as $etape) {
            $subItemsTaxes[] = MenuItem::linkToUrl(strtoupper($etape), 'fas fa-location-crosshairs', $this->reporting_piste_etape($this->adminUrlGenerator, $etape));
        }
        return $subItemsTaxes;
    }

    public function reporting_piste_utilisateur_generer_liens() //fas fa-location-crosshairs
    {
        $subItemsTaxes = [];
        $utilisateurs = $this->entityManager->getRepository(Utilisateur::class)->findBy([
            'entreprise' => $this->serviceEntreprise->getEntreprise()
        ]);
        //USERS
        foreach ($utilisateurs as $user) {
            $subItemsTaxes[] = MenuItem::linkToUrl(strtoupper($user), 'fas fa-user', $this->reporting_piste_utilisateur($this->adminUrlGenerator, $user));
        }
        return $subItemsTaxes;
    }

    public function reporting_sinistre_etape_generer_liens()
    {
        $subItemsTaxes = [];
        $etapes = $this->entityManager->getRepository(EtapeSinistre::class)->findBy([
            'entreprise' => $this->serviceEntreprise->getEntreprise()
        ]);
        //TOUS
        $subItemsTaxes[] = MenuItem::linkToUrl('Tout', 'fas fa-bell', $this->reporting_sinistre_tous($this->adminUrlGenerator));

        //ETAPES
        foreach ($etapes as $etape) {
            $subItemsTaxes[] = MenuItem::linkToUrl(strtoupper($etape), 'fas fa-bell', $this->reporting_sinistre_etape($this->adminUrlGenerator, $etape));
        }
        return $subItemsTaxes;
    }

    private function reporting_production_assureur_tous(AdminUrlGenerator $adminUrlGenerator): string
    {
        $titre = "PRODUCTION GLOBALE";
        $adminUrlGenerator = $this->resetFilters($adminUrlGenerator);
        $url = $adminUrlGenerator
            ->setController(PoliceCrudController::class)
            ->setAction(Action::INDEX)
            ->set("titre", $titre)
            ->set("codeReporting", ServiceCrossCanal::REPORTING_CODE_PRODUCTION_TOUS)
            ->setEntityId(null)
            ->generateUrl();
        return $url;
    }

    private function reporting_sinistre_tous(AdminUrlGenerator $adminUrlGenerator): string
    {
        $titre = "ETAT GLOBALE SINISTRE";
        $adminUrlGenerator = $this->resetFilters($adminUrlGenerator);
        $url = $adminUrlGenerator
            ->setController(SinistreCrudController::class)
            ->setAction(Action::INDEX)
            ->set("titre", $titre)
            ->set("codeReporting", ServiceCrossCanal::REPORTING_CODE_SINISTRE_TOUS)
            ->setEntityId(null)
            ->generateUrl();
        return $url;
    }

    private function reporting_piste_tous(AdminUrlGenerator $adminUrlGenerator): string
    {
        $titre = "ETAT GLOBALE DES PISTES DU CRM";
        $adminUrlGenerator = $this->resetFilters($adminUrlGenerator);
        $url = $adminUrlGenerator
            ->setController(PisteCrudController::class)
            ->setAction(Action::INDEX)
            ->set("titre", $titre)
            ->set("codeReporting", ServiceCrossCanal::REPORTING_CODE_PISTE_TOUS)
            ->setEntityId(null)
            ->generateUrl();
        return $url;
    }

    private function reporting_production_assureur(AdminUrlGenerator $adminUrlGenerator, Assureur $assureur): string
    {
        $titre = "PRODUCTION AVEC " . strtoupper($assureur);
        $adminUrlGenerator = $this->resetFilters($adminUrlGenerator);
        $url = $adminUrlGenerator
            ->setController(PoliceCrudController::class)
            ->setAction(Action::INDEX)
            ->set("titre", $titre)
            ->set("codeReporting", ServiceCrossCanal::REPORTING_CODE_PRODUCTION_TOUS)
            ->set('filters[assureur][value]', $assureur->getId())
            ->set('filters[assureur][comparison]', '=')
            ->setEntityId(null)
            ->generateUrl();
        return $url;
    }

    private function reporting_production_gestionnaire(AdminUrlGenerator $adminUrlGenerator, Utilisateur $gestionnaire): string
    {
        $titre = "PRODUCTION DE " . strtoupper($gestionnaire);
        $adminUrlGenerator = $this->resetFilters($adminUrlGenerator);
        $url = $adminUrlGenerator
            ->setController(PoliceCrudController::class)
            ->setAction(Action::INDEX)
            ->set("titre", $titre)
            ->set("codeReporting", ServiceCrossCanal::REPORTING_CODE_PRODUCTION_TOUS)
            ->set('filters[gestionnaire][value]', $gestionnaire->getId())
            ->set('filters[gestionnaire][comparison]', '=')
            ->setEntityId(null)
            ->generateUrl();
        return $url;
    }

    private function reporting_production_partenaire(AdminUrlGenerator $adminUrlGenerator, Partenaire $partenaire): string
    {
        $titre = "PRODUCTION AVEC " . strtoupper($partenaire);
        $adminUrlGenerator = $this->resetFilters($adminUrlGenerator);
        $url = $adminUrlGenerator
            ->setController(PoliceCrudController::class)
            ->setAction(Action::INDEX)
            ->set("titre", $titre)
            ->set("codeReporting", ServiceCrossCanal::REPORTING_CODE_PRODUCTION_TOUS)
            ->set('filters[partenaire][value]', $partenaire->getId())
            ->set('filters[partenaire][comparison]', '=')
            ->setEntityId(null)
            ->generateUrl();
        return $url;
    }

    private function reporting_production_produit(AdminUrlGenerator $adminUrlGenerator, Produit $produit): string
    {
        $titre = "PRODUCTION EN " . strtoupper($produit);
        $adminUrlGenerator = $this->resetFilters($adminUrlGenerator);
        $url = $adminUrlGenerator
            ->setController(PoliceCrudController::class)
            ->setAction(Action::INDEX)
            ->set("titre", $titre)
            ->set("codeReporting", ServiceCrossCanal::REPORTING_CODE_PRODUCTION_TOUS)
            ->set('filters[produit][value]', $produit->getId())
            ->set('filters[produit][comparison]', '=')
            ->setEntityId(null)
            ->generateUrl();
        return $url;
    }

    private function reporting_sinistre_etape(AdminUrlGenerator $adminUrlGenerator, EtapeSinistre $etape): string
    {
        $titre = "SINISTRE - ETAPE ACTUELLE: " . strtoupper($etape);
        $adminUrlGenerator = $this->resetFilters($adminUrlGenerator);
        $url = $adminUrlGenerator
            ->setController(SinistreCrudController::class)
            ->setAction(Action::INDEX)
            ->set("titre", $titre)
            ->set("codeReporting", ServiceCrossCanal::REPORTING_CODE_SINISTRE_TOUS)
            ->set('filters[etape][value]', $etape->getId())
            ->set('filters[etape][comparison]', '=')
            ->setEntityId(null)
            ->generateUrl();
        return $url;
    }

    private function reporting_piste_etape(AdminUrlGenerator $adminUrlGenerator, EtapeCrm $etape): string
    {
        $titre = "PISTE - ETAPE ACTUELLE: " . strtoupper($etape);
        $adminUrlGenerator = $this->resetFilters($adminUrlGenerator);
        $url = $adminUrlGenerator
            ->setController(PisteCrudController::class)
            ->setAction(Action::INDEX)
            ->set("titre", $titre)
            ->set("codeReporting", ServiceCrossCanal::REPORTING_CODE_PISTE_TOUS)
            ->set('filters[etape][value]', $etape->getId())
            ->set('filters[etape][comparison]', '=')
            ->setEntityId(null)
            ->generateUrl();
        return $url;
    }

    private function reporting_piste_utilisateur(AdminUrlGenerator $adminUrlGenerator, Utilisateur $user): string
    {
        $titre = "PISTES DE " . strtoupper($user);
        $adminUrlGenerator = $this->resetFilters($adminUrlGenerator);
        $url = $adminUrlGenerator
            ->setController(PisteCrudController::class)
            ->setAction(Action::INDEX)
            ->set("titre", $titre)
            ->set("codeReporting", ServiceCrossCanal::REPORTING_CODE_PISTE_TOUS)
            ->set('filters[utilisateur][value]', $user->getId())
            ->set('filters[utilisateur][comparison]', '=')
            ->setEntityId(null)
            ->generateUrl();
        return $url;
    }

    private function reporting_taxe_unpaid_courtier(AdminUrlGenerator $adminUrlGenerator): string
    {
        $titre = "TOUTES LES TAXES " . strtoupper($this->serviceTaxes->getNomTaxeCourtier()) . " IMPAYEES";
        $adminUrlGenerator = $this->resetFilters($adminUrlGenerator);
        $url = $adminUrlGenerator
            ->setController(PoliceCrudController::class)
            ->setAction(Action::INDEX)
            ->set("titre", $titre)
            ->set("codeReporting", ServiceCrossCanal::REPORTING_CODE_UNPAID_TAXE_COURTIER)
            ->set('filters[unpaidtaxecourtier][value]', 0)
            ->set('filters[unpaidtaxecourtier][comparison]', '>')
            ->setEntityId(null)
            ->generateUrl();
        return $url;
    }

    private function reporting_taxe_paid_courtier(AdminUrlGenerator $adminUrlGenerator): string
    {
        $titre = "TOUTES LES TAXES " . strtoupper($this->serviceTaxes->getNomTaxeCourtier()) . " PAYEES";
        $adminUrlGenerator = $this->resetFilters($adminUrlGenerator);
        $url = $adminUrlGenerator
            ->setController(PoliceCrudController::class)
            ->setAction(Action::INDEX)
            ->set("titre", $titre)
            ->set("codeReporting", ServiceCrossCanal::REPORTING_CODE_PAID_TAXE_COURTIER)
            ->set('filters[paidtaxecourtier][value]', 0)
            ->set('filters[paidtaxecourtier][comparison]', '>')
            ->setEntityId(null)
            ->generateUrl();
        return $url;
    }

    private function reporting_taxe_unpaid_assureur(AdminUrlGenerator $adminUrlGenerator): string
    {
        $titre = "TOUTES LES TAXES " . strtoupper($this->serviceTaxes->getNomTaxeAssureur()) . " IMPAYEES";
        $adminUrlGenerator = $this->resetFilters($adminUrlGenerator);
        $url = $adminUrlGenerator
            ->setController(PoliceCrudController::class)
            ->setAction(Action::INDEX)
            ->set("titre", $titre)
            ->set("codeReporting", ServiceCrossCanal::REPORTING_CODE_UNPAID_TAXE_ASSUREUR)
            ->set('filters[unpaidtaxeassureur][value]', 0)
            ->set('filters[unpaidtaxeassureur][comparison]', '>')
            ->setEntityId(null)
            ->generateUrl();
        return $url;
    }

    private function reporting_taxe_paid_assureur(AdminUrlGenerator $adminUrlGenerator): string
    {
        $titre = "TOUTES LES TAXES " . strtoupper($this->serviceTaxes->getNomTaxeAssureur()) . " PAYEES";
        $adminUrlGenerator = $this->resetFilters($adminUrlGenerator);
        $url = $adminUrlGenerator
            ->setController(PoliceCrudController::class)
            ->setAction(Action::INDEX)
            ->set("titre", $titre)
            ->set("codeReporting", ServiceCrossCanal::REPORTING_CODE_PAID_TAXE_ASSUREUR)
            ->set('filters[paidtaxeassureur][value]', 0)
            ->set('filters[paidtaxeassureur][comparison]', '>')
            ->setEntityId(null)
            ->generateUrl();
        return $url;
    }
}
