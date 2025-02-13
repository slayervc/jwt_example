<?php

declare(strict_types=1);

namespace App\Infrastructure\Symfony\Security\JWT;

use Gesdinet\JWTRefreshTokenBundle\Generator\RefreshTokenGeneratorInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use App\Domain\Model\User\User;
use App\Infrastructure\Symfony\Security\User\SecurityUser;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class TokenGenerator
{
    public function __construct(
        private readonly JWTTokenManagerInterface $JWTManager,
        private readonly RefreshTokenGeneratorInterface $refreshTokenGenerator,
        private readonly RefreshTokenManagerInterface $refreshTokenManager,
        private readonly int $jwtTtl
    ) {
    }

    public function createFromUser(User $user): TokenStringsDto
    {
        $securityUser = SecurityUser::createFromDomainUser($user);
        $token = $this->JWTManager->create($securityUser);
        $refreshToken = $this->refreshTokenGenerator->createForUserWithTtl($securityUser, $this->jwtTtl);
        $this->refreshTokenManager->save($refreshToken);
        $refreshTokenString = $refreshToken->getRefreshToken();

        return new TokenStringsDto($token, $refreshTokenString);
    }
}