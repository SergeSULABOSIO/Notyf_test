<?php

namespace App\Controller\Admin;

use DateTime;
use App\Entity\Client;
use App\Entity\Police;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
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
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class PoliceCrudController extends AbstractCrudController
{
    public const TAB_POLICE_REPONSES_OUI_NON = [
        'Non' => 0,
        'Oui' => 1
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

    public static function getEntityFqcn(): string
    {
        return Police::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDateTimeFormat ('dd/MM/yyyy à HH:mm:ss')
            ->setDateFormat ('dd/MM/yyyy')
            ->setPaginatorPageSize(30)
            ->renderContentMaximized()
            ->setEntityLabelInSingular("Police")
            ->setEntityLabelInPlural("Polices")
            ->setPageTitle("index", "Liste des polices")
            ->setDefaultSort(['updatedAt' => 'DESC'])
            // ...
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
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
    
    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addTab(' Informations générales')
            ->setIcon('fas fa-file-shield') //<i class="fa-sharp fa-solid fa-address-book"></i>
            ->setHelp("Le contrat d'assurance en place."),

            //Ligne 01
            NumberField::new('idavenant', "N° Avenant")->setColumns(2),
            ChoiceField::new('typeavenant', "Type d'avenant")->hideOnIndex()->setColumns(4)->setChoices(self::TAB_POLICE_TYPE_AVENANT),
            TextField::new('reference', "Référence")->setColumns(6),
            FormField::addPanel('')->onlyOnForms(),
            AssociationField::new('client', "Assuré")->setColumns(6),
            AssociationField::new('produit', "Couverture / Risque")->setColumns(6),
            
            //Ligne 02
            AssociationField::new('assureur', "Assureur")->setColumns(6),
            TextField::new('reassureurs', "Réassureur")->hideOnIndex()->setColumns(6),

            //Ligne 03
            FormField::addPanel('')->onlyOnForms(),
            DateTimeField::new('dateeffet', "Date d'effet"),//d-flex ->addCssClass('d-flex flex-column')
            DateTimeField::new('dateexpiration', "Echéance"),
            DateTimeField::new('dateoperation', "Date de l'opération")->hideOnIndex(),
            DateTimeField::new('dateemission', "Date d'émission")->hideOnIndex(),
            FormField::addPanel('')->onlyOnForms(),
            AssociationField::new('piste', "Pistes")->setColumns(6)->onlyOnForms(),
            CollectionField::new('piste', "Pistes")->setColumns(6)->onlyOnIndex(),
            ArrayField::new('piste', "Pistes")->setColumns(6)->onlyOnDetail(),
            

            FormField::addTab(' Offre financière')
            ->setIcon('fas fa-file-shield') //<i class="fa-sharp fa-solid fa-address-book"></i>
            ->setHelp("Le contrat d'assurance en place."),
            
            //Ligne 01
            AssociationField::new('monnaie', "Monnaie")->setColumns(2),
            NumberField::new('capital', "Capital")->setColumns(2),
            FormField::addPanel('')->onlyOnForms(),
            
            NumberField::new('primenette', "Prime nette")->hideOnIndex()->setColumns(2),
            NumberField::new('fronting', "Frais/Fronting")->hideOnIndex()->setColumns(2),
            NumberField::new('arca', "Frais/Régul.")->hideOnIndex()->setColumns(2),
            NumberField::new('tva', "Tva")->hideOnIndex()->setColumns(2),
            NumberField::new('fraisadmin', "Frais admin.")->hideOnIndex()->setColumns(2),
            NumberField::new('discount', "Remise")->hideOnIndex()->setColumns(2),
            NumberField::new('primetotale', "Prime totale")->setColumns(2),
            FormField::addPanel('')->onlyOnForms(),
            NumberField::new('modepaiement', "Mode de paiement")->hideOnIndex()->setColumns(6),

            //Ligne 13
            NumberField::new('ricom', "Commission de réassurance (ht)")->hideOnIndex()->setColumns(6),
            NumberField::new('localcom', "Commission ordinaire (ht)")->hideOnIndex()->setColumns(6),

            //Ligne 14
            NumberField::new('frontingcom', "Commission sur Fronting (ht)")->hideOnIndex()->setColumns(6),
            TextEditorField::new('remarques', "Remarques")->hideOnIndex()->setColumns(6),

            //Ligne 15
            AssociationField::new('partenaire', "Partenaire")->hideOnIndex()->setColumns(6),
            BooleanField::new('cansharericom', "Partager Com. de réassurance?")->hideOnIndex()->setColumns(6),

            //Ligne 16
            BooleanField::new('cansharelocalcom', "Partager Com. ordinaire?")->hideOnIndex()->setColumns(6),
            BooleanField::new('cansharefrontingcom', "Partager Com. sur Fronting?")->hideOnIndex()->setColumns(6),

            //Ligne 17
            TextField::new('ricompayableby', "Com. de réa. - Débiteur")->hideOnIndex()->setColumns(6),
            TextField::new('localcompayableby', "Com. ord. - Débiteur")->hideOnIndex()->setColumns(6),

            //Ligne 18
            TextField::new('frontingcompayableby', "Com. sur Fronting - Débiteur")->hideOnIndex()->setColumns(6),
            AssociationField::new('pieces', "Documents / pièces justificatives")->hideOnIndex()->setColumns(6),

            //Ligne 19
            DateTimeField::new('createdAt', 'Date creation')->hideOnIndex()->hideOnForm(),
            DateTimeField::new('updatedAt', 'Dernière modification')->hideOnForm(),
            AssociationField::new('entreprise', 'Entreprise')->hideOnIndex()->setColumns(6)
        ];
    }


    public function configureActions(Actions $actions): Actions
    {
        $duplicate = Action::new(DashboardController::ACTION_DUPLICATE)->setIcon('fa-solid fa-copy')
            ->linkToCrudAction('dupliquerEntite');//<i class="fa-solid fa-copy"></i>
        $ouvrir = Action::new(DashboardController::ACTION_OPEN)
            ->setIcon('fa-solid fa-eye')->linkToCrudAction('ouvrirEntite');//<i class="fa-solid fa-eye"></i>
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
            ->reorder(Crud::PAGE_EDIT, [DashboardController::ACTION_OPEN, DashboardController::ACTION_DUPLICATE]);
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
