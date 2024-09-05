<?php

namespace App\Commands;

use App\Application;
use App\Cache\Redis;
use App\EventSender\TelegramSender;
use Predis\Client;
use Psr\SimpleCache\InvalidArgumentException;

class TgMessagesCommand extends Command
{
    protected Application $app;
    protected TelegramSender $tgApp;
    protected Redis $redis;
    private int $offset;
    private array|null|object $oldMessages;


    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->tgApp = new TelegramSender($this->app->env('TELEGRAM_TOKEN'));
        $this->offset = 0;
        $this->oldMessages = [];
        $this->redis = new Redis(new Client([
            'schema' => 'tcp',
            'host' => 'localhost',
            'port' => 6379
        ]));
    }

    function run(array $options = [], TelegramSender $tgApp = null): void
    {
        if ($tgApp) {
            $this->tgApp = $tgApp;
        }

        try {
            echo json_encode($this->receiveNewMessages()) . PHP_EOL;
        } catch (InvalidArgumentException $e) {
            echo $e->getMessage() . PHP_EOL;
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    private function receiveNewMessages(): array
    {
        $this->offset = $this->redis->get('tg_messages:offset', 0);

        $newMessages = $this->tgApp->getMessages($this->offset);

        if ($newMessages['user']) {
            $userId = $newMessages['user']['id'];

            $this->redis->set('tg_messages:offset', $newMessages['offset'] ?? 0);

            $this->oldMessages = json_decode($this->redis->get('tg_messages:old_messages'), true);

            if (isset($this->oldMessages[$userId])) {
                $this->oldMessages[$userId] = [...$this->oldMessages[$userId], ...$newMessages];
            } else {
                $this->oldMessages[$userId] = $newMessages;
            }

            $this->redis->set('tg_messages:old_messages', json_encode($this->oldMessages));
        }

        return $newMessages;
    }
}