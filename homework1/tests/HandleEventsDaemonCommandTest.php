<?php

use App\Application;
use App\Commands\HandleEventsDaemonCommand;
use PHPUnit\Framework\TestCase;

class HandleEventsDaemonCommandTest extends TestCase
{
    /**
     * @dataProvider getDataProvider
     */
    public function testGetCurrentTime(array $data)
    {
        $handleEventsDaemonCommand = new HandleEventsDaemonCommand(new Application(dirname(__DIR__)));

        $result = $handleEventsDaemonCommand->getCurrentTime();

        self::assertEquals($result, $data);
    }

    public static function getDataProvider(): array
    {
        return [
            [
                [
                    date("i"),
                    date("H"),
                    date("d"),
                    date("m"),
                    date("w")
                ]
            ]
        ];
    }
}