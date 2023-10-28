<?php

namespace App\EventSubscriber;

use App\Entity\Piste;
use App\Entity\Police;
use DateTimeImmutable;
use App\Entity\Facture;
use App\Entity\Monnaie;
use App\Entity\Cotation;
use App\Entity\ActionCRM;
use App\Entity\Entreprise;
use App\Entity\Preference;
use App\Entity\FeedbackCRM;
use App\Entity\Utilisateur;
use App\Service\ServiceDates;
use App\Entity\ElementFacture;
use App\Service\ServiceEntreprise;
use App\Service\ServiceCalculateur;
use App\Service\ServicePreferences;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use App\Controller\Admin\MonnaieCrudController;
use App\Entity\Paiement;
use App\Service\ServiceFacture;
use App\Service\ServiceSuppression;
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
        private ServiceSuppression $serviceSuppression
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
        if($entityInstance instanceof Paiement){
            $this->serviceFacture->updatePieceInfos($entityInstance);
        }

        $entityInstance->setUtilisateur($this->serviceEntreprise->getUtilisateur());
        $entityInstance->setEntreprise($this->serviceEntreprise->getEntreprise());
        $entityInstance->setCreatedAt(new \DateTimeImmutable());
        $entityInstance->setUpdatedAt(new \DateTimeImmutable());
        //$this->cleanElementFacture();
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
        $entityInstance->setUpdatedAt(new \DateTimeImmutable());
        //ici il faut aussi actualiser les instances de Police et Facture
        //dd($entityInstance);
    }

    
}
