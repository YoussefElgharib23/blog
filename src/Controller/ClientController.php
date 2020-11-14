<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Ip;
use App\Entity\Notification;
use App\Entity\Post;
use App\Entity\User;
use App\Form\CommentTypeFormType;
use App\Repository\CategoryRepository;
use App\Repository\CommentRepository;
use App\Repository\IpRepository;
use App\Repository\NotificationRepository;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use MercurySeries\FlashyBundle\FlashyNotifier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;
    /**
     * @var FlashyNotifier
     */
    private FlashyNotifier $flashyNotifier;
    /**
     * @var CommentRepository
     */
    private CommentRepository $commentRepository;
    /**
     * @var IpRepository
     */
    private IpRepository $ipRepository;

    /**
     * ClientController constructor.
     * @param IpRepository $ipRepository
     * @param CommentRepository $commentRepository
     * @param FlashyNotifier $flashyNotifier
     * @param EntityManagerInterface $entityManager
     * @param PostRepository $postRepository
     * @param CategoryRepository $categoryRepository
     * @param NotificationRepository $notificationRepository
     */
    public function __construct(IpRepository $ipRepository, CommentRepository $commentRepository, FlashyNotifier $flashyNotifier, EntityManagerInterface $entityManager, PostRepository $postRepository, CategoryRepository $categoryRepository, NotificationRepository $notificationRepository)
    {
        $this->postRepository = $postRepository;
        $this->categoryRepository = $categoryRepository;
        $this->notificationRepository = $notificationRepository;
        $this->entityManager = $entityManager;
        $this->flashyNotifier = $flashyNotifier;
        $this->commentRepository = $commentRepository;
        $this->ipRepository = $ipRepository;
    }

    private const TEMPLATE_PATH_CLIENT = 'client/';
    /**
     * @Route("/", name="app_client_index", methods={"GET"})
     * @return string
     */
    public function index()
    {
        $firstPost = $this->postRepository->findOneBy([], ['id' => 'DESC']);
        $posts = $this->postRepository->findExcept($firstPost);
        return $this->render(self::TEMPLATE_PATH_CLIENT.'/index.html.twig', [
            'firstPost'         => $firstPost,
            'posts'             => $posts
        ]);
    }

    /**
     * @Route("/{slug}-{id}", name="app_client_show_post", methods={"GET", "POST"}, requirements={"id": "\d+", "slug": "[a-z0-9\-]*"})
     * @param Request $request
     * @param Post $post
     * @param string $slug
     * @return Response
     */
    public function showPost(Request $request, Post $post, string $slug): Response
    {
        if ( $slug !== $post->getSlug() ) return $this->redirectToRoute('app_client_show_post', ['id' => $post->getId(), 'slug' => $post->getSlug()]);

        // ADD THE VIEWS ALSO THE CLIENT IP TO THE DATABASE AND STORE THE CHANGES
        if ( $this->getUser() AND in_array('ROLE_USER', $this->getUser()->getRoles()) ) {
            $post->setViews( $post->getViews() + 1 );
            $this->entityManager->persist($post);

            $ip = ( new Ip() )->setIpType('USER')->setIpAddress( $request->getClientIp() );
            if ( NULL === $this->ipRepository->findOneBy(['ipAddress' => $ip->getIpAddress()]) ) {
                $this->entityManager->persist($ip);
            }
            $this->entityManager->flush();
        }

        // IF A USER IS CONNECTED CREATE NEW COMMENT FORM AND PASS IT TO THE VIEW
        $currentUser = $this->getUser();
        if ( $currentUser !== NULL ) {
            $comment = ( new Comment() )->setPost($post)->setUser($currentUser);
            $formComment = $this->createForm(CommentTypeFormType::class, $comment);
        }

        if ( isset($formComment) ) $formComment->handleRequest($request);

        if ( isset($formComment) && $formComment->isSubmitted() && $formComment->isValid() ) {
            $this->entityManager->persist($comment);

            if ( in_array('ROLE_USER', $currentUser->getRoles()) ) {
                $notification = new Notification();
                $notification
                    ->setIsViewed(false)
                    ->setUser($currentUser)
                    ->setPost($post)
                    ->setDescription(' commented on post')
                ;
                $this->entityManager->persist($notification);
            }

            $this->entityManager->flush();
            $this->flashyNotifier->success('The comment was posted with successfully !');

            return $this->redirectToRoute('app_client_show_post', [
                'id' => $post->getId(),
                'slug' => $post->getSlug()
            ]);
        }

        $postComments = $this->commentRepository->findBy(['post' => $post], ['id' => 'DESC']);

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
        $ids = [];
        $mayLikePosts = [];
        for ($i = 0; $i < 5; $i++) {
        sfi:
            $id = $this->postRepository->findOneBy(['id' => mt_rand($firstPostId, $lastPostId)])->getId();
            if ( in_array($id, $ids) OR $id === $post->getId() OR $id === $relatedPost->getId() ) goto sfi;
            $ids[] = $id;
            $mayLikePosts[] = $this->postRepository->findOneBy(['id' => $id]);
        }

        // DELETE COMMENT
        if ( $request->getMethod() === 'POST' and $this->isCsrfTokenValid('delete_comment', $request->request->get('_token')) )
        {
            $commentToDelete = $this->commentRepository->find($request->request->get('_comment_id'));
            $notifications = $this->notificationRepository->findBy(['Post' => $commentToDelete->getPost(), 'User' => $commentToDelete->getUser()]);
            $this->denyAccessUnlessGranted('deleteComment', $commentToDelete);
            $this->entityManager->remove($commentToDelete);
            foreach ($notifications as $notification) {
                $this->entityManager->remove($notification);
            }
            $this->entityManager->flush();

            $this->flashyNotifier->success('The comment was deleted with success !');

            return $this->redirectToRoute('app_client_show_post', [
                'id'   => $post->getId(),
                'slug' => $post->getSlug()
            ]);
        }

        return $this->render('client/show.html.twig', [
            'post'         => $post,
            'previousPost' => $previousPost,
            'nextPost'     => $nextPost,
            'relatedPost'  => $relatedPost,
            'categories'   => $categories,
            'mayLikePosts' => $mayLikePosts,
            'formComment'  => isset($formComment) ? $formComment->createView() : null,
            'postComment'  => $postComments
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
