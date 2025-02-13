<?php

declare(strict_types=1);


use App\Application\Command\User\CreateUserFromTelegramData;
use App\Application\Service\IdGeneratorInterface;
use App\Domain\Model\Contact\Contact;
use App\Domain\Model\Contact\ContactType;
use App\Domain\Model\User\UserRepositoryInterface;
use App\Infrastructure\PortAdapter\Http\Response\Security\AfterLoginAction;
use App\Infrastructure\PortAdapter\Http\Response\Security\LoginResponse;
use App\Infrastructure\Symfony\Security\JWT\TokenGenerator;
use App\Infrastructure\Symfony\Security\Telegram\TelegramAuthChecker;
use App\Infrastructure\Symfony\Security\Telegram\TelegramUserData;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Annotation\Route;

class SecurityController extends AbstractController
{
    public function __construct(
        private readonly TelegramAuthChecker $telegramAuthChecker,
        private readonly UserRepositoryInterface $userRepository,
        private readonly TokenGenerator $tokenGenerator,
        private readonly IdGeneratorInterface $idGenerator,
    ) {
    }

    #[Route('/login_auth', methods: ['GET'])]
    public function auth(): Response
    {
        return $this->render('auth.html.twig');
    }

    #[Route('/login_tg', methods: ['GET'])]
    public function login(Request $request): Response
    {
        $data = $request->query->all();
        if (!$this->telegramAuthChecker->check($data)) {
            throw new UnauthorizedHttpException('Failed to check authenticity of sent data');
        }

        $decodedData = array_map(fn(string $v) => htmlspecialchars($v), $data);
        $tgUserData = TelegramUserData::fromArray($decodedData);
        $user = $this->userRepository->findOneByContact(new Contact(ContactType::TELEGRAM, $tgUserData->username));
        if (null === $user) {
            $command = new CreateUserFromTelegramData($this->userRepository, $this->idGenerator);
            $user = $command->execute($tgUserData);
        }

        $tokenStrings = $this->tokenGenerator->createFromUser($user);

        return new JsonResponse(
            new LoginResponse(
                $tokenStrings->token,
                $tokenStrings->refreshToken
            )
        );
    }
}