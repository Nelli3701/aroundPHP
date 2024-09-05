<?php

namespace App\EventSender;

class TelegramSender implements TelegramApi
{
    const ENDPOINT = "https://api.telegram.org/bot";
    private int $firstMessage = 0;
    private string $token;

    public function __construct(string $token)
    {
        $this->token = $token;

        $url = self::ENDPOINT . $this->token . "/getUpdates?timeout=1";

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = json_decode(curl_exec($ch), true);
        curl_close($ch);

        if ($response['ok'] && !empty($response['result'])) {
            $this->firstMessage = $response['result'][0]['update_id'];
        }
    }

    function getMessages(int $offset = 0): array
    {
        $url = self::ENDPOINT . $this->token . "/getUpdates?timeout=1";
        $result = [];
        $user = 0;

        while (true) {
            $ch = curl_init("{$url}&offset={$offset}");

            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = json_decode(curl_exec($ch), true);

            if (!$response['ok'] || empty($response['result'])) break;

            $user = $response['result'][0]['message']['from'];

            foreach ($response['result'] as $data) {
                $result = [...$result, $data['message']['text']];
                $offset = $data['update_id'] + 1;
            }
            curl_close($ch);

            if (count($response['result']) < 100) break;
        }

        return [
            'user' => $user,
            'offset' => $offset,
            'result' => $result
        ];
    }

    function sendMessage(string $chatId, string $text): void
    {
        $url = self::ENDPOINT . $this->token . "/sendMessage";

        $data = [
            'chat_id' => $chatId,
            'text' => $text
        ];

        $jsonData = json_encode($data);

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_exec($ch);

        curl_close($ch);
    }
}