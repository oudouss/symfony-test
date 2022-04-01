<?php

namespace App\Repository;

use App\Entity\NewsLetter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method NewsLetter|null find($id, $lockMode = null, $lockVersion = null)
 * @method NewsLetter|null findOneBy(array $criteria, array $orderBy = null)
 * @method NewsLetter[]    findAll()
 * @method NewsLetter[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NewsLetterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NewsLetter::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(NewsLetter $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(NewsLetter $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return NewsLetter[] Returns an array of NewsLetter objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('n.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?NewsLetter
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
