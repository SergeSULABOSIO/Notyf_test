<?php

namespace App\Controller\Admin;

use App\Entity\Monnaie;
use Doctrine\ORM\QueryBuilder;
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
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class MonnaieCrudController extends AbstractCrudController
{
    public const TAB_MONNAIE_MONNAIE_LOCALE = [
        'Non' => 0,
        'Oui' => 1
    ];

    public const TAB_MONNAIES = [
        "XUA - ADB Unit of Account" => "XUA",
        "AFN - Afghan afghani" => "AFN",
        "ALL - Albanian lek" => "ALL",
        "DZD - Algerian dinar" => "DZD",
        "AOA - Angolan kwanza" => "AOA",
        /* 

	
ARS	Argentine peso
AMD	Armenian dram
AWG	Aruban florin
AUD	Australian dollar
AZN	Azerbaijani manat
BSD	Bahamian dollar
BHD	Bahraini dinar
BDT	Bangladeshi taka
BBD	Barbados dollar
BYN	Belarusian ruble
BZD	Belize dollar
BMD	Bermudian dollar
BTN	Bhutanese ngultrum
BOV	Bolivian Mvdol (funds code)
BOB	Boliviano
BAM	Bosnia and Herzegovina convertible mark
BWP	Botswana pula
BRL	Brazilian real
BND	Brunei dollar
BGN	Bulgarian lev
BIF	Burundian franc
KHR	Cambodian riel
CAD	Canadian dollar
CVE	Cape Verdean escudo
KYD	Cayman Islands dollar
XOF	CFA franc BCEAO
XAF	CFA franc BEAC
XPF	CFP franc (franc Pacifique)
CLP	Chilean peso
XTS	Code reserved for testing
COP	Colombian peso
KMF	Comoro franc
CDF	Congolese franc
CRC	Costa Rican colon
CUC	Cuban convertible peso
CUP	Cuban peso
CZK	Czech koruna
DKK	Danish krone
DJF	Djiboutian franc
DOP	Dominican peso
XCD	East Caribbean dollar
EGP	Egyptian pound
ERN	Eritrean nakfa
ETB	Ethiopian birr
EUR	Euro
XBA	European Composite Unit (EURCO) (bond market unit)
XBB	European Monetary Unit (E.M.U.-6) (bond market unit)
XBD	European Unit of Account 17 (E.U.A.-17) (bond market unit)
XBC	European Unit of Account 9 (E.U.A.-9) (bond market unit)
FKP	Falkland Islands pound
FJD	Fiji dollar
GMD	Gambian dalasi
GEL	Georgian lari
GHS	Ghanaian cedi
GIP	Gibraltar pound
XAU	Gold (one troy ounce)
GTQ	Guatemalan quetzal
GNF	Guinean franc
GYD	Guyanese dollar
HTG	Haitian gourde
HNL	Honduran lempira
HKD	Hong Kong dollar
HUF	Hungarian forint
ISK	Icelandic króna (plural: krónur)
INR	Indian rupee
IDR	Indonesian rupiah
IRR	Iranian rial
IQD	Iraqi dinar
ILS	Israeli new shekel
JMD	Jamaican dollar
JPY	Japanese yen
JOD	Jordanian dinar
KZT	Kazakhstani tenge
KES	Kenyan shilling
KWD	Kuwaiti dinar
KGS	Kyrgyzstani som
LAK	Lao kip
LBP	Lebanese pound
LSL	Lesotho loti
LRD	Liberian dollar
LYD	Libyan dinar
MOP	Macanese pataca
MKD	Macedonian denar
MGA	Malagasy ariary
MWK	Malawian kwacha
MYR	Malaysian ringgit
MVR	Maldivian rufiyaa
MRU	Mauritanian ouguiya
MUR	Mauritian rupee
MXN	Mexican peso
MXV	Mexican Unidad de Inversion (UDI) (funds code)
MDL	Moldovan leu
MNT	Mongolian tögrög
MAD	Moroccan dirham
MZN	Mozambican metical
MMK	Myanmar kyat
NAD	Namibian dollar
NPR	Nepalese rupee
ANG	Netherlands Antillean guilder
TWD	New Taiwan dollar
NZD	New Zealand dollar
NIO	Nicaraguan córdoba
NGN	Nigerian naira
XXX	No currency
KPW	North Korean won
NOK	Norwegian krone
OMR	Omani rial
PKR	Pakistani rupee
XPD	Palladium (one troy ounce)
PAB	Panamanian balboa
PGK	Papua New Guinean kina
PYG	Paraguayan guaraní
PEN	Peruvian sol
PHP	Philippine peso[10]
XPT	Platinum (one troy ounce)
PLN	Polish złoty
GBP	Pound sterling
QAR	Qatari riyal
CNY	Renminbi[11]
RON	Romanian leu
RUB	Russian ruble
RWF	Rwandan franc
SHP	Saint Helena pound
SVC	Salvadoran colón
WST	Samoan tala
STN	São Tomé and Príncipe dobra
SAR	Saudi riyal
RSD	Serbian dinar
SCR	Seychelles rupee
SLE	Sierra Leonean leone (new leone)[12][13][14]
SLL	Sierra Leonean leone (old leone)[12][13][14][15]
XAG	Silver (one troy ounce)
SGD	Singapore dollar
SBD	Solomon Islands dollar
SOS	Somali shilling
ZAR	South African rand
KRW	South Korean won
SSP	South Sudanese pound
XDR	Special drawing rights
LKR	Sri Lankan rupee
XSU	SUCRE
SDG	Sudanese pound
SRD	Surinamese dollar
SZL	Swazi lilangeni
SEK	Swedish krona (plural: kronor)
CHF	Swiss franc
SYP	Syrian pound
TJS	Tajikistani somoni
TZS	Tanzanian shilling
THB	Thai baht
TOP	Tongan paʻanga
TTD	Trinidad and Tobago dollar
TND	Tunisian dinar
TRY	Turkish lira
TMT	Turkmenistan manat
UGX	Ugandan shilling
UAH	Ukrainian hryvnia
CLF	Unidad de Fomento (funds code)
COU	Unidad de Valor Real (UVR) (funds code)[6]
UYW	Unidad previsional[17]
AED	United Arab Emirates dirham
USD	United States dollar
USN	United States dollar (next day) (funds code)
UYI	Uruguay Peso en Unidades Indexadas (URUIURUI) (funds code)
UYU	Uruguayan peso
UZS	Uzbekistan sum
VUV	Vanuatu vatu
VED	Venezuelan digital bolívar[18]
VES	Venezuelan sovereign bolívar[10]
VND	Vietnamese đồng
CHE	WIR euro (complementary currency)
CHW	WIR franc (complementary currency)
YER	Yemeni rial
ZMW	Zambian kwacha
ZWL	Zimbabwean dollar (fifth)[e]
 */
    ];

    public function __construct(
        private ServiceSuppression $serviceSuppression,
        private EntityManagerInterface $entityManager,
        private ServiceEntreprise $serviceEntreprise,
        private ServicePreferences $servicePreferences
    ) {
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
        return $crud
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
    }

    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->serviceSuppression->supprimer($entityInstance, ServiceSuppression::FINANCE_MONNAIE);
        //dd($reponse);
    }


    public function createEntity(string $entityFqcn)
    {
        $objet = new Monnaie();
        //$objet->setStartedAt(new DateTimeImmutable("+1 day"));
        //$objet->setEndedAt(new DateTimeImmutable("+7 day"));
        //$objet->setClos(0);
        return $objet;
    }

    public function configureFields(string $pageName): iterable
    {
        return $this->servicePreferences->getChamps(new Monnaie());
    }


    public function configureActions(Actions $actions): Actions
    {
        $duplicate = Action::new(DashboardController::ACTION_DUPLICATE)->setIcon('fa-solid fa-copy')
            ->linkToCrudAction('dupliquerEntite'); //<i class="fa-solid fa-copy"></i>
        $ouvrir = Action::new(DashboardController::ACTION_OPEN)
            ->setIcon('fa-solid fa-eye')->linkToCrudAction('ouvrirEntite'); //<i class="fa-solid fa-eye"></i>
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
