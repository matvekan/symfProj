<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Interest;
use App\Repository\Contract\InterestRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Interest>
 */
class InterestRepository extends ServiceEntityRepository implements InterestRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em, ManagerRegistry $registry)
    {
        parent::__construct($registry, Interest::class);
    }

    public function store(Interest $interest, bool $isFlush = true): Interest
    {
        $this->em->persist($interest);
        if ($isFlush) {
            $this->em->flush();
        }

        return $interest;
    }

    public function findByName(string $name): ?Interest
    {
        return $this->findOneBy(['name' => $name]);
    }

    public function findAllOrderedByName(): array
    {
        return $this->findBy([], ['name' => 'ASC']);
    }

    public function findByIds(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        return $this->findBy(['id' => $ids]);
    }
}
