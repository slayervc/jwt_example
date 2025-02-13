<?php

declare(strict_types=1);

namespace App\Domain\Model\User;

use App\Domain\Exception\EntityNotFoundException;
use App\Domain\Group\Group;
use App\Domain\Model\Contact\Contact;
use App\Domain\Model\Contact\ContactType;
use App\Domain\Model\Notification\RecipientList;

interface UserRepositoryInterface
{
    /**
     * @throws EntityNotFoundException
     */
    public function getById(string $id): User;

    public function findById(string $id): ?User;

    public function findOneByContact(Contact $contact): ?User;

    public function findAllByTag(string $tagId): UserCollection;

    public function save(User $user): void;

    public function removeTagFromAllMemberships(string $tagId): void;
}