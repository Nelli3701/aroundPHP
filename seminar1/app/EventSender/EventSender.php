<?php
namespace App\EventSender;

class EventSender
{
    public function sendMessage(string $receiver, string $message): void
    {
        echo date('d.m.y H:i') . " Я отправил сообщение $message получателю с id $receiver\n";
    }
}