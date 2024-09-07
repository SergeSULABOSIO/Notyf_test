<?php

namespace App\Service\RefactoringJS\JSUIComponents\Police;

use App\Entity\Piste;
use App\Entity\Police;
use App\Service\ServiceTaxes;
use App\Service\ServiceMonnaie;
use Doctrine\ORM\EntityManager;
use App\Service\ServiceEntreprise;
use Doctrine\ORM\EntityRepository;
use App\Controller\Admin\DocPieceCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use App\Controller\Admin\PreferenceCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSChamp;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSPanelRenderer;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSCssHtmlDecoration;

class PoliceFormRenderer extends JSPanelRenderer
{
    public function __construct(
        private EntityManager $entityManager,
        private ServiceMonnaie $serviceMonnaie,
        private ServiceTaxes $serviceTaxes,
        private ServiceEntreprise $serviceEntreprise,
        private string $pageName,
        private $objetInstance,
        private $crud,
        private AdminUrlGenerator $adminUrlGenerator
    ) {
        parent::__construct(self::TYPE_FORMULAIRE, $pageName, $objetInstance, $crud, $adminUrlGenerator);
    }

    private function isExoneree(): bool
    {
        //dd($this->adminUrlGenerator->get("isExoneree"));
        $rep = false;
        if ($this->adminUrlGenerator->get("isExoneree")) {
            $rep = $this->adminUrlGenerator->get("isExoneree");
        }
        return $rep;
    }

    private function isIard(): bool
    {
        $rep = false;
        if ($this->adminUrlGenerator->get("isIard")) {
            $rep = $this->adminUrlGenerator->get("isIard");
        }
        return $rep;
    }

    public function isNewPiste()
    {
        $isNewPiste = true;
        if ($this->objetInstance instanceof Piste) {
            if ($this->objetInstance->getId()) {
                $isNewPiste = false;
            }
        }
        return $isNewPiste;
    }

