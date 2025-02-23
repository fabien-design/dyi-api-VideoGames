<?php 

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailerService {

    public function __construct(private MailerInterface $mailer) {}

    public function sendEmail(string $to, string $subject, array $videoGames): void {
        $email = (new TemplatedEmail())
        ->from('no-reply@videogames.com')
        ->to($to)
        ->subject($subject)
        ->htmlTemplate('emails/weekly_videogames.html.twig')
        ->locale('fr')
        ->context([
            'games' => $videoGames
        ]); 

        $this->mailer->send($email);
    }
}
