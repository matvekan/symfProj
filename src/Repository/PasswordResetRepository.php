<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\PasswordReset;
use App\Repository\Contract\PasswordResetRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PasswordReset>
 */
class PasswordResetRepository extends ServiceEntityRepository implements PasswordResetRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em, ManagerRegistry $registry)
    {
        parent::__construct($registry, PasswordReset::class);
    }

    public function store(PasswordReset $passwordReset, bool $isFlush = true): PasswordReset
    {
        $this->em->persist($passwordReset);
        if ($isFlush) {
            $this->em->flush();
        }

        return $passwordReset;
    }

    public function findValidByToken(string $token): ?PasswordReset
    {
        $result = $this->createQueryBuilder('pr')
            ->andWhere('pr.token = :token')
            ->andWhere('pr.usedAt IS NULL')
            ->andWhere('pr.expiresAt > :now')
            ->setParameter('token', $token)
            ->setParameter('now', new \DateTimeImmutable())
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $result instanceof PasswordReset ? $result : null;
    }
}
