<?php

use App\Application;
use App\Commands\TgMessagesCommand;
use App\EventSender\TelegramSender;
use PHPUnit\Framework\TestCase;

/**
 * @covers TgMessagesCommand
 */
class TgMessagesCommandTest extends TestCase
{

    public function testTgMessageCommandRun(array $options = []): void
    {
        $mock = $this->getMockBuilder(TelegramSender::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->once())
            ->method("getMessages")
            ->with($options['offset'] ?? 0)
            ->willReturn([
                'user' => "some-user",
                'offset' => "some-offset",
                'result' => "some-result"
            ]);
        
        $tgMessagesCommand = new TgMessagesCommand(new Application(dirname(__DIR__)));
        $tgMessagesCommand->run($options, $mock);
    }
}