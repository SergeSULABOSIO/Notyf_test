<?php

namespace App\Service;

use DateTime;
use DateInterval;
use App\Entity\Piste;
use App\Entity\Police;
use DateTimeImmutable;
use App\Entity\Cotation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use App\Controller\Admin\PoliceCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class ServiceFacture
{
    public function __construct(
        private ServiceDates $serviceDates,
        private EntityManagerInterface $entityManager,
        private ServiceEntreprise $serviceEntreprise,
        private Security $security
    ) {
    }
}
