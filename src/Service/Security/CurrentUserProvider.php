<?php

declare(strict_types=1);

namespace App\Service\Security;

use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;

final class CurrentUserProvider
{
    public function __construct(private Security $security)
    {
    }

    public function getCurrentUser(): ?User
    {
        $user = $this->security->getUser();

        return $user instanceof User ? $user : null;
    }
}
