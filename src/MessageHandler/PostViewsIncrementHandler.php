<?php

namespace App\MessageHandler;


use App\Entity\Post;
use App\Message\PostViewsIncerement;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class PostViewsIncrementHandler implements MessageHandlerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function __invoke(PostViewsIncerement $postViewsIncerement)
    {
        $post = $this->entityManager->find(Post::class, $postViewsIncerement->getPostId());
        $post->incrementViews();
        $this->entityManager->flush();
    }
}