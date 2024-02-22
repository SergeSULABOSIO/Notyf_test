<?php

namespace App\Controller\Admin;

use mapped;
use App\Entity\Piste;
use App\Entity\Client;
use App\Entity\Police;
use App\Entity\Produit;
use App\Entity\Tranche;
use App\Entity\Assureur;
use App\Entity\Partenaire;
use App\Entity\Utilisateur;
use App\Service\ServiceDates;
use App\Service\ServiceTaxes;
use Doctrine\ORM\QueryBuilder;
use App\Service\ServiceAvenant;
use App\Service\ServiceMonnaie;
use App\Service\ServiceCrossCanal;
use App\Service\ServiceEntreprise;
use App\Service\ServicePreferences;
use App\Service\ServiceSuppression;
use App\Service\ServiceCompteBancaire;
use App\Service\ServiceFiltresNonMappes;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Dto\BatchActionDto;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use App\Service\RefactoringJS\Initialisateurs\Facture\ObjetMultiCom;
use App\Service\RefactoringJS\JSUIComponents\Tranche\TrancheUIBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use App\Service\RefactoringJS\Initialisateurs\Facture\FacturePrimeInit;
use App\Service\RefactoringJS\Initialisateurs\Facture\FactureComLocaleInit;
use App\Service\RefactoringJS\Initialisateurs\Facture\FactureComFrontingInit;
use App\Service\RefactoringJS\Initialisateurs\Facture\FactureFraisGestionInit;
use App\Service\RefactoringJS\Initialisateurs\Facture\FactureTaxeAssureurInit;
use App\Service\RefactoringJS\Initialisateurs\Facture\FactureTaxeCourtierInit;
use App\Service\RefactoringJS\Initialisateurs\Facture\FactureComReassuranceInit;
use App\Service\RefactoringJS\Initialisateurs\Facture\FactureRetroCommissionInit;

class TrancheCrudController extends AbstractCrudController
{
    public ?Crud $crud = null;
    public $tranche = null;
    public ?TrancheUIBuilder $trancheUIBuilder = null;

    public function __construct(
        private ServiceAvenant $serviceAvenant,
        private ServiceMonnaie $serviceMonnaie,
        private ServiceCompteBancaire $serviceCompteBancaire,
        private ServiceSuppression $serviceSuppression,
        private EntityManagerInterface $entityManager,
        private ServiceDates $serviceDates,
        private ServiceEntreprise $serviceEntreprise,
        private ServicePreferences $servicePreferences,
        private ServiceCrossCanal $serviceCrossCanal,
        private AdminUrlGenerator $adminUrlGenerator,
        private ServiceTaxes $serviceTaxes,
        private ServiceFiltresNonMappes $serviceFiltresNonMappes

    ) {
        $this->trancheUIBuilder = new TrancheUIBuilder();
    }

    public static function getEntityFqcn(): string
    {
        return Tranche::class;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $connected_entreprise = $this->serviceEntreprise->getEntreprise();
        $hasVisionGlobale = $this->isGranted(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE]);

        //On applique le critère basé sur les attributs non mappés dans l'entité
        $defaultQueryBuilder = $this->serviceFiltresNonMappes->appliquerCriteresAttributsNonMappes(
            $searchDto,
            $entityDto,
            $fields,
            $filters,
            function (SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder {
                return parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
            }
        );

        //Filtre standard pour Utilisateur et Entreprise
        if ($hasVisionGlobale == false) {
            $defaultQueryBuilder
                ->Where('entity.utilisateur = :user')
                ->setParameter('user', $this->getUser());
        }
        return $defaultQueryBuilder
            ->andWhere('entity.entreprise = :ese')
            ->setParameter('ese', $connected_entreprise);
    }

    function configureFilters(Filters $filters): Filters
    {
        if ($this->isGranted(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])) {
            $filters->add('utilisateur');
        }

