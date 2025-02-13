<?php

declare(strict_types=1);


namespace App\Domain\Model\User;


class User
{
    private string $id;
    private bool $rulesAccepted;
    private bool $isActive = false;
    private \DateTimeImmutable $createdAt;

    //...
}