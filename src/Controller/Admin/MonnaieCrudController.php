<?php

namespace App\Controller\Admin;

use DateTimeImmutable;
use App\Entity\Monnaie;
use App\Service\ServiceTaxes;
use Doctrine\ORM\QueryBuilder;
use App\Service\ServiceMonnaie;
use App\Service\ServiceCrossCanal;
use App\Service\ServiceEntreprise;
use App\Service\ServicePreferences;
use App\Service\ServiceSuppression;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Dto\BatchActionDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use App\Service\RefactoringJS\JSUIComponents\Monnaie\MonnaieUIBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\Configurator\MoneyConfigurator;

class MonnaieCrudController extends AbstractCrudController
{
    public ?Monnaie $monnaie = null;
    public ?MonnaieUIBuilder $uiBuilder = null;

    public const TAB_MONNAIE_MONNAIE_LOCALE = [
        'Non' => 0,
        'Oui' => 1
    ];

    public const FONCTION_SAISIE_ET_AFFICHAGE = "Saisie et Affichage";
    public const FONCTION_SAISIE_UNIQUEMENT = "Saisie Uniquement";
    public const FONCTION_AFFICHAGE_UNIQUEMENT = "Affichage Uniquement";
    public const FONCTION_AUCUNE = "Aucune";

    public const TAB_MONNAIE_FONCTIONS = [
        self::FONCTION_AUCUNE => -1,
        self::FONCTION_SAISIE_ET_AFFICHAGE => 0,
        self::FONCTION_SAISIE_UNIQUEMENT => 1,
        self::FONCTION_AFFICHAGE_UNIQUEMENT => 2,
    ];

