<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Post;
use App\Form\CategoryFormType;
use App\Form\PostFormType;
use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use MercurySeries\FlashyBundle\FlashyNotifier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AdminController
 * @package App\Controller
 * @Route("/admin", name="app_admin_")
 */
class AdminController extends AbstractController
{
    const INDEX_ROUTE = 'app_admin_index';

    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var CategoryRepository
     */
    private $categoryRepository;
    /**
     * @var PostRepository
     */
    private $postRepository;
    /**
     * @var FlashyNotifier
     */
    private $flashyNotifier;

    public function __construct(FlashyNotifier $flashyNotifier, EntityManagerInterface $em, CategoryRepository $categoryRepository, PostRepository $postRepository)
    {
        $this->em = $em;
        $this->categoryRepository = $categoryRepository;
        $this->postRepository = $postRepository;
        $this->flashyNotifier = $flashyNotifier;
    }

    /**
     * @Route("/", name="index", methods={"GET", "POST"})
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $post     = ( new Post() )->setViews(0);
        $category = new Category();

        $posts        = $this->postRepository->findBy([], ['created_at' => 'DESC']);
        $categories   = $this->categoryRepository->findBy([], ['created_at' => 'DESC']);
        $postForm     = $this->createForm(PostFormType::class, $post);
        $categoryForm = $this->createForm(CategoryFormType::class, $category);

        $postForm->handleRequest($request);
        $categoryForm->handleRequest($request);

        if ($postForm->isSubmitted() AND $postForm->isValid())
        {
            $this->em->persist($post);
            $this->flashyNotifier->success('The post was created with success');
            $this->em->flush();

            return $this->redirectToRoute(self::INDEX_ROUTE);
        }

        return $this->render('admin/index.html.twig', [
            'posts' => $posts,
            'categories' => $categories,
            'postForm' => $postForm->createView(),
            'categoryForm' => $categoryForm->createView()
        ]);
    }

    /**
     * SHOW THE POST BY ID
     *
     * @Route("/posts/{slug}-{id}", name="post_show", methods={"GET"}, requirements={"id": "\d+", "slug": "[a-z0-9\-]*"})
     * @param Post $post
     * @return Response
     * @throws NonUniqueResultException
     */
    public function showPost(Post $post): Response
    {
        $relatedPost = $this->postRepository->findRelatedPost($post);
        $categories = $this->categoryRepository->findAll();
        $previousPosts = $this->postRepository->previousPosts($post, $relatedPost);
        return $this->render('admin/show_post.html.twig', [
            'post' => $post,
            'relatedPost' => $relatedPost,
            'categories' => $categories,
            'previousPosts' => $previousPosts
        ]);
    }

    /**
     *
     * @Route("/{id}/delete", name="post_delete", methods={"DELETE"}, requirements={"id": "\d+"})
     * @param Request $request
     * @param Post $post
     * @return Response
     */
    public function deletePost(Request $request, Post $post): Response
    {
        if ($this->isCsrfTokenValid('delete_post' . $post->getId(), $request->request->get('_token')))
        {
            $this->em->remove($post);
            $this->em->flush();

            $this->flashyNotifier->success('The post was deleted with success');
        }
        return $this->redirectToRoute(self::INDEX_ROUTE);
    }
}
