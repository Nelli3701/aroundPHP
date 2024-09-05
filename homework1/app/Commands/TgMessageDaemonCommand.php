<?php

namespace App\Commands;

use App\Alarm\Manager;
use App\Alarm\TgManager;
use App\Application;
use App\EventSender\EventSender;
use App\EventSender\TelegramSender;
use App\Queue\RabbitMQ;
use JetBrains\PhpStorm\NoReturn;

class TgMessageDaemonCommand extends Command
{
    protected Application $app;
    protected EventSender $eventApp;
    private array $messages = [];
    private Manager $manager;


    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    #[NoReturn]
    function run(array $options = []): void
    {
        $this->initDaemon();
        $this->runDaemon();
    }

    function initDaemon(): void
    {
        $this->eventApp = new EventSender(
            new TelegramSender($this->app->env('TELEGRAM_TOKEN')),
            new RabbitMQ('eventSender')
        );
        $jsonMessages = file_get_contents($this->app->env('TELEGRAM_HISTORY'));

        if ($jsonMessages) {
            $this->messages = json_decode($jsonMessages, true);
        } else {
            $this->messages = $this->eventApp->getMessages();
        }

        $this->manager = new TgManager($this->app, $this->messages['user']['id']);
    }

    #[NoReturn]
    function runDaemon(): void
    {
        while (true) {
            $newMessages = $this->eventApp->getMessages($this->messages['offset']);
            if ($newMessages['result']) {
                $this->messages['offset'] = $newMessages['offset'];
                $this->messages['result'] = [...$this->messages['result'], ...$newMessages['result']];

                $this->saveHistory();

                $textMessage = $newMessages['result'][0];

                @[$command, $arg] = explode(" ", $textMessage, 2);

                if ($this->manager->checkCommand($command)) {
                    $answer = $this->manager->getAnswer($command, $arg ?? "");
                    $this->eventApp->sendMessage($this->messages['user']['id'], $answer);
                }
            } else {
                sleep(1);
            }
        }
    }

    function saveHistory(): void
    {
        $jsonHistory = json_encode($this->messages);
        file_put_contents('files/messages.json', $jsonHistory);
    }
}
