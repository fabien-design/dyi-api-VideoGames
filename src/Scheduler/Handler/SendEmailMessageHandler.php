<?php

namespace App\Scheduler\Handler;

use App\Repository\UserRepository;
use App\Repository\VideoGameRepository;
use App\Scheduler\SendEmailMessage;
use App\Service\MailerService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SendEmailMessageHandler
{
    public function __construct(
        private MailerService $mailerService,
        private UserRepository $userRepository,
        private VideoGameRepository $videoGameRepository
    ) {}

    public function __invoke(SendEmailMessage $message)
    {
        $recentGames = $this->videoGameRepository->findGamesReleasedLastWeek();
        $subscribedUsers = $this->userRepository->findByNewsletterSubscription();

        foreach ($subscribedUsers as $user) {
            $this->mailerService->sendEmail(
                $user->getEmail(),
                'Les nouveaux jeux de la semaine',
                $recentGames
            );
        }
    }
}