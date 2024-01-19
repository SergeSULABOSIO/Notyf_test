<?php

namespace App\Controller\Admin;

use App\Entity\Paiement;
use App\Service\ServiceDates;
use App\Service\ServiceTaxes;
use Doctrine\ORM\QueryBuilder;
use App\Service\ServiceAvenant;
use App\Service\ServiceMonnaie;
use App\Service\ServiceCrossCanal;
use App\Service\ServiceEntreprise;
use App\Service\ServiceCalculateur;
use App\Service\ServicePreferences;
use App\Service\ServiceSuppression;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Dto\BatchActionDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class PaiementCrudController extends AbstractCrudController
{
    public const TYPE_PAIEMENT_ENTREE  = "Entrée des fonds";
    public const TYPE_PAIEMENT_SORTIE  = "Sortie des fonds";
    
    public const TAB_TYPE_PAIEMENT = [
        self::TYPE_PAIEMENT_ENTREE  => 0,
        self::TYPE_PAIEMENT_SORTIE  => 1
    ];

    public ?Paiement $paiement = null;
    public ?Crud $crud = null;

    public function __construct(
        private ServiceMonnaie $serviceMonnaie,
        private ServiceDates $serviceDates,
        private ServiceAvenant $serviceAvenant,
        private ServiceSuppression $serviceSuppression,
        private ServiceCalculateur $serviceCalculateur,
        private EntityManagerInterface $entityManager,
        private ServiceEntreprise $serviceEntreprise,
        private ServicePreferences $servicePreferences,
        private ServiceCrossCanal $serviceCrossCanal,
        private AdminUrlGenerator $adminUrlGenerator,
        private ServiceTaxes $serviceTaxes
    ) {
        //$this->dompdf = new Dompdf();
    }


    public static function getEntityFqcn(): string
    {
        return Paiement::class;
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
        //dd($this->serviceEntreprise->getEntreprise());
        //Application de la préférence sur la taille de la liste
        if ($this->serviceEntreprise->getUtilisateur() != null || $this->serviceEntreprise->getEntreprise() != null) {
            $this->servicePreferences->appliquerPreferenceTaille(new Paiement(), $crud);
        }

        $this->crud = $crud
            ->setDateTimeFormat('dd/MM/yyyy')
            ->setDateFormat('dd/MM/yyyy')
            ->setHelp(Crud::PAGE_NEW, "Founissez les informations recquises puis validez le formulaire pour les sauvegarder.")
            ->setHelp(Crud::PAGE_INDEX, "Résultat du filtrage.")
            ->setHelp(Crud::PAGE_DETAIL, "Information détailée sur l'enregistrement séléctioné.")
            ->setHelp(Crud::PAGE_EDIT, "Mise à jour d'un enregistrement. N'oubliez pas de valider le formulaire.")
            ->renderContentMaximized()
            ->setEntityLabelInSingular("Paiement")
            ->setEntityLabelInPlural("Paiements")
            ->setPageTitle("index", "Liste des paiments")
            ->setDefaultSort(['updatedAt' => 'DESC'])
            ->setEntityPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACCES_FINANCES])
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
            ->add('facture')
            ->add(ChoiceFilter::new('type', PreferenceCrudController::PREF_FIN_PAIEMENT_TYPE)->setChoices(self::TAB_TYPE_PAIEMENT))
            ->add('paidAt')
            ->add('createdAt')
            ->add('updatedAt');
    }

    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->serviceSuppression->supprimer($entityInstance, ServiceSuppression::FINANCE_PAIEMENT);
    }

    public function createEntity(string $entityFqcn)
    {
        
        $objet = new Paiement();
        $objet->setPaidAt(new \DateTimeImmutable("now"));
        $objet->setCreatedAt(new \DateTimeImmutable("now"));
        $objet->setUpdatedAt(new \DateTimeImmutable("now"));
        $objet->setEntreprise($this->serviceEntreprise->getEntreprise());
        $objet->setUtilisateur($this->serviceEntreprise->getUtilisateur());
        $objet = $this->serviceCrossCanal->crossCanal_Paiement_setFacture($this->container, $objet, $this->adminUrlGenerator);
        
        //dd($objet);
        return $objet;
    }

    public function configureFields(string $pageName): iterable
    {
        if ($pageName == Crud::PAGE_EDIT) {
            /** @var Paiement */
            $this->paiement = $this->getContext()->getEntity()->getInstance();
        }
        //dd($this->getContext()->getEntity()->getInstance());
        $this->crud = $this->serviceCrossCanal->crossCanal_setTitrePage($this->crud, $this->adminUrlGenerator, $this->getContext()->getEntity()->getInstance());
        //Actualisation des attributs calculables - Merci Seigneur Jésus !
        //$this->serviceCalculateur->calculate($this->container, ServiceCalculateur::RUBRIQUE_FACTURE);
        return $this->servicePreferences->getChamps(new Paiement(), $this->crud, $this->adminUrlGenerator);
    }

    public function configureActions(Actions $actions): Actions
    {
        $duplicate = Action::new(DashboardController::ACTION_DUPLICATE)
            ->setIcon('fa-solid fa-copy')
            ->linkToCrudAction('dupliquerEntite');
        $modifier = Action::new(DashboardController::ACTION_MODIFIER)
            ->setIcon('fa-solid fa-pen-to-square')
            //->addCssClass('btn btn-primary')
            ->linkToCrudAction('editEntite');
        $ouvrir = Action::new(DashboardController::ACTION_OPEN)
            ->setIcon('fa-solid fa-eye')
            ->linkToCrudAction('ouvrirEntite'); //<i class="fa-solid fa-eye"></i>
        $exporter_ms_excels = Action::new("exporter_ms_excels", DashboardController::ACTION_EXPORTER_EXCELS)
            ->linkToCrudAction('exporterMSExcels')
            ->addCssClass('btn btn-primary')
            ->setIcon('fa-solid fa-file-excel');

        $actions
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

            //action custom Modifier
            ->add(Crud::PAGE_INDEX, $modifier)
            ->add(Crud::PAGE_DETAIL, $modifier)
            ->update(Crud::PAGE_DETAIL, DashboardController::ACTION_MODIFIER, function (Action $action) {
                return $action->addCssClass('btn btn-primary'); //<i class="fa-solid fa-floppy-disk"></i>
            })

            //action dupliquer Assureur
            ->add(Crud::PAGE_DETAIL, $duplicate)
            ->add(Crud::PAGE_EDIT, $duplicate)
            ->add(Crud::PAGE_INDEX, $duplicate)

            //Application des roles
            ->setPermission(Action::NEW, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
            ->setPermission(Action::EDIT, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
            ->setPermission(Action::DELETE, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
            ->setPermission(Action::BATCH_DELETE, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
            ->setPermission(Action::SAVE_AND_ADD_ANOTHER, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
            ->setPermission(Action::SAVE_AND_CONTINUE, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
            ->setPermission(Action::SAVE_AND_RETURN, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
            ->setPermission(DashboardController::ACTION_DUPLICATE, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])

            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->remove(Crud::PAGE_DETAIL, Action::EDIT)

            //Reorganisation des boutons
            ->reorder(Crud::PAGE_INDEX, [DashboardController::ACTION_OPEN, DashboardController::ACTION_DUPLICATE])
            ->reorder(Crud::PAGE_EDIT, [DashboardController::ACTION_OPEN, DashboardController::ACTION_DUPLICATE])
            ->reorder(Crud::PAGE_DETAIL, [
                DashboardController::ACTION_DUPLICATE,
                Action::INDEX,
                DashboardController::ACTION_MODIFIER,
                Action::DELETE,
            ]);

        return $actions;
    }

    public function dupliquerEntite(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        /** @var Paiement */
        $paiement = $context->getEntity()->getInstance();
        $paiementDuplique = clone $paiement;
        parent::persistEntity($em, $paiementDuplique);
        $url = $adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::DETAIL)
            ->setEntityId($paiementDuplique->getId())
            ->generateUrl();
        return $this->redirect($url);
    }

    public function editEntite(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        /** @var Paiement */
        $paiement = $context->getEntity()->getInstance();
        return $this->redirect($this->serviceCrossCanal->crossCanal_modifier_paiement($adminUrlGenerator, $paiement));
    }

    public function ouvrirEntite(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        /** @var Paiement */
        $paiement = $context->getEntity()->getInstance();
        return $this->redirect($this->serviceCrossCanal->crossCanal_ouvrir_paiement($adminUrlGenerator, $paiement));
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
