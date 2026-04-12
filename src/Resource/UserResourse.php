<?php

declare(strict_types=1);

namespace App\Resource;

use App\DTO\Output\User\UserOutputDTO;
use Symfony\Component\Serializer\SerializerInterface;

final class UserResourse
{
    public function __construct(private SerializerInterface $serializer)
    {
    }

    public function userItem(UserOutputDTO $user): string
    {
        return $this->serializer->serialize($user, 'json', ['groups' => ['user:item']]);
    }

    public function userCollection(array $users): string
    {
        return $this->serializer->serialize($users, 'json', ['groups' => ['user:item']]);
    }
}
