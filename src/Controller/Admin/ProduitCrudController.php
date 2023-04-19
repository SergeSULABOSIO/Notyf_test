<?php

namespace App\Controller\Admin;

use App\Entity\Produit;
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
use EasyCorp\Bundle\EasyAdminBundle\Field\PercentField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;

class ProduitCrudController extends AbstractCrudController
{
    public const TAB_PRODUIT_IS_OBLIGATOIRE = [
        'Non' => 0,
        'Oui' => 1
    ];

    public const TAB_PRODUIT_IS_ABONNEMENT = [
        'Non' => 0,
        'Oui' => 1
    ];

    public const TAB_PRODUIT_CATEGORIE = [
        'IARD' => 0,
        'VIE' => 1
    ];

    public static function getEntityFqcn(): string
    {
        return Produit::class;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(ChoiceFilter::new('isobligatoire', 'Obligatoire?')->setChoices(self::TAB_PRODUIT_IS_OBLIGATOIRE))
            ->add(ChoiceFilter::new('isabonnement', 'Abonnement?')->setChoices(self::TAB_PRODUIT_IS_ABONNEMENT))
            ->add('tauxarca')
        ;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDateTimeFormat ('dd/MM/yyyy HH:mm:ss')
            ->setDateFormat ('dd/MM/yyyy')
            ->setPaginatorPageSize(30)
            ->renderContentMaximized()
            ->setEntityLabelInSingular("Produit")
            ->setEntityLabelInPlural("Produits")
            ->setPageTitle("index", "Liste des produits")
            ->setDefaultSort(['updatedAt' => 'DESC'])
            // ...
        ;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addPanel('Informations générales')
            ->setIcon('fas fa-gifts') //<i class="fa-sharp fa-solid fa-address-book"></i>
            ->setHelp("Une couverture d'assurance."),

            //Ligne 01
            TextField::new('code', "Code")->setColumns(6),
            TextField::new('nom', "Intitulé")->setColumns(6),

            //Ligne 02
            TextareaField::new('description', "Description")->setColumns(6),
            PercentField::new('tauxarca', "Taux/Com.")->setColumns(6),

            //Ligne 03
            ChoiceField::new('isobligatoire', "Obligatoire?")->setColumns(6)->setChoices(self::TAB_PRODUIT_IS_OBLIGATOIRE),
            ChoiceField::new('isabonnement', "Abonnement?")->setColumns(6)->setChoices(self::TAB_PRODUIT_IS_ABONNEMENT),
            
            //Ligne 04
            NumberField::new('categorie', "Catégorie")->hideOnIndex(),
            AssociationField::new('entreprise', "Entreprise")->hideOnIndex(),
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
