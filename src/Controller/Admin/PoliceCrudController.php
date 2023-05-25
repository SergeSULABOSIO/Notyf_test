<?php

namespace App\Controller\Admin;

use DateTime;
use App\Entity\Piste;
use App\Entity\Client;
use App\Entity\Police;
use DateTimeImmutable;
use Doctrine\ORM\QueryBuilder;
use App\Service\ServiceEntreprise;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
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
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Dto\BatchActionDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class PoliceCrudController extends AbstractCrudController
{
    public const TAB_POLICE_REPONSES_OUI_NON = [
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

    public const TAB_POLICE_TYPE_AVENANT = [
        'Souscription' => 0,
        'Ristourne' => 1,
        'Résiliation' => 2,
        'Renouvellement' => 3,
        'Prorogation' => 4,
        'Incorporation' => 5,
        'Annulation' => 6
    ];

    public function __construct
    (
        private EntityManagerInterface $entityManager, 
        private ServiceEntreprise $serviceEntreprise
    )
    {
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
            ->setParameter('user', $this->getUser())
            ;
        }
        return $defaultQueryBuilder
            ->andWhere('entity.entreprise = :ese')
            ->setParameter('ese', $connected_entreprise)
        ;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDateTimeFormat ('dd/MM/yyyy à HH:mm:ss')
            ->setDateFormat ('dd/MM/yyyy')
            ->setPaginatorPageSize(100)
            ->renderContentMaximized()
            ->setEntityLabelInSingular("Police")
            ->setEntityLabelInPlural("Polices")
            ->setPageTitle("index", "Liste des polices")
            ->setDefaultSort(['updatedAt' => 'DESC'])
            ->setEntityPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACCES_PRODUCTION])
           // ...
        ;
    }



    public function configureFilters(Filters $filters): Filters
    {
        if($this->isGranted(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])){
            $filters->add('utilisateur');
        }
        return $filters
            ->add('dateeffet')
            ->add('dateexpiration')
            ->add('client')
            ->add('produit')
            ->add('assureur')
            ->add('partenaire')
            ->add('idavenant')
            ->add('monnaie')
            ->add('capital')
            ->add('primenette')
            ->add('fronting')
            ->add('primetotale')
            ->add(ChoiceFilter::new('cansharericom', 'Com. de réa. partageable?')->setChoices(self::TAB_POLICE_REPONSES_OUI_NON))
            ->add(ChoiceFilter::new('cansharelocalcom', 'Com. locale partageable?')->setChoices(self::TAB_POLICE_REPONSES_OUI_NON))
            ->add(ChoiceFilter::new('cansharefrontingcom', 'Com. fronting partageable?')->setChoices(self::TAB_POLICE_REPONSES_OUI_NON))
        ;
    }

    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        //C'est dans cette méthode qu'il faut préalablement supprimer les enregistrements fils/déscendant de cette instance pour éviter l'erreur due à la contrainte d'intégrité
        //dd($entityInstance);
    }

    public function createEntity(string $entityFqcn)
    {
        $objet = new Police();
        $objet->setDateemission(new DateTimeImmutable("now"));
        $objet->setDateoperation(new DateTimeImmutable("now"));
        $objet->setDateeffet(new DateTimeImmutable("now"));
        $objet->setDateexpiration(new DateTimeImmutable("+365 day"));
        $objet->setIdavenant(0);
        $objet->setTypeavenant(0);
        $objet->setReassureurs("Voir le traité de réassurance en place.");
        $objet->setCapital(1000000.00);
        $objet->setPrimenette(0);
        $objet->setFronting(0);
        $objet->setArca(0);
        $objet->setTva(0);
        $objet->setFraisadmin(0);
        $objet->setPrimetotale(0);
        $objet->setModepaiement(0);
        $objet->setDiscount(0);

        $objet->setRicom(0);
        $objet->setCansharericom(false);
        $objet->setRicompayableby(0);

        $objet->setFrontingcom(0);
        $objet->setCansharefrontingcom(false);
        $objet->setFrontingcompayableby(0);

        $objet->setLocalcom(0);
        $objet->setCansharelocalcom(false);
        $objet->setLocalcompayableby(0);

        $objet->setPartenaire(null);
        
        return $objet;
    }
    
    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addTab(' Informations de base')
            ->setIcon('fas fa-file-shield'), //<i class="fa-sharp fa-solid fa-address-book"></i>
            //->setHelp("Le contrat d'assurance en place."),

            //Ligne 01
            NumberField::new('idavenant', "N° Avenant")->setColumns(2),
            ChoiceField::new('typeavenant', "Type d'avenant")->setColumns(4)->setChoices(self::TAB_POLICE_TYPE_AVENANT),
            TextField::new('reference', "Référence")->setColumns(6),

            FormField::addPanel('')->onlyOnForms(),
            AssociationField::new('client', "Assuré")->setColumns(6)
            ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                return $entityRepository
                    ->createQueryBuilder('e')
                    ->Where('e.entreprise = :ese')
                    ->setParameter('ese', $this->serviceEntreprise->getEntreprise())
                    ;
            })
            ,
            AssociationField::new('produit', "Couverture / Risque")->setColumns(6)
            ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                return $entityRepository
                    ->createQueryBuilder('e')
                    ->Where('e.entreprise = :ese')
                    ->setParameter('ese', $this->serviceEntreprise->getEntreprise())
                    ;
            })
            ,
            
            //Ligne 02
            AssociationField::new('assureur', "Assureur")->setColumns(6)
            ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                return $entityRepository
                    ->createQueryBuilder('e')
                    ->Where('e.entreprise = :ese')
                    ->setParameter('ese', $this->serviceEntreprise->getEntreprise())
                    ;
            })
            ,
            TextField::new('reassureurs', "Réassureur")->hideOnIndex()->setColumns(6),

            //Ligne 03
            FormField::addPanel('')->onlyOnForms(),
            DateTimeField::new('dateoperation', "Date de l'opération")->hideOnIndex()->setColumns(2),
            DateTimeField::new('dateemission', "Date d'émission")->hideOnIndex()->setColumns(2),
            
            FormField::addPanel('')->onlyOnForms(),
            DateTimeField::new('dateeffet', "Date d'effet")->setColumns(2),//d-flex ->addCssClass('d-flex flex-column')
            DateTimeField::new('dateexpiration', "Echéance")->setColumns(2),

            FormField::addPanel('')->onlyOnForms(),
            AssociationField::new('piste', "Pistes")->setColumns(6)->onlyOnForms()
            ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                return $entityRepository
                    ->createQueryBuilder('e')
                    ->Where('e.entreprise = :ese')
                    ->setParameter('ese', $this->serviceEntreprise->getEntreprise())
                    ;
            })
            ,
            CollectionField::new('piste', "Pistes")->setColumns(6)->onlyOnIndex(),
            ArrayField::new('piste', "Pistes")->setColumns(6)->onlyOnDetail(),
            

            FormField::addTab(' Prime & Capitaux')
            ->setIcon('fas fa-bag-shopping'), //<i class="fa-sharp fa-solid fa-address-book"></i>
            //->setHelp("Le contrat d'assurance en place."),
            
            //Ligne 01
            AssociationField::new('monnaie', "Monnaie")->setColumns(2)
            ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                return $entityRepository
                    ->createQueryBuilder('e')
                    ->Where('e.entreprise = :ese')
                    ->setParameter('ese', $this->serviceEntreprise->getEntreprise())
                    ;
            })
            ,
            NumberField::new('capital', "Capital")->setColumns(2),
            ChoiceField::new('modepaiement', "Mode de paiement")->setColumns(2)->hideOnIndex()->setChoices(self::TAB_POLICE_MODE_PAIEMENT),

            FormField::addPanel('Facture client')->onlyOnForms(),
            NumberField::new('primenette', "Prime nette")->hideOnIndex(),
            NumberField::new('fronting', "Frais/Fronting")->hideOnIndex(),
            NumberField::new('arca', "Frais/Régul.")->hideOnIndex(),
            NumberField::new('tva', "Tva")->hideOnIndex(),
            NumberField::new('fraisadmin', "Frais admin.")->hideOnIndex(),
            NumberField::new('discount', "Remise")->hideOnIndex(),
            NumberField::new('primetotale', "Prime totale"),
            
            //Ligne 13
            FormField::addTab(' Structure des revenus')
            ->setIcon('fas fa-sack-dollar'), //<i class="fa-sharp fa-solid fa-address-book"></i>
            //->setHelp("Le contrat d'assurance en place."),
            
            AssociationField::new('partenaire', "Partenaire")->hideOnIndex()->setColumns(6)
            ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                return $entityRepository
                    ->createQueryBuilder('e')
                    ->Where('e.entreprise = :ese')
                    ->setParameter('ese', $this->serviceEntreprise->getEntreprise())
                    ;
            })
            //->setEmptyData(" ")
            ,

            FormField::addPanel('Commission de réassurance')
            //->onlyOnForms()
            ,
            NumberField::new('ricom', "Montant ht")->hideOnIndex()->setColumns(2),
            ChoiceField::new('cansharericom', "Est-elle partagée?")->hideOnIndex()->setColumns(2)->setChoices(self::TAB_POLICE_REPONSES_OUI_NON),
            ChoiceField::new('ricompayableby', "Débiteur")->hideOnIndex()->setColumns(3)->setChoices(self::TAB_POLICE_DEBITEUR),
            
            FormField::addPanel("Commission locale")
            //->onlyOnForms()
            ,
            NumberField::new('localcom', "Montant ht")->hideOnIndex()->setColumns(2),
            ChoiceField::new('cansharelocalcom', "Est-elle partagée?")->hideOnIndex()->setColumns(2)->setChoices(self::TAB_POLICE_REPONSES_OUI_NON),
            ChoiceField::new('localcompayableby', "Débiteur")->hideOnIndex()->setColumns(3)->setChoices(self::TAB_POLICE_DEBITEUR),
            //Ligne 14
            FormField::addPanel("Commission sur Fronting")
            //->onlyOnForms()
            ,
            NumberField::new('frontingcom', "Montant ht")->hideOnIndex()->setColumns(2),
            ChoiceField::new('cansharefrontingcom', "Est-elle partagée?")->hideOnIndex()->setColumns(2)->setChoices(self::TAB_POLICE_REPONSES_OUI_NON),
            ChoiceField::new('frontingcompayableby', "Débiteur")->hideOnIndex()->setColumns(3)->setChoices(self::TAB_POLICE_DEBITEUR),
            
            TextareaField::new('remarques', "Remarques")->hideOnIndex()->setColumns(12),
            
            AssociationField::new('utilisateur', "Utilisateur")->setColumns(6)->hideOnForm()
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE]),

            
            //Ligne 19
            DateTimeField::new('createdAt', 'Date creation')->hideOnIndex()->hideOnForm(),
            DateTimeField::new('updatedAt', 'Dernière modification')->hideOnForm(),
            //AssociationField::new('entreprise', 'Entreprise')->hideOnIndex()->setColumns(3),


            FormField::addTab(' Documents')->setIcon('fas fa-book'), 
            AssociationField::new('pieces', "Documents")->setColumns(12)->onlyOnForms()
            ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                return $entityRepository
                    ->createQueryBuilder('e')
                    ->Where('e.entreprise = :ese')
                    ->setParameter('ese', $this->serviceEntreprise->getEntreprise())
                    ;
            })
            ,
            ArrayField::new('pieces', "Documents")->setColumns(12)->onlyOnDetail(),

            FormField::addTab(' Attributs calculés')->setIcon('fa-solid fa-temperature-high')->onlyOnDetail(),
            FormField::addPanel('Commissions')->setIcon('fa-solid fa-toggle-off')->onlyOnDetail(),//<i class="fa-solid fa-toggle-off"></i>
            //LES CHAMPS CALCULABLES
            //SECTION - REVENU
            NumberField::new('calc_revenu_reserve', "Réserve")->onlyOnDetail(),
            NumberField::new('calc_revenu_partageable', "Commissions partegeables")->onlyOnDetail(),
            NumberField::new('calc_revenu_ht', "Commissions hors taxes")->onlyOnDetail(),
            NumberField::new('calc_revenu_ttc', "Commissions ttc")->onlyOnDetail(),
            NumberField::new('calc_revenu_ttc_encaisse', "Commissions encaissées")->onlyOnDetail(),
            ArrayField::new('calc_revenu_ttc_encaisse_tab_ref_factures', "Factures / Notes de débit")->onlyOnDetail(),
            //ArrayField::new('calc_revenu_ttc_encaisse_tab_dates', "Dates")->onlyOnDetail(),
            NumberField::new('calc_revenu_ttc_solde_restant_du', "Solde restant dû")->onlyOnDetail(),
            
            FormField::addPanel('Retrocommossions')->setIcon('fa-solid fa-toggle-off')->onlyOnDetail(),
            //SECTION - PARTENAIRES
            NumberField::new('calc_retrocom', "Retrocommissions dûes")->onlyOnDetail(),
            NumberField::new('calc_retrocom_payees', "Retrocommissions payées")->onlyOnDetail(),
            ArrayField::new('calc_retrocom_payees_tab_factures', "Factures / Notes de débit")->onlyOnDetail(),
            //ArrayField::new('calc_retrocom_payees_tab_dates', "Dates")->onlyOnDetail(),
            NumberField::new('calc_retrocom_solde', "Solde restant dû")->onlyOnDetail(),

            FormField::addPanel('Impôts et Taxes')->setIcon('fa-solid fa-toggle-off')->onlyOnDetail(),
            //SECTION - TAXES
            ArrayField::new('calc_taxes_courtier_tab', "Taxes concernées")->onlyOnDetail(),
            NumberField::new('calc_taxes_courtier', "Montant dû")->onlyOnDetail(),
            NumberField::new('calc_taxes_courtier_payees', "Montant payé")->onlyOnDetail(),
            ArrayField::new('calc_taxes_courtier_payees_tab_ref_factures', "Factures / Notes de débit")->onlyOnDetail(),
            //ArrayField::new('calc_taxes_courtier_payees_tab_dates', "Dates")->onlyOnDetail(),
            NumberField::new('calc_taxes_courtier_solde', "Solde restant dû")->onlyOnDetail(),

            FormField::addPanel()->onlyOnDetail(),
            ArrayField::new('calc_taxes_assureurs_tab', "Taxes concernées")->onlyOnDetail(),
            NumberField::new('calc_taxes_assureurs', "Montant dû")->onlyOnDetail(),
            NumberField::new('calc_taxes_assureurs_payees', "Montant payé")->onlyOnDetail(),
            ArrayField::new('calc_taxes_assureurs_payees_tab_ref_factures', "Factures / Notes de débit")->onlyOnDetail(),
            //ArrayField::new('calc_taxes_assureurs_payees_tab_dates', "Dates")->onlyOnDetail(),
            NumberField::new('calc_taxes_assureurs_solde', "Solde restant dû")->onlyOnDetail(),

        ];
    }


    public function configureActions(Actions $actions): Actions
    {
        $duplicate = Action::new(DashboardController::ACTION_DUPLICATE)
            ->setIcon('fa-solid fa-copy')
            ->linkToCrudAction('dupliquerEntite');//<i class="fa-solid fa-copy"></i>
        $ouvrir = Action::new(DashboardController::ACTION_OPEN)
            ->setIcon('fa-solid fa-eye')
            ->linkToCrudAction('ouvrirEntite');//<i class="fa-solid fa-eye"></i>
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
                return $action->setIcon('fa-solid fa-pen-to-square')->setLabel(DashboardController::ACTION_MODIFIER);//<i class="fa-solid fa-pen-to-square"></i>
            })
            ->update(Crud::PAGE_DETAIL, Action::INDEX, function (Action $action) {
                return $action->setIcon('fa-regular fa-rectangle-list')->setLabel(DashboardController::ACTION_LISTE);//<i class="fa-regular fa-rectangle-list"></i>
            })
            //Updates sur la page Index
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setIcon('fas fa-file-shield')->setCssClass('btn btn-primary')->setLabel(DashboardController::ACTION_AJOUTER);
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action->setIcon('fa-solid fa-trash')->setLabel(DashboardController::ACTION_SUPPRIMER);//<i class="fa-solid fa-trash"></i>
            })
            ->update(Crud::PAGE_INDEX, Action::BATCH_DELETE, function (Action $action) {
                return $action->setIcon('fa-solid fa-trash')->setLabel(DashboardController::ACTION_SUPPRIMER);//<i class="fa-solid fa-trash"></i>
            })
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action->setIcon('fa-solid fa-pen-to-square')->setLabel(DashboardController::ACTION_MODIFIER);
            })
            //Updates Sur la page Edit
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN, function (Action $action) {
                return $action->setIcon('fa-solid fa-floppy-disk')->setLabel(DashboardController::ACTION_ENREGISTRER);//<i class="fa-solid fa-floppy-disk"></i>
            })
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE, function (Action $action) {
                return $action->setIcon('fa-solid fa-floppy-disk')->setLabel(DashboardController::ACTION_ENREGISTRER_ET_CONTINUER);
            })
            //Updates Sur la page NEW
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER, function (Action $action) {
                return $action->setIcon('fa-solid fa-floppy-disk')->setLabel(DashboardController::ACTION_ENREGISTRER_ET_CONTINUER);
            })
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, function (Action $action) {
                return $action->setIcon('fa-solid fa-floppy-disk')->setLabel(DashboardController::ACTION_ENREGISTRER);//<i class="fa-solid fa-floppy-disk"></i>
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
