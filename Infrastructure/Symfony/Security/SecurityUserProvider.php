<?php

declare(strict_types=1);

namespace App\Infrastructure\Symfony\Security;

use App\Domain\Model\User\UserRepositoryInterface;
use App\Infrastructure\Symfony\Security\User\SecurityUser;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class SecurityUserProvider implements UserProviderInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $domainUser = $this->userRepository->findById($identifier);
        if (null === $domainUser) {
            throw new UserNotFoundException(sprintf('User "%s" not found.', $identifier));
        }

        return SecurityUser::createFromDomainUser($domainUser);
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        return $user;
    }

    public function supportsClass(string $class): bool
    {
        return $class === SecurityUser::class;
    }
}