    public function design()
    {
        $tauxAssureur = $this->serviceTaxes->getTauxTaxeBranche($this->isIard(), false);
        $tauxCourtier = $this->serviceTaxes->getTauxTaxeBranche($this->isIard(), true);

        $column = 12;
        if ($this->objetInstance instanceof Police) {
            $column = 10;
        }
        //**************************************************************** */
        //Section - Principale
        $this->addChamp(
            (new JSChamp())
                ->createSection("Informations générales")
                ->setIcon('fas fa-file-shield') //<i class="fa-sharp fa-solid fa-address-book"></i>
                ->setHelp("Le contrat d'assurance en place.")
                ->setColumns($column)
                ->getChamp()
        );
        //Cotation
        $this->addChamp(
            (new JSChamp())
                ->createAssociation('cotation', PreferenceCrudController::PREF_PRO_POLICE_COTATION)
                ->setColumns($column)
                ->setRequired(false)
                ->setFormTypeOptions('query_builder', function (EntityRepository $entityRepository) {
                    if ($this->objetInstance instanceof Piste) {
                        /** @var Piste */
                        $piste = $this->objetInstance;
                        if ($this->isNewPiste() == false) {
                            return $entityRepository
                                ->createQueryBuilder('e')
                                ->Where('e.entreprise = :ese')
                                ->andWhere('e.piste = :piste')
                                ->setParameter('piste', $piste)
                                ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
                        } else {
                            return $entityRepository
                                ->createQueryBuilder('e')
                                ->Where('e.entreprise = :ese')
                                ->andWhere('e.validated = :val')
                                ->andWhere('e.piste = :piste')
                                ->setParameter('val', 0)
                                ->setParameter('piste', null)
                                ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
                        }
                    } else {
                        return $entityRepository
                            ->createQueryBuilder('e')
                            ->Where('e.entreprise = :ese')
                            ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
                    }
                })
                ->getChamp()
        );
        //Reference
        $this->addChamp(
            (new JSChamp())
                ->createTexte('reference', PreferenceCrudController::PREF_PRO_POLICE_REFERENCE)
                ->setColumns($column)
                ->getChamp()
        );
        //Date opération
        $this->addChamp(
            (new JSChamp())
                ->createDate('dateoperation', PreferenceCrudController::PREF_PRO_POLICE_DATE_OPERATION)
                ->setColumns($column)
                ->getChamp()
        );
        //Date émission
        $this->addChamp(
            (new JSChamp())
                ->createDate('dateemission', PreferenceCrudController::PREF_PRO_POLICE_DATE_EMISSION)
                ->setColumns($column)
                ->getChamp()
        );
        //Date effet
        $this->addChamp(
            (new JSChamp())
                ->createDate('dateeffet', PreferenceCrudController::PREF_PRO_POLICE_DATE_EFFET)
                ->setColumns($column)
                ->getChamp()
        );

        if ($this->isNewPiste() == false) {
            /** @var Piste */
            $police = $this->objetInstance;
            if (count($police->getPolices()) != 0) {
                //Date expiration
                $this->addChamp(
                    (new JSChamp())
                        ->createDate('dateexpiration', PreferenceCrudController::PREF_PRO_POLICE_DATE_EXPIRATION)
                        ->setColumns($column)
                        ->setDisabled(true)
                        ->getChamp()
                );
                //Id Avenant
                $this->addChamp(
                    (new JSChamp())
                        ->createTexte('idAvenant', PreferenceCrudController::PREF_PRO_POLICE_ID_AVENANT)
                        ->setColumns($column)
                        ->setDisabled(true)
                        ->getChamp()
                );
                //Type Avenant
                $this->addChamp(
                    (new JSChamp())
                        ->createTexte('typeavenant', PreferenceCrudController::PREF_PRO_POLICE_TYPE_AVENANT)
                        ->setColumns($column)
                        ->setDisabled(true)
                        ->getChamp()
                );
                //Produit
                $this->addChamp(
                    (new JSChamp())
                        ->createTexte('produit', "Couverture")
                        ->setColumns($column)
                        ->setDisabled(true)
                        ->getChamp()
                );
                //Client
                $this->addChamp(
                    (new JSChamp())
                        ->createTexte('client', "Client")
                        ->setColumns($column)
                        ->setDisabled(true)
                        ->getChamp()
                );
                //Gestionnaire
                $this->addChamp(
                    (new JSChamp())
                        ->createTexte('gestionnaire', PreferenceCrudController::PREF_PRO_POLICE_GESTIONNAIRE)
                        ->setColumns($column)
                        ->setDisabled(true)
                        ->getChamp()
                );
                //Assistant Gestionnaire
                $this->addChamp(
                    (new JSChamp())
                        ->createTexte('assistant', PreferenceCrudController::PREF_PRO_POLICE_ASSISTANT)
                        ->setColumns($column)
                        ->setDisabled(true)
                        ->getChamp()
                );
                //******************************************************* */
                //Section - Document
                $this->addChamp(
                    (new JSChamp())
                        ->createSection("Documents ou pièces jointes")
                        ->setIcon("fa-solid fa-paperclip")
                        ->setColumns($column)
                        ->getChamp()
                );
                //Document
                $this->addChamp(
                    (new JSChamp())
                        ->createCollection('documents', PreferenceCrudController::PREF_CRM_COTATION_DOCUMENTS)
                        ->useEntryCrudForm(DocPieceCrudController::class)
                        ->setColumns($column)
                        ->setRequired(false)
                        ->allowDelete(true)
                        ->allowAdd(true)
                        ->getChamp()
                );
                //****************************************************** */
                //Section - Contact
                $this->addChamp(
                    (new JSChamp())
                        ->createSection("Contacts")
                        ->setIcon("fas fa-address-book")
                        ->setHelp("Personnes impliquées dans les échanges.")
                        ->setColumns($column)
                        ->getChamp()
                );
                //Contact
                $this->addChamp(
                    (new JSChamp())
                        ->createCollection('contacts', "Contacts")
                        ->setColumns($column)
                        ->setRequired(true)
                        ->getChamp()
                );
                //******************************************************* */
                //Section - Primes d'assurance
                $this->addChamp(
                    (new JSChamp())
                        ->createSection("Prime d'assurance")
                        ->setIcon("fa-solid fa-cash-register")
                        ->setHelp("Structure de la prime d'assurance résultant de la mise en place de l'avenant.")
                        ->setColumns($column)
                        ->getChamp()
                );
                //Chargements
                $this->addChamp(
                    (new JSChamp())
                        ->createCollection('chargements', "Structure")
                        ->setColumns($column)
                        ->setRequired(true)
                        ->getChamp()
                );
                //Prime totale
                $this->addChamp(
                    (new JSChamp())
                        ->createArgent('primeTotale', PreferenceCrudController::PREF_CRM_COTATION_PRIME_TTC)
                        ->setCurrency($this->serviceMonnaie->getCodeSaisie())
                        ->setColumns($column)
                        ->setDisabled(true)
                        ->getChamp()
                );
                //******************************************************* */
                //Section - Termes de paiement
                $this->addChamp(
                    (new JSChamp())
                        ->createSection("Termes de paiement de la prime")
                        ->setIcon("fa-solid fa-cash-register")
                        ->setHelp("La manière dont la prime d'assurance devra être versée par le client.")
                        ->setColumns($column)
                        ->getChamp()
                );
                //Tranches
                $this->addChamp(
                    (new JSChamp())
                        ->createCollection('tranches', "Structure")
                        ->setColumns($column)
                        ->setRequired(true)
                        ->getChamp()
                );
                //******************************************************* */
                //Section - Commission de courtage
                $this->addChamp(
                    (new JSChamp())
                        ->createSection("Commission de courtage")
                        ->setIcon("fa-solid fa-cash-register")
                        ->setHelp("Les différents revenus du courtier d'assurance.")
                        ->setColumns($column)
                        ->getChamp()
                );
                //Revenus
                $this->addChamp(
                    (new JSChamp())
                        ->createCollection('revenus', "Structure")
                        ->setColumns($column)
                        ->setRequired(true)
                        ->getChamp()
                );
                //Revenu Net Total
                $this->addChamp(
                    (new JSChamp())
                        ->createArgent('revenuNetTotal', "Revenu pure")
                        ->setCurrency($this->serviceMonnaie->getCodeSaisie())
                        ->setColumns($column)
                        ->setDisabled(true)
                        ->getChamp()
                );
                //Taxe courtier totale
                $this->addChamp(
                    (new JSChamp())
                        ->createArgent('taxeCourtierTotale', ucfirst($this->serviceTaxes->getNomTaxeCourtier() . " (" . ($tauxCourtier * 100) . "%)"))
                        ->setCurrency($this->serviceMonnaie->getCodeSaisie())
                        ->setColumns($column)
                        ->setDisabled(true)
                        ->getChamp()
                );
                //Commission totale ht
                $this->addChamp(
                    (new JSChamp())
                        ->createArgent('commissionTotaleHT', "Revenu hors taxe")
                        ->setCurrency($this->serviceMonnaie->getCodeSaisie())
                        ->setColumns($column)
                        ->setDisabled(true)
                        ->getChamp()
                );
                //Taxe Assureur
                $this->addChamp(
                    (new JSChamp())
                        ->createArgent('taxeAssureur', ucfirst($this->serviceTaxes->getNomTaxeAssureur() . " (" . ($this->isExoneree() == true ? 0 : ($tauxAssureur * 100)) . "%)"))
                        ->setCurrency($this->serviceMonnaie->getCodeSaisie())
                        ->setColumns($column)
                        ->setDisabled(true)
                        ->getChamp()
                );
                //Commission totale ttc
                $this->addChamp(
                    (new JSChamp())
                        ->createArgent('commissionTotaleTTC', "Revenu totale")
                        ->setCurrency($this->serviceMonnaie->getCodeSaisie())
                        ->setColumns($column)
                        ->setDisabled(true)
                        ->getChamp()
                );
                //******************************************************* */
                //Section - Partenaire
                $this->addChamp(
                    (new JSChamp())
                        ->createSection("Retrocommission")
                        ->setIcon("fas fa-handshake")
                        ->setHelp("Détails sur la commission à rétrocéder au partenaire.")
                        ->setColumns($column)
                        ->getChamp()
                );
                //Partenaire
                $this->addChamp(
                    (new JSChamp())
                        ->createTexte('partenaire', "Partenaire")
                        ->setColumns($column)
                        ->setDisabled(true)
                        ->getChamp()
                );
                //Taux retrocommission partenaire
                $this->addChamp(
                    (new JSChamp())
                        ->createPourcentage('tauxretrocompartenaire', "Taux exceptionnel")
                        ->setHelp("Si différent de 0%, alors c'est le taux ci-dessus qui est appliqué pour la retrocommission.")
                        ->setColumns($column)
                        ->setDisabled(true)
                        ->getChamp()
                );
                //Retrocommission partenaire
                $this->addChamp(
                    (new JSChamp())
                        ->createArgent('revenuTotalHTPartageable', "Revenu HT (partageable)")
                        ->setCurrency($this->serviceMonnaie->getCodeSaisie())
                        ->setHelp("Revenu hors taxe faisant l'objet du partage.")
                        ->setColumns($column)
                        ->setDisabled(true)
                        ->getChamp()
                );
                //Taxe courtier total partageable
                $this->addChamp(
                    (new JSChamp())
                        ->createArgent('taxeCourtierTotalePartageable', "Frais " . ucfirst($this->serviceTaxes->getNomTaxeCourtier() . " (" . ($tauxCourtier * 100) . "%)"))
                        ->setCurrency($this->serviceMonnaie->getCodeSaisie())
                        ->setColumns($column)
                        ->setDisabled(true)
                        ->getChamp()
                );
                //Revenu Net total partageable
                $this->addChamp(
                    (new JSChamp())
                        ->createArgent('revenuNetTotalPartageable', "Revenu net (partageable)")
                        ->setCurrency($this->serviceMonnaie->getCodeSaisie())
                        ->setColumns($column)
                        ->setDisabled(true)
                        ->getChamp()
                );
                //retroCom Partenaire
                $this->addChamp(
                    (new JSChamp())
                        ->createArgent('retroComPartenaire', "Retrocommission")
                        ->setCurrency($this->serviceMonnaie->getCodeSaisie())
                        ->setColumns($column)
                        ->setDisabled(true)
                        ->getChamp()
                );
                //Réserve
                $this->addChamp(
                    (new JSChamp())
                        ->createArgent('reserve', "Réserve dû au courtier lui-même")
                        ->setCurrency($this->serviceMonnaie->getCodeSaisie())
                        ->setColumns($column)
                        ->setDisabled(true)
                        ->getChamp()
                );
            }
        }
    }

    public function batchActions(?array $champs, ?string $type = null, ?string $pageName = null, $objetInstance = null, ?Crud $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array
    {
        return $champs;
    }
}
