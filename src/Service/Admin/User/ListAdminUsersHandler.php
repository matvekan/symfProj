<?php

declare(strict_types=1);

namespace App\Service\Admin\User;

use App\DTO\Query\Admin\ListAdminUsersQueryDto;
use App\Factory\UserFactory;
use App\Repository\Contract\UserRepositoryInterface;
use App\Resource\UserResourse;
use Psr\Cache\CacheItemPoolInterface;

final class ListAdminUsersHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserFactory $userFactory,
        private UserResourse $userResourse,
        private CacheItemPoolInterface $cachePool,
    ) {
    }

    public function handle(ListAdminUsersQueryDto $queryDto): string
    {
        $cacheKey = 'admin_users_' . md5((string) json_encode($queryDto->toCachePayload()));
        $cacheItem = $this->cachePool->getItem($cacheKey);
        if ($cacheItem->isHit()) {
            return (string) $cacheItem->get();
        }

        $users = $this->userRepository->findByAdminFilters($queryDto->filters, $queryDto->page, $queryDto->limit);
        $payload = $this->userResourse->userCollection($this->userFactory->makeUserOutputDTOs($users));

        $cacheItem->set($payload);
        $cacheItem->expiresAfter(300);
        $this->cachePool->save($cacheItem);

        return $payload;
    }
}
