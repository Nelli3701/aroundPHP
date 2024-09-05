<?php

namespace App\Alarm;

use App\Application;
use App\Commands\SaveEventCommand;
use App\Database\SQLite;
use App\Models\Event;

class TgManager implements Manager
{
    private static array $commands = [
        '/start' => 'start',
        '/text' => 'text',
        '/cron' => 'cron',
        '/create' => 'create',
        '/show_text' => 'showText',
        '/show_cron' => 'showCron',
    ];

    private Application $app;
    private int $userId;
    private string $text;
    private array $cron;

    public function __construct(Application $app, int $userId)
    {
        $this->app = $app;
        $this->userId = $userId;
    }

    function getAnswer(string $key, string $arg = "")
    {
        $func = self::$commands[$key];
        return $this->$func($arg);
    }

    function checkCommand(string $key): bool
    {
        return key_exists($key, self::$commands);
    }

    private function start(): string
    {
        return 'Укажите событие:' . PHP_EOL . PHP_EOL .
            '/text {сообщение} - записать текст напоминания' . PHP_EOL .
            '/cron {расписание} - добавить расписание в формате cron' . PHP_EOL .
            '/create - создать напоминание'. PHP_EOL .
            '/show_text - показать текст'. PHP_EOL .
            '/show_cron - показать расписание';
    }

    private function create(): string
    {
        if (isset($this->text) && isset($this->cron)) {
            $options = [
                'name' => 'tg_alarm',
                'text' => $this->text,
                'receiver_id' => $this->userId,
                'minute' => $this->cron[0] === '*' ? null : $this->cron[0],
                'hour' => $this->cron[1] === '*' ? null : $this->cron[1],
                'day' => $this->cron[2] === '*' ? null : $this->cron[2],
                'month' => $this->cron[3] === '*' ? null : $this->cron[3],
                'day_of_week' => $this->cron[4] === '*' ? null : $this->cron[4]
            ];

            $event = new Event(new SQLite($this->app));
            $event->insert(implode(', ', array_keys($options)), array_values($options));

            return "Напоминание создано:" . PHP_EOL . $this->text . PHP_EOL . implode(" ", $this->cron);
        }

        $msg = "";

        if (!isset($this->text)) {
            $msg .= "Необходимо задать текст напоминания" . PHP_EOL;
        }

        if (isset($this->cron)) {
            $msg .= "Необходимо задать расписание";
        }

        return $msg;
    }

    private function text(string $str): string
    {
        $this->text = $str;
        return "Текст добавлен";
    }

    private function cron(string $str): string
    {
        $args = explode(" ", $str);

        if ((count($args) != 5) || !$this->checkCron($args)) {
            return "Формат расписания указан неверно" . PHP_EOL .
                "* * * * *
| | | | |
| | | | +----- Дни недели (диапазон: 1-7)
| | | +------- Месяцы     (диапазон: 1-12)
| | +--------- Дни месяца (диапазон: 1-31)
| +----------- Часы       (диапазон: 0-23)
+------------- Минуты     (диапазон: 0-59)";
        }

        $this->cron = $args;

        return "Расписание добавлено";
    }

    private function showText(): string
    {
        return $this->text ?? "Текст напоминания не задан";
    }

    private function showCron(): string
    {
        return isset($this->cron) ? implode(" ", $this->cron) : "Расписание не задано";
    }

    private function checkCron(array $args): bool
    {
        if ($args[0] !== '*' && $args[0] < 0 || $args[0] > 59) {
            return false;
        }
        if ($args[1] !== '*' && $args[1] < 0 || $args[1] > 23) {
            return false;
        }
        if ($args[2] !== '*' && $args[2] < 1 || $args[2] > 31) {
            return false;
        }
        if ($args[3] !== '*' && $args[3] < 1 || $args[3] > 12) {
            return false;
        }
        if ($args[4] !== '*' && $args[4] < 1 || $args[4] > 7) {
            return false;
        }

        //TODO Добавить проверку правильности дат?

        return true;
    }


}