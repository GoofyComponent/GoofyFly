<?php

namespace App\Repository;

use App\Entity\FtpCredentials;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FtpCredentials>
 *
 * @method FtpCredentials|null find($id, $lockMode = null, $lockVersion = null)
 * @method FtpCredentials|null findOneBy(array $criteria, array $orderBy = null)
 * @method FtpCredentials[]    findAll()
 * @method FtpCredentials[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FtpCredentialsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FtpCredentials::class);
    }

    public function save(FtpCredentials $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(FtpCredentials $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return FtpCredentials[] Returns an array of FtpCredentials objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('f.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?FtpCredentials
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
