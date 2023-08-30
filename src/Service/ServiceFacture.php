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
use App\Controller\Admin\TaxeCrudController;
use App\Entity\ElementFacture;
use App\Entity\Facture;
use App\Entity\Taxe;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use PhpParser\Node\Expr\Cast\Array_;

class ServiceFacture
{
    private $taxes = [];
    public function __construct(
        private ServiceTaxes $serviceTaxes,
        private ServiceDates $serviceDates,
        private ServiceCalculateur $serviceCalculateur,
        private EntityManagerInterface $entityManager,
        private ServiceEntreprise $serviceEntreprise,
        private Security $security
    ) {

    }

    public function initFature(Facture $facture, AdminUrlGenerator $adminUrlGenerator): Facture
    {
        $facture->setReference(strtoupper(str_replace(" ", "", "ND" . Date("dmYHis") . "/" . $this->serviceEntreprise->getEntreprise()->getNom() . "/" . Date("Y"))));
        $facture->setCreatedAt($this->serviceDates->aujourdhui());
        $facture->setUpdatedAt($this->serviceDates->aujourdhui());
        $facture->setUtilisateur($this->serviceEntreprise->getUtilisateur());
        $facture->setEntreprise($this->serviceEntreprise->getEntreprise());
        if ($adminUrlGenerator->get("donnees")) {
            $data = $adminUrlGenerator->get("donnees");
            $description = "";
            if ($data["type"] && $data["tabPolices"]) {
                $description = $data["type"] . "<br>Ref.:" . $facture->getReference();
                $description = $description . "<br>" . count($data["tabPolices"]) . " élément(s).";
                $facture->setType(FactureCrudController::TAB_TYPE_FACTURE[$data["type"]]);
                $this->chargerElementFactures($facture, $data["type"], $data["tabPolices"]);
            }
            $facture->setDescription($description);
        }

        //$facture->setType(self::TAB_TYPE_FACTURE[self::TYPE_FACTURE_COMMISSIONS]);

        return $facture;
    }

    private function chargerElementFactures(Facture $facture, $typeFacture, array $tabIdPolices)
    {
        foreach ($tabIdPolices as $idPolice) {
            /** @var Police */
            $oPolice = $this->entityManager->getRepository(Police::class)->find($idPolice);
            if ($oPolice) {
                $this->serviceCalculateur->updatePoliceCalculableFileds($oPolice);
                switch ($typeFacture) {
                    case FactureCrudController::TYPE_FACTURE_COMMISSIONS:
                        /** @var ElementFacture */
                        $ef = new ElementFacture();
                        $ef->setPolice($oPolice);
                        $ef->setMontant($oPolice->calc_revenu_ttc_solde_restant_du);
                        $facture->setAssureur($oPolice->getAssureur());
                        break;
                    case FactureCrudController::TYPE_FACTURE_RETROCOMMISSIONS:
                        /** @var ElementFacture */
                        $ef = new ElementFacture();
                        $ef->setPolice($oPolice);
                        $ef->setMontant($oPolice->calc_retrocom_solde);
                        $facture->setAssureur($oPolice->getPartenaire());
                        break;
                    case FactureCrudController::TYPE_FACTURE_NOTE_DE_PERCEPTION_TVA:
                        /** @var Taxe */
                        $taxe = $this->serviceTaxes->getTaxe(false);
                        /** @var ElementFacture */
                        $ef = new ElementFacture();
                        $ef->setPolice($oPolice);
                        $ef->setMontant($oPolice->calc_taxes_assureurs_solde);
                        $facture->setAutreTiers($taxe->getOrganisation());
                        break;
                    case FactureCrudController::TYPE_FACTURE_NOTE_DE_PERCEPTION_ARCA:
                        /** @var Taxe */
                        $taxe = $this->serviceTaxes->getTaxe(true);
                        /** @var ElementFacture */
                        $ef = new ElementFacture();
                        $ef->setPolice($oPolice);
                        $ef->setMontant($oPolice->calc_taxes_courtier_solde);
                        $facture->setAutreTiers($taxe->getOrganisation());
                        break;
                    default:
                        # code...
                        break;
                }
            }
            if ($ef->getMontant() != 0) {
                $this->setAutresAttributs($facture, $ef);
            }
        }
    }

    private function setAutresAttributs(Facture $facture, ElementFacture $ef)
    {
        $ef->setEntreprise($this->serviceEntreprise->getEntreprise());
        $ef->setUtilisateur($this->serviceEntreprise->getUtilisateur());
        $ef->setCreatedAt($this->serviceDates->aujourdhui());
        $ef->setUpdatedAt($this->serviceDates->aujourdhui());
        $ef->setFacture($facture);
        $facture->addElementFacture($ef);
    }
}
