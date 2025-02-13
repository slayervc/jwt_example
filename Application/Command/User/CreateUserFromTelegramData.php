<?php

declare(strict_types=1);


use App\Application\Service\IdGeneratorInterface;
use App\Domain\Model\Contact\Contact;
use App\Domain\Model\Contact\ContactType;
use App\Domain\Model\User\Profile\ProfileName;
use App\Domain\Model\User\User;
use App\Domain\Model\User\UserRepositoryInterface;
use App\Infrastructure\Symfony\Security\Telegram\TelegramUserData;

class CreateUserFromTelegramData
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly IdGeneratorInterface $idGenerator,
    ) {
    }

    public function execute(TelegramUserData $tgUserData): User
    {
        $user = new User(
            $this->idGenerator->generate(),
            new ProfileName($tgUserData->firstName, $tgUserData->lastName),
            new Contact(ContactType::TELEGRAM_CHAT_ID, $tgUserData->chatId),
            new Contact(ContactType::TELEGRAM, $tgUserData->username)
        );

        $this->userRepository->save($user);

        return $user;
    }
}