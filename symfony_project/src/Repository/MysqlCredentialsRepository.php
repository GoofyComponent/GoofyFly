<?php

namespace App\Repository;

use App\Entity\MysqlCredentials;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MysqlCredentials>
 *
 * @method MysqlCredentials|null find($id, $lockMode = null, $lockVersion = null)
 * @method MysqlCredentials|null findOneBy(array $criteria, array $orderBy = null)
 * @method MysqlCredentials[]    findAll()
 * @method MysqlCredentials[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MysqlCredentialsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MysqlCredentials::class);
    }

    public function save(MysqlCredentials $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MysqlCredentials $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return MysqlCredentials[] Returns an array of MysqlCredentials objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('m.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?MysqlCredentials
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
