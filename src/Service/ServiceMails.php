<?php

namespace App\Service;

use App\Entity\Utilisateur;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class ServiceMails
{
    public function __construct(private MailerInterface $mailer)
    {
    }



    public function sendEmailBienvenu(Utilisateur $user): void {
        $from = 'marketing@js-brokers.com';
        $to = $user->getEmail();
        $subject = 'JS Brokers - Bien venue ' . $user->getNom()."!";
        $texte = "
            Salut " . $user->getNom() . ".
            <p>
                Votre compte vient d'être créer avec succès et vos identifiants de connexion sont les suivants:<br>
                <ul>
                    <li>
                        Mot de passe: <strong>". $user->getPlainPassword() . "</strong>.
                    </li>
                    <li>
                        Email: <strong>" . $user->getEmail() ."</strong>.
                    </li>
                </ul>
            </p>
            <p>
                Vous pouvez maintenant commencer à travailler sur JS Brokers!
            </p>
            <p>
                Merci de votre confiance.
            </p>
            L'équipe JS Broker.
        ";

        $email = (new Email())
            ->from($from)
            ->to($to)
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject($subject)
            //->text($content['content'])
            ->html($texte);

        $this->mailer->send($email);

        //dd($email);
    }
}
