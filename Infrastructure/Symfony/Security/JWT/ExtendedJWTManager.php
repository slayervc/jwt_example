<?php
declare(strict_types=1);


namespace App\Infrastructure\Symfony\Security\JWT;


use App\Infrastructure\Symfony\Security\User\SecurityUser;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\HeaderAwareJWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTEncodedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ExtendedJWTManager extends JWTManager
{
    private const USER_IDENTIFIER_FIELD = 'id';

    private PayloadGenerator $payloadGenerator;

    public function __construct(
        PayloadGenerator $payloadGenerator,
        JWTEncoderInterface $encoder,
        EventDispatcherInterface $dispatcher,
    ) {
        parent::__construct($encoder, $dispatcher, self::USER_IDENTIFIER_FIELD);
        $this->payloadGenerator = $payloadGenerator;
    }

    public function create(UserInterface $user): string
    {
        if (!$user instanceof SecurityUser) {
            throw new \RuntimeException(sprintf(
                '%s generate tokens only from %s. Instance of %s provided instead',
                $this::class,
                SecurityUser::class,
                $user::class
            ));
        }

        $payload = $this->payloadGenerator->generate($user);
        $jwtCreatedEvent = new JWTCreatedEvent($payload, $user);
        $this->dispatcher->dispatch($jwtCreatedEvent, Events::JWT_CREATED);
        if ($this->jwtEncoder instanceof HeaderAwareJWTEncoderInterface) {
            $jwtString = $this->jwtEncoder->encode(
                $jwtCreatedEvent->getData(),
                $jwtCreatedEvent->getHeader()
            );
        } else {
            $jwtString = $this->jwtEncoder->encode($jwtCreatedEvent->getData());
        }

        $jwtEncodedEvent = new JWTEncodedEvent($jwtString);
        $this->dispatcher->dispatch($jwtEncodedEvent, Events::JWT_ENCODED);

        return $jwtString;
    }
}
