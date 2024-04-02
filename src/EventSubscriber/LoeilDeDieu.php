<?php

namespace App\EventSubscriber;

use App\Entity\Piste;
use App\Entity\Client;
use App\Entity\Police;
use App\Entity\Revenu;
use DateTimeImmutable;
use App\Entity\Contact;
use App\Entity\Facture;
use App\Entity\Monnaie;
use App\Entity\Tranche;
use App\Entity\Cotation;
use App\Entity\DocPiece;
use App\Entity\Paiement;
use App\Entity\ActionCRM;
use App\Entity\Chargement;
use App\Entity\Partenaire;
use App\Entity\FeedbackCRM;
use App\Entity\Utilisateur;
use App\Service\ServiceDates;
use App\Entity\ElementFacture;
use App\Service\ServiceAvenant;
use App\Service\ServiceFacture;
use App\Service\ServiceEntreprise;
use App\Service\ServicePreferences;
use App\Service\ServiceSuppression;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\Admin\PisteCrudController;
use App\Controller\Admin\PoliceCrudController;
use App\Controller\Admin\RevenuCrudController;
use App\Controller\Admin\FactureCrudController;
use App\Controller\Admin\MonnaieCrudController;
use App\Controller\Admin\ActionCRMCrudController;
use App\Controller\Admin\ChargementCrudController;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use App\Service\RefactoringJS\Initialisateurs\Facture\FactureDgiModif;
use App\Service\RefactoringJS\Initialisateurs\Facture\FactureArcaModif;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Service\RefactoringJS\Initialisateurs\Facture\FactureClientModif;
use App\Service\RefactoringJS\Initialisateurs\Facture\FactureAssureurModif;
use App\Service\RefactoringJS\Initialisateurs\Facture\FacturePartenaireInit;
use App\Service\RefactoringJS\Initialisateurs\Facture\FacturePartenaireModif;

class LoeilDeDieu implements EventSubscriberInterface
{

    public function __construct(
        private UploaderHelper $serviceHelper,
        private ServiceFacture $serviceFacture,
        private EntityManagerInterface $entityManager,
        private ServiceDates $serviceDates,
        private UserPasswordHasherInterface $hasher,
        private ServiceEntreprise $serviceEntreprise,
        private ServicePreferences $servicePreferences,
        private ServiceSuppression $serviceSuppression,
        private ServiceAvenant $serviceAvenant
    ) {
    }

    public static function getSubscribedEvents()
    {
        return [
            //AfterEntityBuiltEvent::class => ['updateCalculableFiledsOnPoliceEntity'],
            // BeforeEntityPersistedEvent::class => ['setCreatedAt'],
            // BeforeEntityUpdatedEvent::class => ['setUpdatedAt']
        ];
    }


    // public function setCreatedAt(BeforeEntityPersistedEvent $event)
    // {
    //     $entityInstance = $event->getEntityInstance();
    //     // dd($entityInstance);

    //     if ($entityInstance instanceof Monnaie) {
    //         $entityInstance = $this->updateNomMonnaie($entityInstance);
    //     }
    //     if ($entityInstance instanceof Utilisateur) {
    //         $newpassword = $entityInstance->getPlainPassword();
    //         if ($newpassword !== "") {
    //             //dd($newpassword);
    //             $hashedPassword = $this->hasher->hashPassword($entityInstance, $entityInstance->getPlainPassword());
    //             $entityInstance->setPassword($hashedPassword);
    //         } else {
    //             $hashedPassword = $this->hasher->hashPassword($entityInstance, "abc");
    //             $entityInstance->setPassword($hashedPassword);
    //         }
    //         //On ajoute par la même occasion les préférences par défaut de cet User
    //         // $entityInstance->setUtilisateur($this->serviceEntreprise->getUtilisateur());
    //         // $entityInstance->setEntreprise($this->serviceEntreprise->getEntreprise());
    //         // $entityInstance->setCreatedAt(new \DateTimeImmutable());
    //         // $entityInstance->setUpdatedAt(new \DateTimeImmutable());
    //         // $this->servicePreferences->creerPreference($entityInstance, $this->serviceEntreprise->getEntreprise());
    //     }
    //     if ($entityInstance instanceof Paiement) {
    //         $this->serviceFacture->updatePieceInfos($entityInstance);
    //         // il faut définir le IDAVENANT de l'avenant et le TYPEAVENANT, utiles pour la génération des prudentiels de l'autorité de régulation
    //         //dd($entityInstance);
    //         /** @var Facture */
    //         $facture = $entityInstance->getFacture();
    //         foreach ($facture->getElementFactures() as $elementFacture) {
    //             /** @var ElementFacture */
    //             $elementFacture->setIdavenant($this->serviceAvenant->generateIdAvenant($elementFacture->getTranche()->getPolice()));
    //             $elementFacture->setTypeavenant(PoliceCrudController::TAB_POLICE_TYPE_AVENANT[PoliceCrudController::AVENANT_TYPE_AUTRE_MODIFICATION]);
    //             $elementFacture->setUpdatedAt(new \DateTimeImmutable());
    //             //ici il faut actualiser la base de données
    //             $this->entityManager->persist($elementFacture);
    //             $this->entityManager->flush();
    //         }
    //         //dd($facture->getElementFactures());
    //     }


