<?php

namespace App\Alarm;

interface Manager
{
    function getAnswer(string $key, string $arg = "");

    function checkCommand(string $key);
}