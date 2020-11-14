<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class CommentAjaxController extends AbstractController
{
    /**
     * @var PostRepository
     */
    private PostRepository $postRepository;
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;
    /**
     * @var CommentRepository
     */
    private CommentRepository $commentRepository;

    public function __construct(PostRepository $postRepository, EntityManagerInterface $entityManager, CommentRepository $commentRepository)
    {
        $this->postRepository = $postRepository;
        $this->entityManager = $entityManager;
        $this->commentRepository = $commentRepository;
    }

    /**
     *
     * @Route("/comment/post", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function commentOnPost(Request $request)
    {
        if ( !in_array('ROLE_USER', $this->getUser()->getRoles()) ) return $this->json([
            'status' => 'error',
            'message' => 'You cannot comment to this post',
        ], 403);

        $data = json_decode($request->getContent(), true);
        $post = $this->postRepository->findOneBy(['id' => $data['postId']]);
        $user = $this->getUser();
        $comment = new Comment();
        $comment
            ->setPost($post)
            ->setUser($user)
            ->setContent($data['commentContent'])
        ;
        $this->entityManager->persist($comment);
        $this->entityManager->flush();
        return $this->json([
            'status' => 'success',
            'message' => 'The comment was posted with success',
            'action' => '+1'
        ]);
    }

    /**
     *
     * @Route("/comment/get", methods={"get"})
     */
    public function getComments()
    {
        $comments = $this->commentRepository->findAll();
        return $this->json($comments, 200, [], [
            'groups' => 'ajax:comment'
        ]);
    }
}