    //     /** @var Facture */
    //     if ($entityInstance instanceof Facture) {
    //         $this->editFacture($entityInstance);
    //     }

    //     $this->updateCollectionsPourPiste($entityInstance, true);

    //     $entityInstance->setUtilisateur($this->serviceEntreprise->getUtilisateur());
    //     $entityInstance->setEntreprise($this->serviceEntreprise->getEntreprise());
    //     $entityInstance->setCreatedAt(new \DateTimeImmutable());
    //     $entityInstance->setUpdatedAt(new \DateTimeImmutable());
    //     //$this->cleanElementFacture();
    //     // dd($entityInstance);
    // }

    // private function editFacture(Facture $entityInstance)
    // {
    //     if ($entityInstance->getDestination() == FactureCrudController::TAB_DESTINATION[FactureCrudController::DESTINATION_ASSUREUR]) {
    //         // Destination Assureur
    //         // Résultat du test: Tout fonctionne très bien!
    //         $modificateurFacture = new FactureAssureurModif(
    //             $this->serviceAvenant,
    //             $this->serviceDates,
    //             $this->serviceEntreprise,
    //             $this->entityManager
    //         );
    //         $entityInstance = $modificateurFacture->getUpdatedFacture($entityInstance);
    //         // dd("Voici la facture modifiée:", $entityInstance);
    //     } else if ($entityInstance->getDestination() == FactureCrudController::TAB_DESTINATION[FactureCrudController::DESTINATION_CLIENT]) {
    //         // Destination Client
    //         // Résultat du test: Tout fonctionne très bien!
    //         $modificateurFacture = new FactureClientModif(
    //             $this->serviceAvenant,
    //             $this->serviceDates,
    //             $this->serviceEntreprise,
    //             $this->entityManager
    //         );
    //         $entityInstance = $modificateurFacture->getUpdatedFacture($entityInstance);
    //     } else if ($entityInstance->getDestination() == FactureCrudController::TAB_DESTINATION[FactureCrudController::DESTINATION_DGI]) {
    //         // Destination DGI
    //         $modificateurFacture = new FactureDgiModif(
    //             $this->serviceAvenant,
    //             $this->serviceDates,
    //             $this->serviceEntreprise,
    //             $this->entityManager
    //         );
    //         $entityInstance = $modificateurFacture->getUpdatedFacture($entityInstance);
    //     } else if ($entityInstance->getDestination() == FactureCrudController::TAB_DESTINATION[FactureCrudController::DESTINATION_ARCA]) {
    //         // Destination ARCA
    //         $modificateurFacture = new FactureArcaModif(
    //             $this->serviceAvenant,
    //             $this->serviceDates,
    //             $this->serviceEntreprise,
    //             $this->entityManager
    //         );
    //         $entityInstance = $modificateurFacture->getUpdatedFacture($entityInstance);
    //     } else if ($entityInstance->getDestination() == FactureCrudController::TAB_DESTINATION[FactureCrudController::DESTINATION_PARTENAIRE]) {
    //         // Destination PARTENAIRE
    //         $modificateurFacture = new FacturePartenaireModif(
    //             $this->serviceAvenant,
    //             $this->serviceDates,
    //             $this->serviceEntreprise,
    //             $this->entityManager
    //         );
    //         $entityInstance = $modificateurFacture->getUpdatedFacture($entityInstance);
    //     }
    // }

    // private function updateCollectionsPourPiste($entityInstance, bool $isCreate)
    // {
    //     //dd($entityInstance);
    //     if ($entityInstance instanceof Piste) {
    //         /** @var Piste */
    //         $piste = $entityInstance;

    //         //Collection pour Contact
    //         foreach ($piste->getContacts() as $contact) {
    //             if ($isCreate || $contact->getCreatedAt() == null) {
    //                 $contact->setCreatedAt(new \DateTimeImmutable());
    //             }
    //             $contact->setUpdatedAt(new \DateTimeImmutable());
    //             $contact->setUtilisateur($this->serviceEntreprise->getUtilisateur());
    //             $contact->setEntreprise($this->serviceEntreprise->getEntreprise());
    //         }

