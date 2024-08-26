<?php

namespace App\Commands;

use App\Application;
use App\EventSender\TelegramSender;

class TgMessagesCommand extends Command
{
    protected Application $app;


    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    function run(array $options = []): void
    {
        $offset = $options['offset'] ?? 0;
        $tgApp = new TelegramSender($this->app->env('TELEGRAM_TOKEN'));
        echo json_encode($tgApp->getMessages($offset));
    }
}