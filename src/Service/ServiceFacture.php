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
use App\Controller\Admin\PoliceCrudController;
use App\Entity\Assureur;
use App\Entity\DocPiece;
use App\Entity\Paiement;
use App\Entity\Tranche;
use Doctrine\Common\Collections\ArrayCollection;
use EasyCorp\Bundle\EasyAdminBundle\Dto\BatchActionDto;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class ServiceFacture
{
    private ?Dompdf $dompdf = null;
    private ?Options $pdfOptions = null;

    public function __construct(
        private ServiceAvenant $serviceAvenant,
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
    }

    private function generateInvoiceReference($indice): string
    {
        return strtoupper(str_replace(" ", "", "ND" . $indice . "/" . Date("dmYHis") . "/" . $this->serviceEntreprise->getEntreprise()->getNom() . "/" . Date("Y")));
    }

    public function initFature(Facture $facture, AdminUrlGenerator $adminUrlGenerator): Facture
    {
        $facture->setReference($this->generateInvoiceReference(1));
        $facture->setCreatedAt($this->serviceDates->aujourdhui());
        $facture->setUpdatedAt($this->serviceDates->aujourdhui());
        $facture->setUtilisateur($this->serviceEntreprise->getUtilisateur());
        $facture->setEntreprise($this->serviceEntreprise->getEntreprise());
        if ($adminUrlGenerator->get("donnees")) {
            $data = $adminUrlGenerator->get("donnees");
            $description = "";
            //dd($data["type"]);
            if (isset($data["type"]) && isset($data["tabTranches"])) {
                $description = $data["type"] . ", Ref.:" . $facture->getReference();
                $facture->setType(FactureCrudController::TAB_TYPE_FACTURE[$data["type"]]);
                $total = $this->chargerElementFactures($facture, $data["type"], $data["tabTranches"]);
            }
            $facture->setDescription($description);
        }
        $this->serviceCompteBancaire->setComptes($facture, "");
        return $facture;
    }

    /**
     * Cette fonction a pour vocation de créer puis initialiser l'objet facture
     * avec les informations collectées depuis l'objet Police que l'on passera en paramètre.
     *
     * @param Police|null $police
     * @return void
     */
    public function processFacturePrime(?Police $police)
    {
        if ($police != null) {
            $indice = 1;
            /** @var Tranche */
            foreach ($police->getTranches() as $tranche) {
                $savedPremiumInvoice = $this->getSavedPremiumInvoice($tranche);
                $newPremiumInvoice = $this->createPremiumInvoice($indice, $tranche);

                dd("Comparaison", $this->areEqual($savedPremiumInvoice, $newPremiumInvoice));

                // //Enregistrement de la facture
                // $this->entityManager->persist($newPremiumInvoice);
                // $this->entityManager->flush();
                // dd("Ancienne facture", $savedInvoice, "Nouvelle facture", $newPremiumInvoice);
                $indice = $indice + 1;
            }
        }
    }


    private function areEqual(?Facture $factureA, ?Facture $factureB):array{
        $sameMontant = $factureA->getMontantTTC() == $factureB->getMontantTTC();
        $sameClient = $factureA->getAutreTiers() == $factureB->getAutreTiers();
        $sameAssureur = $factureA->getAssureur() == $factureB->getAssureur();
        $sameTranche = $factureA->getElementFactures()[0]->getTranche() == $factureB->getElementFactures()[0]->getTranche();
        
        $final = $sameMontant && $sameClient && $sameAssureur && $sameTranche;
        return [
            "sameMontant" => $sameMontant,
            "sameClient" => $sameClient,
            "sameAssureur" => $sameAssureur,
            "sameTranche" => $sameTranche,
            "final" => $final,
        ];
    }

    private function getSavedPremiumInvoice(?Tranche $tranche): ?Facture{
        if($tranche->getElementFactures()[0] != null){
            $factureEnregistrees = $this->entityManager->getRepository(Facture::class)->find($tranche->getElementFactures()[0]->getFacture()->getId());
            if($factureEnregistrees != null){
                return $factureEnregistrees;
            }
        }
        return null;
    }

    private function createPremiumInvoice($indice, ?Tranche $tranche): Facture
    {
        $factureDeLaTranche = new Facture();
        $factureDeLaTranche->setSignedBy("Pour " . $tranche->getPolice()->getAssureur()->getNom());
        $factureDeLaTranche->setPosteSignedBy("Direction financière");
        $factureDeLaTranche->setStatus(FactureCrudController::TAB_STATUS_FACTURE[FactureCrudController::STATUS_FACTURE_IMPAYEE]);
        $factureDeLaTranche->setAutreTiers($tranche->getPolice()->getClient()->getNom());
        $factureDeLaTranche->setPartenaire($tranche->getPolice()->getPartenaire());
        $factureDeLaTranche->setAssureur($tranche->getPolice()->getAssureur());
        $factureDeLaTranche->setDescription($this->generateDescriptionFacture($tranche, $tranche->getPolice()));
        $factureDeLaTranche->setReference($this->generateInvoiceReference($indice));
        $factureDeLaTranche->setType(FactureCrudController::TAB_TYPE_FACTURE[FactureCrudController::TYPE_FACTURE_PRIME]);
        $factureDeLaTranche->setEntreprise($tranche->getPolice()->getEntreprise());
        $factureDeLaTranche->setUtilisateur($tranche->getPolice()->getUtilisateur());
        $factureDeLaTranche->setCreatedAt($tranche->getPolice()->getCreatedAt());
        $factureDeLaTranche->setUpdatedAt($tranche->getPolice()->getUpdatedAt());
        //Element facture / article de la facture
        $elementFacture = $this->generateElementFacturePrime($tranche->getPolice(), $factureDeLaTranche, $tranche);

        $factureDeLaTranche->setTotalDu($elementFacture->getMontant());
        $factureDeLaTranche->setTotalRecu(0);
        $factureDeLaTranche->setTotalSolde(($elementFacture->getMontant() - 0));
        $factureDeLaTranche->addElementFacture($elementFacture);
        $tranche->addElementFacture($elementFacture);
        return $factureDeLaTranche;
    }

    private function generateElementFacturePrime(?Police $police, ?Facture $factureDeLaTranche, ?Tranche $tranche): ElementFacture
    {
        $elementFacture = new ElementFacture();
        $elementFacture->setEntreprise($police->getEntreprise());
        $elementFacture->setUtilisateur($police->getUtilisateur());
        $elementFacture->setCreatedAt($police->getCreatedAt());
        $elementFacture->setUpdatedAt($police->getUpdatedAt());
        $elementFacture->setFacture($factureDeLaTranche);
        $elementFacture->setMontant($tranche->getPrimeTotaleTranche());
        $elementFacture->setTranche($tranche);
        $elementFacture->setTypeavenant($police->getTypeavenant());
        $elementFacture->setIdavenant($this->serviceAvenant->generateIdAvenant($police));
        return $elementFacture;
    }


    private function generateDescriptionFacture(?Tranche $tranche, ?Police $police): string
    {
        return "Prime d'assurance - " .
            $tranche->getNom() .
            " : Ref. police: " .
            $police->getReference() . " / " .
            $police->getProduit() . " / " .
            $police->getClient() . " / " .
            $police->getAssureur() .
            " / Du " .
            $this->serviceDates->getTexteSimple(
                $police->getDateeffet()
            ) .
            " au " .
            $this->serviceDates->getTexteSimple(
                $police->getDateexpiration()
            );
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
            /** @var Tranche */
            $tranche = $this->entityManager->getRepository(Tranche::class)->find($id);
            //$this->serviceCalculateur->updatePoliceCalculableFileds($police);
            //il faut switcher ici : On agit différemment selon le type de facture
            switch ($typeFacture) {
                case FactureCrudController::TYPE_FACTURE_COMMISSIONS:
                    $soldeComNull = ($tranche->getPrimeTotaleTranche() == 0);
                    if (!$tabTiers->contains($tranche->getAssureur())) {
                        $tabTiers_str = $tabTiers_str  . $tranche->getAssureur()->getNom() . ", ";
                    }
                    $tabTiers->add($tranche->getAssureur());
                    break;
                case FactureCrudController::TYPE_FACTURE_FRAIS_DE_GESTION:
                    $soldeComNull = ($tranche->getComFraisGestion() == 0);
                    if (!$tabTiers->contains($tranche->getClient())) {
                        $tabTiers_str = $tabTiers_str  . $tranche->getClient()->getNom() . ", ";
                    }
                    $tabTiers->add($tranche->getClient());
                    break;
                case FactureCrudController::TYPE_FACTURE_RETROCOMMISSIONS:
                    $soldeComNull = ($tranche->getRetroCommissionTotale() == 0);
                    if ($tranche->getPartenaire()) {
                        if (!$tabTiers->contains($tranche->getPartenaire())) {
                            $tabTiers_str = $tabTiers_str  . $tranche->getPartenaire()->getNom() . ", ";
                        }
                        $tabTiers->add($tranche->getPartenaire());
                    }
                    break;
                case FactureCrudController::TYPE_FACTURE_NOTE_DE_PERCEPTION_TVA:
                    $soldeComNull = ($tranche->getTaxeAssureurTotale() == 0);
                    if ($this->serviceTaxes->getTaxe(false)) {
                        $tabTiers->add($this->serviceTaxes->getTaxe(false)->getOrganisation());
                    }
                    break;
                case FactureCrudController::TYPE_FACTURE_NOTE_DE_PERCEPTION_ARCA:
                    $soldeComNull = ($tranche->getTaxeCourtierTotale() == 0);
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
            case FactureCrudController::TYPE_FACTURE_PRIME:
                if ($this->hasUniqueData($tabTiers) == false) {
                    $reponses["status"] = false;
                    $reponses["Messages"] = "Salut " . $this->serviceEntreprise->getUtilisateur() . ". La séléction que vous venez de faire concerne plusieurs assureurs différents (nous avons trouvé " . $tabTiers_str . "). Elle ne devrait conerner qu'un seul assureur à la fois. ";
                }
                if ($soldeComNull) {
                    $reponses["status"] = false;
                    $reponses["Messages"] = $reponses["Messages"] . "La prime totale due est nulle, donc rien à collecter.";
                }
                break;
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

    public function updatePieceInfos(Paiement $paiement)
    {
        foreach ($paiement->getPieces() as $piece) {
            $piece->setCreatedAt($paiement->getCreatedAt());
            $piece->setUpdatedAt($paiement->getUpdatedAt());
            $piece->setEntreprise($paiement->getEntreprise());
            $piece->setUtilisateur($paiement->getUtilisateur());
        }
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

    public function canCollectCommissions(Tranche $tranche)
    {
        $com =
            $tranche->getComAutreChargement() +
            $tranche->getComFronting() +
            $tranche->getComLocale() +
            $tranche->getComReassurance();
        return $com != 0;
    }

    public function canCollectFraisGestion(Tranche $tranche)
    {
        return $tranche->getComFraisGestion() != 0;
    }

    public function canPayPartner(Tranche $tranche)
    {
        return $tranche->getRetroCommissionTotale() != 0;
    }

    public function canPayVAT(Tranche $tranche)
    {
        return $tranche->getTaxeAssureurTotale() != 0;
    }

    public function canPayRegulator(Tranche $tranche)
    {
        return $tranche->getTaxeCourtierTotale() != 0;
    }

    private function chargerElementFactures(Facture $facture, $typeFacture, array $tabIdTranches)
    {
        $total = 0;
        foreach ($tabIdTranches as $idTranche) {
            /** @var Tranche */
            $oTranche = $this->entityManager->getRepository(Tranche::class)->find($idTranche);
            if ($oTranche) {
                //$this->serviceCalculateur->updatePoliceCalculableFileds($oPolice);
                switch ($typeFacture) {
                    case FactureCrudController::TYPE_FACTURE_PRIME:
                        /** @var ElementFacture */
                        $ef = new ElementFacture();
                        $ef->setTranche($oTranche);
                        $ef->setMontant($oTranche->getPrimeTotaleTranche());
                        $facture->setAssureur($oTranche->getAssureur());
                        break;
                    case FactureCrudController::TYPE_FACTURE_COMMISSIONS:
                        /** @var ElementFacture */
                        $ef = new ElementFacture();
                        $ef->setTranche($oTranche);

                        $com =
                            $oTranche->getComAutreChargement() +
                            $oTranche->getComFronting() +
                            $oTranche->getComLocale() +
                            $oTranche->getComReassurance();

                        $ef->setMontant($com);
                        $facture->setAssureur($oTranche->getAssureur());
                        break;
                    case FactureCrudController::TYPE_FACTURE_FRAIS_DE_GESTION:
                        /** @var ElementFacture */
                        $ef = new ElementFacture();
                        $ef->setTranche($oTranche);
                        $ef->setMontant($oTranche->getComFraisGestion() * 100);
                        $facture->setAutreTiers($oTranche->getClient());
                        break;
                    case FactureCrudController::TYPE_FACTURE_RETROCOMMISSIONS:
                        /** @var ElementFacture */
                        $ef = new ElementFacture();
                        $ef->setTranche($oTranche);
                        $ef->setMontant($oTranche->getRetroCommissionTotale() * 100);
                        $facture->setPartenaire($oTranche->getPartenaire());
                        break;
                    case FactureCrudController::TYPE_FACTURE_NOTE_DE_PERCEPTION_TVA:
                        /** @var Taxe */
                        $taxe = $this->serviceTaxes->getTaxe(false);
                        /** @var ElementFacture */
                        $ef = new ElementFacture();
                        $ef->setTranche($oTranche);
                        $ef->setMontant($oTranche->getTaxeAssureurTotale() * 100);
                        $facture->setAutreTiers($taxe->getOrganisation());
                        break;
                    case FactureCrudController::TYPE_FACTURE_NOTE_DE_PERCEPTION_ARCA:
                        /** @var Taxe */
                        $taxe = $this->serviceTaxes->getTaxe(true);
                        /** @var ElementFacture */
                        $ef = new ElementFacture();
                        $ef->setTranche($oTranche);
                        $ef->setMontant($oTranche->getTaxeCourtierTotale() * 100);
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
        return $total;
    }

    private function setAutresAttributs(Facture $facture, ElementFacture $ef)
    {
        $ef->setEntreprise($this->serviceEntreprise->getEntreprise())
            ->setUtilisateur($this->serviceEntreprise->getUtilisateur())
            ->setCreatedAt($this->serviceDates->aujourdhui())
            ->setUpdatedAt($this->serviceDates->aujourdhui())
            ->setFacture($facture);
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
                //$facture->removePolice($ef->getPolice());
                $this->serviceSuppression->supprimer($ef, ServiceSuppression::FINANCE_ELEMENT_FACTURE);
            }
        }
    }


    private function getNomFichierFacture(?Facture $f, bool $isBordereau): null|string
    {
        if ($isBordereau == true) {
            return $f != null ? "Bordereau_id_" . $f->getId() . ".pdf" : "Bordereau_sans_nom.pdf";
        } else {
            return $f != null ? "Note_id_" . $f->getId() . ".pdf" : "Facture_sans_nom.pdf";
        }
    }

    public function imageToBase64($path)
    {
        $path = $path;
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        return $base64;
    }

    private function dessinerContenuFacture(?Facture $facture, $contenuHtml, bool $isBordereau)
    {
        if ($isBordereau) {
            $this->dompdf->setPaper('A4', 'landscape'); // ou 'landscape'
        } else {
            $this->dompdf->setPaper('A4', 'portrait'); // ou ''
        }

        $this->dompdf->loadHtml($contenuHtml);
        $this->dompdf->render();
    }

    public function visualiserFacture(?Facture $facture, $contenuHtml)
    {
        return $this->produireFacture($facture, false, $contenuHtml, false);
    }

    public function visualiserBordereau(?Facture $facture, $contenuHtml)
    {
        return $this->produireFacture($facture, false, $contenuHtml, true);
    }

    private function produireFacture(?Facture $facture, bool $canDownload, $contenuHtml, bool $isBordereau)
    {
        if ($facture != null) {
            $this->dessinerContenuFacture($facture, $contenuHtml, $isBordereau);
            $fileName = $this->getNomFichierFacture($facture, $isBordereau);
            $options = ["Attachment" => $canDownload];
            $streamPDF = $this->dompdf->stream($fileName, $options);
            return new Response(
                //$streamPDF,
                $this->stream($fileName, $options),
                Response::HTTP_OK,
                ['Content-Type' => 'application/pdf']
            );
        } else {
            return new Response("", Response::HTTP_NO_CONTENT, []);
        }
    }

    private function stream(?string $fileName, ?array $options)
    {
        $this->dompdf->stream($fileName, $options);
        return "RAS-SERGE";
    }
}
