<?php

declare(strict_types=1);

namespace App\Repository;

use App\DTO\Query\Admin\AdminUserFiltersDto;
use App\Entity\User;
use App\Repository\Contract\UserRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements UserRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em, ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function store(User $user, bool $isFlush = true): User
    {
        $this->em->persist($user);
        if ($isFlush) {
            $this->em->flush();
        }

        return $user;
    }

    public function remove(User $user, bool $isFlush = true): void
    {
        $this->em->remove($user);
        if ($isFlush) {
            $this->em->flush();
        }
    }

    public function findOneByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]);
    }

    /**
     * @return list<User>
     */
    public function findByAdminFilters(
        AdminUserFiltersDto $filters,
        int $page = 1,
        int $limit = 20,
    ): array {
        $page = max(1, $page);
        $limit = max(1, min(100, $limit));

        $qb = $this->createBaseAdminFiltersQueryBuilder();

        $this->applyNameFilter($qb, $filters);
        $this->applyEmailFilter($qb, $filters);
        $this->applyRoleFilter($qb, $filters);
        $this->applyCreatedFromFilter($qb, $filters);
        $this->applyCreatedToFilter($qb, $filters);
        $this->applyInterestIdsFilter($qb, $filters);
        $this->applyInterestNameFilter($qb, $filters);

        $qb
            ->orderBy('u.id', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        /** @var list<User> $result */
        $result = $qb->getQuery()->getResult();

        return $result;
    }

    private function createBaseAdminFiltersQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.interest', 'i')
            ->addSelect('i');
    }

    private function applyNameFilter(QueryBuilder $qb, AdminUserFiltersDto $filters): void
    {
        if ($filters->name === null || $filters->name === '') {
            return;
        }

        $qb
            ->andWhere('u.name LIKE :name')
            ->setParameter('name', $this->wrapLike($filters->name));
    }

    private function applyEmailFilter(QueryBuilder $qb, AdminUserFiltersDto $filters): void
    {
        if ($filters->email === null || $filters->email === '') {
            return;
        }

        $qb
            ->andWhere('u.email LIKE :email')
            ->setParameter('email', $this->wrapLike($filters->email));
    }

    private function applyRoleFilter(QueryBuilder $qb, AdminUserFiltersDto $filters): void
    {
        if ($filters->role === null || $filters->role === '') {
            return;
        }

        $qb
            ->andWhere('u.role = :role')
            ->setParameter('role', mb_strtoupper($filters->role));
    }

    private function applyCreatedFromFilter(QueryBuilder $qb, AdminUserFiltersDto $filters): void
    {
        if ($filters->createdFrom === null) {
            return;
        }

        $qb
            ->andWhere('u.createdAt >= :createdFrom')
            ->setParameter('createdFrom', $filters->createdFrom);
    }

    private function applyCreatedToFilter(QueryBuilder $qb, AdminUserFiltersDto $filters): void
    {
        if ($filters->createdTo === null) {
            return;
        }

        $qb
            ->andWhere('u.createdAt <= :createdTo')
            ->setParameter('createdTo', $filters->createdTo);
    }

    private function applyInterestIdsFilter(QueryBuilder $qb, AdminUserFiltersDto $filters): void
    {
        if ($filters->interestIds === []) {
            return;
        }

        $qb
            ->andWhere('i.id IN (:interestIds)')
            ->setParameter('interestIds', $filters->interestIds);
    }

    private function applyInterestNameFilter(QueryBuilder $qb, AdminUserFiltersDto $filters): void
    {
        if ($filters->interestName === null || $filters->interestName === '') {
            return;
        }

        $qb
            ->andWhere('i.name LIKE :interestName')
            ->setParameter('interestName', $this->wrapLike($filters->interestName));
    }

    private function wrapLike(string $value): string
    {
        return '%' . trim($value) . '%';
    }
}
