<?php

namespace App\Service;

use App\Entity\Entreprise;
use App\Entity\Police;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Bundle\SecurityBundle\Security;


class ServiceCalculateur
{
    public function __construct() {
        
    }

    public function updatePoliceCalculableFileds(?Police $police){
        $police->calc_revenu_ht = $police->getLocalcom() + $police->getFrontingcom() + $police->getRicom();
    }

}
