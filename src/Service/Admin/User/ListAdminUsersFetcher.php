<?php

declare(strict_types=1);

namespace App\Service\Admin\User;

use App\DTO\Query\Admin\ListAdminUsersQueryDto;
use App\Factory\UserFactory;
use App\Repository\Contract\UserRepositoryInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Serializer\SerializerInterface;

final class ListAdminUsersFetcher
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserFactory $userFactory,
        private CacheItemPoolInterface $cachePool,
        private SerializerInterface $serializer,
    ) {
    }

    public function fetch(ListAdminUsersQueryDto $queryDto): string
    {
        $cacheKey = 'admin_users_'.md5((string) json_encode($queryDto->toCachePayload()));
        $cacheItem = $this->cachePool->getItem($cacheKey);
        if ($cacheItem->isHit()) {
            $cached = $cacheItem->get();

            return \is_string($cached) ? $cached : '[]';
        }

        $users = $this->userRepository->findByAdminFilters($queryDto->filters, $queryDto->page, $queryDto->limit);
        $payload = $this->serializer->serialize($this->userFactory->makeUserOutputDTOs($users), 'json', ['groups' => ['user:item']]);

        $cacheItem->set($payload);
        $cacheItem->expiresAfter(300);
        $this->cachePool->save($cacheItem);

        return $payload;
    }
}
