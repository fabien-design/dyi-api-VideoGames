<?php

namespace App\Command;

use App\Repository\VideoGameRepository;
use App\Repository\UserRepository;
use App\Service\MailerService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Scheduler\Attribute\AsCronTask;

#[AsCommand(
    name: 'app:send-email',
    description: 'Send notification emails to users subscribed to newsletter',
)]
#[AsCronTask('*/2 * * * *')]
class SendEmailCommand extends Command
{
    public function __construct(
        private VideoGameRepository $videoGameRepository,
        private UserRepository $userRepository,
        private MailerService $mailerService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Envoi des emails');

        $recentGames = $this->videoGameRepository->findNextWeekGameRelease();
        
        $subscribedUsers = $this->userRepository->findByNewsletterSubscription();
        $io->text('Envoi des emails en cours...');
        foreach ($subscribedUsers as $user) {
            $this->mailerService->sendEmail(
                $user->getEmail(),
                'Les nouveaux jeux de la semaine',
                $recentGames
            );
        }

        $io->success('Emails envoyés avec succès à ' . count($subscribedUsers) . ' utilisateurs.');

        return Command::SUCCESS;
    }
}