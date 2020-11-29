<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Faker\Factory;

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
     * @param Post $post
     * @return Query
     */
    public function findExcept(Post $post): Query
    {
        return $this->createQueryBuilder('p')
            ->where('p.id != :id')
            ->setParameter('id', $post->getId())
            ->orderBy('p.id', 'DESC')
            ->getQuery()
        ;
    }

    /**
     * RETURN ALL THE POSTS EXCEPT THE POST GIVEN IN PARAMETER
     *
     * @param Post $post
     * @return Post
     * @throws NonUniqueResultException
     */
    public function findSameCategoryExcept(Post $post):? Post
    {
        $faker = Factory::create();
        return $this->createQueryBuilder('p')
            ->andWhere('p.id != :val and p.category = :cat')
            ->setParameters(['val' => $post->getId(), 'cat' => $post->getCategory()])
            ->orderBy('p.id', $faker->randomElement(['ASC', 'DESC']))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * RETURN MAY LIKE POSTS
     *
     * @param Post $post
     * @param Post|null $relatedPost
     * @return array
     */
    public function findMayLikePosts(Post $post, ?Post $relatedPost):? array
    {
        $faker = Factory::create();
        return $this->createQueryBuilder('p')
            ->where('p.id != :pId and p.id != :rId')
            ->setParameters(['pId' => $post->getId(), 'rId' => $relatedPost->getId()])
            ->orderBy('p.id', $faker->randomElement(['ASC', 'DESC']))
            ->setMaxResults(5)
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