    //         //Les documents de la document
    //         foreach ($piste->getDocuments() as $document) {
    //             if ($isCreate || $document->getCreatedAt() == null) {
    //                 $document->setCreatedAt(new \DateTimeImmutable());
    //             }
    //             $document->setPiste($piste);
    //             $document->setUpdatedAt(new \DateTimeImmutable());
    //             $document->setUtilisateur($this->serviceEntreprise->getUtilisateur());
    //             $document->setEntreprise($this->serviceEntreprise->getEntreprise());
    //         }

    //         //Collection pour Action
    //         foreach ($piste->getActionsCRMs() as $action) {
    //             if ($isCreate || $action->getCreatedAt() == null) {
    //                 $action->setCreatedAt(new \DateTimeImmutable());
    //                 $action->setClosed(ActionCRMCrudController::STATUS_MISSION[ActionCRMCrudController::MISSION_ENCOURS]);
    //             }
    //             $action->setUpdatedAt(new \DateTimeImmutable());
    //             $action->setUtilisateur($this->serviceEntreprise->getUtilisateur());
    //             $action->setEntreprise($this->serviceEntreprise->getEntreprise());

    //             //Les documents de l'action
    //             foreach ($action->getDocuments() as $document) {
    //                 if ($isCreate || $document->getCreatedAt() == null) {
    //                     $document->setCreatedAt(new \DateTimeImmutable());
    //                 }
    //                 $document->setActionCRM($action);
    //                 $document->setUpdatedAt(new \DateTimeImmutable());
    //                 $document->setUtilisateur($this->serviceEntreprise->getUtilisateur());
    //                 $document->setEntreprise($this->serviceEntreprise->getEntreprise());
    //             }

    //             //Les feedbacks de l'action
    //             foreach ($action->getFeedbacks() as $feedback) {
    //                 if ($isCreate || $feedback->getCreatedAt() == null) {
    //                     $feedback->setCreatedAt(new \DateTimeImmutable());
    //                 }
    //                 $feedback->setActionCRM($action);
    //                 $feedback->setUpdatedAt(new \DateTimeImmutable());
    //                 $feedback->setUtilisateur($this->serviceEntreprise->getUtilisateur());
    //                 $feedback->setEntreprise($this->serviceEntreprise->getEntreprise());
    //             }
    //         }

    //         //Collection pour Prospect
    //         if (count($piste->getProspect())) {
    //             //on ne prend que le tout premier prospect comme client
    //             /** @var Client */
    //             $newClient = $piste->getProspect()[0];
    //             $newClient->setCreatedAt(new \DateTimeImmutable());
    //             $newClient->setUpdatedAt(new \DateTimeImmutable());
    //             $newClient->setUtilisateur($this->serviceEntreprise->getUtilisateur());
    //             $newClient->setEntreprise($this->serviceEntreprise->getEntreprise());

    //             //ici il faut actualiser la base de données
    //             $this->entityManager->persist($newClient);
    //             $this->entityManager->flush();
    //             $piste->setClient($newClient);

    //             //On vide la liste des prospects
    //             $tabProspect = $piste->getProspect();
    //             foreach ($tabProspect as $pros) {
    //                 $piste->removeProspect($pros);
    //             }
    //         }

    //         //Collection pour Partenaire
    //         if (count($piste->getNewpartenaire())) {
    //             //on ne prend que le tout premier prospect comme client
    //             /** @var Partenaire */
    //             $newPartenaire = $piste->getNewpartenaire()[0];
    //             $newPartenaire->setCreatedAt(new \DateTimeImmutable());
    //             $newPartenaire->setUpdatedAt(new \DateTimeImmutable());
    //             $newPartenaire->setUtilisateur($this->serviceEntreprise->getUtilisateur());
    //             $newPartenaire->setEntreprise($this->serviceEntreprise->getEntreprise());

    //             //ici il faut actualiser la base de données
    //             $this->entityManager->persist($newPartenaire);
    //             $this->entityManager->flush();
    //             $piste->setPartenaire($newPartenaire);

    //             //On vide la liste des nouveaux partenaires
    //             $tabNewPartenaire = $piste->getNewpartenaire();
    //             foreach ($tabNewPartenaire as $newPart) {
    //                 $piste->removeNewpartenaire($newPart);
    //             }
    //         }

    //         //Collection pour Cotation
    //         foreach ($piste->getCotations() as $cotation) {
    //             if ($isCreate || $cotation->getCreatedAt() == null) {
    //                 $cotation->setCreatedAt(new \DateTimeImmutable());
    //                 $cotation->setValidated(false);
    //                 $cotation->setTauxretrocompartenaire(0);

    //                 /**
    //                  * On doit aussi ajouter les chargement habituels automatiquement
    //                  * En dehors de ce que le USER a déjà défini manuellement.
    //                  */
    //                 $this->definirChargementsParDefaut($cotation);
    //             }
    //             $cotation->setPiste($cotation->getPiste());
    //             $cotation->setUpdatedAt(new \DateTimeImmutable());
    //             $cotation->setUtilisateur($this->serviceEntreprise->getUtilisateur());
    //             $cotation->setEntreprise($this->serviceEntreprise->getEntreprise());


