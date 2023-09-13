<?php

namespace App\Service;
//require '../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;
use App\Entity\Taxe;
use App\Entity\Police;
use App\Entity\Facture;
use App\Entity\ElementFacture;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use App\Controller\Admin\FactureCrudController;
use Doctrine\Common\Collections\ArrayCollection;
use EasyCorp\Bundle\EasyAdminBundle\Dto\BatchActionDto;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class ServiceFacture
{
    private ?Dompdf $dompdf;
    private ?Options $pdfOptions;

    public function __construct(
        private ServiceSuppression $serviceSuppression,
        private ServiceCompteBancaire $serviceCompteBancaire,
        private ServiceTaxes $serviceTaxes,
        private ServiceMonnaie $serviceMonnaie,
        private ServiceDates $serviceDates,
        private ServiceCalculateur $serviceCalculateur,
        private EntityManagerInterface $entityManager,
        private ServiceEntreprise $serviceEntreprise,
        private Security $security
    ) {
        $this->pdfOptions = new Options();
        $this->pdfOptions->set('defaultFont', 'Arial');
        $this->dompdf = new Dompdf($this->pdfOptions);
        $this->dompdf->setPaper('A4', 'portrait'); // ou 'landscape'
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
                $total = $this->chargerElementFactures($facture, $data["type"], $data["tabPolices"]);
                $description = $description . "<br>Montant Total: " . $this->serviceMonnaie->getMonantEnMonnaieAffichage($total);
            }
            $facture->setDescription($description);
        }
        $this->serviceCompteBancaire->setComptes($facture, "");
        return $facture;
    }

    public function canIssueFacture(BatchActionDto $batchActionDto, $typeFacture): array
    {
        $reponses = [
            "status" => true,
            "Messages" => "Salut " . $this->serviceEntreprise->getUtilisateur() . ". Vous pouvez ajuster la facture à volonté et même y revenir quand cela vous arrange."
        ];
        $soldeComNull = false;
        $tabTiers_str = "";
        $tabTiers = new ArrayCollection();
        foreach ($batchActionDto->getEntityIds() as $id) {
            /** @var Police */
            $police = $this->entityManager->getRepository(Police::class)->find($id);
            $this->serviceCalculateur->updatePoliceCalculableFileds($police);
            //il faut switcher ici : On agit différemment selon le type de facture
            switch ($typeFacture) {
                case FactureCrudController::TYPE_FACTURE_COMMISSIONS:
                    $soldeComNull = ($police->calc_revenu_ttc_solde_restant_du == 0);
                    if (!$tabTiers->contains($police->getAssureur())) {
                        $tabTiers_str = $tabTiers_str  . $police->getAssureur()->getNom() . ", ";
                    }
                    $tabTiers->add($police->getAssureur());
                    break;
                case FactureCrudController::TYPE_FACTURE_FRAIS_DE_GESTION:
                    $soldeComNull = ($police->calc_revenu_ttc_solde_restant_du == 0);
                    if (!$tabTiers->contains($police->getClient())) {
                        $tabTiers_str = $tabTiers_str  . $police->getClient()->getNom() . ", ";
                    }
                    $tabTiers->add($police->getClient());
                    break;
                case FactureCrudController::TYPE_FACTURE_RETROCOMMISSIONS:
                    $soldeComNull = ($police->calc_retrocom_solde == 0);
                    if ($police->getPartenaire()) {
                        if (!$tabTiers->contains($police->getPartenaire())) {
                            $tabTiers_str = $tabTiers_str  . $police->getPartenaire()->getNom() . ", ";
                        }
                        $tabTiers->add($police->getPartenaire());
                    }
                    break;
                case FactureCrudController::TYPE_FACTURE_NOTE_DE_PERCEPTION_TVA:
                    $soldeComNull = ($police->calc_taxes_assureurs_solde == 0);
                    if ($this->serviceTaxes->getTaxe(false)) {
                        $tabTiers->add($this->serviceTaxes->getTaxe(false)->getOrganisation());
                    }
                    break;
                case FactureCrudController::TYPE_FACTURE_NOTE_DE_PERCEPTION_ARCA:
                    $soldeComNull = ($police->calc_taxes_courtier_solde == 0);
                    if ($this->serviceTaxes->getTaxe(false)) {
                        $tabTiers->add($this->serviceTaxes->getTaxe(true)->getOrganisation());
                    }
                    break;
                default:
                    # code...
                    break;
            }
        }
        /** @var Taxe */
        $taxeArca = $this->serviceTaxes->getTaxe(true);
        /** @var Taxe */
        $taxeTva = $this->serviceTaxes->getTaxe(false);
        //Petit toiletage du string de la liste
        if (strlen($tabTiers_str) > 2) {
            $tabTiers_str = substr($tabTiers_str, 0, -2); //on enlève la dernière virgule et l'espace ", "
            $tabTiers_str = strtolower($tabTiers_str);
            $tabTiers_str = ucwords($tabTiers_str);
        }
        //Construction des messages / réponses
        switch ($typeFacture) {
            case FactureCrudController::TYPE_FACTURE_COMMISSIONS:
                if ($this->hasUniqueData($tabTiers) == false) {
                    $reponses["status"] = false;
                    $reponses["Messages"] = "Salut " . $this->serviceEntreprise->getUtilisateur() . ". La séléction que vous venez de faire concerne plusieurs assureurs différents (nous avons trouvé " . $tabTiers_str . "). Elle ne devrait conerner qu'un seul assureur à la fois. ";
                }
                if ($soldeComNull) {
                    $reponses["status"] = false;
                    $reponses["Messages"] = $reponses["Messages"] . "La commission due est nulle, donc rien à collecter.";
                }
                break;
            case FactureCrudController::TYPE_FACTURE_FRAIS_DE_GESTION:
                if ($this->hasUniqueData($tabTiers) == false) {
                    $reponses["status"] = false;
                    $reponses["Messages"] = "Salut " . $this->serviceEntreprise->getUtilisateur() . ". La séléction que vous venez de faire concerne plusieurs assurés différents (nous avons trouvé " . $tabTiers_str . "). Elle ne devrait conerner qu'un seul assuré à la fois. ";
                }
                if ($soldeComNull) {
                    $reponses["status"] = false;
                    $reponses["Messages"] = $reponses["Messages"] . "Le montant du est nul, donc rien à facturer.";
                }
                break;
            case FactureCrudController::TYPE_FACTURE_RETROCOMMISSIONS:
                if ($this->hasUniqueData($tabTiers) == false) {
                    $reponses["status"] = false;
                    $reponses["Messages"] = "Salut " . $this->serviceEntreprise->getUtilisateur() . ". La séléction que vous venez de faire concerne plusieurs partenaires différents (" . $tabTiers_str . "). Elle ne devrait conerner qu'un seul partenaire à la fois. ";
                }
                if ($soldeComNull) {
                    $reponses["status"] = false;
                    $reponses["Messages"] = $reponses["Messages"] . "La retro-commission due est nulle, donc rien à retrocéder.";
                }
                break;
            case FactureCrudController::TYPE_FACTURE_NOTE_DE_PERCEPTION_TVA:
                if ($soldeComNull) {
                    $reponses["status"] = false;
                    $reponses["Messages"] = "Salut " . $this->serviceEntreprise->getUtilisateur() . ". La " . $taxeTva->getNom() . " due est nulle, donc rien à payer à " . $taxeTva->getOrganisation() . ". ";
                }
                break;
            case FactureCrudController::TYPE_FACTURE_NOTE_DE_PERCEPTION_ARCA:
                if ($soldeComNull) {
                    $reponses["status"] = false;
                    $reponses["Messages"] = "Salut " . $this->serviceEntreprise->getUtilisateur() . ". " . $taxeTva->getNom() . " due est nulle, donc rien à payer à " . $taxeArca->getOrganisation() . ". ";
                }
                break;

            default:
                # code...
                break;
        }
        return $reponses;
    }


    public function hasUniqueData(ArrayCollection $tabData): bool
    {
        //S'il s'agit de la même données
        $isSameData = true;
        $firstData = $tabData->get(0);
        foreach ($tabData as $currentData) {
            if ($firstData != $currentData) {
                $isSameData = false;
            }
        }
        return $isSameData;
    }

    public function getType(int $typeFacture)
    {
        foreach (FactureCrudController::TAB_TYPE_FACTURE as $key => $value) {
            if ($typeFacture === $value) {
                return $key;
            }
        }
        return null;
    }

    public function canCollectCommissions(Police $police)
    {
        return $police->calc_revenu_ttc_solde_restant_du != 0;
    }

    public function canPayPartner(Police $police)
    {
        return $police->calc_retrocom_solde != 0;
    }

    public function canPayVAT(Police $police)
    {
        return $police->calc_taxes_assureurs_solde != 0;
    }

    public function canPayRegulator(Police $police)
    {
        return $police->calc_taxes_courtier_solde != 0;
    }

    private function chargerElementFactures(Facture $facture, $typeFacture, array $tabIdPolices)
    {
        $total = 0;
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
                    case FactureCrudController::TYPE_FACTURE_FRAIS_DE_GESTION:
                        /** @var ElementFacture */
                        $ef = new ElementFacture();
                        $ef->setPolice($oPolice);
                        $ef->setMontant($oPolice->calc_revenu_ttc_solde_restant_du);
                        $facture->setAutreTiers($oPolice->getClient()->getNom());
                        break;
                    case FactureCrudController::TYPE_FACTURE_RETROCOMMISSIONS:
                        /** @var ElementFacture */
                        $ef = new ElementFacture();
                        $ef->setPolice($oPolice);
                        $ef->setMontant($oPolice->calc_retrocom_solde);
                        $facture->setPartenaire($oPolice->getPartenaire());
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
                $total += $ef->getMontant();
            }
            if ($ef->getMontant() != 0) {
                $this->setAutresAttributs($facture, $ef);
            }
        }
        //Etablissement du lien entre Police et Facture
        //C'est super important.
        foreach ($facture->getElementFactures() as $ef) {
            /** @var Police */
            $oPolice = $ef->getPolice();
            $oPolice->addFacture($facture);
        }
        //dd($facture);
        return $total;
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

    public function cleanElementFacture(Facture $facture)
    {
        $elementFactures = $this->entityManager->getRepository(ElementFacture::class)->findBy(
            ['entreprise' => $this->serviceEntreprise->getEntreprise()]
        );
        //dd($elementFactures);
        foreach ($elementFactures as $ef) {
            /** @var ElementFacture */
            if ($ef->getFacture() == null) {
                $facture->removePolice($ef->getPolice());
                $this->serviceSuppression->supprimer($ef, ServiceSuppression::FINANCE_ELEMENT_FACTURE);
            }
        }
    }


    private function getNomFichierFacture(?Facture $f): string
    {
        return $f != null ? "Facture_" . $f->getId() . ".pdf" : "Facture_sans_nom.pdf";
    }

    public function visualiserFacture(?Facture $facture)
    {
        if ($facture != null) {
            $this->dompdf->loadHtml('hello world');
            $this->dompdf->render();
            return new Response(
                $this->dompdf->stream($this->getNomFichierFacture($facture), ["Attachment" => false]),
                Response::HTTP_OK,
                ['Content-Type' => 'application/pdf']
            );
        }
    }

    public function telechargerFacture(?Facture $facture)
    {
        if ($facture != null) {
            $this->dompdf->loadHtml('hello world');
            $this->dompdf->render();
            return new Response(
                $this->dompdf->stream($this->getNomFichierFacture($facture), ["Attachment" => true]),
                Response::HTTP_OK,
                ['Content-Type' => 'application/pdf']
            );
        }
    }
}
