<?php

declare(strict_types=1);


namespace App\Infrastructure\Symfony\Security\JWT;


use App\Infrastructure\PrivatePropertyReflectionTrait;
use App\Infrastructure\Symfony\Security\User\SecurityUser;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PayloadGenerator
{
    public function __construct(
        private readonly NormalizerInterface $normalizer
    ) {
    }

    use PrivatePropertyReflectionTrait;

    public function generate(SecurityUser $user): array
    {
        $payload = $this->normalizer->normalize($user);
        $microtime = microtime(true);
        $microtimeDecimal = $microtime - floor($microtime);
        $microseconds = (int)floor($microtimeDecimal * 1000000);
        $payload['iatMicroseconds'] = $microseconds;

        return $payload;
    }
}
