<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Ip;
use App\Entity\Post;
use App\Form\CategoryFormType;
use App\Form\PostFormType;
use App\Repository\CategoryRepository;
use App\Repository\IpRepository;
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
    /**
     * @var IpRepository
     */
    private $ipRepository;

    public function __construct(IpRepository $ipRepository, FlashyNotifier $flashyNotifier, EntityManagerInterface $em, CategoryRepository $categoryRepository, PostRepository $postRepository)
    {
        $this->em = $em;
        $this->categoryRepository = $categoryRepository;
        $this->postRepository = $postRepository;
        $this->flashyNotifier = $flashyNotifier;
        $this->ipRepository = $ipRepository;
    }

    /**
     * GET ALL THE POSTS OR GET THE POST BY THE TITLE GIVEN IN THE SEARCH BOX
     *
     * @Route("/", name="index", methods={"GET", "POST"})
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $post     = ( new Post() )->setViews(0);
        $category = new Category();

        $posts = [];
        $action['action'] = 'request';
        if ($request->getMethod() === 'POST' AND $request->request->has('_title') && trim($request->request->get('_title')) !== '') {
            $titleSearch = strtolower(trim($request->request->get('_title')));
            $posts = $this->postRepository->findBy(['title' => $titleSearch]);
            $action['action'] = 'search';
            $action['search_var'] = $request->request->get('_title');
            if (count($posts) === 0) {
                $this->flashyNotifier->error('No results found you can create new one !');
                return $this->redirectToRoute(self::INDEX_ROUTE);
            }
        }
        elseif (trim($request->request->get('_title')) === '' OR $request->getMethod() !== 'POST') {
            $posts = $this->postRepository->findBy([], ['created_at' => 'DESC']);
        }
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

        if ($categoryForm->isSubmitted() AND $categoryForm->isValid()) {
            $this->em->persist($category);
            $this->em->flush();
            $this->flashyNotifier->success('The category was created with successfully');

            return $this->redirectToRoute(self::INDEX_ROUTE);
        }

        // FOR STATISTICS PANEL
        $count = $this->postRepository->count([]);
        $views = 0;
        foreach ($this->postRepository->findAll() as $post) $views += $post->getViews();

        return $this->render('admin/index.html.twig', [
            'posts' => $posts,
            'categories' => $categories,
            'postForm' => $postForm->createView(),
            'categoryForm' => $categoryForm->createView(),
            'action' => $action,
            'cm' => 'dashboard',
            'PostCount' => $count,
            'views' => $views
        ]);
    }

    /**
     * SHOW THE POST BY ID
     *
     * @Route("/posts/{slug}-{id}", name="post_show", methods={"GET", "POST"}, requirements={"id": "\d+", "slug": "[a-z0-9\-]*"})
     * @param Request $request
     * @param Post $post
     * @return Response
     * @throws NonUniqueResultException
     */
    public function showPost(Request $request, Post $post): Response
    {
        if (NULL === $this->ipRepository->findOneBy(['ipAddress' => $request->getClientIp()])) {
            $post->incrementViews();
            $userType =  $this->isGranted('ROLE_ADMIN') ? 'ADMIN' : 'USER';
            $this->em->persist(( new Ip() )->setIpAddress($request->getClientIp())->setIpType($userType));
        }
        $this->em->flush();
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
