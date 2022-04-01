<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Article $entity, bool $flush = true): void
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
    public function remove(Article $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }
    /**
     * @return Article[] Returns an array of Article objects
    */
    public function findVisible($limit)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.visible = :val')
            ->setParameter('val', true)
            ->setMaxResults($limit)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Article[] Returns an array of Trending Article objects
    */
    public function findTrending($limit)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.trending = :trending')
            ->setParameter('trending', true)
            ->setMaxResults($limit)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Article[] Returns an array of Popular Article objects
    */
    public function findPopular($limit)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.popular = :popular')
            ->setParameter('popular', true)
            ->setMaxResults($limit)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }
    /**
     * @return Article[] Returns an array of Popular Article objects
    */
    public function search($query, $visible = null)
    {
        $visibility = ($visible) ? $visible : true;
        return $this->createQueryBuilder('a')
        ->andWhere('a.titre LIKE :query OR a.intro LIKE :query OR a.content LIKE :query')
        ->andWhere('a.visible = :visible')
        ->setParameter('query', '%' . $query . '%')
        ->setParameter('visible', $visibility)
        ->orderBy('a.createdAt', 'DESC')
        ->getQuery()
        ->getResult()
    ;

    }

}
