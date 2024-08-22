<?php

namespace App\Commands;

use App\Application;

use App\Database\SQLite;

use App\EventSender\EventSender;

use App\Models\Event;

class HandleEventsCommand extends Command

{

    protected Application $app;

    public function __construct(Application $app)

    {

        $this->app = $app;
    }

    public function run(array $options = []): void

    {

        $event = new Event(new SQLite($this->app));

        $events = $event->select();

        $eventSender = new EventSender();

        foreach ($events as $event) {

            if ($this->shouldEventBeRan($event)) {

                $eventSender->sendMessage($event['receiver_id'], $event['text']);
            }
        }
    }

    private function shouldEventBeRan($event): bool

    {
        $currentMinute = date("i");

        $currentHour = date("H");

        $currentDay = date("d");

        $currentMonth = date("m");

        $currentWeekday = date("w");

        return ((!$event['minute'] || $event['minute'] === $currentMinute) &&

            (!$event['hour'] || $event['hour'] === $currentHour) &&

            (!$event['day'] || $event['day'] === $currentDay) &&

            (!$event['month'] || $event['month'] === $currentMonth) &&

            (!$event['day_of_week'] || $event['day_of_week'] === $currentWeekday));
    }
}
