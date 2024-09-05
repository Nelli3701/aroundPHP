<?php

use App\Application;
use App\Commands\SaveEventCommand;
use PHPUnit\Framework\TestCase;

/**
 * @covers SaveEventCommand
 */
class SaveEventCommandTest extends TestCase
{
    /**
     * @dataProvider getDataProvider
     */
    public function testIsNeedHelp(array $options, bool $isNeededResult): void
    {
        $saveEventCommand = new SaveEventCommand(new Application(dirname(__DIR__)));

        $result = $saveEventCommand->isNeedHelp($options);

        self::assertEquals($result, $isNeededResult);
    }

    /**
     * @testWith ["1 1 1 1 1", [1,1,1,1,1]]
     *           ["* * * * *", [null,null,null,null,null]]
     */
    public function testGetCronValues(string $cron, array $check): void
    {
        $saveEventCommand = new SaveEventCommand(new Application(dirname(__DIR__)));

        $result = $saveEventCommand->getCronValues($cron);

        self::assertEquals($result, $check);
    }

    public static function getDataProvider(): array
    {
        return [
          [
              [
                'name' => 'some data',
                  'text' => 'some data',
                  'receiver' => 'some data',
                  'cron' => 'some data'
              ],
              false
          ],
            [
                [
                    'name' => 'some data',
                    'text' => 'some data',
                    'receiver' => 'some data',
                    'cron' => 'some data',
                    'help' => 'some data',
                    'h' => 'some data'
                ],
                true
            ]
        ];
    }
}