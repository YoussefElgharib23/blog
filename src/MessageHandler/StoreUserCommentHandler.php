<?php


namespace App\MessageHandler;


use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use App\Message\StoreUserComment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class StoreUserCommentHandler implements MessageHandlerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function __invoke(StoreUserComment $userComment)
    {
        $user = $this->entityManager->find(User::class, $userComment->getUserId());
        $post = $this->entityManager->find(Post::class, $userComment->getPostId());
        $comment = new Comment();
        $comment
            ->setUser($user)
            ->setPost($post)
            ->setContent($userComment->getCommentContent())
        ;
        $this->entityManager->persist($comment);
        $this->entityManager->flush();
    }
}