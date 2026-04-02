<?php

namespace App\Resource;

use App\DTO\Output\User\UserOutputDTO;
use App\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;

class UserResourse
{
    public function __construct(private SerializerInterface $serializer){

    }

    public function userItem(UserOutputDTO $user ):string
    {
        return $this->serializer->serialize($user, 'json', ['groups'=>['user:item']]);
    }

    public function userCollection(array $users):string
    {
        return $this->serializer->serialize($users, 'json', ['groups'=>['user:item']]);
    }
}