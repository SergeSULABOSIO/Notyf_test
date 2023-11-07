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
use App\Entity\Client;
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
            //dd($piste);
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
}