        //FILTRES BASES SUR LES ATTRIBUTS NON MAPPES
        $criteresNonMappes = [
            "validated" => [
                "label" => "Validée?",
                "class" => null,
                "defaultValue" => true,
                "userValues" => null,
                "options" => [
                    "Oui" => true,
                    "Non" => false,
                ],
                "multipleChoices" => false,
                "joiningEntity" => "cotation",
            ],
            "piste" => [
                "label" => "Pistes",
                "class" => Piste::class,
                "defaultValue" => [],
                "userValues" => [],
                "options" => [],
                "multipleChoices" => true,
                "joiningEntity" => "cotation",
            ],
            "police" => [
                "label" => "Polices",
                "class" => Police::class,
                "defaultValue" => [],
                "userValues" => [],
                "options" => [],
                "multipleChoices" => true,
                "joiningEntity" => "cotation",
            ],
            "client" => [
                "label" => "Clients",
                "class" => Client::class,
                "defaultValue" => [],
                "userValues" => [],
                "options" => [],
                "multipleChoices" => true,
                "joiningEntity" => "cotation",
            ],
            "partenaire" => [
                "label" => "Partenaires",
                "class" => Partenaire::class,
                "defaultValue" => [],
                "userValues" => [],
                "options" => [],
                "multipleChoices" => true,
                "joiningEntity" => "cotation",
            ],
            "produit" => [
                "label" => "Produits",
                "class" => Produit::class,
                "defaultValue" => [],
                "userValues" => [],
                "options" => [],
                "multipleChoices" => true,
                "joiningEntity" => "cotation",
            ],
            "assureur" => [
                "label" => "Assureurs",
                "class" => Assureur::class,
                "defaultValue" => [],
                "userValues" => [],
                "options" => [],
                "multipleChoices" => true,
                "joiningEntity" => "cotation",
            ],
            "gestionnaire" => [
                "label" => "Gestionnaire",
                "class" => Utilisateur::class,
                "defaultValue" => [],
                "userValues" => [],
                "options" => [],
                "multipleChoices" => true,
                "joiningEntity" => "cotation",
            ],
            "assistant" => [
                "label" => "Assistant",
                "class" => Utilisateur::class,
                "defaultValue" => [],
                "userValues" => [],
                "options" => [],
                "multipleChoices" => true,
                "joiningEntity" => "cotation",
            ],
        ];
        $filters = $this->serviceFiltresNonMappes->definirFiltreNonMappe($criteresNonMappes, $filters);
        //dd($filters);
        return $filters
            ->add(DateTimeFilter::new('startedAt', "Début de la tranche"))
            ->add(DateTimeFilter::new('endedAt', "Echéance de la tranche"))
            ->add(DateTimeFilter::new('dateEffet', "Début de la police"))
            ->add(DateTimeFilter::new('dateExpiration', "Echéance de la police"))
            //->add('police')
            //->add('utilisateur')
            //->add('actionCRMs')
        ;
    }

    public function configureCrud(Crud $crud): Crud
    {
        //Application de la préférence sur la taille de la liste
        $this->servicePreferences->appliquerPreferenceTaille(new Tranche(), $crud);

        $this->crud = $crud
            ->setDateTimeFormat('dd/MM/yyyy') // HH:mm:ss
            ->setDateFormat('dd/MM/yyyy')
            //->setPaginatorPageSize(100)
            ->renderContentMaximized()
            ->setEntityLabelInSingular("Tranche")
            ->setEntityLabelInPlural("Tranches")
            ->setPageTitle("index", "Liste des tranches")
            ->setDefaultSort(['updatedAt' => 'DESC'])
            ->setEntityPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACCES_PRODUCTION])
            // ...
        ;
        return $crud;
    }


    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->serviceSuppression->supprimer($entityInstance, ServiceSuppression::PRODUCTION_TRANCHE);
    }

    public function createEntity(string $entityFqcn)
    {
        $objet = new Tranche();
        $objet->setNom("");
        $objet->setTaux(100);
        $objet->setCreatedAt($this->serviceDates->aujourdhui());
        $objet->setUpdatedAt($this->serviceDates->aujourdhui());
        $objet->setUtilisateur($this->serviceEntreprise->getUtilisateur());
        $objet->setEntreprise($this->serviceEntreprise->getEntreprise());
        //dd($objet);
        return $objet;
    }



    public function configureFields(string $pageName): iterable
    {
        $this->tranche = $this->getContext()->getEntity()->getInstance();
        if ($this->crud != null) {
            $this->crud = $this->serviceCrossCanal->crossCanal_setTitrePage($this->crud, $this->adminUrlGenerator, $this->tranche);
        }

        return $this->trancheUIBuilder->render(
            $this->entityManager,
            $this->serviceMonnaie,
            $this->serviceTaxes,
            $pageName,
            $this->tranche,
            $this->crud,
            $this->adminUrlGenerator
        );
    }

    public function configureActions(Actions $actions): Actions
    {
        $ouvrir = Action::new(DashboardController::ACTION_OPEN)
            ->setIcon('fa-solid fa-eye')
            ->linkToCrudAction('ouvrirEntite'); //<i class="fa-solid fa-eye"></i>

        $facturePrime = Action::new("Facturer Prime")
            ->setIcon('fa-solid fa-receipt')
            ->displayIf(static function (Tranche $tranche) {
                // dd($tranche->getPremiumInvoiceDetails());
                return $tranche->getPremiumInvoiceDetails()[Tranche::PRODUIRE_FACTURE];
            })
            ->linkToCrudAction('facturerPrime'); //<i class="fa-solid fa-eye"></i>

        $factureCommissionLocale = Action::new("Facturer Com. locale")
            ->setIcon('fa-solid fa-receipt')
            ->displayIf(static function (Tranche $tranche) {
                // dd($tranche->getComLocaleInvoiceDetails());
                return $tranche->getComLocaleInvoiceDetails()[Tranche::PRODUIRE_FACTURE];
            })
            ->linkToCrudAction('facturerCommissionLocale'); //<i class="fa-solid fa-eye"></i>

        $factureMultiCommissions = Action::new("Produire les notes")
            ->setIcon('fa-solid fa-receipt')
            ->displayIf(static function (Tranche $tranche) {
                $okFPrime = $tranche->getPremiumInvoiceDetails()[Tranche::PRODUIRE_FACTURE];
                $okComLocal = $tranche->getComLocaleInvoiceDetails()[Tranche::PRODUIRE_FACTURE];
                $okComFronting = $tranche->getComFrontingInvoiceDetails()[Tranche::PRODUIRE_FACTURE];
                $okComFraisGest = $tranche->getFraisGestionInvoiceDetails()[Tranche::PRODUIRE_FACTURE];
                $okTaxeCourtier = $tranche->getTaxCourtierInvoiceDetails()[Tranche::PRODUIRE_FACTURE];
                $okTaxeAssureur = $tranche->getTaxAssureurInvoiceDetails()[Tranche::PRODUIRE_FACTURE];

                // dd(
                //     $tranche->getPremiumInvoiceDetails()[Tranche::PRODUIRE_FACTURE],
                //     $tranche->getComLocaleInvoiceDetails()[Tranche::PRODUIRE_FACTURE],
                //     $tranche->getComFrontingInvoiceDetails()[Tranche::PRODUIRE_FACTURE],
                //     $tranche->getFraisGestionInvoiceDetails()[Tranche::PRODUIRE_FACTURE],
                //     $tranche->getTaxCourtierInvoiceDetails()[Tranche::PRODUIRE_FACTURE],
                //     $tranche->getTaxAssureurInvoiceDetails()[Tranche::PRODUIRE_FACTURE]
                // );

                return $okFPrime || $okComLocal || $okComFronting || $okComFraisGest || $okTaxeCourtier || $okTaxeAssureur;
            })
            ->linkToCrudAction('facturerMultiCommissions');

        $factureCommissionReassurance = Action::new("Facturer Com. de réa.")
            ->setIcon('fa-solid fa-receipt')
            ->displayIf(static function (Tranche $tranche) {
                // dd($tranche->getComReassuranceInvoiceDetails());
                return $tranche->getComReassuranceInvoiceDetails()[Tranche::PRODUIRE_FACTURE];
            })
            ->linkToCrudAction('facturerCommissionReassurance');

        $factureCommissionFronting = Action::new("Facturer Com. fronting.")
            ->setIcon('fa-solid fa-receipt')
            ->displayIf(static function (Tranche $tranche) {
                // dd($tranche->getComFrontingInvoiceDetails());
                return $tranche->getComFrontingInvoiceDetails()[Tranche::PRODUIRE_FACTURE];
            })
            ->linkToCrudAction('facturerCommissionFronting');

        $factureFraisGestion = Action::new("Facturer Frais de Gestion")
            ->setIcon('fa-solid fa-receipt')
            ->displayIf(static function (Tranche $tranche) {
                return $tranche->getFraisGestionInvoiceDetails()[Tranche::PRODUIRE_FACTURE];
            })
            ->linkToCrudAction('facturerFraisGestion'); //<i class="fa-solid fa-eye"></i>

        $factureTaxeCourtier = Action::new("Note de crédit - Taxe Courtier")
            ->setIcon('fa-solid fa-receipt')
            ->displayIf(static function (Tranche $tranche) {
                // dd($tranche->getTaxCourtierInvoiceDetails());
                return $tranche->getTaxCourtierInvoiceDetails()[Tranche::PRODUIRE_FACTURE];
            })
            ->linkToCrudAction('facturerTaxeCourtier'); //<i class="fa-solid fa-eye"></i>

        $factureTaxeAssureur = Action::new("Note de crédit - Taxe Assureur")
            ->setIcon('fa-solid fa-receipt')
            ->displayIf(static function (Tranche $tranche) {
                // dd($tranche->getTaxAssureurInvoiceDetails());
                return $tranche->getTaxAssureurInvoiceDetails()[Tranche::PRODUIRE_FACTURE];
            })
            ->linkToCrudAction('facturerTaxeAssureur');

        $factureRetroCom = Action::new("Note de crédit - Rétro-com")
            ->setIcon('fa-solid fa-receipt')
            ->displayIf(static function (Tranche $tranche) {
                // dd($tranche->getRetrocomInvoiceDetails());
                return $tranche->getRetrocomInvoiceDetails()[Tranche::PRODUIRE_FACTURE];
            })
            ->linkToCrudAction('facturerRetroCommission');

        $batch_exporter_ms_excels = Action::new("exporter_ms_excels", DashboardController::ACTION_EXPORTER_EXCELS)
            ->linkToCrudAction('exporterMSExcels')
            ->addCssClass('btn btn-primary')
            ->setIcon('fa-solid fa-file-excel'); //<i class="fa-solid fa-file-excel"></i>
        
        $batch_produire_lot_notes = Action::new("produire_lot_notes", "Produire un lot des notes")
            ->linkToCrudAction('produireLotNotes')
            ->addCssClass('btn btn-primary')
            ->setIcon('fa-solid fa-file-excel'); //<i class="fa-solid fa-file-excel"></i>

        return $actions
            //Sur la page Index - Selection
            ->addBatchAction($batch_exporter_ms_excels)
            ->addBatchAction($batch_produire_lot_notes)
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
                return $action->setIcon('fas fa-location-crosshairs')->setCssClass('btn btn-primary')->setLabel(DashboardController::ACTION_AJOUTER);
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

            ->add(Crud::PAGE_INDEX, $factureTaxeAssureur)
            ->add(Crud::PAGE_INDEX, $factureTaxeCourtier)
            ->add(Crud::PAGE_INDEX, $factureRetroCom)
            ->add(Crud::PAGE_INDEX, $factureCommissionLocale)
            ->add(Crud::PAGE_INDEX, $factureCommissionReassurance)
            ->add(Crud::PAGE_INDEX, $factureCommissionFronting)
            ->add(Crud::PAGE_INDEX, $factureFraisGestion)
            ->add(Crud::PAGE_INDEX, $facturePrime)
            ->add(Crud::PAGE_INDEX, $factureMultiCommissions)

            //Action ouvrir
            ->add(Crud::PAGE_EDIT, $ouvrir)
            ->add(Crud::PAGE_INDEX, $ouvrir)

            //Reorganisation des boutons
            // ->reorder(Crud::PAGE_INDEX, [DashboardController::ACTION_OPEN, DashboardController::ACTION_DUPLICATE])
            // ->reorder(Crud::PAGE_EDIT, [DashboardController::ACTION_OPEN, DashboardController::ACTION_DUPLICATE])

            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->remove(Crud::PAGE_DETAIL, Action::EDIT)
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->remove(Crud::PAGE_DETAIL, Action::DELETE)

            //Application des roles
            ->setPermission(Action::NEW, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
            ->setPermission(Action::EDIT, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
            ->setPermission(Action::DELETE, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
            ->setPermission(Action::BATCH_DELETE, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
            ->setPermission(Action::SAVE_AND_ADD_ANOTHER, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
            ->setPermission(Action::SAVE_AND_CONTINUE, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
            ->setPermission(Action::SAVE_AND_RETURN, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
            ->setPermission(DashboardController::ACTION_DUPLICATE, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION]);
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

    public function produireLotNotes(BatchActionDto $batchActionDto)
    {
        dd("Je reste ici");
        // $className = $batchActionDto->getEntityFqcn();
        // $entityManager = $this->container->get('doctrine')->getManagerForClass($className);

        // dd($batchActionDto->getEntityIds());

        // foreach ($batchActionDto->getEntityIds() as $id) {
        //     $user = $entityManager->find($className, $id);
        //     $user->approve();
        // }
        // $entityManager->flush();
        // return $this->redirect($batchActionDto->getReferrerUrl());
    }

    public function ouvrirEntite(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        /** @var Tranche */
        $entite = $context->getEntity()->getInstance();

        $url = $adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::DETAIL)
            ->setEntityId($entite->getId())
            ->generateUrl();

        return $this->redirect($url);
    }

    public function facturerPrime(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect(
            $this->editFacture(
                [($context->getEntity()->getInstance())->getId()],
                FactureCrudController::TYPE_FACTURE_PRIME,
                $adminUrlGenerator
            )
        );
    }

    public function facturerCommissionLocale(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect(
            $this->editFacture(
                [($context->getEntity()->getInstance())->getId()],
                FactureCrudController::TYPE_FACTURE_COMMISSION_LOCALE,
                $adminUrlGenerator
            )
        );
    }

    // Pas besoin ce décorateur pour l'instant.
    #[Route('/multiCom', name: 'multiCom')]
    public function facturerMultiCommissions(Request $request = null, AdminContext $context = null, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em, ServiceTaxes $serviceTaxes): Response
    {
        /** @var Tranche */
        $tranche = null;
        if ($context != null) {
            $tranche = $context->getEntity()->getInstance();
        }

        $objetMultiCom = new ObjetMultiCom(
            $serviceTaxes->getTaxe(true),
            $serviceTaxes->getTaxe(false)
        );
        $objetMultiCom
            ->setProduireNDPrime($tranche->getPremiumInvoiceDetails()['produire'])
            ->setProduireNDFraisGestion($tranche->getFraisGestionInvoiceDetails()['produire'])
            ->setProduireNDComLocale($tranche->getComLocaleInvoiceDetails()['produire'])
            ->setProduireNDComReassurance($tranche->getComReassuranceInvoiceDetails()['produire'])
            ->setProduireNDComFronting($tranche->getComFrontingInvoiceDetails()['produire'])
            ->setProduireNCRetrocommission($tranche->getRetrocomInvoiceDetails()['produire'])
            ->setProduireNCTaxeCourtier($tranche->getTaxCourtierInvoiceDetails()['produire'])
            ->setProduireNCTaxeAssureur($tranche->getTaxAssureurInvoiceDetails()['produire']);

        $taxeCourtier = $serviceTaxes->getTaxe(true);
        $taxeAssureur = $serviceTaxes->getTaxe(false);

        $formulaire = $this->createFormBuilder($objetMultiCom)
            ->add(
                "produireNDPrime",
                CheckboxType::class,
                [
                    "label" => "Note de débit pour prime d'assurance.",
                    'required' => false,
                ]
            )
            ->add(
                "produireNDFraisGestion",
                CheckboxType::class,
                [
                    "label" => "Note de débit pour frais de gestion.",
                    'required' => false,
                ]
            )
            ->add(
                "produireNDComLocale",
                CheckboxType::class,
                [
                    "label" => "Note de débit pour commission locale / ordinaire.",
                    'required' => false,
                ]
            )
            ->add(
                "produireNDComReassurance",
                CheckboxType::class,
                [
                    "label" => "Note de débit pour commission de réassurance.",
                    'required' => false,
                ]
            )
            ->add(
                "produireNDComFronting",
                CheckboxType::class,
                [
                    "label" => "Note de débit pour commission sur fronting / commission de cession facultative.",
                    'required' => false,
                ]
            )
            ->add(
                "produireNCRetrocommission",
                CheckboxType::class,
                [
                    "label" => "Note de crédit pour rétro-commission.",
                    'required' => false,
                ]
            )
            ->add(
                "produireNCTaxeCourtier",
                CheckboxType::class,
                [
                    "label" => "Note de crédit pour les frais " . $taxeCourtier->getNom() . " destinés à \"" . $taxeCourtier->getOrganisation() . "\".",
                    'required' => false,
                ]
            )
            ->add(
                "produireNCTaxeAssureur",
                CheckboxType::class,
                [
                    "label" => "Note de crédit pour les frais " . $taxeAssureur->getNom() . " destinés à \"" . $taxeAssureur->getOrganisation() . "\".",
                    'required' => false,
                ]
            )
            ->getForm();

        $formulaire->handleRequest($request);
        // dd($formulaire);

        if ($formulaire->isSubmitted() && $formulaire->isValid()) {
            /** @var ObjetMultiCom */
            $objetReponse = $formulaire->getData();
            // dd("Réponse de l'utilisateur:", $objetReponse);
            if ($objetReponse->getProduireNDPrime() == true) {
                $ffg = new FacturePrimeInit(
                    $this->serviceAvenant,
                    $this->serviceDates,
                    $this->serviceEntreprise,
                    $this->entityManager,
                    $this->serviceCompteBancaire
                );
                $facture = $ffg->buildFacture(1, $tranche);
                $ffg->saveFacture();
            }
            if ($objetReponse->getProduireNDFraisGestion() == true) {
                $ffg = new FactureFraisGestionInit(
                    $this->serviceAvenant,
                    $this->serviceDates,
                    $this->serviceEntreprise,
                    $this->entityManager,
                    $this->serviceCompteBancaire
                );
                $facture = $ffg->buildFacture(1, $tranche);
                $ffg->saveFacture();
            }
            if ($objetReponse->getProduireNDComLocale() == true) {
                $ffg = new FactureComLocaleInit(
                    $this->serviceAvenant,
                    $this->serviceDates,
                    $this->serviceEntreprise,
                    $this->entityManager,
                    $this->serviceCompteBancaire
                );
                $facture = $ffg->buildFacture(1, $tranche);
                $ffg->saveFacture();
            }
            if ($objetReponse->getProduireNDComReassurance() == true) {
                $ffg = new FactureComReassuranceInit(
                    $this->serviceAvenant,
                    $this->serviceDates,
                    $this->serviceEntreprise,
                    $this->entityManager,
                    $this->serviceCompteBancaire
                );
                $facture = $ffg->buildFacture(1, $tranche);
                $ffg->saveFacture();
            }
            if ($objetReponse->getProduireNDComFronting() == true) {
                $ffg = new FactureComFrontingInit(
                    $this->serviceAvenant,
                    $this->serviceDates,
                    $this->serviceEntreprise,
                    $this->entityManager,
                    $this->serviceCompteBancaire
                );
                $facture = $ffg->buildFacture(1, $tranche);
                $ffg->saveFacture();
            }
            if ($objetReponse->getProduireNCRetrocommission() == true) {
                $ffg = new FactureRetroCommissionInit(
                    $this->serviceAvenant,
                    $this->serviceDates,
                    $this->serviceEntreprise,
                    $this->entityManager,
                    $this->serviceCompteBancaire
                );
                $facture = $ffg->buildFacture(1, $tranche);
                $ffg->saveFacture();
            }
            if ($objetReponse->getProduireNCTaxeCourtier() == true) {
                $ffg = new FactureTaxeCourtierInit(
                    $this->serviceAvenant,
                    $this->serviceDates,
                    $this->serviceTaxes,
                    $this->serviceEntreprise,
                    $this->entityManager,
                    $this->serviceCompteBancaire
                );
                $facture = $ffg->buildFacture(1, $tranche);
                $ffg->saveFacture();
            }
            if ($objetReponse->getProduireNCTaxeAssureur() == true) {
                $ffg = new FactureTaxeAssureurInit(
                    $this->serviceAvenant,
                    $this->serviceDates,
                    $this->serviceTaxes,
                    $this->serviceEntreprise,
                    $this->entityManager,
                    $this->serviceCompteBancaire
                );
                $facture = $ffg->buildFacture(1, $tranche);
                $ffg->saveFacture();
            }

            //On se redirige vers la page des facture
            //Mais l'idéal c'est de filtrer les factures de cette tranche uniquement
            $url = $adminUrlGenerator
                ->setController(TrancheCrudController::class)
                ->setAction(Action::INDEX)
                ->setEntityId(null)
                ->generateUrl();
            $this->addFlash("success", "Salut " . $this->serviceEntreprise->getUtilisateur()->getNom() . ". Les factures/notes ont été générées avec succès.");
            return $this->redirect($url);
        }
        // dd("Ici - MultiCommissions", $objetMultiCom, $formulaire);
        return $this->render(
            'admin/segment/view_multi_com.html.twig',
            [
                'form' => $formulaire,
                'tranche' => $tranche,
            ]
        );
    }

    public function facturerCommissionReassurance(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em): Response
    {
        return $this->redirect(
            $this->editFacture(
                [($context->getEntity()->getInstance())->getId()],
                FactureCrudController::TYPE_FACTURE_COMMISSION_REASSURANCE,
                $adminUrlGenerator
            )
        );
    }

    public function facturerCommissionFronting(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect(
            $this->editFacture(
                [($context->getEntity()->getInstance())->getId()],
                FactureCrudController::TYPE_FACTURE_COMMISSION_FRONTING,
                $adminUrlGenerator
            )
        );
    }

    public function facturerFraisGestion(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect(
            $this->editFacture(
                [($context->getEntity()->getInstance())->getId()],
                FactureCrudController::TYPE_FACTURE_FRAIS_DE_GESTION,
                $adminUrlGenerator
            )
        );
    }


    public function facturerRetroCommission(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect(
            $this->editFacture(
                [($context->getEntity()->getInstance())->getId()],
                FactureCrudController::TYPE_FACTURE_RETROCOMMISSIONS,
                $adminUrlGenerator
            )
        );
    }

    public function facturerTaxeCourtier(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect(
            $this->editFacture(
                [($context->getEntity()->getInstance())->getId()],
                FactureCrudController::TYPE_FACTURE_NOTE_DE_PERCEPTION_ARCA,
                $adminUrlGenerator
            )
        );
    }

    public function facturerTaxeAssureur(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect(
            $this->editFacture(
                [($context->getEntity()->getInstance())->getId()],
                FactureCrudController::TYPE_FACTURE_NOTE_DE_PERCEPTION_TVA,
                $adminUrlGenerator
            )
        );
    }


    private function editFacture(?array $tabIdTranches, ?string $typeFacture, AdminUrlGenerator $adminUrlGenerator): ?string
    {
        return $this->serviceCrossCanal->crossCanal_creer_facture(
            $adminUrlGenerator,
            $tabIdTranches,
            $typeFacture
        );
    }
}