    //             //Les revenus de la cotation
    //             foreach ($cotation->getRevenus() as $revenu) {
    //                 if ($isCreate || $revenu->getCreatedAt() == null) {
    //                     $revenu->setCreatedAt(new \DateTimeImmutable());
    //                 }
    //                 $revenu->setUpdatedAt(new \DateTimeImmutable());
    //                 $revenu->setUtilisateur($this->serviceEntreprise->getUtilisateur());
    //                 $revenu->setEntreprise($this->serviceEntreprise->getEntreprise());
    //                 if ($revenu->getMontantFlat() == null) {
    //                     $revenu->setMontantFlat(0);
    //                 }
    //                 if ($revenu->getTaux() == null) {
    //                     $revenu->setTaux(0);
    //                 }
    //             }

    //             //Les chargements de la cotation
    //             foreach ($cotation->getChargements() as $chargement) {
    //                 if ($isCreate || $chargement->getCreatedAt() == null) {
    //                     $chargement->setCreatedAt(new \DateTimeImmutable());
    //                 }
    //                 $chargement->setUpdatedAt(new \DateTimeImmutable());
    //                 $chargement->setUtilisateur($this->serviceEntreprise->getUtilisateur());
    //                 $chargement->setEntreprise($this->serviceEntreprise->getEntreprise());
    //                 if ($chargement->getMontant() == null) {
    //                     $chargement->setMontant(0);
    //                 }
    //             }

    //             //Les documents de la cotation
    //             foreach ($cotation->getDocuments() as $document) {
    //                 if ($isCreate || $document->getCreatedAt() == null) {
    //                     $document->setCreatedAt(new \DateTimeImmutable());
    //                 }
    //                 $document->setCotation($cotation);
    //                 $document->setUpdatedAt(new \DateTimeImmutable());
    //                 $document->setUtilisateur($this->serviceEntreprise->getUtilisateur());
    //                 $document->setEntreprise($this->serviceEntreprise->getEntreprise());
    //             }

    //             //Les tranches de la cotation
    //             //Il faut aussi équilibrer les période des tranches pour être égale à la période de couverture globale
    //             foreach ($cotation->getTranches() as $tranche) {
    //                 if ($isCreate || $tranche->getCreatedAt() == null) {
    //                     $tranche->setCreatedAt(new \DateTimeImmutable());
    //                 }
    //                 $tranche->setUpdatedAt(new \DateTimeImmutable());
    //                 $tranche->setUtilisateur($this->serviceEntreprise->getUtilisateur());
    //                 $tranche->setEntreprise($this->serviceEntreprise->getEntreprise());
    //             }

    //             //ici - il faut revoir et perfectionner la fonction
    //             //On équilibre les tranches
    //             $this->equilibrerTranches($cotation);

    //             //On équilibre le revenu par défaut
    //             $this->equilibrerRevenu($cotation);

    //             if ($cotation->getNom() == null) {
    //                 $cotation->setNom("Offre #" . count($piste->getCotations()));
    //             }
    //         }

    //         //Enregistrement de la police
    //         if ($piste->getPolices()) {
    //             if (count($piste->getPolices()) != 0) {
    //                 /** @var Police */
    //                 $policeRetenue = $piste->getPolices()[0];
    //                 if ($isCreate || $policeRetenue->getCreatedAt() == null) {
    //                     $policeRetenue->setIdAvenant($this->serviceAvenant->generateIdAvenant($policeRetenue));
    //                     $policeRetenue->setCreatedAt(new \DateTimeImmutable());
    //                 }
    //                 $policeRetenue->setUpdatedAt(new \DateTimeImmutable());
    //                 $policeRetenue->setUtilisateur($this->serviceEntreprise->getUtilisateur());
    //                 $policeRetenue->setEntreprise($this->serviceEntreprise->getEntreprise());

    //                 //Les documents de la police
    //                 foreach ($policeRetenue->getDocuments() as $document) {
    //                     if ($isCreate || $document->getCreatedAt() == null) {
    //                         $document->setCreatedAt(new \DateTimeImmutable());
    //                     }
    //                     $document->setPolice($policeRetenue);
    //                     $document->setUpdatedAt(new \DateTimeImmutable());
    //                     $document->setUtilisateur($this->serviceEntreprise->getUtilisateur());
    //                     $document->setEntreprise($this->serviceEntreprise->getEntreprise());
    //                 }

