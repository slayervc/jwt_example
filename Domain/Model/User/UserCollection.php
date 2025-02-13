<?php

declare(strict_types=1);

namespace App\Domain\Model\User;

class UserCollection
{
    /** @var User[] */
    private array $elements;

    public function __construct(User ...$elements)
    {
        $this->elements = $elements;
    }

    /**
     * @return User[]
     */
    public function getElements(): array
    {
        return $this->elements;
    }
}