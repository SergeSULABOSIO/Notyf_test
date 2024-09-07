<?php

namespace App\Controller\Admin;

use App\Entity\Piste;
use App\Entity\Police;
use App\Service\ServiceDates;
use App\Service\ServiceTaxes;
use Doctrine\ORM\QueryBuilder;
use App\Service\ServiceAvenant;
use App\Service\ServiceFacture;
use App\Service\ServiceMonnaie;
use App\Service\ServiceCrossCanal;
use App\Service\ServiceEntreprise;
use App\Service\ServicePreferences;
use App\Service\ServiceSuppression;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use App\Service\RefactoringJS\Commandes\Commande;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use Symfony\Component\HttpFoundation\RedirectResponse;
use EasyCorp\Bundle\EasyAdminBundle\Dto\BatchActionDto;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use App\Service\RefactoringJS\Commandes\CommandeExecuteur;
use App\Service\RefactoringJS\Evenements\SuperviseurSujet;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use App\Service\RefactoringJS\JSUIComponents\Police\PoliceUIBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use App\Service\RefactoringJS\Commandes\ComDefinirObservateursEvenements;

class PoliceCrudController extends AbstractCrudController implements CommandeExecuteur
{
    public ?Crud $crud = null;
    public ?PoliceUIBuilder $uiBuilder = null;

    public const TAB_POLICE_REPONSES_OUI_NON = [
        'Non' => 0,
        'Oui' => 1
    ];

    public const TAB_POLICE_OUTSTANDING_RESPONSE = [
        'Non' => 0,
        'Oui' => 1
    ];

    public const TAB_POLICE_DEBITEUR = [
        "L'assureur" => 0,
        "Le client" => 1,
        "Le courtier de réassurance" => 2,
        "Le réassureur" => 3
    ];

    public const TAB_POLICE_MODE_PAIEMENT = [
        'Paiement Annuel' => 0,
        'Paiements Trimestriels' => 1,
        'Paiements Semestriels' => 2,
        'Paiements Mensuels' => 3
    ];


    public const AVENANT_TYPE_SOUSCRIPTION = "Souscription";
    public const AVENANT_TYPE_RISTOURNE = "Ristourne";
    public const AVENANT_TYPE_RESILIATION = "Résiliation";
    public const AVENANT_TYPE_RENOUVELLEMENT = "Renouvellement";
    public const AVENANT_TYPE_PROROGATION = "Prorogation";
    public const AVENANT_TYPE_INCORPORATION = "Incorporation";
    public const AVENANT_TYPE_ANNULATION = "Annulation";
    public const AVENANT_TYPE_AUTRE_MODIFICATION = "Autre modification";

    public const TAB_POLICE_TYPE_AVENANT = [
        self::AVENANT_TYPE_SOUSCRIPTION => 0,
        self::AVENANT_TYPE_RISTOURNE => 1,
        self::AVENANT_TYPE_RESILIATION => 2,
        self::AVENANT_TYPE_RENOUVELLEMENT => 3,
        self::AVENANT_TYPE_PROROGATION => 4,
        self::AVENANT_TYPE_INCORPORATION => 5,
        self::AVENANT_TYPE_ANNULATION => 6,
        self::AVENANT_TYPE_AUTRE_MODIFICATION => 7
    ];

    //private $codeReporting = -100;

    public function __construct(
        private SuperviseurSujet $superviseurSujet,
        private ServiceDates $serviceDates,
        private ServiceMonnaie $serviceMonnaie,
        private ServiceFacture $serviceFacture,
        private ServiceAvenant $serviceAvenant,
        private ServiceSuppression $serviceSuppression,
        private EntityManagerInterface $entityManager,
        private ServiceEntreprise $serviceEntreprise,
        private ServicePreferences $servicePreferences,
        private ServiceCrossCanal $serviceCrossCanal,
        private AdminUrlGenerator $adminUrlGenerator,
        private ServiceTaxes $serviceTaxes
    ) {
        $this->uiBuilder = new PoliceUIBuilder($this->serviceEntreprise);
        //AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em
    }

