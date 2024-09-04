<?php

namespace App\Commands;

use App\Alarm\Manager;
use App\Alarm\TgManager;
use App\Application;
use App\EventSender\TelegramApi;
use App\EventSender\TelegramSender;
use JetBrains\PhpStorm\NoReturn;

class TgMessageDaemonCommand extends Command
{
    protected Application $app;
    protected TelegramApi $tgApp;
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
        $this->tgApp = new TelegramSender($this->app->env('TELEGRAM_TOKEN'));

        $jsonMessages = file_get_contents($this->app->env('TELEGRAM_HISTORY'));

        if ($jsonMessages) {
            $this->messages = json_decode($jsonMessages, true);
        } else {
            $this->messages = $this->tgApp->getMessages();
        }

        $this->manager = new TgManager($this->app, $this->messages['user']['id']);
    }

    #[NoReturn]
    function runDaemon(): void
    {
        while (true) {
            $newMessages = $this->tgApp->getMessages($this->messages['offset']);
            if ($newMessages['result']) {
                $this->messages['offset'] = $newMessages['offset'];
                $this->messages['result'] = [...$this->messages['result'], ...$newMessages['result']['message']['chat']['id']];

                $this->saveHistory();

                $textMessage = $newMessages['result']['message']['chat']['id'][0];

                @[$command, $arg] = explode(" ", $textMessage, 2);

                if ($this->manager->checkCommand($command)) {
                    $answer = $this->manager->getAnswer($command, $arg ?? "");
                    $this->tgApp->sendMessage($this->messages['user']['id'], $answer);
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