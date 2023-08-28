<?php

namespace App\Service;

use App\Controller\Admin\FactureCrudController;
use DateTime;
use DateInterval;
use App\Entity\Piste;
use App\Entity\Police;
use DateTimeImmutable;
use App\Entity\Cotation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use App\Controller\Admin\PoliceCrudController;
use App\Entity\Facture;
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

    public function initFature(Facture $facture, AdminUrlGenerator $adminUrlGenerator): Facture{
        $facture->setReference(strtoupper(str_replace(" ", "", "ND" . Date("dmYHis") . "/" . $this->serviceEntreprise->getEntreprise()->getNom() . "/" . Date("Y"))));
        $facture->setCreatedAt($this->serviceDates->aujourdhui());
        $facture->setUpdatedAt($this->serviceDates->aujourdhui());
        $facture->setUtilisateur($this->serviceEntreprise->getUtilisateur());
        $facture->setEntreprise($this->serviceEntreprise->getEntreprise());
        if($adminUrlGenerator->get("donnees")){
            $data = $adminUrlGenerator->get("donnees");
            $description = "";
            if($data["type"]){
                $description = $data["type"] . "<br>Ref.:" . $facture->getReference();
                $facture->setType(FactureCrudController::TAB_TYPE_FACTURE[$data["type"]]); //Date("dmYHis")
            }
            if($data["tabPolices"]){
                $description = $description . "<br>" . count($data["tabPolices"]) . " élément(s).";
            }
            $facture->setDescription($description);
        }
        
        //$facture->setType(self::TAB_TYPE_FACTURE[self::TYPE_FACTURE_COMMISSIONS]);

        return $facture;
    }
}