    public static function getEntityFqcn(): string
    {
        return Police::class;
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
        //Application de la préférence sur la taille de la liste
        $this->servicePreferences->appliquerPreferenceTaille(new Police(), $crud);
        $this->crud = $crud
            ->setDateTimeFormat('dd/MM/yyyy') // à HH:mm:ss
            //->setDateTimeFormat('dd/MM/yyyy')
            ->setDateFormat('dd/MM/yyyy')
            //->setPaginatorPageSize(100)
            ->renderContentMaximized()
            ->setEntityLabelInSingular("POLICE")
            ->setEntityLabelInPlural("Police d'assurance")
            ->setPageTitle("index", "POLICES")
            ->setDefaultSort(['updatedAt' => 'DESC'])
            ->setEntityPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACCES_PRODUCTION])
            // ...
        ;
        return $crud;
    }


    public function configureFilters(Filters $filters): Filters
    {
        if ($this->isGranted(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])) {
            $filters->add('utilisateur');
        }
        return $filters
            // ->add('gestionnaire')
            // ->add('gestionnaire')
            //->add('isCommissionUnpaid')
            //->add('dateeffet')
            //->add('dateexpiration')
            // ->add('client')
            // ->add('produit')
            // ->add('assureur')
            // // ->add('partenaire')
            // ->add('docPieces')
            //->add('idavenant');
            // ->add('capital')
            // ->add('primenette')
            // ->add('fronting')
            // ->add('primetotale')
            // ->add(ChoiceFilter::new('cansharericom', 'Com. de réa. partageable?')->setChoices(self::TAB_POLICE_REPONSES_OUI_NON))
            // ->add(ChoiceFilter::new('cansharelocalcom', 'Com. locale partageable?')->setChoices(self::TAB_POLICE_REPONSES_OUI_NON))
            // ->add(ChoiceFilter::new('cansharefrontingcom', 'Com. fronting partageable?')->setChoices(self::TAB_POLICE_REPONSES_OUI_NON))
            // ->add('paidcommission')
            // ->add('paidretrocommission')
            // ->add('paidtaxecourtier')
            // ->add('paidtaxeassureur')
            // ->add('unpaidcommission')
            // ->add('unpaidretrocommission')
            // ->add('unpaidtaxecourtier')
            // ->add('unpaidtaxeassureur')
            // ->add('paidtaxe')
            // ->add('unpaidtaxe');
        ;
    }

    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        /** @var Police */
        $policeToDelete = $entityInstance;
        //Exécuter - Ecouteurs d'évènements
        $this->executer(new ComDefinirObservateursEvenements(
            $this->superviseurSujet,
            $this->entityManager,
            $this->serviceEntreprise,
            $this->serviceDates,
            $policeToDelete
        ));

        //destruction définitive de la piste
        $this->entityManager->remove($policeToDelete);
        $this->entityManager->flush();
        // $this->serviceSuppression->supprimer($entityInstance, ServiceSuppression::PRODUCTION_POLICE);
    }

    public function createEntity(string $entityFqcn)
    {
        $objet = new Police();
        $objet = $this->serviceAvenant->setAvenant($objet, $this->adminUrlGenerator);
        $objet = $this->serviceCrossCanal->crossCanal_Police_setCotation($objet, $this->adminUrlGenerator);
        //Exécuter - Ecouteurs d'évènements
        $this->executer(new ComDefinirObservateursEvenements(
            $this->superviseurSujet,
            $this->entityManager,
            $this->serviceEntreprise,
            $this->serviceDates,
            $objet
        ));
        return $objet;
    }



    public function configureFields(string $pageName): iterable
    {
        $instance = $this->getContext()->getEntity()->getInstance();
        //dd($this->getContext()->getEntity()->getInstance());
        if ($this->crud) {
            $this->crud = $this->serviceCrossCanal->crossCanal_setTitrePage($this->crud, $this->adminUrlGenerator, $this->getContext()->getEntity()->getInstance());
        }
        //Actualisation des attributs calculables - Merci Seigneur Jésus !
        //$this->serviceCalculateur->calculate($this->container, ServiceCalculateur::RUBRIQUE_POLICE);
        //$this->servicePreferences->setEntite($pageName, $this->getContext()->getEntity()->getInstance());
        if ($instance != null) {
            if ($instance instanceof Piste) {
                //On envoie ces paramètres à tous les formulaires
                /** @var Piste */
                if ($instance->getProduit()) {
                    $this->adminUrlGenerator->set("isIard", $instance->getProduit()->isIard());
                }
                if ($instance->getClient()) {
                    $this->adminUrlGenerator->set("isExoneree", $instance->getClient()->isExoneree());
                }
            } else if ($instance instanceof Police) {
                //On envoie ces paramètres à tous les formulaires
                /** @var Piste */
                if ($instance->getPiste() != null) {
                    if ($instance->getPiste()->getProduit()) {
                        $this->adminUrlGenerator->set("isIard", $instance->getProduit()->isIard());
                    }
                    if ($instance->getPiste()->getClient()) {
                        $this->adminUrlGenerator->set("isExoneree", $instance->getClient()->isExoneree());
                    }
                }
            }
        }
        //Exécuter - Ecouteurs d'évènements
        $this->executer(new ComDefinirObservateursEvenements(
            $this->superviseurSujet,
            $this->entityManager,
            $this->serviceEntreprise,
            $this->serviceDates,
            $instance
        ));

        return $this->uiBuilder->render(
            $this->entityManager,
            $this->serviceMonnaie,
            $this->serviceTaxes,
            $pageName,
            $instance,
            $this->crud,
            $this->adminUrlGenerator
        );
        //dd($this->adminUrlGenerator->get("isIard"));
        // $this->servicePreferences->setEntite($pageName, $instance);
        // return $this->servicePreferences->getChamps(new Police(), $this->crud, $this->adminUrlGenerator);
    }


    public function configureActions(Actions $actions): Actions
    {
        $duplicate = Action::new(DashboardController::ACTION_DUPLICATE)
            ->setIcon('fa-solid fa-copy')
            ->linkToCrudAction('dupliquerEntite'); //<i class="fa-solid fa-copy"></i>
        $ouvrir = Action::new(DashboardController::ACTION_OPEN)
            ->setIcon('fa-solid fa-eye')
            ->linkToCrudAction('ouvrirEntite'); //<i class="fa-solid fa-eye"></i>

        //LES ACTIONS BATCH
        $batch_creer_facture_commission = Action::new("facture_commissions", "Facture pour Commissions")
            ->linkToCrudAction('facture_commissions')
            ->setIcon('fa-solid fa-receipt');
        $batch_creer_facture_frais_de_gestion = Action::new("facture_frais_de_gestion", "Facture pour Frais de Gestion (Honoraires, etc.)")
            ->linkToCrudAction('facture_frais_de_gestion')
            ->setIcon('fa-solid fa-receipt');
        $batch_creer_facture_retrocommission = Action::new("facture_retrocommissions", "Facture pour Retrocommissions")
            ->linkToCrudAction('facture_retrocommissions')
            ->setIcon('fa-solid fa-receipt');
        $nomTaxe = $this->serviceTaxes->getTaxe(false) ? $this->serviceTaxes->getTaxe(false)->getNom() : "TVA";

        $nomTaxe = $this->serviceTaxes->getTaxe(true) ? $this->serviceTaxes->getTaxe(true)->getNom() : "le régulateur";
        $batch_creer_facture_arca = Action::new("facture_arca", "Note de perception pour " . $nomTaxe)
            ->linkToCrudAction('facture_arca')
            ->setIcon('fa-solid fa-receipt');

        $batch_exporter_ms_excels = Action::new("exporter_ms_excels", DashboardController::ACTION_EXPORTER_EXCELS)
            ->linkToCrudAction('exporterMSExcels')
            //->addCssClass('btn btn-primary')
            ->setIcon('fa-solid fa-file-excel');

        $actions
            //Sur la page Index - Selection
            ->addBatchAction($batch_exporter_ms_excels)
            ->addBatchAction($batch_creer_facture_arca)
            // ->addBatchAction($batch_creer_facture_tva)
            ->addBatchAction($batch_creer_facture_retrocommission)
            ->addBatchAction($batch_creer_facture_frais_de_gestion)
            ->addBatchAction($batch_creer_facture_commission)

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
                return $action->setIcon('fas fa-file-shield')->setCssClass('btn btn-primary')->setLabel(DashboardController::ACTION_AJOUTER);
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
        return $actions;
    }

    public function dupliquerEntite(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        //dd($context);
        /**@var Assureur $assureur */
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

    public function construireFacture(BatchActionDto $batchActionDto, AdminUrlGenerator $adminUrlGenerator, $typeFacture)
    {
        $reponse = $this->serviceFacture->canIssueFacture($batchActionDto, $typeFacture);
        if ($reponse["status"] == true) {
            $this->addFlash("success", $reponse["Messages"]);
            return $this->creerFacture($batchActionDto, $adminUrlGenerator, $typeFacture);
        } else {
            $this->addFlash("danger", $reponse["Messages"]);
            return $this->redirect($batchActionDto->getReferrerUrl());
        }
    }

    public function facture_frais_de_gestion(BatchActionDto $batchActionDto, AdminUrlGenerator $adminUrlGenerator)
    {
        return $this->construireFacture($batchActionDto, $adminUrlGenerator, FactureCrudController::TYPE_NOTE_FRAIS_DE_GESTION);
    }

    public function facture_commissions(BatchActionDto $batchActionDto, AdminUrlGenerator $adminUrlGenerator)
    {
        dd("Ici");
        // return $this->construireFacture($batchActionDto, $adminUrlGenerator, FactureCrudController::TYPE_FACTURE_COMMISSIONS);
    }

    public function facture_retrocommissions(BatchActionDto $batchActionDto, AdminUrlGenerator $adminUrlGenerator)
    {
        return $this->construireFacture($batchActionDto, $adminUrlGenerator, FactureCrudController::TYPE_NOTE_RETROCOMMISSIONS);
    }

    public function facture_arca(BatchActionDto $batchActionDto, AdminUrlGenerator $adminUrlGenerator)
    {
        return $this->construireFacture($batchActionDto, $adminUrlGenerator, FactureCrudController::TYPE_NOTE_NOTE_DE_PERCEPTION_ARCA);
    }

    public function facture_tva(BatchActionDto $batchActionDto, AdminUrlGenerator $adminUrlGenerator)
    {
        return $this->construireFacture($batchActionDto, $adminUrlGenerator, FactureCrudController::TYPE_NOTE_NOTE_DE_PERCEPTION_TVA);
    }

    public function creerFacture(BatchActionDto $batchActionDto, AdminUrlGenerator $adminUrlGenerator, $type)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_creer_facture($adminUrlGenerator, $batchActionDto->getEntityIds(), $type));
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

    public function cross_canal_ajouterPiece(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Police_ajouterPiece($context, $adminUrlGenerator));
    }

    public function cross_canal_listerPiece(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Police_listerPiece($context, $adminUrlGenerator));
    }

    public function cross_canal_ajouterPOPComm(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Police_ajouterPOPComm($context, $adminUrlGenerator));
    }

    public function cross_canal_listerPOPComm(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Police_listerPOPComm($context, $adminUrlGenerator));
    }

    public function cross_canal_listerPOPRetroComm(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Police_listerPOPRetroComm($context, $adminUrlGenerator));
    }

    public function cross_canal_listerPOPTaxeCourtier(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Police_listerPOPTaxe($context, $adminUrlGenerator, $this->serviceTaxes->getTaxe(true)));
    }

    public function cross_canal_listerPOPTaxeAssureur(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Police_listerPOPTaxe($context, $adminUrlGenerator, $this->serviceTaxes->getTaxe(false)));
    }

    public function cross_canal_ajouterSinistre(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Police_ajouterSinistre($context, $adminUrlGenerator));
    }

    public function cross_canal_listerSinistre(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Police_listerSinistre($context, $adminUrlGenerator));
    }

    public function cross_canal_ajouterMission(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Police_ajouterMission($context, $adminUrlGenerator));
    }

    public function cross_canal_listerMission(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Police_listerMission($context, $adminUrlGenerator));
    }

    public function cross_canal_ajouterPiste(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Police_ajouterPiste($context, $adminUrlGenerator));
    }

    public function cross_canal_listerPiste(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Police_listerPiste($context, $adminUrlGenerator));
    }

    public function cross_canal_ajouterContact(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Police_ajouterContact($context, $adminUrlGenerator));
    }

    public function cross_canal_listerContact(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Police_listerContact($context, $adminUrlGenerator));
    }

    public function cross_canal_ajouterAutomobile(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Police_ajouterAutomobile($context, $adminUrlGenerator));
    }

    public function avenant_annulation(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Avenant_Annulation($context, $adminUrlGenerator));
    }

    public function avenant_incorporation(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Avenant_Incorporation($context, $adminUrlGenerator));
    }

    public function avenant_resiliation(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Avenant_Resiliation($context, $adminUrlGenerator));
    }

    public function avenant_autres_modifications(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Avenant_Autres_Modifications($context, $adminUrlGenerator));
    }

    public function avenant_prorogation(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Avenant_Prorogation($context, $adminUrlGenerator));
    }

    public function avenant_ristourne(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Avenant_Ristourne($context, $adminUrlGenerator));
    }

    public function avenant_renouvellement(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Avenant_Renouvellement($context, $adminUrlGenerator));
    }

    public function cross_canal_listerAutomobile(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Police_listerAutomobile($context, $adminUrlGenerator));
    }

    protected function getRedirectResponseAfterSave(AdminContext $context, string $action): RedirectResponse
    {
        /** @var Police */
        $police = $context->getEntity()->getInstance();
        $url = $this->adminUrlGenerator
            ->setController(PoliceCrudController::class)
            ->setAction(Action::DETAIL)
            ->setEntityId($police->getId())
            ->generateUrl();
        $this->addFlash("success", "Salut " . $this->serviceEntreprise->getUtilisateur()->getNom() . ". La police " . $police .  " vient d'être enregistrée avec succès. Vous pouvez maintenant y ajouter d'autres informations.");
        return $this->redirect($url);
    }

    public function executer(?Commande $commande)
    {
        if ($commande != null) {
            $commande->executer();
        }
    }
}