    //                 //On vide le tableau des polices temporaire
    //                 $tabPolicesTempo = $piste->getPolices();
    //                 foreach ($tabPolicesTempo as $ptempo) {
    //                     if ($ptempo !== $policeRetenue) {
    //                         $piste->removePolice($ptempo);
    //                     }
    //                 }
    //                 //On marque la cotation retenue
    //                 $this->updateValidatedQuote($piste, $policeRetenue);
    //                 //On génère ou actualise la/les facture(s) payables par le client
    //                 // $this->serviceFacture->processFacturePrime($policeRetenue);
    //             }
    //         }

    //         $this->cleanCotations();
    //         $this->cleanPolices();
    //         $this->cleanDocuments();
    //         $this->cleanFeedbacks();
    //         $this->cleanActions();
    //         $this->cleanContacts();
    //         $this->updateEtapePiste($piste);
    //     }
    // }




    /**
     * Cette fonction charge les frais habituels à facturer au client
     * Càd, les élements constitutifs de la prime d'assurance totale.
     * Comme par exemple la prime net, la Tva, les frais accéssoires ou administratifs, les frais de surveillance, le fronting.
     * 
     * Après, le USER a toujours la possibilité de modifier à volonté ces chargement ou même les supprimer.
     *
     * @param Cotation|null $cotation
     * @return void
     */
    // private function definirChargementsParDefaut(?Cotation $cotation)
    // {
    //     /**
    //      * On identifie d'abord les chargements que le USER aura déjà saisie manuellement
    //      * Ces chargements doivent plus être concidérées (pas de doublon, pas de TVA deux fois par example)
    //      * 
    //      */
    //     $tab_int_types_chargement_a_ignorer = [];
    //     if (count($cotation->getChargements()) != 0) {
    //         /** @var Chargement */
    //         foreach ($cotation->getChargements() as $ancienChargement) {
    //             $tab_int_types_chargement_a_ignorer[] = $ancienChargement->getType();
    //         }
    //     }

    //     /**
    //      * On récupère le table globale des chargements 
    //      * selon que l'on est devant un client exonéré à la TVA ou pas
    //      */
    //     $tab_asoc_types_chargement = [];
    //     if ($cotation->getPiste()->getClient()->isExoneree() == true) {
    //         $tab_asoc_types_chargement = ChargementCrudController::TAB_TYPE_CHARGEMENT_EXONEREE;
    //     } else {
    //         $tab_asoc_types_chargement = ChargementCrudController::TAB_TYPE_CHARGEMENT_ORDINAIRE;
    //     }

    //     /**
    //      * Enfin, on crée automatiquement les chargement qui n'ont pas été saisis 
    //      * par le client avec comme montant "Zéro".
    //      * Pour ne pas influer la prime totale
    //      */
    //     foreach ($tab_asoc_types_chargement as $nom_type_chargement => $code_type_chargement) {
    //         if ($this->canIgnore($code_type_chargement, $tab_int_types_chargement_a_ignorer) == false) {
    //             $newChargement = new Chargement();
    //             $newChargement->setType($code_type_chargement);
    //             $newChargement->setDescription($nom_type_chargement);
    //             $newChargement->setMontant(0);
    //             $newChargement->setUtilisateur($cotation->getUtilisateur());
    //             $newChargement->setEntreprise($cotation->getEntreprise());
    //             $newChargement->setCreatedAt($this->serviceDates->aujourdhui());
    //             $newChargement->setUpdatedAt($this->serviceDates->aujourdhui());
    //             $newChargement->setCotation($cotation);
    //             $cotation->addChargement($newChargement);
    //         }
    //     }
    //     //dd($tab_asoc_types_chargement);
    // }

    // private function canIgnore(?int $type_chargement, $tab_int_types_chargement_a_ignorer): bool
    // {
    //     foreach ($tab_int_types_chargement_a_ignorer as $int_type_chargement_a_ignorer) {
    //         if ($int_type_chargement_a_ignorer == $type_chargement) {
    //             return true;
    //         }
    //     }
    //     return false;
    // }

    // private function updateEtapePiste(?Piste $piste)
    // {
    //     if (count($piste->getPolices()) != 0) {
    //         $piste->setEtape(PisteCrudController::TAB_ETAPES[PisteCrudController::ETAPE_CONCLUSION]);
    //     } else {
    //         if (count($piste->getCotations()) != 0) {
    //             $piste->setEtape(PisteCrudController::TAB_ETAPES[PisteCrudController::ETAPE_PRODUCTION_DES_DEVIS]);
    //         } else {
    //             if (count($piste->getActionsCRMs()) != 0 || count($piste->getContacts()) != 0) {
    //                 $piste->setEtape(PisteCrudController::TAB_ETAPES[PisteCrudController::ETAPE_COLLECTE_DE_DONNEES]);
    //             } else {
    //                 $piste->setEtape(PisteCrudController::TAB_ETAPES[PisteCrudController::ETAPE_CREATION]);
    //             }
    //         }
    //     }
    // }