    public const TAB_MONNAIES = [
        "XUA - ADB Unit of Account" => "XUA",
        "AFN - Afghan afghani" => "AFN",
        "ALL - Albanian lek" => "ALL",
        "DZD - Algerian dinar" => "DZD",
        "AOA - Angolan kwanza" => "AOA",
        "ARS - Argentine peso" => "ARS",
        "AMD - Armenian dram" => "AMD",
        "AWG - Aruban florin" => "AWG",
        "AUD - Australian dollar" => "AUD",
        "AZN - Azerbaijani manat" => "AZN",
        "BSD - Bahamian dollar" => "BSD",
        "BHD - Bahraini dinar" => "BHD",
        "BDT - Bangladeshi taka" => "BDT",
        "BBD - Barbados dollar" => "BBD",
        "BYN - Belarusian ruble" => "BYN",
        "BZD - Belize dollar" => "BZD",
        "BMD - Bermudian dollar" => "BMD",
        "BTN - Bhutanese ngultrum" => "BTN",
        "BOV - Bolivian Mvdol (funds code)" => "BOV",
        "BOB - Boliviano" => "BOB",
        "BAM - Bosnia and Herzegovina convertible mark" => "BAM",
        "BWP - Botswana pula" => "BWP",
        "BRL - Brazilian real" => "BRL",
        "BND - Brunei dollar" => "BND",
        "BGN - Bulgarian lev" => "BGN",
        "BIF - Burundian franc" => "BIF",
        "KHR - Cambodian riel" => "KHR",
        "CAD - Canadian dollar" => "CAD",
        "CVE - Cape Verdean escudo" => "CVE",
        "KYD - Cayman Islands dollar" => "KYD",
        "XOF - CFA franc BCEAO" => "XOF",
        "XAF - CFA franc BEAC" => "XAF",
        "XPF - CFP franc (franc Pacifique)" => "XPF",
        "CLP - Chilean peso" => "CLP",
        "XTS - Code reserved for testing" => "XTS",
        "COP - Colombian peso" => "COP",
        "KMF - Comoro franc" => "KMF",
        "CDF - Congolese franc" => "CDF",
        "CRC - Costa Rican colon" => "CRC",
        "CUC - Cuban convertible peso" => "CUC",
        "CUP - Cuban peso" => "CUP",
        "CZK - Czech koruna" => "CZK",
        "DKK - Danish krone" => "DKK",
        "DJF - Djiboutian franc" => "DJF",
        "DOP - Dominican peso" => "DOP",
        "XCD - East Caribbean dollar" => "XCD",
        "EGP - Egyptian pound" => "EGP",
        "ERN - Eritrean nakfa" => "ERN",
        "ETB - Ethiopian birr" => "ETB",
        "EUR - Euro" => "EUR",
        "XBA - European Composite Unit (EURCO) (bond market unit)" => "XBA",
        "XBB - European Monetary Unit (E.M.U.-6) (bond market unit)" => "XBB",
        "XBD - European Unit of Account 17 (E.U.A.-17) (bond market unit)" => "XBD",
        "XBC - European Unit of Account 9 (E.U.A.-9) (bond market unit)" => "XBC",
        "FKP - Falkland Islands pound" => "FKP",
        "FJD - Fiji dollar" => "FJD",
        "GMD - Gambian dalasi" => "GMD",
        "GEL - Georgian lari" => "GEL",
        "GHS - Ghanaian cedi" => "GHS",
        "GIP - Gibraltar pound" => "GIP",
        "XAU - Gold (one troy ounce)" => "XAU",
        "GTQ - Guatemalan quetzal" => "GTQ",
        "GNF - Guinean franc" => "GNF",
        "GYD - Guyanese dollar" => "GYD",
        "HTG - Haitian gourde" => "HTG",
        "HNL - Honduran lempira" => "HNL",
        "HKD - Hong Kong dollar" => "HKD",
        "HUF - Hungarian forint" => "HUF",
        "ISK - Icelandic króna (plural: krónur)" => "ISK",
        "INR - Indian rupee" => "INR",
        "IDR - Indonesian rupiah" => "IDR",
        "IRR - Iranian rial" => "IRR",
        "IQD - Iraqi dinar" => "IQD",
        "ILS - Israeli new shekel" => "ILS",
        "JMD - Jamaican dollar" => "JMD",
        "JPY - Japanese yen" => "JPY",
        "JOD - Jordanian dinar" => "JOD",
        "KZT - Kazakhstani tenge" => "KZT",
        "KES - Kenyan shilling" => "KES",
        "KWD - Kuwaiti dinar" => "KWD",
        "KGS - Kyrgyzstani som" => "KGS",
        "LAK - Lao kip" => "LAK",
        "LBP - Lebanese pound" => "LBP",
        "LSL - Lesotho loti" => "LSL",
        "LRD - Liberian dollar" => "LRD",
        "LYD - Libyan dinar" => "LYD",
        "MOP - Macanese pataca" => "MOP",
        "MKD - Macedonian denar" => "MKD",
        "MGA - Malagasy ariary" => "MGA",
        "MWK - Malawian kwacha" => "MWK",
        "MYR - Malaysian ringgit" => "MYR",
        "MVR - Maldivian rufiyaa" => "MVR",
        "MRU - Mauritanian ouguiya" => "MRU",
        "MUR - Mauritian rupee" => "MUR",
        "MXN - Mexican peso" => "MXN",
        "MXV - Mexican Unidad de Inversion (UDI) (funds code)" => "MXV",
        "MDL - Moldovan leu" => "MDL",
        "MNT - Mongolian tögrög" => "MNT",
        "MAD - Moroccan dirham" => "MAD",
        "MZN - Mozambican metical" => "MZN",
        "MMK - Myanmar kyat" => "MMK",
        "NAD - Namibian dollar" => "NAD",
        "NPR - Nepalese rupee" => "NPR",
        "ANG - Netherlands Antillean guilder" => "ANG",
        "TWD - New Taiwan dollar" => "TWD",
        "NZD - New Zealand dollar" => "NZD",
        "NIO - Nicaraguan córdoba" => "NIO",
        "NGN - Nigerian naira" => "NGN",
        "XXX - No currency" => "XXX",
        "KPW - North Korean won" => "KPW",
        "NOK - Norwegian krone" => "NOK",
        "OMR - Omani rial" => "OMR",
        "PKR - Pakistani rupee" => "PKR",
        "XPD - Palladium (one troy ounce)" => "XPD",
        "PAB - Panamanian balboa" => "PAB",
        "PGK - Papua New Guinean kina" => "PGK",
        "PYG - Paraguayan guaraní" => "PYG",
        "PEN - Peruvian sol" => "PEN",
        "PHP - Philippine peso[10]" => "PHP",
        "XPT - Platinum (one troy ounce)" => "XPT",
        "PLN - Polish złoty" => "PLN",
        "GBP - Pound sterling" => "GBP",
        "QAR - Qatari riyal" => "QAR",
        "CNY - Renminbi[11]" => "CNY",
        "RON - Romanian leu" => "RON",
        "RUB - Russian ruble" => "RUB",
        "RWF - Rwandan franc" => "RWF",
        "SHP - Saint Helena pound" => "SHP",
        "SVC - Salvadoran colón" => "SVC",
        "WST - Samoan tala" => "WST",
        "SAR - Saudi riyal" => "SAR",
        "RSD - Serbian dinar" => "RSD",
        "SCR - Seychelles rupee" => "SCR",
        "SLE - Sierra Leonean leone (new leone)[12][13][14]" => "SLE",
        "SLL - Sierra Leonean leone (old leone)[12][13][14][15]" => "SLL",
        "XAG - Silver (one troy ounce)" => "XAG",
        "SGD - Singapore dollar" => "SGD",
        "SBD - Solomon Islands dollar" => "SBD",
        "SOS - Somali shilling" => "SOS",
        "ZAR - South African rand" => "ZAR",
        "KRW - South Korean won" => "KRW",
        "SSP - South Sudanese pound" => "SSP",
        "XDR - Special drawing rights" => "XDR",
        "LKR - Sri Lankan rupee" => "LKR",
        "XSU - SUCRE" => "XSU",
        "SDG - Sudanese pound" => "SDG",
        "SRD - Surinamese dollar" => "SRD",
        "SZL - Swazi lilangeni" => "SZL",
        "SEK - Swedish krona (plural: kronor)" => "SEK",
        "CHF - Swiss franc" => "CHF",
        "SYP - Syrian pound" => "SYP",
        "TJS - Tajikistani somoni" => "TJS",
        "TZS - Tanzanian shilling" => "TZS",
        "THB - Thai baht" => "THB",
        "TOP - Tongan paʻanga" => "TOP",
        "TTD - Trinidad and Tobago dollar" => "TTD",
        "TND - Tunisian dinar" => "TND",
        "TRY - Turkish lira" => "TRY",
        "TMT - Turkmenistan manat" => "TMT",
        "UGX - Ugandan shilling" => "UGX",
        "UAH - Ukrainian hryvnia" => "UAH",
        "CLF - Unidad de Fomento (funds code)" => "CLF",
        "COU - Unidad de Valor Real (UVR) (funds code)[6]" => "COU",
        "UYW - Unidad previsional[17]" => "UYW",
        "AED - United Arab Emirates dirham" => "AED",
        "USD - United States dollar" => "USD",
        "USN - United States dollar (next day) (funds code)" => "USN",
        "UYI - Uruguay Peso en Unidades Indexadas (URUIURUI) (funds code)" => "UYI",
        "UYU - Uruguayan peso" => "UYU",
        "UZS - Uzbekistan sum" => "UZS",
        "VUV - Vanuatu vatu" => "VUV",
        "VED - Venezuelan digital bolívar[18]" => "VED",
        "VES - Venezuelan sovereign bolívar[10]" => "VES",
        "VND - Vietnamese đồng" => "VND",
        "CHE - WIR euro (complementary currency)" => "CHE",
        "CHW - WIR franc (complementary currency)" => "CHW",
        "YER - Yemeni rial" => "YER",
        "ZMW - Zambian kwacha" => "ZMW",
        "ZWL - Zimbabwean dollar (fifth)[e]" => "ZWL"
    ];

