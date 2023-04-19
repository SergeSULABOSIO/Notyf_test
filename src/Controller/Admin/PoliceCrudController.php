<?php

namespace App\Controller\Admin;

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
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
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
            FormField::addPanel('Informations générales')
            ->setIcon('fas fa-file-shield') //<i class="fa-sharp fa-solid fa-address-book"></i>
            ->setHelp("Le contrat d'assurance en place."),

            //Ligne 01
            TextField::new('reference', "Référence")->setColumns(12),

            //Ligne 02
            AssociationField::new('client', "Assuré")->setColumns(12),

            //Ligne 03
            DateTimeField::new('dateoperation', "Date de l'opérat°")->hideOnIndex()->setColumns(6),
            DateTimeField::new('dateemission', "Date d'émission")->hideOnIndex()->setColumns(6),

            //Ligne 04
            DateField::new('dateeffet', "Date d'effet")->setColumns(6),
            DateField::new('dateexpiration', "Echéance")->setColumns(6),

            //Ligne 05
            NumberField::new('idavenant', "N° Avenant")->setColumns(6),
            TextField::new('typeavenant', "Type d'avenant")->hideOnIndex()->setColumns(6),

            //Ligne 06
            AssociationField::new('produit', "Couverture / Risque")->setColumns(6),
            AssociationField::new('assureur', "Assureur")->setColumns(6),

            //Ligne 07
            AssociationField::new('piste', "Pistes")->setColumns(6),
            TextField::new('reassureurs', "Réassureur")->hideOnIndex()->setColumns(6),

            //Ligne 08
            AssociationField::new('monnaie', "Monnaie"),
            NumberField::new('capital', "Capital"),

            //Ligne 09
            NumberField::new('primenette', "Prime nette")->hideOnIndex(),
            NumberField::new('fronting', "Frais Fronting")->hideOnIndex(),
            NumberField::new('arca', "Frais Arca/régulateur")->hideOnIndex(),
            NumberField::new('tva', "Tva")->hideOnIndex(),
            NumberField::new('fraisadmin', "Frais admin.")->hideOnIndex(),
            NumberField::new('primetotale', "Prime totale"),
            NumberField::new('discount', "Remise")->hideOnIndex(),
            NumberField::new('modepaiement', "Mode de paiement")->hideOnIndex(),
            NumberField::new('ricom', "Commission de réassurance (ht)")->hideOnIndex(),
            NumberField::new('localcom', "Commission ordinaire (ht)")->hideOnIndex(),
            NumberField::new('frontingcom', "Commission sur Fronting (ht)")->hideOnIndex(),
            TextEditorField::new('remarques', "Remarques")->hideOnIndex(),
            AssociationField::new('entreprise', "Entreprise")->hideOnIndex(),
            AssociationField::new('partenaire', "Partenaire")->hideOnIndex(),
            
            BooleanField::new('cansharericom', "Partager Com. de réassurance?")->hideOnIndex(),
            BooleanField::new('cansharelocalcom', "Partager Com. ordinaire?")->hideOnIndex(),
            BooleanField::new('cansharefrontingcom', "Partager Com. sur Fronting?")->hideOnIndex(),
            TextField::new('ricompayableby', "Com. de réa. - Débiteur")->hideOnIndex(),
            TextField::new('localcompayableby', "Com. ord. - Débiteur")->hideOnIndex(),
            TextField::new('frontingcompayableby', "Com. sur Fronting - Débiteur")->hideOnIndex(),
            AssociationField::new('pieces', "Documents / pièces justificatives")->hideOnIndex(),
            DateTimeField::new('createdAt', "Date création")->hideOnIndex()->hideOnForm(),
            DateTimeField::new('updatedAt', "Dernière modification")->hideOnForm()
        ];
    }


    public function configureActions(Actions $actions): Actions
    {
        $duplicate = Action::new(self::ACTION_DUPLICATE)
            ->linkToCrudAction('dupliquerEntite');//->setCssClass("btn btn-warning");

        $ouvrir = Action::new(self::ACTION_OPEN)
            ->linkToCrudAction('ouvrirEntite');

        return $actions
        //Action ouvrir
        ->add(Crud::PAGE_EDIT, $ouvrir)
        ->add(Crud::PAGE_INDEX, $ouvrir)
        //action dupliquer Assureur
        ->add(Crud::PAGE_DETAIL, $duplicate)
        ->add(Crud::PAGE_EDIT, $duplicate)
        ->add(Crud::PAGE_INDEX, $duplicate)
        ->reorder(Crud::PAGE_INDEX, [self::ACTION_OPEN, self::ACTION_DUPLICATE])
        ->reorder(Crud::PAGE_EDIT, [self::ACTION_OPEN, self::ACTION_DUPLICATE]);
    }
    
    public function dupliquerEntite(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        /**@var Assureur $assureur */
        $entite = $context->getEntity()->getInstance();
        $entiteDuplique = clone $entite;
        $this->parent::persistEntity($em, $entiteDuplique);

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
    
}
