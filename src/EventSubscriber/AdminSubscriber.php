<?php

namespace App\EventSubscriber;

use App\Entity\Piste;
use App\Entity\Police;
use DateTimeImmutable;
use App\Entity\Facture;
use App\Entity\Monnaie;
use App\Entity\Cotation;
use App\Entity\Paiement;
use App\Entity\ActionCRM;
use App\Entity\Entreprise;
use App\Entity\Preference;
use App\Entity\FeedbackCRM;
use App\Entity\Utilisateur;
use App\Service\ServiceDates;
use App\Entity\ElementFacture;
use App\Service\ServiceAvenant;
use App\Service\ServiceFacture;
use App\Service\ServiceEntreprise;
use App\Service\ServiceCalculateur;
use App\Service\ServicePreferences;
use App\Service\ServiceSuppression;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Bundle\SecurityBundle\Security;
use App\Controller\Admin\PoliceCrudController;
use App\Controller\Admin\MonnaieCrudController;
use App\Controller\Admin\ActionCRMCrudController;
use App\Entity\Chargement;
use App\Entity\Client;
use App\Entity\Contact;
use App\Entity\Partenaire;
use App\Entity\Revenu;
use App\Entity\Tranche;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityBuiltEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeCrudActionEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminSubscriber implements EventSubscriberInterface
{

    public function __construct(
        private ServiceFacture $serviceFacture,
        private EntityManagerInterface $entityManager,
        private ServiceDates $serviceDates,
        private UserPasswordHasherInterface $hasher,
        private ServiceEntreprise $serviceEntreprise,
        private ServiceCalculateur $serviceCalculateur,
        private ServicePreferences $servicePreferences,
        private ServiceSuppression $serviceSuppression,
        private ServiceAvenant $serviceAvenant
    ) {
    }

    public static function getSubscribedEvents()
    {
        return [
            //AfterEntityBuiltEvent::class => ['updateCalculableFiledsOnPoliceEntity'],
            BeforeEntityPersistedEvent::class => ['setCreatedAt'],
            BeforeEntityUpdatedEvent::class => ['setUpdatedAt']
        ];
    }

    /* public function updateCalculableFiledsOnPoliceEntity(AfterEntityBuiltEvent $event)
    {
        //dd($event);
        $entityInstance = $event->getEntity()->getInstance();
        //dd($entityInstance->getReference());
        if($entityInstance instanceof Police){
            $this->serviceCalculateur->updatePoliceCalculableFileds($entityInstance);
        }
    } */

    public function setCreatedAt(BeforeEntityPersistedEvent $event)
    {
        $entityInstance = $event->getEntityInstance();
        //dd($entityInstance);
        if ($entityInstance instanceof Monnaie) {
            $entityInstance = $this->updateNomMonnaie($entityInstance);
        }

        if ($entityInstance instanceof Utilisateur) {
            $newpassword = $entityInstance->getPlainPassword();
            if ($newpassword !== "") {
                //dd($newpassword);
                $hashedPassword = $this->hasher->hashPassword($entityInstance, $entityInstance->getPlainPassword());
                $entityInstance->setPassword($hashedPassword);
            } else {
                $hashedPassword = $this->hasher->hashPassword($entityInstance, "abc");
                $entityInstance->setPassword($hashedPassword);
            }
            //On ajoute par la même occasion les préférences par défaut de cet User
            $entityInstance->setUtilisateur($this->serviceEntreprise->getUtilisateur());
            $entityInstance->setEntreprise($this->serviceEntreprise->getEntreprise());
            $entityInstance->setCreatedAt(new \DateTimeImmutable());
            $entityInstance->setUpdatedAt(new \DateTimeImmutable());
            $this->servicePreferences->creerPreference($entityInstance, $this->serviceEntreprise->getEntreprise());
        }
        if ($entityInstance instanceof Paiement) {
            $this->serviceFacture->updatePieceInfos($entityInstance);
            // il faut définir le IDAVENANT de l'avenant et le TYPEAVENANT, utiles pour la génération des prudentiels de l'autorité de régulation
            //dd($entityInstance);
            /** @var Facture */
            $facture = $entityInstance->getFacture();
            foreach ($facture->getElementFactures() as $elementFacture) {
                /** @var ElementFacture */
                $elementFacture->setIdavenant($this->serviceAvenant->generateIdAvenant($elementFacture->getPolice()));
                $elementFacture->setTypeavenant(PoliceCrudController::TAB_POLICE_TYPE_AVENANT[PoliceCrudController::AVENANT_TYPE_AUTRE_MODIFICATION]);
                $elementFacture->setUpdatedAt(new \DateTimeImmutable());
                //ici il faut actualiser la base de données
                $this->entityManager->persist($elementFacture);
                $this->entityManager->flush();
            }
            //dd($facture->getElementFactures());
        }

        $this->updateCollectionsPourPiste($entityInstance, true);

        $entityInstance->setUtilisateur($this->serviceEntreprise->getUtilisateur());
        $entityInstance->setEntreprise($this->serviceEntreprise->getEntreprise());
        $entityInstance->setCreatedAt(new \DateTimeImmutable());
        $entityInstance->setUpdatedAt(new \DateTimeImmutable());
        //$this->cleanElementFacture();
    }

    private function updateCollectionsPourPiste($entityInstance, bool $isCreate)
    {
        //dd($entityInstance);
        if ($entityInstance instanceof Piste) {
            /** @var Piste */
            $piste = $entityInstance;
            //Collection pour Contact
            foreach ($piste->getContacts() as $contact) {
                if ($isCreate || $contact->getCreatedAt() == null) {
                    $contact->setCreatedAt(new \DateTimeImmutable());
                }
                $contact->setUpdatedAt(new \DateTimeImmutable());
                $contact->setUtilisateur($this->serviceEntreprise->getUtilisateur());
                $contact->setEntreprise($this->serviceEntreprise->getEntreprise());
            }

            //Collection pour Action
            foreach ($piste->getActionsCRMs() as $action) {
                if ($isCreate || $action->getCreatedAt() == null) {
                    $action->setCreatedAt(new \DateTimeImmutable());
                    $action->setClos(ActionCRMCrudController::STATUS_MISSION[ActionCRMCrudController::MISSION_ENCOURS]);
                }
                $action->setUpdatedAt(new \DateTimeImmutable());
                $action->setUtilisateur($this->serviceEntreprise->getUtilisateur());
                $action->setEntreprise($this->serviceEntreprise->getEntreprise());
            }

            //Collection pour Prospect
            if (count($piste->getProspect())) {
                //on ne prend que le tout premier prospect comme client
                /** @var Client */
                $newClient = $piste->getProspect()[0];
                $newClient->setCreatedAt(new \DateTimeImmutable());
                $newClient->setUpdatedAt(new \DateTimeImmutable());
                $newClient->setUtilisateur($this->serviceEntreprise->getUtilisateur());
                $newClient->setEntreprise($this->serviceEntreprise->getEntreprise());

                //ici il faut actualiser la base de données
                $this->entityManager->persist($newClient);
                $this->entityManager->flush();
                $piste->setClient($newClient);

                //On vide la liste des prospects
                $tabProspect = $piste->getProspect();
                foreach ($tabProspect as $pros) {
                    $piste->removeProspect($pros);
                }
            }

            //Collection pour Partenaire
            if (count($piste->getNewpartenaire())) {
                //on ne prend que le tout premier prospect comme client
                /** @var Partenaire */
                $newPartenaire = $piste->getNewpartenaire()[0];
                $newPartenaire->setCreatedAt(new \DateTimeImmutable());
                $newPartenaire->setUpdatedAt(new \DateTimeImmutable());
                $newPartenaire->setUtilisateur($this->serviceEntreprise->getUtilisateur());
                $newPartenaire->setEntreprise($this->serviceEntreprise->getEntreprise());

                //ici il faut actualiser la base de données
                $this->entityManager->persist($newPartenaire);
                $this->entityManager->flush();
                $piste->setPartenaire($newPartenaire);

                //On vide la liste des nouveaux partenaires
                $tabNewPartenaire = $piste->getNewpartenaire();
                foreach ($tabNewPartenaire as $newPart) {
                    $piste->removeNewpartenaire($newPart);
                }
            }

            //Collection pour Cotation
            foreach ($piste->getCotations() as $cotation) {
                if ($isCreate || $cotation->getCreatedAt() == null) {
                    $cotation->setCreatedAt(new \DateTimeImmutable());
                }
                $cotation->setPiste($cotation->getPiste());
                $cotation->setUpdatedAt(new \DateTimeImmutable());
                $cotation->setUtilisateur($this->serviceEntreprise->getUtilisateur());
                $cotation->setEntreprise($this->serviceEntreprise->getEntreprise());

                //Les revenus de la cotation
                foreach ($cotation->getRevenus() as $revenu) {
                    if ($isCreate || $revenu->getCreatedAt() == null) {
                        $revenu->setCreatedAt(new \DateTimeImmutable());
                    }
                    $revenu->setUpdatedAt(new \DateTimeImmutable());
                    $revenu->setUtilisateur($this->serviceEntreprise->getUtilisateur());
                    $revenu->setEntreprise($this->serviceEntreprise->getEntreprise());
                    if ($revenu->getMontant() == null) {
                        $revenu->setMontant(0);
                    }
                    if ($revenu->getTaux() == null) {
                        $revenu->setTaux(0);
                    }
                }

                //Les chargements de la cotation
                foreach ($cotation->getChargements() as $chargement) {
                    if ($isCreate || $chargement->getCreatedAt() == null) {
                        $chargement->setCreatedAt(new \DateTimeImmutable());
                    }
                    $chargement->setUpdatedAt(new \DateTimeImmutable());
                    $chargement->setUtilisateur($this->serviceEntreprise->getUtilisateur());
                    $chargement->setEntreprise($this->serviceEntreprise->getEntreprise());
                    if ($chargement->getMontant() == null) {
                        $chargement->setMontant(0);
                    }
                }

                //Les tranches de la cotation
                //Il faut aussi équilibrer les période des tranches pour être égale à la période de couverture globale
                foreach ($cotation->getTranches() as $tranche) {
                    if ($isCreate || $tranche->getCreatedAt() == null) {
                        $tranche->setCreatedAt(new \DateTimeImmutable());
                    }
                    $tranche->setUpdatedAt(new \DateTimeImmutable());
                    $tranche->setUtilisateur($this->serviceEntreprise->getUtilisateur());
                    $tranche->setEntreprise($this->serviceEntreprise->getEntreprise());
                }
                //On équilibre les tranches
                $this->equilibrerTranches($cotation);
            }
            //dd($entityInstance);
            $this->cleanCotations();
        }
    }

    /**
     * L'objectif de cette fonction est de vérifier si la sommes des périodes des tranches
     * est égale à la période globale définie dans la cotation.
     * S'il y a des différences la fonction va soit ajouter, soit retrancher.
     *
     * @param Cotation|null $cotation
     * @return void
     */
    public function equilibrerTranches(?Cotation $cotation)
    {
        $dureeGlobale = $cotation->getDureeCouverture(); //En mois
        $dureeTrancheTotale = 0;
        $tauxTranchesTotale = 0;
        if ($cotation->getTranches()) {
            foreach ($cotation->getTranches() as $tranche) {
                $dureeTrancheTotale = $dureeTrancheTotale + $tranche->getDuree();
                $tauxTranchesTotale = $tauxTranchesTotale + $tranche->getTaux();
            }
        }
        $diffDuree = $dureeGlobale - $dureeTrancheTotale;
        $diffTaux = 1 - $tauxTranchesTotale;
        //dd($diffTaux);
        if ($diffDuree != 0 && $diffTaux != 0) {
            $newTranche = new Tranche();
            $newTranche->setNom("Tranche #" . (count($cotation->getTranches()) + 1));
            $newTranche->setDuree($diffDuree);
            $newTranche->setTaux($diffTaux);
            $newTranche->setCotation($cotation);
            $newTranche->setCreatedAt(new DateTimeImmutable());
            $newTranche->setUpdatedAt(new DateTimeImmutable());
            $newTranche->setUtilisateur($this->serviceEntreprise->getUtilisateur());
            $newTranche->setEntreprise($this->serviceEntreprise->getEntreprise());
            $cotation->addTranch($newTranche);
        }
    }

    public function updateNomMonnaie(Monnaie $entityInstance): Monnaie
    {
        foreach (MonnaieCrudController::TAB_MONNAIES as $key => $value) {
            if ($value == $entityInstance->getCode()) {
                $entityInstance->setNom($key);
            }
        }
        //dd($entityInstance);
        return $entityInstance;
    }

    public function setUpdatedAt(BeforeEntityUpdatedEvent $event)
    {
        //dd($event);
        $entityInstance = $event->getEntityInstance();
        //dd($entityInstance);

        if ($entityInstance instanceof Monnaie) {
            $entityInstance = $this->updateNomMonnaie($entityInstance);
        }

        if ($entityInstance instanceof Utilisateur) {
            $newpassword = $entityInstance->getPlainPassword();
            //Si le mot de passe n'est pas vide = C'est que l'on désire le modifier
            if ($newpassword !== "") {
                //dd($newpassword);
                $hashedPassword = $this->hasher->hashPassword($entityInstance, $entityInstance->getPlainPassword());
                $entityInstance->setPassword($hashedPassword);
            }
            //S'il s'agit de l'utilisateur actuellement connecté, alors il faut lui déconnecter
            //dd($this->security->getUser());
            /* if($this->serviceEntreprise->getUtilisateur() == $entityInstance){
                $response = $this->security->logout(false);
            } */
        }
        if ($entityInstance instanceof Facture) {
            $this->serviceFacture->cleanElementFacture($entityInstance);
        }

        $this->updateCollectionsPourPiste($entityInstance, false);

        $entityInstance->setUpdatedAt(new \DateTimeImmutable());
        //ici il faut aussi actualiser les instances de Police et Facture
        //dd($entityInstance);
    }


    private function cleanChargements()
    {
        $chargements = $this->entityManager->getRepository(Chargement::class)->findBy(
            ['entreprise' => $this->serviceEntreprise->getEntreprise()]
        );
        /** @var Chargement */
        foreach ($chargements as $chargement) {
            if ($chargement->getCotation() == null) {
                $this->entityManager->remove($chargement);
                $this->entityManager->flush();
            }
        }
    }

    private function cleanRevenus()
    {
        $revenus = $this->entityManager->getRepository(Revenu::class)->findBy(
            ['entreprise' => $this->serviceEntreprise->getEntreprise()]
        );
        /** @var Revenu */
        foreach ($revenus as $revenu) {
            if ($revenu->getCotation() == null) {
                $this->entityManager->remove($revenu);
                $this->entityManager->flush();
            }
        }
    }

    private function cleanCotations()
    {
        $cotations = $this->entityManager->getRepository(Cotation::class)->findBy(
            ['entreprise' => $this->serviceEntreprise->getEntreprise()]
        );
        /** @var Cotation */
        foreach ($cotations as $cotation) {
            if ($cotation->getPiste() == null) {
                //On vide les chargement
                /** @var Chargement */
                foreach ($cotation->getChargements() as $chargement) {
                    $this->entityManager->remove($chargement);
                    $this->entityManager->flush();
                }
                //On vide les revenus
                /** @var Revenu */
                foreach ($cotation->getRevenus() as $revenu) {
                    $this->entityManager->remove($revenu);
                    $this->entityManager->flush();
                }
                //On vide les tranches
                /** @var Tranche */
                foreach ($cotation->getTranches() as $tranche) {
                    $this->entityManager->remove($tranche);
                    $this->entityManager->flush();
                }
                //On detruit enfin la cotation
                $this->entityManager->remove($cotation);
                $this->entityManager->flush();
            }
        }
        $this->cleanChargements();
        $this->cleanRevenus();
        //dd($cotations);
    }
}