    private ?Crud $crud;

    public function __construct(
        private ServiceCrossCanal $serviceCrossCanal,
        private AdminUrlGenerator $adminUrlGenerator,
        private ServiceSuppression $serviceSuppression,
        private EntityManagerInterface $entityManager,
        private ServiceEntreprise $serviceEntreprise,
        private ServicePreferences $servicePreferences,
        private ServiceTaxes $serviceTaxes,
        private ServiceMonnaie $serviceMonnaie
    ) {
        $this->uiBuilder = new MonnaieUIBuilder();
    }

    public static function getEntityFqcn(): string
    {
        return Monnaie::class;
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

    public function configureFilters(Filters $filters): Filters
    {
        if ($this->isGranted(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])) {
            $filters->add('utilisateur');
        }
        return $filters
            ->add('tauxusd')
            ->add(ChoiceFilter::new('islocale', 'Monnaie locale?')->setChoices(self::TAB_MONNAIE_MONNAIE_LOCALE));
    }

    public function configureCrud(Crud $crud): Crud
    {
        //Application de la préférence sur la taille de la liste
        $this->servicePreferences->appliquerPreferenceTaille(new Monnaie(), $crud);
        $this->crud = $crud
            ->setDateTimeFormat('dd/MM/yyyy à HH:mm:ss')
            ->setDateFormat('dd/MM/yyyy')
            //->setPaginatorPageSize(100)
            ->renderContentMaximized()
            ->setEntityLabelInSingular("Monnaie")
            ->setEntityLabelInPlural("Monnaies")
            ->setPageTitle("index", "Liste des monnaies")
            ->setDefaultSort(['updatedAt' => 'DESC'])
            ->setEntityPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACCES_FINANCES])
            // ...
        ;
        return $crud;
    }

    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->serviceSuppression->supprimer($entityInstance, ServiceSuppression::FINANCE_MONNAIE);
        //dd($reponse);
    }


    public function createEntity(string $entityFqcn)
    {
        $objet = new Monnaie();
        $objet->setFonction(MonnaieCrudController::TAB_MONNAIE_FONCTIONS[MonnaieCrudController::FONCTION_SAISIE_ET_AFFICHAGE]);
        $objet->setTauxusd(100);
        $objet->setIslocale(0);
        //$objet->setStartedAt(new DateTimeImmutable("+1 day"));
        //$objet->setEndedAt(new DateTimeImmutable("+7 day"));
        //$objet->setClos(0);
        return $objet;
    }

    public function configureFields(string $pageName): iterable
    {
        // $this->crud = $this->serviceCrossCanal->crossCanal_setTitrePage($this->crud, $this->adminUrlGenerator, $this->getContext()->getEntity()->getInstance());
        // return $this->servicePreferences->getChamps(new Monnaie(), $this->crud, $this->adminUrlGenerator);
    
        /** @var Monnaie */
        $this->monnaie = $this->getContext()->getEntity()->getInstance();
        $this->crud = $this->serviceCrossCanal->crossCanal_setTitrePage($this->crud, $this->adminUrlGenerator, $this->monnaie);

        return $this->uiBuilder->render(
            $this->entityManager,
            $this->serviceMonnaie,
            $this->serviceTaxes,
            $pageName,
            $this->monnaie,
            $this->crud,
            $this->adminUrlGenerator
        );
    }


    public function configureActions(Actions $actions): Actions
    {
        $definirCommeMonnaieAffichageEtSaisie = Action::new(DashboardController::ACTION_FONCTION_AFFICHAGE_ET_SAISIE)
            ->linkToCrudAction('definirCommeMonnaieAffichageEtSaisie')
            ->setIcon('fa-solid fa-cash-register') //<i class="fa-solid fa-cash-register"></i>
            ->displayIf(static function (Monnaie $entity) {
                return $entity->getFonction() != MonnaieCrudController::TAB_MONNAIE_FONCTIONS[MonnaieCrudController::FONCTION_SAISIE_ET_AFFICHAGE];
            });
        $definirCommeMonnaieAffichageUniquement = Action::new(DashboardController::ACTION_FONCTION_AFFICHAGE_UNIQUEMENT)
            ->linkToCrudAction('definirCommeMonnaieAffichageUniquement')
            ->setIcon('fa-solid fa-desktop') //<i class="fa-solid fa-desktop"></i>
            ->displayIf(static function (Monnaie $entity) {
                return $entity->getFonction() != MonnaieCrudController::TAB_MONNAIE_FONCTIONS[MonnaieCrudController::FONCTION_AFFICHAGE_UNIQUEMENT];
            });
        $definirCommeMonnaieSaisieUniquement = Action::new(DashboardController::ACTION_FONCTION_SAISIE_UNIQUEMENT)
            ->linkToCrudAction('definirCommeMonnaieSaisieUniquement')
            ->setIcon('fa-regular fa-keyboard') //<i class="fa-regular fa-keyboard"></i>
            ->displayIf(static function (Monnaie $entity) {
                return $entity->getFonction() != MonnaieCrudController::TAB_MONNAIE_FONCTIONS[MonnaieCrudController::FONCTION_SAISIE_UNIQUEMENT];
            });

        $duplicate = Action::new(DashboardController::ACTION_DUPLICATE)
            ->setIcon('fa-solid fa-copy')
            ->linkToCrudAction('dupliquerEntite'); //<i class="fa-solid fa-copy"></i>
        $ouvrir = Action::new(DashboardController::ACTION_OPEN)
            ->setIcon('fa-solid fa-eye')
            ->linkToCrudAction('ouvrirEntite'); //<i class="fa-solid fa-eye"></i>
        $exporter_ms_excels = Action::new("exporter_ms_excels", DashboardController::ACTION_EXPORTER_EXCELS)
            ->linkToCrudAction('exporterMSExcels')
            ->addCssClass('btn btn-primary')
            ->setIcon('fa-solid fa-file-excel');

        return $actions
            //Sur la page Index - Selection
            ->addBatchAction($exporter_ms_excels)
            //les Updates sur la page détail
            ->update(Crud::PAGE_DETAIL, Action::DELETE, function (Action $action) {
                return $action->setIcon('fa-solid fa-trash')->setLabel(DashboardController::ACTION_SUPPRIMER);
            })
            ->update(Crud::PAGE_DETAIL, Action::EDIT, function (Action $action) {
                return $action->setIcon('fa-solid fa-pen-to-square')->setLabel(DashboardController::ACTION_MODIFIER); //<i class="fa-solid fa-pen-to-square"></i>
            })
            ->update(Crud::PAGE_DETAIL, Action::INDEX, function (Action $action) {
                return $action->setIcon('fa-regular fa-rectangle-list')->setLabel(DashboardController::ACTION_LISTE); //<i class="fa-regular fa-rectangle-list"></i>
            })
            //Updates sur la page Index
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setIcon('fas fa-landmark-dome')->setCssClass('btn btn-primary')->setLabel(DashboardController::ACTION_AJOUTER);
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action->setIcon('fa-solid fa-trash')->setLabel(DashboardController::ACTION_SUPPRIMER); //<i class="fa-solid fa-trash"></i>
            })
            ->update(Crud::PAGE_INDEX, Action::BATCH_DELETE, function (Action $action) {
                return $action->setIcon('fa-solid fa-trash')->setLabel(DashboardController::ACTION_SUPPRIMER); //<i class="fa-solid fa-trash"></i>
            })
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action->setIcon('fa-solid fa-pen-to-square')->setLabel(DashboardController::ACTION_MODIFIER);
            })
            //Updates Sur la page Edit
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN, function (Action $action) {
                return $action->setIcon('fa-solid fa-floppy-disk')->setLabel(DashboardController::ACTION_ENREGISTRER); //<i class="fa-solid fa-floppy-disk"></i>
            })
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE, function (Action $action) {
                return $action->setIcon('fa-solid fa-floppy-disk')->setLabel(DashboardController::ACTION_ENREGISTRER_ET_CONTINUER);
            })
            //Updates Sur la page NEW
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER, function (Action $action) {
                return $action->setIcon('fa-solid fa-floppy-disk')->setLabel(DashboardController::ACTION_ENREGISTRER_ET_CONTINUER);
            })
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, function (Action $action) {
                return $action->setIcon('fa-solid fa-floppy-disk')->setLabel(DashboardController::ACTION_ENREGISTRER); //<i class="fa-solid fa-floppy-disk"></i>
            })


            //Action ouvrir
            ->add(Crud::PAGE_EDIT, $ouvrir)
            ->add(Crud::PAGE_INDEX, $ouvrir)
            //action dupliquer Assureur
            ->add(Crud::PAGE_DETAIL, $duplicate)
            ->add(Crud::PAGE_EDIT, $duplicate)
            ->add(Crud::PAGE_INDEX, $duplicate)

            //FONCTIONS DE LA MONNAIE
            ->add(Crud::PAGE_INDEX, $definirCommeMonnaieAffichageEtSaisie)
            ->add(Crud::PAGE_DETAIL, $definirCommeMonnaieAffichageEtSaisie)
            ->add(Crud::PAGE_EDIT, $definirCommeMonnaieAffichageEtSaisie)

            ->add(Crud::PAGE_INDEX, $definirCommeMonnaieAffichageUniquement)
            ->add(Crud::PAGE_DETAIL, $definirCommeMonnaieAffichageUniquement)
            ->add(Crud::PAGE_EDIT, $definirCommeMonnaieAffichageUniquement)

            ->add(Crud::PAGE_INDEX, $definirCommeMonnaieSaisieUniquement)
            ->add(Crud::PAGE_DETAIL, $definirCommeMonnaieSaisieUniquement)
            ->add(Crud::PAGE_EDIT, $definirCommeMonnaieSaisieUniquement)

            //Reorganisation des boutons
            ->reorder(Crud::PAGE_INDEX, [DashboardController::ACTION_OPEN, DashboardController::ACTION_DUPLICATE])
            ->reorder(Crud::PAGE_EDIT, [DashboardController::ACTION_OPEN, DashboardController::ACTION_DUPLICATE])

            //Application des roles
            ->setPermission(Action::NEW, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
            ->setPermission(Action::EDIT, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
            ->setPermission(Action::DELETE, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
            ->setPermission(Action::BATCH_DELETE, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
            ->setPermission(Action::SAVE_AND_ADD_ANOTHER, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
            ->setPermission(Action::SAVE_AND_CONTINUE, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
            ->setPermission(Action::SAVE_AND_RETURN, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
            ->setPermission(DashboardController::ACTION_DUPLICATE, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
            //->setPermission(self::ACTION_ACHEVER_MISSION, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
            //->setPermission(self::ACTION_AJOUTER_UN_FEEDBACK, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
        ;
    }

    public function definirCommeMonnaieAffichageUniquement(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        $entite = $context->getEntity()->getInstance();
        $entite->setFonction(MonnaieCrudController::TAB_MONNAIE_FONCTIONS[MonnaieCrudController::FONCTION_AFFICHAGE_UNIQUEMENT]);
        return $this->updateFonctionMonaie($entite, $adminUrlGenerator, $em);
    }

    public function definirCommeMonnaieSaisieUniquement(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        $entite = $context->getEntity()->getInstance();
        $entite->setFonction(MonnaieCrudController::TAB_MONNAIE_FONCTIONS[MonnaieCrudController::FONCTION_SAISIE_UNIQUEMENT]);
        return $this->updateFonctionMonaie($entite, $adminUrlGenerator, $em);
    }

    public function definirCommeMonnaieAffichageEtSaisie(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        $entite = $context->getEntity()->getInstance();
        $entite->setFonction(MonnaieCrudController::TAB_MONNAIE_FONCTIONS[MonnaieCrudController::FONCTION_SAISIE_ET_AFFICHAGE]);
        return $this->updateFonctionMonaie($entite, $adminUrlGenerator, $em);
    }

    private function updateFonctionMonaie(Monnaie $entite, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        $entite->setUpdatedAt(new \DateTimeImmutable());
        parent::persistEntity($em, $entite);
        $url = $adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::INDEX)
            //->setEntityId($entite->getId())
            ->generateUrl();
        return $this->redirect($url);
    }

    public function dupliquerEntite(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {

        $entite = $context->getEntity()->getInstance();
        $entiteDuplique = clone $entite;
        parent::persistEntity($em, $entiteDuplique);

        $url = $adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::DETAIL)
            ->setEntityId($entiteDuplique->getId())
            ->generateUrl();

        return $this->redirect($url);
    }

    public function ouvrirEntite(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        /**@var Assureur $assureur */
        $entite = $context->getEntity()->getInstance();

        $url = $adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::DETAIL)
            ->setEntityId($entite->getId())
            ->generateUrl();

        return $this->redirect($url);
    }


    public function exporterMSExcels(BatchActionDto $batchActionDto)
    {
        $className = $batchActionDto->getEntityFqcn();
        $entityManager = $this->container->get('doctrine')->getManagerForClass($className);

        dd($batchActionDto->getEntityIds());

        foreach ($batchActionDto->getEntityIds() as $id) {
            $user = $entityManager->find($className, $id);
            $user->approve();
        }

        $entityManager->flush();

        return $this->redirect($batchActionDto->getReferrerUrl());
    }
}
