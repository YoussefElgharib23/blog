<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Post;
use App\Repository\CategoryRepository;
use App\Repository\NotificationRepository;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ClientController extends AbstractController
{
    /**
     * @var PostRepository
     */
    private PostRepository $postRepository;
    /**
     * @var CategoryRepository
     */
    private CategoryRepository $categoryRepository;
    /**
     * @var NotificationRepository
     */
    private NotificationRepository $notificationRepository;

    public function __construct(PostRepository $postRepository, CategoryRepository $categoryRepository, NotificationRepository $notificationRepository)
    {
        $this->postRepository = $postRepository;
        $this->categoryRepository = $categoryRepository;
        $this->notificationRepository = $notificationRepository;
    }

    private const TEMPLATE_PATH_CLIENT = 'client/';
    /**
     * @Route("/", name="app_client_index", methods={"GET"})
     * @return string
     */
    public function index()
    {
        $countNotification = null;
        if ( $this->getUser() AND in_array('ROLE_ADMIN', $this->getUser()->getRoles()) ) {
            $countNotification = count($this->notificationRepository->findBy(['IsViewed' => false]));
        }
        $firstPost = $this->postRepository->findOneBy([], ['id' => 'DESC']);
        $posts = $this->postRepository->findExcept($firstPost);
        return $this->render(self::TEMPLATE_PATH_CLIENT.'/index.html.twig', [
            'firstPost'         => $firstPost,
            'posts'             => $posts,
            'countNotification' => $countNotification
        ]);
    }

    /**
     * @Route("/{slug}-{id}", name="app_client_show_post", methods={"GET"}, requirements={"id": "\d+", "slug": "[a-z0-9\-]*"})
     * @param Post $post
     * @param string $slug
     * @return Response
     */
    public function showPost(Post $post, string $slug): Response
    {
        if ( $slug !== $post->getSlug()) return $this->redirectToRoute('app_client_show_post', ['id' => $post->getId(), 'slug' => $post->getSlug()]);
        // GET PREVIOUS, NEXT POST
        $previousPost = $this->postRepository->findOneBy(['id' => $post->getId() - 1]);
        $nextPost = $this->postRepository->findOneBy(['id' => $post->getId() + 1]);

        // GET ONE THE POST WHICH HAS THE SAME CATEGORY RANDOMLY
        $firstPostId = $this->postRepository->findOneBy([])->getId();
        $lastPostId = $this->postRepository->findOneBy([], ['id' => 'DESC'])->getId();
        $relatedPost = $post;
        while ( $relatedPost === $post OR $relatedPost === null ) {
            $relatedPost = $this->postRepository->findOneBy(['id' => mt_rand($firstPostId, $lastPostId), 'category' => $post->getCategory()]);
        }

        // GET ALL THE CATEGORIES
        $categories = $this->categoryRepository->findAll();

        // GET MAY LIKE POST
        $fId = $this->postRepository->findOneBy([])->getId();
        $lId = $this->postRepository->findOneBy([], ['id' => 'DESC'])->getId();
        $ids = [];
        $mayLikePosts = [];
        for ($i = 0; $i < 5; $i++) {
        sfi:
            $id = $this->postRepository->findOneBy(['id' => mt_rand($fId, $lId)])->getId();
            if ( in_array($id, $ids) OR $id === $post->getId() OR $id === $relatedPost->getId() ) goto sfi;
            $ids[] = $id;
            $mayLikePosts[] = $this->postRepository->findOneBy(['id' => $id]);
        }

        return $this->render('client/show.html.twig', [
            'post' => $post,
            'previousPost' => $previousPost,
            'nextPost' => $nextPost,
            'relatedPost' => $relatedPost,
            'categories' => $categories,
            'mayLikePosts' => $mayLikePosts
        ]);
    }

    /**
     * FIND ALL POSTS UNDER THE GIVEN CATEGORY
     *
     * @Route("/{slug<[a-z\-]+>}/search", name="app_client_posts_category_search", methods={"GET"})
     * @param Category $category
     * @return Response
     */
    public function findPostsSubCategory(Category $category): Response
    {
        $posts = $this->postRepository->findBy(['category' => $category]);
        return $this->render('client/search.html.twig', [
            'posts'    => $posts,
            'category' => $category
        ]);
    }
}