    // private function updateValidatedQuote(?Piste $piste, ?Police $policeRetenue)
    // {
    //     //On définit sa cotation comme étant validée
    //     if ($policeRetenue->getCotation()) {
    //         foreach ($piste->getCotations() as $quote) {
    //             if ($quote->getId() == $policeRetenue->getCotation()->getId()) {
    //                 $quote->setValidated(true);
    //                 $quote->setPolice($policeRetenue);
    //                 $quote->setDateEffet($policeRetenue->getDateeffet());
    //                 $quote->setDateExpiration($policeRetenue->getDateexpiration());
    //                 $quote->setDateOperation($policeRetenue->getDateoperation());
    //                 $quote->setDateEmition($policeRetenue->getDateemission());

    //                 //on ajuste les periodes de chaque tranche
    //                 $this->serviceDates->ajusterPeriodesPourTranches_et_Revenus($policeRetenue);
    //             } else {
    //                 $quote->setValidated(false);
    //                 $quote->setPolice(null);
    //                 $quote->setDateEffet(null);
    //                 $quote->setDateExpiration(null);
    //                 $quote->setDateOperation(null);
    //                 $quote->setDateEmition(null);

    //                 //on ajuste les periodes de chaque tranche
    //                 $this->serviceDates->detruirePeriodesPourTranches_et_Revenus($quote);
    //             }
    //         }
    //         $quote->setPartenaire($piste->getPartenaire());
    //         $quote->setClient($piste->getClient());
    //         $quote->setProduit($piste->getProduit());
    //         $quote->setGestionnaire($piste->getGestionnaire());
    //         $quote->setAssistant($piste->getAssistant());
    //         $this->entityManager->persist($quote);
    //         $this->entityManager->flush();
    //     }
    // }

    /**
     * L'objectif de cette fonction est de vérifier si la sommes des périodes des tranches
     * est égale à la période globale définie dans la cotation.
     * S'il y a des différences la fonction va soit ajouter, soit retrancher.
     *
     * @param Cotation|null $cotation
     * @return void
     */
    // public function equilibrerTranches(?Cotation $cotation)
    // {
    //     //Durées
    //     $dureeMax = $cotation->getDureeCouverture();
    //     $dureeCourante = 0;
    //     $dureeDifference = 0;
    //     //Portion (en pourcentage)
    //     $portionMax = 100;
    //     $portionCourante = 0;
    //     $portionDifference = 0;
    //     $gamma = [];
    //     foreach ($cotation->getTranches() as $tranche) {
    //         $dureeCourante = $dureeCourante + $tranche->getDuree();
    //         $portionCourante = $portionCourante + ($tranche->getTaux() * 100);
    //     }
    //     //dd($dureeMax . " " . $portionMax);
    //     //dd($dureeCourante . " " . $portionCourante);
    //     $dureeDifference = $dureeMax - $dureeCourante;
    //     $portionDifference = $portionMax - $portionCourante;
    //     //dd("Durée: " . $dureeDifference . " & Portion: " . $portionDifference . "%");
    //     if ($dureeDifference != 0 || $portionDifference != 0) {
    //         $gamma = [
    //             "dureeDifference" => $dureeDifference,
    //             "portionDifference" => $portionDifference,
    //         ];
    //     }
    //     //dd($gamma); 
    //     //dd(count($gamma));
    //     if (count($gamma) != 0) {
    //         $newTranche = new Tranche();
    //         $newTranche->setNom("Tranche #" . (count($cotation->getTranches()) + 1));
    //         $newTranche->setDuree($gamma["dureeDifference"]);
    //         $newTranche->setTaux($gamma["portionDifference"] / 100);
    //         $newTranche->setCotation($cotation);
    //         $newTranche->setCreatedAt(new DateTimeImmutable());
    //         $newTranche->setUpdatedAt(new DateTimeImmutable());
    //         $newTranche->setUtilisateur($this->serviceEntreprise->getUtilisateur());
    //         $newTranche->setEntreprise($this->serviceEntreprise->getEntreprise());
    //         //dd($newTranche);
    //         $cotation->addTranch($newTranche);
    //     }
    // }

