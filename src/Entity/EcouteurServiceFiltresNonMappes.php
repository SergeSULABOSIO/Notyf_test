<?php

namespace App\Entity;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;



abstract class EcouteurServiceFiltresNonMappes
{
    public function __construct() {

    }

    //public abstract function genererQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder;
    public abstract function genererQueryBuilder(): QueryBuilder;
}
