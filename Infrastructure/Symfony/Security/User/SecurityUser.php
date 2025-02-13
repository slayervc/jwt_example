<?php

declare(strict_types=1);

namespace App\Infrastructure\Symfony\Security\User;

use App\Domain\Model\Contact\ContactType;
use App\Domain\Model\User\User;
use App\Infrastructure\Formatter\MembershipTypeFormatter;
use Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUserInterface;

class SecurityUser implements JWTUserInterface
{
    public function __construct(
        public readonly string $id,
        public readonly string $telegramLogin,
    ) {
    }

    public static function createFromDomainUser(User $domainUser): self
    {
        $telegramLogin = $domainUser->getProfileContact(ContactType::TELEGRAM);
        if (null === $telegramLogin) {
            throw new \RuntimeException('Unable to instantiate security user without telegram login');
        }

        return new self(
            $domainUser->getId(),
            $telegramLogin,
        );
    }

    public static function createFromPayload($username, array $payload): self
    {
        return new self(
            $payload['id'],
            $payload['telegramLogin'],
        );
    }

    public function getRoles(): array
    {
        return [];
    }

    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->id;
    }
}