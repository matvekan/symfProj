<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(private EntityManagerInterface $em,ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    //    /**
    //     * @return User[] Returns an array of User objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?User
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function store(User $user, $isFlush=true):User{
        $this->em->persist($user);
        if($isFlush){
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

    public function findByAdminFilters(array $filters = [], int $page = 1, int $limit = 20): array
    {
        $qb = $this->createQueryBuilder('u')
            ->leftJoin('u.interest', 'i')
            ->addSelect('i');

        if (!empty($filters['name'])) {
            $qb->andWhere('u.name LIKE :name')->setParameter('name', '%' . $filters['name'] . '%');
        }

        if (!empty($filters['email'])) {
            $qb->andWhere('u.email LIKE :email')->setParameter('email', '%' . $filters['email'] . '%');
        }

        if (!empty($filters['role'])) {
            $qb->andWhere('u.role = :role')->setParameter('role', strtoupper((string) $filters['role']));
        }

        if (!empty($filters['createdFrom'])) {
            $qb->andWhere('u.createdAt >= :createdFrom')->setParameter('createdFrom', new \DateTimeImmutable((string) $filters['createdFrom']));
        }

        if (!empty($filters['createdTo'])) {
            $qb->andWhere('u.createdAt <= :createdTo')->setParameter('createdTo', new \DateTimeImmutable((string) $filters['createdTo']));
        }

        if (!empty($filters['interestIds']) && is_array($filters['interestIds'])) {
            $qb->andWhere('i.id IN (:interestIds)')->setParameter('interestIds', $filters['interestIds']);
        }

        if (!empty($filters['interestName'])) {
            $qb->andWhere('i.name LIKE :interestName')->setParameter('interestName', '%' . $filters['interestName'] . '%');
        }

        $offset = max(0, ($page - 1) * $limit);
        $qb->orderBy('u.id', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }
}
