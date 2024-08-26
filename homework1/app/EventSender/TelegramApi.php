<?php

namespace App\EventSender;

interface TelegramApi
{
    function __construct(string $token);
    function getMessages(int $offset) : array;
    function sendMessage(string $chatId, string $text);

}