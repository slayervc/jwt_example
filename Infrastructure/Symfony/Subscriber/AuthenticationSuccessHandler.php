<?php

declare(strict_types=1);

namespace App\Infrastructure\Symfony\Subscriber;

use App\Infrastructure\Symfony\Security\UserAttributesModification\UserAttributesModifyingTimestampsStorageInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWSProvider\JWSProviderInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Core\Event\AuthenticationSuccessEvent;

#[AsEventListener]
class AuthenticationSuccessHandler
{
    public function __construct(
        private readonly UserAttributesModifyingTimestampsStorageInterface $storage,
        private readonly RequestStack $requestStack,
        private readonly JWSProviderInterface $jwsProvider,
    ) {
    }

    public function __invoke(AuthenticationSuccessEvent $event)
    {
        $request = $this->requestStack->getMainRequest();
        $authHeader = $request?->headers->get('Authorization');
        if (empty($authHeader)) {
            return;
        }

        $token = substr($authHeader, strlen('Bearer '));
        $jws = $this->jwsProvider->load($token);
        $tokenCreatedAt = new \DateTimeImmutable();
        $payload = $jws->getPayload();
        $tokenCreatedAt = $tokenCreatedAt->setTimestamp($payload['iat']);
        $tokenCreatedAt = $tokenCreatedAt->modify(sprintf('+%s microseconds', $payload['iatMicroseconds']));
        $userId = $event->getAuthenticationToken()->getUser()->id;
        $tokenWithdrawnAt = $this->storage->findTimestamp($userId);
        if (null !== $tokenWithdrawnAt && $tokenCreatedAt < $tokenWithdrawnAt) {
            throw new UnauthorizedHttpException('', 'JWT is withdrawn');
        }
    }
}