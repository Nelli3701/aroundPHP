<?php

use App\Helpers\Str;
use PHPUnit\Framework\TestCase;

class StrTest extends TestCase
{
    /**
     * @testWith ["some_command", "SomeCommand"]
     *           ["some-command", "SomeCommand"]
     */
    public function testStudly(string $line, string $check): void
    {
        $result = Str::studly($line);

        self::assertEquals($result, $check);
    }

}