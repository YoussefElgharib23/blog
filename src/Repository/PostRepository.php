<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    /**
     * RETURN ALL THE POSTS WITHOUT THE CURRENT ONE
     *
     * @param Post $post
     * @param Post $relatedPost
     * @return array | null
     */
    public function previousPosts(Post $post, Post $relatedPost):? array
    {
        return $this->createQueryBuilder('p')
            ->where('p.id != :id and p.id != :idR')
            ->setParameters(['id' => $post->getId(), 'idR' => $relatedPost->getId()])
            ->orderBy('p.created_at', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * RETURN ALL THE POSTS EXCEPT THE POST GIVEN IN PARAMETER
     *
     * @param Post $post
     * @return array|null
     */
    public function findExcept(Post $post):? array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.title != :val')
            ->setParameter('val', $post->getTitle())
            ->orderBy('p.id', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * FIND THE RELATED POST
     *
     * @param Post $post
     * @return Post
     * @throws NonUniqueResultException
     */
    public function findRelatedPost(Post $post):? Post
    {
        return $this->createQueryBuilder('p')
            ->where('p.category = :cat')
            ->andWhere('p.id != :id')
            ->setMaxResults(1)
            ->setParameter('cat', $post->getCategory())
            ->setParameter('id', $post->getId())
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    // /**
    //  * @return Post[] Returns an array of Post objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Post
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
