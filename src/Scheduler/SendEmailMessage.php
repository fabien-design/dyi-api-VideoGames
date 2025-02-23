<?php 

namespace App\Scheduler;

class SendEmailMessage
{
    private array $videogames;

    public function __construct(array $videogames = [])
    {
        $this->videogames = $videogames;
    }

    public function getVideogames(): array
    {
        return $this->videogames;
    }
}