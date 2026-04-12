<?php

declare(strict_types=1);

namespace App\Repository;

use App\DTO\Query\Admin\AdminUserFiltersDto;
use App\Entity\User;
use App\Repository\Contract\UserRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
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

    public function findByAdminFilters(AdminUserFiltersDto $filters, int $page = 1, int $limit = 20): array
    {
        $qb = $this->createQueryBuilder('u')
            ->leftJoin('u.interest', 'i')
            ->addSelect('i');

        if ($filters->name !== null) {
            $qb->andWhere('u.name LIKE :name')->setParameter('name', '%' . $filters->name . '%');
        }

        if ($filters->email !== null) {
            $qb->andWhere('u.email LIKE :email')->setParameter('email', '%' . $filters->email . '%');
        }

        if ($filters->role !== null) {
            $qb->andWhere('u.role = :role')->setParameter('role', mb_strtoupper($filters->role));
        }

        if ($filters->createdFrom !== null) {
            $qb->andWhere('u.createdAt >= :createdFrom')->setParameter('createdFrom', $filters->createdFrom);
        }

        if ($filters->createdTo !== null) {
            $qb->andWhere('u.createdAt <= :createdTo')->setParameter('createdTo', $filters->createdTo);
        }

        if ($filters->interestIds !== []) {
            $qb->andWhere('i.id IN (:interestIds)')->setParameter('interestIds', $filters->interestIds);
        }

        if ($filters->interestName !== null) {
            $qb->andWhere('i.name LIKE :interestName')->setParameter('interestName', '%' . $filters->interestName . '%');
        }

        $offset = max(0, ($page - 1) * $limit);
        $qb->orderBy('u.id', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }
}