    /**
     * S'il y a chargement et que le USER ne défnisse pas de revenu,
     * Cette fonction charge le revenu par défaut selon le type de produit défini.
     *
     * @param Cotation|null $cotation
     * @return void
     */
    // public function equilibrerRevenu(?Cotation $cotation)
    // {
    //     //On équilibre les données par défaut s'il y a les chargement
    //     if (count($cotation->getChargements()) != 0) {
    //         if ($cotation->getRevenus()) {
    //             if (count($cotation->getRevenus()) == 0) {
    //                 //On doit ajouter automatiquement un revenu standard.
    //                 if ($cotation->getPiste()) {
    //                     if ($cotation->getPiste()->getProduit()) {
    //                         $stanRevenu = new Revenu();
    //                         $stanRevenu->setCotation($cotation);
    //                         $stanRevenu->setMontantFlat(0);
    //                         $stanRevenu->setTaux($cotation->getPiste()->getProduit()->getTauxarca());
    //                         $stanRevenu->setType(RevenuCrudController::TAB_TYPE[RevenuCrudController::TYPE_COM_LOCALE]);
    //                         $stanRevenu->setBase(RevenuCrudController::TAB_BASE[RevenuCrudController::BASE_PRIME_NETTE]);
    //                         $stanRevenu->setIspartclient(false);
    //                         if (count($cotation->getTranches()) != 0) {
    //                             $stanRevenu->setIsparttranche(true);
    //                         } else {
    //                             $stanRevenu->setIsparttranche(false);
    //                         }
    //                         if ($cotation->getPiste()->getPartenaire()) {
    //                             $stanRevenu->setPartageable(RevenuCrudController::TAB_PARTAGEABLE[RevenuCrudController::PARTAGEABLE_OUI]);
    //                         } else {
    //                             $stanRevenu->setPartageable(RevenuCrudController::TAB_PARTAGEABLE[RevenuCrudController::PARTAGEABLE_NON]);
    //                         }
    //                         $stanRevenu->setTaxable(RevenuCrudController::TAB_TAXABLE[RevenuCrudController::TAXABLE_OUI]);
    //                         $stanRevenu->setUpdatedAt(new \DateTimeImmutable());
    //                         $stanRevenu->setCreatedAt(new \DateTimeImmutable());
    //                         $stanRevenu->setUtilisateur($this->serviceEntreprise->getUtilisateur());
    //                         $stanRevenu->setEntreprise($this->serviceEntreprise->getEntreprise());

    //                         //ici il faut actualiser la base de données
    //                         $this->entityManager->persist($stanRevenu);
    //                         $this->entityManager->flush();
    //                     }
    //                 }
    //             }
    //         }
    //     }
    // }

    // public function updateNomMonnaie(Monnaie $entityInstance): Monnaie
    // {
    //     foreach (MonnaieCrudController::TAB_MONNAIES as $key => $value) {
    //         if ($value == $entityInstance->getCode()) {
    //             $entityInstance->setNom($key);
    //         }
    //     }
    //     //dd($entityInstance);
    //     return $entityInstance;
    // }

    // public function setUpdatedAt(BeforeEntityUpdatedEvent $event)
    // {
    //     //dd($event);
    //     $entityInstance = $event->getEntityInstance();
    //     //dd($entityInstance);

    //     if ($entityInstance instanceof Monnaie) {
    //         $entityInstance = $this->updateNomMonnaie($entityInstance);
    //     }

    //     if ($entityInstance instanceof Utilisateur) {
    //         $newpassword = $entityInstance->getPlainPassword();
    //         //Si le mot de passe n'est pas vide = C'est que l'on désire le modifier
    //         if ($newpassword !== "") {
    //             //dd($newpassword);
    //             $hashedPassword = $this->hasher->hashPassword($entityInstance, $entityInstance->getPlainPassword());
    //             $entityInstance->setPassword($hashedPassword);
    //         }
    //         //S'il s'agit de l'utilisateur actuellement connecté, alors il faut lui déconnecter
    //         //dd($this->security->getUser());
    //         /* if($this->serviceEntreprise->getUtilisateur() == $entityInstance){
    //             $response = $this->security->logout(false);
    //         } */
    //     }
    //     if ($entityInstance instanceof Facture) {
    //         $this->serviceFacture->cleanElementFacture($entityInstance);
    //         $this->editFacture($entityInstance);
    //     }

    //     // dd("Ajout identifié: ", $entityInstance);


    //     $this->updateCollectionsPourPiste($entityInstance, false);

    //     $entityInstance->setUpdatedAt(new \DateTimeImmutable());
    //     //ici il faut aussi actualiser les instances de Police et Facture
    //     //dd($entityInstance);
    // }


    // private function cleanChargements()
    // {
    //     $chargements = $this->entityManager->getRepository(Chargement::class)->findBy(
    //         ['entreprise' => $this->serviceEntreprise->getEntreprise()]
    //     );
    //     /** @var Chargement */
    //     foreach ($chargements as $chargement) {
    //         if ($chargement->getCotation() == null) {
    //             $this->entityManager->remove($chargement);
    //             $this->entityManager->flush();
    //         }
    //     }
    // }

    // private function cleanFeedbacks()
    // {
    //     $feedbacks = $this->entityManager->getRepository(FeedbackCRM::class)->findBy(
    //         ['entreprise' => $this->serviceEntreprise->getEntreprise()]
    //     );
    //     /** @var FeedbackCRM */
    //     foreach ($feedbacks as $feedback) {
    //         if ($feedback->getActionCRM() == null) {
    //             $this->entityManager->remove($feedback);
    //             $this->entityManager->flush();
    //         }
    //     }
    // }

