<?php

use App\Alarm\TgManager;
use App\Application;
use PHPUnit\Framework\TestCase;

/**
 * @covers TgManager
 */
class TgManagerTest extends TestCase
{
    /**
     * @testWith ["/start"]
     *           ["/text"]
     *           ["/cron"]
     *           ["/create"]
     *           ["/show_text"]
     *           ["/show_cron"]
     */
    public function testCheckCommand(string $key): void
    {
        $tgManager = new TgManager(new Application(dirname(__DIR__)), 0);

        $result = $tgManager->checkCommand($key);

        self::assertTrue($result);
    }

    /**
     * @dataProvider getAnswerDataProvider
     */
    public function testGetAnswerWithProvider(string $key, string $args, string $answer): void
    {
        $tgManager = new TgManager(new Application(dirname(__DIR__)), 0);

        $result = $tgManager->getAnswer($key);

        self::assertEquals($result, $answer);
    }

    private function getAnswerDataProvider(): array
    {
        return [
            [
                "/start",
                "",
                'Укажите событие:' . PHP_EOL . PHP_EOL .
                '/text {сообщение} - записать текст напоминания' . PHP_EOL .
                '/cron {расписание} - добавить расписание в формате cron' . PHP_EOL .
                '/create - создать напоминание' . PHP_EOL .
                '/show_text - показать текст' . PHP_EOL .
                '/show_cron - показать расписание'
            ],
            [
                "/text",
                "some text",
                "Текст добавлен"
            ],
            [
                "/cron",
                "* * * *",
                "Формат расписания указан неверно" . PHP_EOL .
                "* * * * *
| | | | |
| | | | +----- Дни недели (диапазон: 1-7)
| | | +------- Месяцы     (диапазон: 1-12)
| | +--------- Дни месяца (диапазон: 1-31)
| +----------- Часы       (диапазон: 0-23)
+------------- Минуты     (диапазон: 0-59)"
            ]
        ];
    }
}