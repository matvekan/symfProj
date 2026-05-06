<?php

declare(strict_types=1);

namespace App\Service\Admin\Interest;

use App\DTO\Request\Admin\Interest\CreateAdminInterestRequestDto;
use App\Entity\Interest;
use App\Repository\Contract\InterestRepositoryInterface;
use Psr\Cache\CacheItemPoolInterface;

final class CreateAdminInterestHandler
{
    public function __construct(
        private InterestRepositoryInterface $interestRepository,
        private CacheItemPoolInterface $cachePool,
    ) {
    }

    public function handle(CreateAdminInterestRequestDto $requestDto): Interest
    {
        $existing = $this->interestRepository->findByName($requestDto->name);
        if (null !== $existing) {
            throw new \DomainException('Interest already exists.');
        }

        $interest = new Interest();
        $interest->setName($requestDto->name);
        $this->interestRepository->store($interest);
        $this->cachePool->clear();

        return $interest;
    }
}
