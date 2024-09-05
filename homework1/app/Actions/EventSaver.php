<?php

namespace App\Actions;

use App\Models\Event;

class EventSaver
{
    private Event $event;

    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    public function handle(array $eventDto): void
    {
        $this->saveEvent($eventDto);
    }

    public function saveEvent(array $params): void
    {
        $this->event->insert(
            implode(', ', array_keys($params)),
            array_values($params)
        );
    }
}