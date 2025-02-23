<?php

namespace App\Scheduler;

use App\Repository\VideoGameRepository;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;
use Symfony\Contracts\Cache\CacheInterface;

#[AsSchedule('WeeklyMail')]
final class MailSchedule implements ScheduleProviderInterface
{
    public function __construct(
        private CacheInterface $cache,
        private VideoGameRepository $videoGameRepository
    ) {
    }

    public function getSchedule(): Schedule
    {
        $games = $this->videoGameRepository->findNextWeekGameRelease();
        
        return (new Schedule())
            ->add(
                RecurringMessage::cron('*/2 * * * *', new SendEmailMessage($games)),
                RecurringMessage::every('10 seconds', new SendEmailMessage($games))
            )
            ->stateful($this->cache)
        ;
    }
}