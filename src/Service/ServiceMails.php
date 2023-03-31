<?php

namespace App\Service;

use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;

class ServiceMails
{
    public function __construct(private MailerInterface $mailer)
    {
        
    }
    
    public function sendEmail(
        $from = 'marketing@js-brokers.com',
        $to = 'sulabosiog@gmail.com',
        $subject = 'JS Brokers - Message Test',
        $content = [
            'content' => 'Salut! Ceci est un petit test.'
        ]
    ): void
    {
        $email = (new Email())
            ->from($from)
            ->to($to)
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject($subject)
            ->text($content['content'])
            ->html('<p>'. $content['content'] .'</p>');

        $this->mailer->send($email);

        //dd($email);
    }
}