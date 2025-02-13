<?php

declare(strict_types=1);

namespace App\Infrastructure\Symfony\Security\Telegram;

readonly class TelegramUserData
{
    public function __construct(
        public string $chatId,
        public string $username,
        public ?string $firstName,
        public ?string $lastName,
        public ?string $photoUrl,
    ) {
    }

    public static function fromArray(array $array): self
    {
        $id = $array['id'];

        return new self(
            $id,
            $array['username'] ?? 'tguser_' . $id,
            $array['first_name'] ?? null,
            $array['last_name'] ?? null,
            $array['photo_url'] ?? null
        );
    }
}