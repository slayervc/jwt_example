<?php

declare(strict_types=1);

namespace App\Infrastructure\Symfony\Security\JWT;

readonly class TokenStringsDto
{
    public function __construct(
        public string $token,
        public string $refreshToken
    ) {
    }
}