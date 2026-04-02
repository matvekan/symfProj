<?php

namespace App\Repository;

use App\Entity\PasswordReset;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PasswordReset>
 */
class PasswordResetRepository extends ServiceEntityRepository
{
    public function __construct(private EntityManagerInterface $em, ManagerRegistry $registry)
    {
        parent::__construct($registry, PasswordReset::class);
    }

    //    /**
    //     * @return PasswordReset[] Returns an array of PasswordReset objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

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
        return $this->createQueryBuilder('pr')
            ->andWhere('pr.token = :token')
            ->andWhere('pr.usedAt IS NULL')
            ->andWhere('pr.expiresAt > :now')
            ->setParameter('token', $token)
            ->setParameter('now', new \DateTimeImmutable())
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
