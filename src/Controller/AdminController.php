<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Post;
use App\Form\CategoryFormType;
use App\Form\PostFormType;
use App\Repository\CategoryRepository;
use App\Repository\CommentRepository;
use App\Repository\IpRepository;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use MercurySeries\FlashyBundle\FlashyNotifier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
    private EntityManagerInterface $em;
    /**
     * @var CategoryRepository
     */
    private CategoryRepository $categoryRepository;
    /**
     * @var PostRepository
     */
    private PostRepository $postRepository;
    /**
     * @var FlashyNotifier
     */
    private FlashyNotifier $flashyNotifier;
    /**
     * @var IpRepository
     */
    private IpRepository $ipRepository;
    /**
     * @var CommentRepository
     */
    private CommentRepository $commentRepository;

    public function __construct(CommentRepository $commentRepository, IpRepository $ipRepository, FlashyNotifier $flashyNotifier, EntityManagerInterface $em, CategoryRepository $categoryRepository, PostRepository $postRepository)
    {
        $this->em = $em;
        $this->categoryRepository = $categoryRepository;
        $this->postRepository = $postRepository;
        $this->flashyNotifier = $flashyNotifier;
        $this->ipRepository = $ipRepository;
        $this->commentRepository = $commentRepository;
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
        foreach ( $posts as $post) $views += $post->getViews();
        return $this->render('admin/index.html.twig', [
            'posts' => $posts,
            'categories' => $categories,
            'postForm' => $postForm->createView(),
            'categoryForm' => $categoryForm->createView(),
            'action' => $action,
            'cm' => 'dashboard',
            'PostCount' => $count,
            'commentCount' => count($this->commentRepository->findAll()),
            'views' => $views
        ]);
    }

    /**
     * @Route("/{id}/edit", name="edit_post", methods={"GET", "PATCH"}, requirements={"id": "\d+"})
     * @param Request $request
     * @param Post $post
     * @return Response
     */
    public function editPost(Request $request, Post $post)
    {
        $this->denyAccessUnlessGranted('edit', $post);
        $formPost = $this->createForm(PostFormType::class, $post, [
            'method' => 'PATCH'
        ]);

        $formPost->handleRequest($request);

        if ( $formPost->isSubmitted() && $formPost->isValid() ) {
            $this->em->flush();

            $this->flashyNotifier->success('The post was updated with successfully !');

            return $this->redirectToRoute('app_client_show_post', [
                'id'   => $post->getId(),
                'slug' => $post->getSlug(),
            ]);
        }

        return $this->render('admin/post/edit.html.twig', [
            'form' => $formPost->createView(),
            'post' => $post,
        ]);
    }

    /**
     * @Route("/admin/{id}/delete", name="post_delete", methods={"GET"}, requirements={"id": "\d+"})
     * @param Post $post
     * @return RedirectResponse
     */
    public function deletePost(Post $post)
    {
        $this->denyAccessUnlessGranted('delete', $post);
        $this->em->remove($post);
        $this->em->flush();

        $this->flashyNotifier->success('The post was deleted with successfully !');
        return $this->redirectToRoute('app_client_index');
    }
}