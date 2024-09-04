<?php

use App\Application;
use App\Commands\HandleEventsCommand;
use PHPUnit\Framework\TestCase;

class HandleEventCommandTest extends TestCase
{
    /**
     * @dataProvider eventDtoProvider
     */
    public function testShouldEventBeRan(array $event, bool $shouldEventBeRan): void
    {
        $handleEventCommand = new HandleEventsCommand(new Application(dirname(__DIR__)));

        $result = $handleEventCommand->shouldEventBeRan($event);

        self::assertEquals($result, $shouldEventBeRan);
    }

    public static function eventDtoProvider(): array
    {
        return [
            [
                [
                    'minute' => date("i"),
                    'hour' => date("H"),
                    'day' => date("d"),
                    'month' => date("m"),
                    'day_of_week' => date("w")
                ],
                true
            ],
            [
                [
                    'minute' => '651',
                    'hour' => '123',
                    'day' => '123',
                    'month' => '123',
                    'day_of_week' => '123'
                ],
                false
            ]
        ];
    }
}