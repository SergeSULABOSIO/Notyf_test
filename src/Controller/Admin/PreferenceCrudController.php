<?php

namespace App\Controller\Admin;

use App\Entity\Preference;
use App\Service\ServiceEntreprise;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;

class PreferenceCrudController extends AbstractCrudController
{
    //CRM - ACTION / MISSION
    public const PREF_CRM_MISSION_ID = 0;
    public const PREF_CRM_MISSION_MISSION = 1;
    public const PREF_CRM_MISSION_OBJECTIF = 2;
    public const PREF_CRM_MISSION_STARTED_AT = 3;
    public const PREF_CRM_MISSION_ENDED_AT = 4;
    public const PREF_CRM_MISSION_UTILISATEUR = 5;
    public const PREF_CRM_MISSION_ENTREPRISE = 6;
    public const PREF_CRM_MISSION_CREATED_AT = 7;
    public const PREF_CRM_MISSION_UPDATED_AT = 8;
    public const TAB_CRM_MISSION = [
        "Id" => self::PREF_CRM_MISSION_ID,
        "Nom" => self::PREF_CRM_MISSION_MISSION,
        "Objectif" => self::PREF_CRM_MISSION_OBJECTIF,
        "Date d'effet" => self::PREF_CRM_MISSION_STARTED_AT,
        "Echéance" => self::PREF_CRM_MISSION_ENDED_AT,
        "Utilisateur" => self::PREF_CRM_MISSION_UTILISATEUR,
        "Entreprise" => self::PREF_CRM_MISSION_ENTREPRISE,
        "Date de création" => self::PREF_CRM_MISSION_CREATED_AT,
        "Dernière modification" => self::PREF_CRM_MISSION_UPDATED_AT
    ];