    // private function cleanActions()
    // {
    //     $actions = $this->entityManager->getRepository(ActionCRM::class)->findBy(
    //         ['entreprise' => $this->serviceEntreprise->getEntreprise()]
    //     );
    //     /** @var ActionCRM */
    //     foreach ($actions as $action) {
    //         if ($action->getPiste() == null) {
    //             $this->entityManager->remove($action);
    //             $this->entityManager->flush();
    //         }
    //     }
    // }

    // private function cleanContacts()
    // {
    //     $contacts = $this->entityManager->getRepository(Contact::class)->findBy(
    //         ['entreprise' => $this->serviceEntreprise->getEntreprise()]
    //     );
    //     /** @var ActionCRM */
    //     foreach ($contacts as $contact) {
    //         if ($contact->getPiste() == null) {
    //             $this->entityManager->remove($contact);
    //             $this->entityManager->flush();
    //         }
    //     }
    // }

    // private function cleanRevenus()
    // {
    //     $revenus = $this->entityManager->getRepository(Revenu::class)->findBy(
    //         ['entreprise' => $this->serviceEntreprise->getEntreprise()]
    //     );
    //     /** @var Revenu */
    //     foreach ($revenus as $revenu) {
    //         if ($revenu->getCotation() == null) {
    //             $this->entityManager->remove($revenu);
    //             $this->entityManager->flush();
    //         }
    //     }
    // }

    // private function cleanDocuments()
    // {
    //     $documents = $this->entityManager->getRepository(DocPiece::class)->findBy(
    //         ['entreprise' => $this->serviceEntreprise->getEntreprise()]
    //     );
    //     /** @var DocPiece */
    //     foreach ($documents as $doc) {
    //         if ($doc->getCotation() == null && $doc->getPolice() == null && $doc->getPiste() == null && $doc->getActionCRM() == null) {
    //             //On detruit enfin le document
    //             $this->entityManager->remove($doc);
    //             $this->entityManager->flush();
    //         }
    //     }
    // }

    // private function cleanCotations()
    // {
    //     $cotations = $this->entityManager->getRepository(Cotation::class)->findBy(
    //         ['entreprise' => $this->serviceEntreprise->getEntreprise()]
    //     );
    //     /** @var Cotation */
    //     foreach ($cotations as $cotation) {
    //         if ($cotation->getPiste() == null) {
    //             //On vide les chargement
    //             /** @var Chargement */
    //             foreach ($cotation->getChargements() as $chargement) {
    //                 $this->entityManager->remove($chargement);
    //                 $this->entityManager->flush();
    //             }
    //             //On vide les revenus
    //             /** @var Revenu */
    //             foreach ($cotation->getRevenus() as $revenu) {
    //                 $this->entityManager->remove($revenu);
    //                 $this->entityManager->flush();
    //             }
    //             //On vide les tranches
    //             /** @var Tranche */
    //             foreach ($cotation->getTranches() as $tranche) {
    //                 $this->entityManager->remove($tranche);
    //                 $this->entityManager->flush();
    //             }

    //             //On doit aussi supprimer les éventuels documents / pièces justificatives
    //             /** @var DocPiece */
    //             foreach ($cotation->getDocuments() as $document) {
    //                 $this->entityManager->remove($document);
    //                 $this->entityManager->flush();
    //             }

    //             //On detruit enfin la cotation
    //             $this->entityManager->remove($cotation);
    //             $this->entityManager->flush();
    //         } else {
    //             //Toute cotation qui n'est pas liée à une police doit être définie "Validated = FALSE"
    //             $tabPolicesDeLaPiste = $cotation->getPiste()->getPolices();
    //             $isValidated = false;
    //             foreach ($tabPolicesDeLaPiste as $pol) {
    //                 if ($pol->getCotation() === $cotation) {
    //                     $isValidated = true;
    //                 }
    //             }
    //             $cotation->setValidated($isValidated);
    //             $this->entityManager->persist($cotation);
    //             $this->entityManager->flush();
    //         }
    //     }
    //     $this->cleanChargements();
    //     $this->cleanRevenus();
    //     //dd($cotations);
    // }

    // private function cleanPolices()
    // {
    //     $polices = $this->entityManager->getRepository(Police::class)->findBy(
    //         ['entreprise' => $this->serviceEntreprise->getEntreprise()]
    //     );
    //     /** @var Police */
    //     foreach ($polices as $pol) {
    //         if ($pol->getPiste() == null) {
    //             //On detruit enfin la police
    //             $this->entityManager->remove($pol);
    //             $this->entityManager->flush();
    //         }
    //     }
    //     //dd($polices);
    // }
}
