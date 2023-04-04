<?php

namespace App\Controller\Admin;

use App\Entity\EntreeStock;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class EntreeStockCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return EntreeStock::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDateTimeFormat ('dd/MM/yyyy HH:mm:ss')
            ->setDateFormat ('dd/MM/yyyy')
            ->setPaginatorPageSize(30)
            ->renderContentMaximized()
            ->setEntityLabelInSingular("Arrivage")
            ->setEntityLabelInPlural("Arrivages")
            ->setPageTitle("index", "Liste d'arrivages")
            // ...
        ;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            //IdField::new('id'),
            DateTimeField::new('date', 'Date'),
            AssociationField::new('article', 'Article'),
            NumberField::new('quantite', 'Quantité'),
            NumberField::new('prixUnitaire', 'Prix Unit.'),
            DateTimeField::new('updated_at', 'Last update')
            ->hideOnform()
        ];
    }
   
}