    //CRM - FEEDBACK
    public const PREF_CRM_FEEDBACK_ID = 0;
    public const PREF_CRM_FEEDBACK_MESAGE = 1;
    public const PREF_CRM_FEEDBACK_PROCHAINE_ETAPE = 2;
    public const PREF_CRM_FEEDBACK_DATE_EFFET = 3;
    public const PREF_CRM_FEEDBACK_ACTION = 4;
    public const PREF_CRM_FEEDBACK_DATE_CREATION = 5;
    public const PREF_CRM_FEEDBACK_DATE_MODIFICATION = 6;
    public const PREF_CRM_FEEDBACK_UTILISATEUR = 7;
    public const PREF_CRM_FEEDBACK_ENTREPRISE = 8;
    public const TAB_CRM_FEEDBACK = [
        "Id" => self::PREF_CRM_FEEDBACK_ID,
        "Message" => self::PREF_CRM_FEEDBACK_MESAGE,
        "Mission suivante" => self::PREF_CRM_FEEDBACK_PROCHAINE_ETAPE,
        "Date d'effet" => self::PREF_CRM_FEEDBACK_DATE_EFFET,
        "Mission" => self::PREF_CRM_FEEDBACK_ACTION,
        "Utilisateur" => self::PREF_CRM_FEEDBACK_UTILISATEUR,
        "Entreprise" => self::PREF_CRM_FEEDBACK_ENTREPRISE,
        "Date de création" => self::PREF_CRM_FEEDBACK_DATE_CREATION,
        "Dernière modification" => self::PREF_CRM_FEEDBACK_DATE_MODIFICATION
    ];
    //CRM - COTATION
    public const PREF_CRM_COTATION_ID = 0;
    public const PREF_CRM_COTATION_NOM = 1;
    public const PREF_CRM_COTATION_ASSUREUR = 2;
    public const PREF_CRM_COTATION_MONNAIE = 3;
    public const PREF_CRM_COTATION_PRIME_TOTALE = 4;
    public const PREF_CRM_COTATION_RISQUE = 5;
    public const PREF_CRM_COTATION_PISTE = 6;
    public const PREF_CRM_COTATION_PIECES = 7;
    public const PREF_CRM_COTATION_DATE_CREATION = 8;
    public const PREF_CRM_COTATION_DATE_MODIFICATION = 9;
    public const PREF_CRM_COTATION_DATE_UTILISATEUR = 10;
    public const PREF_CRM_COTATION_DATE_ENTREPRISE = 11;
    public const TAB_CRM_COTATIONS = [
        'Id' => self::PREF_CRM_COTATION_ID,
        'Nom' => self::PREF_CRM_COTATION_NOM,
        'Assureur' => self::PREF_CRM_COTATION_ASSUREUR,
        'Monnaie' => self::PREF_CRM_COTATION_MONNAIE,
        'Prime totale' => self::PREF_CRM_COTATION_PRIME_TOTALE,
        'Risque' => self::PREF_CRM_COTATION_RISQUE,
        'Piste' => self::PREF_CRM_COTATION_PISTE,
        'Pièces' => self::PREF_CRM_COTATION_PIECES,
        'Utilisateur' => self::PREF_CRM_COTATION_DATE_UTILISATEUR,
        'Entreprise' => self::PREF_CRM_COTATION_DATE_ENTREPRISE,
        'Date de création' => self::PREF_CRM_COTATION_DATE_CREATION,
        'Dernière modification' => self::PREF_CRM_COTATION_DATE_MODIFICATION
    ];
    //CRM - ETAPES
    public const PREF_CRM_ETAPES_ID = 0;
    public const PREF_CRM_ETAPES_NOM = 1;
    public const PREF_CRM_ETAPES_UTILISATEUR = 2;
    public const PREF_CRM_ETAPES_ENTREPRISE = 3;
    public const PREF_CRM_ETAPES_DATE_CREATION = 4;
    public const PREF_CRM_ETAPES_DATE_MODIFICATION = 5;
    public const TAB_CRM_ETAPES = [
        'Id' => self::PREF_CRM_ETAPES_ID,
        'Nom' => self::PREF_CRM_ETAPES_NOM,
        'Utilisateur' => self::PREF_CRM_ETAPES_UTILISATEUR,
        'Entreprise' => self::PREF_CRM_ETAPES_ENTREPRISE,
        'Date de création' => self::PREF_CRM_ETAPES_DATE_CREATION,
        'Dernière modification' => self::PREF_CRM_ETAPES_DATE_MODIFICATION
    ];
    //CRM - PISTE
    public const PREF_CRM_PISTE_ID = 0;
    public const PREF_CRM_PISTE_NOM = 1;
    public const PREF_CRM_PISTE_CONTACT = 2;
    public const PREF_CRM_PISTE_OBJECTIF = 3;
    public const PREF_CRM_PISTE_MONTANT = 4;
    public const PREF_CRM_PISTE_ETAPE = 5;
    public const PREF_CRM_PISTE_DATE_EXPIRATION = 6;
    public const PREF_CRM_PISTE_ACTIONS = 7;
    public const PREF_CRM_PISTE_COTATION = 8;
    public const PREF_CRM_PISTE_UTILISATEUR = 9;
    public const PREF_CRM_PISTE_ENTREPRISE = 10;
    public const PREF_CRM_PISTE_DATE_DE_CREATION = 11;
    public const PREF_CRM_PISTE_DATE_DE_MODIFICATION = 12;
    public const TAB_CRM_PISTE = [
        'Id' => self::PREF_CRM_PISTE_ID,
        'Nom' => self::PREF_CRM_PISTE_NOM,
        'Contact' => self::PREF_CRM_PISTE_CONTACT,
        'Objectif' => self::PREF_CRM_PISTE_OBJECTIF,
        'Montant' => self::PREF_CRM_PISTE_MONTANT,
        'Etape' => self::PREF_CRM_PISTE_ETAPE,
        'Echéance' => self::PREF_CRM_PISTE_DATE_EXPIRATION,
        'Actions' => self::PREF_CRM_PISTE_ACTIONS,
        'Cotactions' => self::PREF_CRM_PISTE_COTATION,
        'Utilisateur' => self::PREF_CRM_PISTE_UTILISATEUR,
        'Entreprise' => self::PREF_CRM_PISTE_ENTREPRISE,
        'Date de création' => self::PREF_CRM_PISTE_DATE_DE_CREATION,
        'Dernière modification' => self::PREF_CRM_PISTE_DATE_DE_MODIFICATION
    ];
    //PRODUCTION - ASSUEUR
    public const PREF_PRO_ASSUREUR_ID = 0;
    public const PREF_PRO_ASSUREUR_NOM = 1;
    public const PREF_PRO_ASSUREUR_ADRESSE = 2;
    public const PREF_PRO_ASSUREUR_TELEPHONE = 3;
    public const PREF_PRO_ASSUREUR_EMAIL = 4;
    public const PREF_PRO_ASSUREUR_SITE_WEB = 5;
    public const PREF_PRO_ASSUREUR_RCCM = 6;
    public const PREF_PRO_ASSUREUR_IDNAT = 7;
    public const PREF_PRO_ASSUREUR_LICENCE = 8;
    public const PREF_PRO_ASSUREUR_NUM_IMPOT = 9;
    public const PREF_PRO_ASSUREUR_IS_REASSUREUR = 10;
    public const PREF_PRO_ASSUREUR_UTILISATEUR = 11;
    public const PREF_PRO_ASSUREUR_ENTREPRISE = 12;
    public const PREF_PRO_ASSUREUR_DATE_DE_CREATION = 13;
    public const PREF_PRO_ASSUREUR_DATE_DE_MODIFICATION = 14;
    public const TAB_PRO_ASSUREURS = [
        'Id' => self::PREF_PRO_ASSUREUR_ID,
        'Nom' => self::PREF_PRO_ASSUREUR_NOM,
        'Adresse' => self::PREF_PRO_ASSUREUR_ADRESSE,
        'Téléphone' => self::PREF_PRO_ASSUREUR_TELEPHONE,
        'Email' => self::PREF_PRO_ASSUREUR_EMAIL,
        'Site Web' => self::PREF_PRO_ASSUREUR_SITE_WEB,
        'Rccm' => self::PREF_PRO_ASSUREUR_RCCM,
        'IdNat' => self::PREF_PRO_ASSUREUR_IDNAT,
        'Licence' => self::PREF_PRO_ASSUREUR_LICENCE,
        'N° Impôt' => self::PREF_PRO_ASSUREUR_NUM_IMPOT,
        'Réassureur?' => self::PREF_PRO_ASSUREUR_IS_REASSUREUR,
        'Utilisateur' => self::PREF_PRO_ASSUREUR_UTILISATEUR,
        'Entreprise' => self::PREF_PRO_ASSUREUR_ENTREPRISE,
        'Date de création' => self::PREF_PRO_ASSUREUR_DATE_DE_CREATION,
        'Dernière modification' => self::PREF_PRO_ASSUREUR_DATE_DE_MODIFICATION
    ];
    //PRODUCTION - ENGIN
    public const PREF_PRO_ENGIN_ID = 0;
    public const PREF_PRO_ENGIN_MODEL = 1;
    public const PREF_PRO_ENGIN_MARQUE = 2;
    public const PREF_PRO_ENGIN_ANNEE = 3;
    public const PREF_PRO_ENGIN_PUISSANCE = 4;
    public const PREF_PRO_ENGIN_MONNAIE = 5;
    public const PREF_PRO_ENGIN_VALEUR = 6;
    public const PREF_PRO_ENGIN_NB_SIEGES = 7;
    public const PREF_PRO_ENGIN_USAGE = 8;
    public const PREF_PRO_ENGIN_NATURE = 9;
    public const PREF_PRO_ENGIN_N°_PLAQUE = 10;
    public const PREF_PRO_ENGIN_N°_CHASSIS = 11;
    public const PREF_PRO_ENGIN_POLICE = 12;
    public const PREF_PRO_ENGIN_UTILISATEUR = 13;
    public const PREF_PRO_ENGIN_ENTREPRISE = 14;
    public const PREF_PRO_ENGIN_DATE_DE_CREATION = 15;
    public const PREF_PRO_ENGIN_DATE_DE_MODIFICATION = 16;
    public const TAB_PRO_ENGINS = [
        'Id' => self::PREF_PRO_ENGIN_ID,
        'Modèle' => self::PREF_PRO_ENGIN_MODEL,
        'Marque' => self::PREF_PRO_ENGIN_MARQUE,
        'Année' => self::PREF_PRO_ENGIN_ANNEE,
        'Puissance' => self::PREF_PRO_ENGIN_PUISSANCE,
        'Monnaie' => self::PREF_PRO_ENGIN_MONNAIE,
        'Valeur' => self::PREF_PRO_ENGIN_VALEUR,
        'Sièges' => self::PREF_PRO_ENGIN_NB_SIEGES,
        'Usage' => self::PREF_PRO_ENGIN_USAGE,
        'Nature' => self::PREF_PRO_ENGIN_NATURE,
        'N° de Plaque' => self::PREF_PRO_ENGIN_N°_PLAQUE,
        'N° de Chassis' => self::PREF_PRO_ENGIN_N°_CHASSIS,
        'Police' => self::PREF_PRO_ENGIN_POLICE,
        'Utilisateur' => self::PREF_PRO_ENGIN_UTILISATEUR,
        'Entreprise' => self::PREF_PRO_ENGIN_ENTREPRISE,
        'Date de création' => self::PREF_PRO_ENGIN_DATE_DE_CREATION,
        'Dernière modification' => self::PREF_PRO_ENGIN_DATE_DE_MODIFICATION
    ];
    //PRODUCTION - CONTACT
    public const PREF_PRO_CONTACT_ID = 0;
    public const PREF_PRO_CONTACT_NOM = 1;
    public const PREF_PRO_CONTACT_POSTE = 2;
    public const PREF_PRO_CONTACT_TELEPHONE = 3;
    public const PREF_PRO_CONTACT_EMAIL = 4;
    public const PREF_PRO_CONTACT_CLIENT = 5;
    public const PREF_PRO_CONTACT_UTILISATEUR = 6;
    public const PREF_PRO_CONTACT_ENTREPRISE = 7;
    public const PREF_PRO_CONTACT_DATE_DE_CREATION = 8;
    public const PREF_PRO_CONTACT_DATE_DE_MODIFICATION = 9;
    public const TAB_PRO_CONTACTS = [
        'Id' => self::PREF_PRO_CONTACT_ID,
        'Nom' => self::PREF_PRO_CONTACT_NOM,
        'Poste' => self::PREF_PRO_CONTACT_POSTE,
        'Téléphone' => self::PREF_PRO_CONTACT_TELEPHONE,
        'Email' => self::PREF_PRO_CONTACT_EMAIL,
        'Client' => self::PREF_PRO_CONTACT_CLIENT,
        'Utilisateur' => self::PREF_PRO_CONTACT_UTILISATEUR,
        'Entreprise' => self::PREF_PRO_CONTACT_ENTREPRISE,
        'Date de création' => self::PREF_PRO_CONTACT_DATE_DE_CREATION,
        'Dernière modification' => self::PREF_PRO_CONTACT_DATE_DE_MODIFICATION
    ];
    //PRODUCTION - CLIENT
    public const PREF_PRO_CLIENT_ID = 0;
    public const PREF_PRO_CLIENT_NOM = 1;
    public const PREF_PRO_CLIENT_PERSONNE_MORALE = 2;
    public const PREF_PRO_CLIENT_ADRESSE = 3;
    public const PREF_PRO_CLIENT_TELEPHONE = 4;
    public const PREF_PRO_CLIENT_EMAIL = 5;
    public const PREF_PRO_CLIENT_SITEWEB = 6;
    public const PREF_PRO_CLIENT_RCCM = 7;
    public const PREF_PRO_CLIENT_IDNAT = 8;
    public const PREF_PRO_CLIENT_NUM_IMPOT = 9;
    public const PREF_PRO_CLIENT_SECTEUR = 10;
    public const PREF_PRO_CLIENT_UTILISATEUR = 11;
    public const PREF_PRO_CLIENT_ENTREPRISE = 12;
    public const PREF_PRO_CLIENT_DATE_DE_CREATION = 13;
    public const PREF_PRO_CLIENT_DATE_DE_MODIFICATION = 14;
    public const TAB_PRO_CLIENTS = [
        'Id' => self::PREF_PRO_CLIENT_ID,
        'Nom' => self::PREF_PRO_CLIENT_NOM,
        'Societé (O/N)?' => self::PREF_PRO_CLIENT_PERSONNE_MORALE,
        'Adresse' => self::PREF_PRO_CLIENT_ADRESSE,
        'Téléphone' => self::PREF_PRO_CLIENT_TELEPHONE,
        'Email' => self::PREF_PRO_CLIENT_EMAIL,
        'Site Web' => self::PREF_PRO_CLIENT_SITEWEB,
        'Rccm' => self::PREF_PRO_CLIENT_RCCM,
        'Id.Nat' => self::PREF_PRO_CLIENT_IDNAT,
        'Num.Impôt' => self::PREF_PRO_CLIENT_NUM_IMPOT,
        'Secteur' => self::PREF_PRO_CLIENT_SECTEUR,
        'Utilisateur' => self::PREF_PRO_CLIENT_UTILISATEUR,
        'Entreprise' => self::PREF_PRO_CLIENT_ENTREPRISE,
        'Date de création' => self::PREF_PRO_CLIENT_DATE_DE_CREATION,
        'Dernière modification' => self::PREF_PRO_CLIENT_DATE_DE_MODIFICATION
    ];
    //PRODUCTION - PARTENAIRE
    public const PREF_PRO_PARTENAIRE_ID = 0;
    public const PREF_PRO_PARTENAIRE_NOM = 1;
    public const PREF_PRO_PARTENAIRE_PART = 2;
    public const PREF_PRO_PARTENAIRE_ADRESSE = 3;
    public const PREF_PRO_PARTENAIRE_EMAIL = 4;
    public const PREF_PRO_PARTENAIRE_SITEWEB = 5;
    public const PREF_PRO_PARTENAIRE_RCCM = 6;
    public const PREF_PRO_PARTENAIRE_IDNAT = 7;
    public const PREF_PRO_PARTENAIRE_NUM_IMPOT = 8;
    public const PREF_PRO_PARTENAIRE_UTILISATEUR = 9;
    public const PREF_PRO_PARTENAIRE_ENTREPRISE = 10;
    public const PREF_PRO_PARTENAIRE_DATE_DE_CREATION = 11;
    public const PREF_PRO_PARTENAIRE_DATE_DE_MODIFICATION = 12;
    public const TAB_PRO_PARTENAIRES = [
        'Id' => self::PREF_PRO_PARTENAIRE_ID,
        'Nom' => self::PREF_PRO_PARTENAIRE_NOM,
        'Part (%)' => self::PREF_PRO_PARTENAIRE_PART,
        'Adresse' => self::PREF_PRO_PARTENAIRE_ADRESSE,
        'Email' => self::PREF_PRO_PARTENAIRE_EMAIL,
        'Site Web' => self::PREF_PRO_PARTENAIRE_SITEWEB,
        'Rccm' => self::PREF_PRO_PARTENAIRE_RCCM,
        'Id.Nat' => self::PREF_PRO_PARTENAIRE_IDNAT,
        'Num.Impôt' => self::PREF_PRO_PARTENAIRE_NUM_IMPOT,
        'Utilisateur' => self::PREF_PRO_PARTENAIRE_UTILISATEUR,
        'Entreprise' => self::PREF_PRO_PARTENAIRE_ENTREPRISE,
        'Date de création' => self::PREF_PRO_PARTENAIRE_DATE_DE_CREATION,
        'Dernière modification' => self::PREF_PRO_PARTENAIRE_DATE_DE_MODIFICATION
    ];
    //PRODUCTION - POLICE
    public const PREF_PRO_POLICE_ID = 0;
    public const PREF_PRO_POLICE_REFERENCE = 1;
    public const PREF_PRO_POLICE_DATE_OPERATION = 2;
    public const PREF_PRO_POLICE_DATE_EMISSION = 3;
    public const PREF_PRO_POLICE_DATE_EFFET = 4;
    public const PREF_PRO_POLICE_DATE_EXPIRATION = 5;
    public const PREF_PRO_POLICE_ID_AVENANT = 6;
    public const PREF_PRO_POLICE_TYPE_AVENANT = 7;
    public const PREF_PRO_POLICE_CAPITAL = 8;
    public const PREF_PRO_POLICE_PRIME_NETTE = 9;
    public const PREF_PRO_POLICE_FRONTING = 10;
    public const PREF_PRO_POLICE_ARCA = 11;
    public const PREF_PRO_POLICE_TVA = 12;
    public const PREF_PRO_POLICE_FRAIS_ADMIN = 13;
    public const PREF_PRO_POLICE_PRIME_TOTALE = 14;
    public const PREF_PRO_POLICE_DISCOUNT = 15;
    public const PREF_PRO_POLICE_MODE_PAIEMENT = 16;
    public const PREF_PRO_POLICE_RI_COM = 17;
    public const PREF_PRO_POLICE_LOCAL_COM = 18;
    public const PREF_PRO_POLICE_FRONTIN_COM = 19;
    public const PREF_PRO_POLICE_REMARQUE = 20;
    public const PREF_PRO_POLICE_MONNAIE = 21;
    public const PREF_PRO_POLICE_CLIENT = 22;
    public const PREF_PRO_POLICE_PRODUIT = 23;
    public const PREF_PRO_POLICE_PARTENAIRE = 24;
    public const PREF_PRO_POLICE_PART_EXCEPTIONNELLE = 24;
    public const PREF_PRO_POLICE_REASSUREURS = 25;
    public const PREF_PRO_POLICE_ASSUREURS = 26;
    public const PREF_PRO_POLICE_PISTE = 27;
    public const PREF_PRO_POLICE_GESTIONNAIRE = 28;
    public const PREF_PRO_POLICE_CANHSARE_RI_COM = 29;
    public const PREF_PRO_POLICE_CANHSARE_LOCAL_COM = 29;
    public const PREF_PRO_POLICE_CANHSARE_FRONTING_COM = 29;

    

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Entreprise $entreprise = null;



