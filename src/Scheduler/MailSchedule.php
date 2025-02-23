<?php

namespace App\Scheduler;

use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;
use Symfony\Component\Console\Messenger\RunCommandMessage;
use Symfony\Contracts\Cache\CacheInterface;

#[AsSchedule('WeeklyMail')]
final class MailSchedule implements ScheduleProviderInterface
{
    public function __construct(
        private CacheInterface $cache
    ) {
    }

    public function getSchedule(): Schedule
    {
        return (new Schedule())
            ->add(
                RecurringMessage::cron('30 8 * * 1', 
                    new RunCommandMessage('app:send-email'))
            )
            ->stateful($this->cache)
        ;
    }
}
