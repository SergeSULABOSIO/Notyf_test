<?php

namespace App\Controller\Admin;

use App\Entity\Piste;
use App\Entity\Cotation;
use App\Service\ServiceDates;
use App\Service\ServiceTaxes;
use Doctrine\ORM\QueryBuilder;
use App\Service\ServiceAvenant;
use App\Service\ServiceMonnaie;
use App\Service\ServiceCrossCanal;
use App\Service\ServiceEntreprise;
use Doctrine\ORM\EntityRepository;
use App\Service\ServicePreferences;
use App\Service\ServiceSuppression;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use App\Service\RefactoringJS\Commandes\Commande;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use Symfony\Component\HttpFoundation\RedirectResponse;
use EasyCorp\Bundle\EasyAdminBundle\Dto\BatchActionDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use App\Service\RefactoringJS\Evenements\SuperviseurSujet;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use App\Service\RefactoringJS\JSUIComponents\Cotation\CotationUIBuilder;
use App\Service\RefactoringJS\Commandes\ComDefinirObservateursEvenements;

class CotationCrudController extends AbstractCrudController
{
    public ?Crud $crud = null;
    public ?CotationUIBuilder $uiBuilder = null;

    public const TYPE_RESULTAT_VALIDE = "Validée";
    public const TYPE_RESULTAT_NON_VALIDEE = "Non Validée";
    public const TAB_TYPE_RESULTAT = [
        self::TYPE_RESULTAT_NON_VALIDEE => 0,
        self::TYPE_RESULTAT_VALIDE => 1,
    ];


    public function __construct(
        private ServiceDates $serviceDates,
        private SuperviseurSujet $superviseurSujet,
        private ServiceTaxes $serviceTaxes,
        private ServiceMonnaie $serviceMonnaie,
        private ServiceAvenant $serviceAvenant,
        private ServiceSuppression $serviceSuppression,
        private EntityManagerInterface $entityManager,
        private ServiceEntreprise $serviceEntreprise,
        private ServicePreferences $servicePreferences,
        private ServiceCrossCanal $serviceCrossCanal,
        private AdminUrlGenerator $adminUrlGenerator
    ) {
        $this->uiBuilder = new CotationUIBuilder($this->serviceEntreprise);
    }


    public static function getEntityFqcn(): string
    {
        return Cotation::class;
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
            ->add('piste')
            //->add('client')
            //->add('police')
            //->add('produit')
            ->add('assureur');
    }