    #[ORM\Column(length: 255)]
    private ?string $ricompayableby = null;

    #[ORM\Column(length: 255)]
    private ?string $localcompayableby = null;

    #[ORM\Column(length: 255)]
    private ?string $frontingcompayableby = null;


    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToMany(targetEntity: DocPiece::class)]
    private Collection $pieces;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;


    #[ORM\ManyToOne]
    private ?Utilisateur $utilisateur = null;



    public const PREF_APPARENCE_CLAIRE = 0;
    public const PREF_APPARENCE_SOMBRE = 1;
    public const TAB_APPARENCES = [
        'Mode sombre désactivé' => self::PREF_APPARENCE_CLAIRE,
        'Mode sombre activé' => self::PREF_APPARENCE_SOMBRE
    ];
    public const PREF_UTILISATEUR = "Utilisateur";
    public const PREF_ENTREPRISE = "Entreprise";


    public function __construct(
        private EntityManagerInterface $entityManager,
        private ServiceEntreprise $serviceEntreprise
    ) {
        //AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em

    }

    public static function getEntityFqcn(): string
    {
        return Preference::class;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $connected_entreprise = $this->serviceEntreprise->getEntreprise();
        $hasVisionGlobale = $this->isGranted(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE]);
        $defaultQueryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        if ($hasVisionGlobale == false) {
            $defaultQueryBuilder
                ->Where('entity.utilisateur = :user')
                ->setParameter('user', $this->getUser());
        }
        return $defaultQueryBuilder
            ->andWhere('entity.entreprise = :ese')
            ->setParameter('ese', $connected_entreprise);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDateTimeFormat('dd/MM/yyyy à HH:mm:ss')
            ->setDateFormat('dd/MM/yyyy')
            ->setPaginatorPageSize(100)
            ->renderContentMaximized()
            ->setEntityLabelInSingular("Paramètres d'affichage")
            ->setEntityLabelInPlural("Paramètres d'affichage")
            ->setPageTitle("index", "Liste des préférences")
            ->setDefaultSort(['updatedAt' => 'DESC'])
            ->setEntityPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACCES_PRODUCTION])
            // ...
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        if ($this->isGranted(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])) {
            $filters->add('utilisateur');
        }
        return $filters;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            //Onglet 01 - Généralités
            FormField::addTab(' GENERALITES')
                ->setIcon('fas fa-file-shield')
                ->setHelp("Les paramètres qui s'appliquent sur toutes les rubriques de l'espade de travail."),

            ChoiceField::new('apparence', "Arrière-plan Sombre")
                ->renderExpanded()
                ->setChoices(self::TAB_APPARENCES)
                ->renderAsBadges([
                    self::PREF_APPARENCE_SOMBRE => 'success', //info
                    self::PREF_APPARENCE_CLAIRE => 'danger',
                ]),
            AssociationField::new('utilisateur', self::PREF_UTILISATEUR)
                ->onlyOnDetail()
                ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                    return $entityRepository
                        ->createQueryBuilder('e')
                        ->Where('e.entreprise = :ese')
                        ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
                }),
            AssociationField::new('entreprise', self::PREF_ENTREPRISE)->onlyOnDetail(),
            DateTimeField::new('createdAt', "Date de création")->onlyOnDetail(), //->setColumns(2),
            DateTimeField::new('updatedAt', "Date de modification")->onlyOnDetail(), //->setColumns(2),


            //Onglet 02 - CRM
            FormField::addTab(' COMMERCIAL / CRM')
                ->setIcon('fas fa-bullseye')
                ->setHelp("Les paramètres qui s'appliquent uniquement sur les fonctions de la section CRM."),
            NumberField::new('crmTaille', "Eléments par page")->setColumns(2), //->setColumns(3),
            ChoiceField::new('crmMissions', "Attributs Mission")
                ->setColumns(2)
                ->renderExpanded()
                ->allowMultipleChoices()
                ->setChoices(self::TAB_CRM_MISSION),
            ChoiceField::new('crmFeedbacks', "Attributs Feedback")
                ->setColumns(2)
                ->renderExpanded()
                ->allowMultipleChoices()
                ->setChoices(self::TAB_CRM_FEEDBACK),
            ChoiceField::new('crmCotations', "Attributs Cotations")
                ->setColumns(2)
                ->renderExpanded()
                ->allowMultipleChoices()
                ->setChoices(self::TAB_CRM_COTATIONS),
            ChoiceField::new('crmEtapes', "Attributs Etapes")
                ->setColumns(2)
                ->renderExpanded()
                ->allowMultipleChoices()
                ->setChoices(self::TAB_CRM_ETAPES),
            ChoiceField::new('crmPistes', "Attributs Piste")
                ->setColumns(2)
                ->renderExpanded()
                ->allowMultipleChoices()
                ->setChoices(self::TAB_CRM_PISTE),

            //Onglet 03 - PRODUCTION
            FormField::addTab(' PRODUCTION')
                ->setIcon('fas fa-bag-shopping')
                ->setHelp("Les paramètres qui s'appliquent uniquement sur les fonctions de la section PRODUCTION."),
            NumberField::new('proTaille', "Eléments par page")->setColumns(2),
            ChoiceField::new('proAssureurs', "Attributs Assureur")
                ->setColumns(2)
                ->renderExpanded()
                ->allowMultipleChoices()
                ->setChoices(self::TAB_PRO_ASSUREURS),
            ChoiceField::new('proAutomobiles', "Attributs Engins")
                ->setColumns(2)
                ->renderExpanded()
                ->allowMultipleChoices()
                ->setChoices(self::TAB_PRO_ENGINS),
            ChoiceField::new('proContacts', "Attributs Contact")
                ->setColumns(2)
                ->renderExpanded()
                ->allowMultipleChoices()
                ->setChoices(self::TAB_PRO_CONTACTS),
            ChoiceField::new('proClients', "Attributs Clients")
                ->setColumns(2)
                ->renderExpanded()
                ->allowMultipleChoices()
                ->setChoices(self::TAB_PRO_CLIENTS),
            ChoiceField::new('proPartenaires', "Attributs Partenaires")
                ->setColumns(2)
                ->renderExpanded()
                ->allowMultipleChoices()
                ->setChoices(self::TAB_PRO_PARTENAIRES),

            //Onglet 04 - FINANCES
            FormField::addTab(' FINANCES')
                ->setIcon('fas fa-sack-dollar')
                ->setHelp("Les paramètres qui s'appliquent uniquement sur les fonctions de la section FINANCES."),
            NumberField::new('finTaille', "Eléments par page")->setColumns(2),

            //Onglet 05 - SINISTRE
            FormField::addTab(' SINISTRE')
                ->setIcon('fas fa-fire')
                ->setHelp("Les paramètres qui s'appliquent uniquement sur les fonctions de la section SINISTRE."),
            NumberField::new('sinTaille', "Eléments par page")->setColumns(2),

            //Onglet 06 - BIBLIOTHEQUE
            FormField::addTab(' BIBLIOTHEQUE')
                ->setIcon('fas fa-book')
                ->setHelp("Les paramètres qui s'appliquent uniquement sur les fonctions de la section BIBLIOTHEQUE."),
            NumberField::new('bibTaille', "Eléments par page")->setColumns(2),

            //Onglet 07 - PARAMETRES
            FormField::addTab(' PARAMETRES')
                ->setIcon('fas fa-gears')
                ->setHelp("Les paramètres qui s'appliquent uniquement sur les fonctions de la section PARAMETRES."),
            NumberField::new('parTaille', "Eléments par page")->setColumns(2),


        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            //les Updates sur la page détail
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action->setIcon('fa-solid fa-pen-to-square')->setLabel(DashboardController::ACTION_MODIFIER);
            })
            //Updates Sur la page Edit
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN, function (Action $action) {
                return $action->setIcon('fa-solid fa-floppy-disk')->setLabel(DashboardController::ACTION_ENREGISTRER); //<i class="fa-solid fa-floppy-disk"></i>
            })
            ->update(Crud::PAGE_DETAIL, Action::EDIT, function (Action $action) {
                return $action->setIcon('fa-solid fa-pen-to-square')->setLabel(DashboardController::ACTION_MODIFIER);
            })

            ->remove(Crud::PAGE_DETAIL, Action::DELETE)
            ->remove(Crud::PAGE_DETAIL, Action::INDEX)
            ->remove(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE);
    }
}
