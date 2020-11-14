<?php

namespace App\Controller;

use App\Entity\Like;
use App\Entity\Notification;
use App\Entity\Post;
use App\Repository\CategoryRepository;
use App\Repository\LikeRepository;
use App\Repository\NotificationRepository;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AjaxController
 * @package App\Controller
 * @IsGranted ("ROLE_ADMIN")
 */
class AjaxController extends AbstractController
{
    /**
     * @var CategoryRepository
     */
    private CategoryRepository $repository;
    /**
     * @var PostRepository
     */
    private PostRepository $postRepository;
    /**
     * @var NotificationRepository
     */
    private NotificationRepository $notificationRepository;
    /**
     * @var LikeRepository
     */
    private LikeRepository $likeRepository;
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager, LikeRepository $likeRepository, NotificationRepository $notificationRepository, CategoryRepository $repository, PostRepository $postRepository)
    {
        $this->repository = $repository;
        $this->postRepository = $postRepository;
        $this->notificationRepository = $notificationRepository;
        $this->likeRepository = $likeRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * RETURN ALL THE CATEGORY BY THE TITLE
     *
     * @param Request $request
     * @Route("/category/find", name="app_ajax_search_category" ,methods={"POST"})
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $name = NULL !== json_decode($request->getContent(), true) ? json_decode($request->getContent(), true)['Name'] : $request->request->get('name');
        $categories = $this->repository->findAll();
        $foundCategory = [];
        if ($name !== "") {
            $name = strtolower($name);
            $len = strlen($name);
            foreach($categories as $category) {
                if (stristr($name, substr(strtolower($category->getName()), 0, $len)) AND $len <= strlen($category->getName())) {
                    $foundCategory[] = $category;
                }
            }
        }
        elseif (trim($name) === '') {
            $foundCategory = $categories;
        }

        return $this->json($foundCategory, 200, [], ['groups' => "category:search"]);
    }

    /**
     * FIND THE POSTS BY THE NAME
     *
     * @Route("/post/find", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function searchPosts(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $title = $data['title'];
        $posts = $this->postRepository->findAll();
        $foundedPosts = [];
        if (trim($title) !== '') {
            $title = strtolower($title);
            $len = strlen($title);
            foreach ($posts as $post)
            {
                if (stristr($title, substr(strtolower($post->getTitle()), 0, $len))) $foundedPosts[] = $post;
            }

            return $this->json($foundedPosts , 200, [], ['groups' => 'post:ajax']);
        }

        return $this->json($posts , 200, [], ['groups' => 'post:ajax']);
    }

    /**
     * @Route("/admin/notifcations/get", name="get_notifications", methods={"POST"})
     * @return JsonResponse
     */
    public function getNotifications(): JsonResponse
    {
        $notifications = $this->notificationRepository->findBy(['IsViewed' => false], ['id' => 'DESC']);
        $todayNotifications = [];
        $previousNotifications = [];
        foreach ($notifications as $notification) {
            if (date_format($notification->getCreatedAt(), 'd') === date('d')) $todayNotifications[] = $notification;
            else $previousNotifications = $notification;
        }
        return $this->json(['notViewedNotifications' => $notifications, 'previousNotifications' => $previousNotifications, 'todayNotifications' => $todayNotifications]);
    }

    /**
     *
     * @Route("/{slug}-{id}/like", methods={"GET", "POST"}, requirements={"id": "\d+", "slug": "[a-z0-9\-]*"})
     * @param Post $post
     * @return JsonResponse
     */
    public function likePost(Post $post)
    {
        $user = $this->getUser();
        if ( $post->isLikedByUser($user) ) {
            $userLike = $this->likeRepository->findOneBy(['User' => $user]);
            $post->removeLike($userLike);

            $this->entityManager->remove($userLike);
            $this->entityManager->flush();

            return $this->json([
                'code'      => 'success',
                'message'   => '-1',
                'likeCount' => count($post->getLikes())
            ], 200);
        }
        else {
            $like = new Like();
            $like
                ->setUser($user)
                ->setPost($post)
            ;
            $post->addLike($like);

            $this->entityManager->persist($like);
            $this->entityManager->flush();
        }
        return $this->json([
            'code'      => 'success',
            'message'   => '+1',
            'likeCount' => count($post->getLikes()),
        ], 200);
    }

    /**
     * @Route("/notification/count", methods={"POST"})
     * @return JsonResponse
     */
    public function getNotificationCount(): JsonResponse
    {
        $notifications = $this->notificationRepository->findBy(['IsViewed' => false]);
        return $this->json(['count' => count($notifications)]);
    }

    /**
     * @Route("/notifications/get", methods={"POST"})
     * @return JsonResponse
     */
    public function getUnseenNotifications(): JsonResponse
    {
        $notifications =$this->notificationRepository->findBy([], ['createdAt' => 'DESC']);
        $returnedNotifications = [];
        foreach ($notifications as $notification)
            if (date_format($notification->getCreatedAt(), 'd') === date('d'))
                $returnedNotifications[] = $notification;
        return $this->json($returnedNotifications
            , 200, [], [
            'groups' => 'ajax_notifications'
        ]);
    }
}
