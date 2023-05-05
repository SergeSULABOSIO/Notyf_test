<?php
namespace App\EventSubscriber;

use App\Entity\ActionCRM;
use App\Entity\Cotation;
use App\Entity\Entreprise;
use App\Entity\FeedbackCRM;
use App\Entity\Piste;
use App\Entity\Utilisateur;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminSubscriber implements EventSubscriberInterface
{

    public function __construct(private UserPasswordHasherInterface $hasher, private Security $security)
    {
    }

    public static function getSubscribedEvents()
    {
        return [
            BeforeEntityPersistedEvent::class => ['setCreatedAt'],
            BeforeEntityUpdatedEvent::class => ['setUpdatedAt']
        ];
    }

    public function setCreatedAt(BeforeEntityPersistedEvent $event)
    {
        //dd($event);
        $entityInstance = $event->getEntityInstance();
        if($entityInstance instanceof Utilisateur){
            $newpassword = $entityInstance->getPlainPassword();
            if($newpassword !== ""){
                //dd($newpassword);
                $hashedPassword = $this->hasher->hashPassword($entityInstance, $entityInstance->getPlainPassword());
                $entityInstance->setPassword($hashedPassword);
            }else{
                $hashedPassword = $this->hasher->hashPassword($entityInstance, "abc");
                $entityInstance->setPassword($hashedPassword);
            }
        }
        //Si Entreprise
        if($entityInstance instanceof Entreprise){
            $entityInstance->addUtilisateur($this->security->getUser()); //$this->security->getUser()
        }

        //Si Piste
        if($entityInstance instanceof Piste){
            $entityInstance->setUtilisateur($this->security->getUser()); //$this->security->getUser()
        }
        //Si Etape
        if($entityInstance instanceof Piste){
            $entityInstance->setUtilisateur($this->security->getUser()); //$this->security->getUser()
        }
        //Si Cotation
        if($entityInstance instanceof Cotation){
            $entityInstance->setUtilisateur($this->security->getUser()); //$this->security->getUser()
        }
        //Si Feedback
        if($entityInstance instanceof FeedbackCRM){
            $entityInstance->setUtilisateur($this->security->getUser()); //$this->security->getUser()
        }
        //Si Mission/Action
        if($entityInstance instanceof ActionCRM){
            $entityInstance->setUtilisateur($this->security->getUser()); //$this->security->getUser()
        }

        $entityInstance->setCreatedAt(new \DateTimeImmutable());
        $entityInstance->setUpdatedAt(new \DateTimeImmutable());
    }

    public function setUpdatedAt(BeforeEntityUpdatedEvent $event)
    {
        //dd($event);
        $entityInstance = $event->getEntityInstance();
        if($entityInstance instanceof Utilisateur){
            $newpassword = $entityInstance->getPlainPassword();
            //Si le mot de passe n'est pas vide = C'est que l'on désire le modifier
            if($newpassword !== ""){
                //dd($newpassword);
                $hashedPassword = $this->hasher->hashPassword($entityInstance, $entityInstance->getPlainPassword());
                $entityInstance->setPassword($hashedPassword);
            }
            //S'il s'agit de l'utilisateur actuellement connecté, alors il faut lui déconnecter
            //dd($this->security->getUser());
            if($this->security->getUser()->getId() == $entityInstance->getId()){
                $response = $this->security->logout(false);
            }
        }
        $entityInstance->setUpdatedAt(new \DateTimeImmutable());
    }
}