    public function configureCrud(Crud $crud): Crud
    {
        //Application de la préférence sur la taille de la liste
        $this->servicePreferences->appliquerPreferenceTaille(new Cotation(), $crud);
        $this->crud = $crud
            ->setDateTimeFormat('dd/MM/yyyy') //HH:mm:ss
            ->setDateFormat('dd/MM/yyyy')
            //->setPaginatorPageSize(100)
            ->renderContentMaximized()
            ->setEntityLabelInSingular("Cotation")
            ->setEntityLabelInPlural("Cotations")
            ->setPageTitle("index", "Liste des cotations")
            ->setDefaultSort(['updatedAt' => 'DESC'])
            ->setEntityPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACCES_COMMERCIAL])
            // ...
        ;
        return $crud;
    }


    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $cotationToDelete = $entityInstance;
        //Exécuter - Ecouteurs d'évènements
        $this->executer(new ComDefinirObservateursEvenements(
            $this->superviseurSujet,
            $this->entityManager,
            $this->serviceEntreprise,
            $this->serviceDates,
            $cotationToDelete
        ));

        //destruction définitive de la piste
        $this->entityManager->remove($cotationToDelete);
        $this->entityManager->flush();

        //C'est dans cette méthode qu'il faut préalablement supprimer les enregistrements fils/déscendant de cette instance pour éviter l'erreur due à la contrainte d'intégrité
        //dd($entityInstance);
        $this->serviceSuppression->supprimer($entityInstance, ServiceSuppression::CRM_COTATION);
    }


    public function createEntity(string $entityFqcn)
    {
        $objet = new Cotation();
        $objet->setNom("Offre");
        $objet = $this->serviceAvenant->setAvenant($objet, $this->adminUrlGenerator);
        $objet = $this->serviceCrossCanal->crossCanal_Cotation_setPiste($objet, $this->adminUrlGenerator);
        //$objet->setStartedAt(new DateTimeImmutable("+1 day"));
        //$objet->setEndedAt(new DateTimeImmutable("+7 day"));
        //$objet->setClos(0);

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
        if ($this->crud != null) {
            $this->crud = $this->serviceCrossCanal->crossCanal_setTitrePage($this->crud, $this->adminUrlGenerator, $instance);
        }
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

        // return $this->servicePreferences->getChamps(new Cotation(), $this->crud, $this->adminUrlGenerator);
        return $this->uiBuilder->render(
            $this->entityManager,
            $this->serviceMonnaie,
            $this->serviceTaxes,
            $pageName,
            $instance,
            $this->crud,
            $this->adminUrlGenerator
        );
    }


    public function configureActions(Actions $actions): Actions
    {
        //cross canal
        $mission_ajouter = Action::new(ServiceCrossCanal::OPTION_MISSION_AJOUTER)
            ->setIcon('fas fa-paper-plane')
            ->linkToCrudAction('cross_canal_ajouterMission');
        /* $mission_lister = Action::new(ServiceCrossCanal::OPTION_MISSION_LISTER)
            ->displayIf(static function (?Cotation $entity) {
                return count($entity->getActionCRMs()) != 0;
            })
            ->setIcon('fas fa-paper-plane')
            ->linkToCrudAction('cross_canal_listerMission'); */

        $actions
            ->add(Crud::PAGE_DETAIL, $mission_ajouter)
            ->add(Crud::PAGE_INDEX, $mission_ajouter)
            //->add(Crud::PAGE_DETAIL, $mission_lister)
            //->add(Crud::PAGE_INDEX, $mission_lister)
        ;

        $piece_ajouter = Action::new(ServiceCrossCanal::OPTION_PIECE_AJOUTER)
            ->setIcon('fa-solid fa-paperclip')
            ->linkToCrudAction('cross_canal_ajouterPiece');
        $piece_lister = Action::new(ServiceCrossCanal::OPTION_PIECE_LISTER)
            ->setIcon('fa-solid fa-paperclip')
            ->linkToCrudAction('cross_canal_listerPiece');

        //cross canal
        /* $police_creer = Action::new(ServiceCrossCanal::OPTION_POLICE_CREER)
            ->displayIf(static function (?Cotation $entity) {
                return $entity->getPolice() == null;
            })
            ->setIcon('fas fa-file-shield')
            ->linkToCrudAction('cross_canal_creerPolice'); */
        /* $police_ouvrir = Action::new(ServiceCrossCanal::OPTION_POLICE_OUVRIR)
            ->displayIf(static function (?Cotation $entity) {
                return $entity->getPolice() != null;
            })
            ->setIcon('fas fa-file-shield')
            ->linkToCrudAction('cross_canal_ouvrirPolice'); */

        //cross canal
        /* $client_creer = Action::new(ServiceCrossCanal::OPTION_CLIENT_CREER)
            ->displayIf(static function (?Cotation $entity) {
                return $entity->getClient() == null;
            })
            ->setIcon('fas fa-person-shelter')
            ->linkToCrudAction('cross_canal_creerClient'); */
        /* $client_ouvrir = Action::new(ServiceCrossCanal::OPTION_CLIENT_OUVRIR)
            ->displayIf(static function (?Cotation $entity) {
                return $entity->getClient() != null;
            })
            ->setIcon('fas fa-person-shelter')
            ->linkToCrudAction('cross_canal_ouvrirClient'); */


        $duplicate = Action::new(DashboardController::ACTION_DUPLICATE)->setIcon('fa-solid fa-copy')
            ->linkToCrudAction('dupliquerEntite'); //<i class="fa-solid fa-copy"></i>
        $ouvrir = Action::new(DashboardController::ACTION_OPEN)
            ->setIcon('fa-solid fa-eye')->linkToCrudAction('ouvrirEntite'); //<i class="fa-solid fa-eye"></i>
        $exporter_ms_excels = Action::new("exporter_ms_excels", DashboardController::ACTION_EXPORTER_EXCELS)
            ->linkToCrudAction('exporterMSExcels')
            ->addCssClass('btn btn-primary')
            ->setIcon('fa-solid fa-file-excel'); //<i class="fa-solid fa-file-excel"></i>

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
                return $action->setIcon('fas fa-cash-register')->setCssClass('btn btn-primary')->setLabel(DashboardController::ACTION_AJOUTER);
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

            //cross canal
            ->add(Crud::PAGE_DETAIL, $piece_ajouter)
            ->add(Crud::PAGE_INDEX, $piece_ajouter)

            ->add(Crud::PAGE_DETAIL, $piece_lister)
            ->add(Crud::PAGE_INDEX, $piece_lister)

            /* ->add(Crud::PAGE_DETAIL, $police_creer)
            ->add(Crud::PAGE_INDEX, $police_creer) */

            /* ->add(Crud::PAGE_DETAIL, $police_ouvrir)
            ->add(Crud::PAGE_INDEX, $police_ouvrir) */

            /* ->add(Crud::PAGE_DETAIL, $client_creer)
            ->add(Crud::PAGE_INDEX, $client_creer) */

            /* ->add(Crud::PAGE_DETAIL, $client_ouvrir)
            ->add(Crud::PAGE_INDEX, $client_ouvrir) */


            ->remove(Crud::PAGE_INDEX, Action::NEW)



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

    public function cross_canal_ajouterPiece(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Cotation_ajouterPiece($context, $adminUrlGenerator));
    }

    public function cross_canal_listerPiece(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Cotation_listerPiece($context, $adminUrlGenerator));
    }

    // public function cross_canal_creerPolice(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    // {
    //     return $this->redirect($this->serviceCrossCanal->crossCanal_Cotation_creerPolice($context, $adminUrlGenerator));
    // }

    public function cross_canal_ouvrirPolice(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Cotation_ouvrirPolice($context, $adminUrlGenerator));
    }

    public function cross_canal_creerClient(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Cotation_creerClient($context, $adminUrlGenerator));
    }

    public function cross_canal_ouvrirClient(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Cotation_ouvrirClient($context, $adminUrlGenerator));
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

    public function dupliquerEntite(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
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

    protected function getRedirectResponseAfterSave(AdminContext $context, string $action): RedirectResponse
    {
        /** @var Cotation */
        $cotation = $context->getEntity()->getInstance();
        $url = $this->adminUrlGenerator
            ->setController(CotationCrudController::class)
            ->setAction(Action::DETAIL)
            ->setEntityId($cotation->getId())
            ->generateUrl();
        $this->addFlash("success", "Salut " . $this->serviceEntreprise->getUtilisateur()->getNom() . ". La cotation " . $cotation->getNom() .  " vient d'être enregistrée avec succès. Vous pouvez maintenant y ajouter d'autres informations.");
        return $this->redirect($url);
    }

    public function cross_canal_ajouterMission(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Cotation_ajouterMission($context, $adminUrlGenerator));
    }

    public function cross_canal_listerMission(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Cotation_listerMission($context, $adminUrlGenerator));
    }

    public function executer(?Commande $commande)
    {
        if ($commande != null) {
            $commande->executer();
        }
    }
}
