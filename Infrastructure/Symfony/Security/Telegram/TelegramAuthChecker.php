<?php

declare(strict_types=1);

namespace App\Infrastructure\Symfony\Security\Telegram;

class TelegramAuthChecker
{
    public function __construct(
        private readonly string $telegramBotToken,
        private readonly int $telegramAuthLifetime,
        private readonly bool $telegramAuthCheckEnabled
    ) {
    }

    public function check(array $data): bool
    {
        if (!$this->telegramAuthCheckEnabled) {
            return true;
        }

        if ((time() - $data['auth_date']) > $this->telegramAuthLifetime) {
            return false;
        }

        $incomingHash = $data['hash'];
        unset($data['hash']);
        $asArray = [];
        foreach ($data as $key => $value) {
            $asArray[] = $key . '=' . $value;
        }

        sort($asArray);
        $asString = implode("\n", $asArray);
        $secretKey = hash('sha256', $this->telegramBotToken, true);
        $hash = hash_hmac('sha256', $asString, $secretKey);
        if (strcmp($hash, $incomingHash) !== 0) {
            return false;
        }

        return true;
    }
}