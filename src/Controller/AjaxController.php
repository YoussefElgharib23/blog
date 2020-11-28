<?php

namespace App\Controller;

use App\Entity\Dislike;
use App\Entity\Like;
use App\Entity\Notification;
use App\Entity\Post;
use App\Repository\CategoryRepository;
use App\Repository\DislikeRepository;
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
 * @IsGranted ("ROLE_USER")
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
    /**
     * @var DislikeRepository
     */
    private DislikeRepository $dislikeRepository;

    public function __construct(DislikeRepository $dislikeRepository, EntityManagerInterface $entityManager, LikeRepository $likeRepository, NotificationRepository $notificationRepository, CategoryRepository $repository, PostRepository $postRepository)
    {
        $this->repository = $repository;
        $this->postRepository = $postRepository;
        $this->notificationRepository = $notificationRepository;
        $this->likeRepository = $likeRepository;
        $this->entityManager = $entityManager;
        $this->dislikeRepository = $dislikeRepository;
    }

    /**
     *
     * @Route("/{slug}-{id}/like", name="post_like_ajax", methods={"GET", "POST"}, requirements={"id": "\d+", "slug": "[a-z0-9\-]*"})
     * @param Post $post
     * @return JsonResponse
     */
    public function likePost(Post $post)
    {
        $jsonArrayReturn = [
            'code'         => 'success',
            'message'      => '+1',
            'likeCount'    => 0,
            'dislikeCount' => 0,
            'isDisliked' => false
        ];
        $user = $this->getUser();
        if ( $post->isLikedByUser($user) ) {
            $userLike = $this->likeRepository->findOneBy(['User' => $user]);
            $post->removeLike($userLike);

            $this->entityManager->remove($userLike);
            $this->entityManager->flush();

            $jsonArrayReturn['likeCount'] = $post->getLikes()->count();
            $jsonArrayReturn['message'] = '-1';
        }
        else {
            $like = new Like();
            $like
                ->setUser($user)
                ->setPost($post)
            ;
            $post->addLike($like);
            if ( $post->isDislikedByUser($user) ) {
                $userDislike = $this->dislikeRepository->findOneBy(['User' => $user, 'Post' => $post]);
                $post->removeDislike($userDislike);
                $this->entityManager->remove($userDislike);
                $jsonArrayReturn['isDisliked'] = true;
                $jsonArrayReturn['dislikeCount'] = $post->getDislikes()->count();
            }
            $this->entityManager->persist($like);
            if ( !in_array('ROLE_ADMIN', $user->getRoles()) ) {
                $this->entityManager->persist(
                    (new Notification())
                        ->setIsViewed(false)
                        ->setPost($post)
                        ->setUser($user)
                        ->setDescription(' liked the post ')
                );
            }
            $this->entityManager->flush();

            $jsonArrayReturn['message'] = '+1';
            $jsonArrayReturn['likeCount'] = $post->getLikes()->count();
        }
        return $this->json($jsonArrayReturn, 200);
    }

    //DISLIKE POST
    /**
     * @Route("/{slug}-{id}/dislike", name="post_dislike_ajax", methods={"GET", "POST"}, requirements={"id": "\d+", "slug": "[a-z0-9\-]*"})
     * @param Post $post
     * @return JsonResponse
     */
    public function dislikePost(Post $post)
    {
        $jsonArrayReturn = [
            'status' => 'success',
            'message' => '+1',
            'isLiked' => false,
            'dislikeCount' => 0,
            'likeCount' => 0
        ];
        $user = $this->getUser();
        $userDislike = (new Dislike())->setUser($user)->setPost($post);
        if ( $post->isDislikedByUser($user)) {
            $userDislike = $this->dislikeRepository->findOneBy(['User' => $user, 'Post' => $post]);
            $post->removeDislike($userDislike);

            $this->entityManager->remove($userDislike);
            $this->entityManager->flush();

            $jsonArrayReturn['message'] = '-1';
            $jsonArrayReturn['dislikeCount'] = $post->getDislikes()->count();
        }
        else {
            $post->addDislike($userDislike);
            if ( $post->isLikedByUser($user) ) {
                $userLike = $this->likeRepository->findOneBy(['User' => $user, 'post' => $post]);
                $post->removeLike($userLike);
                $this->entityManager->remove($userLike);
                $jsonArrayReturn['isLiked']  = true;
            }
            $this->entityManager->persist($userDislike);
            $jsonArrayReturn['dislikeCount'] = $post->getDislikes()->count();
            $jsonArrayReturn['likeCount'] = $post->getLikes()->count();
        }
        $this->entityManager->flush();
        return $this->json($jsonArrayReturn);
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
     * @Route("/notification/count", methods={"POST"}, name="admin_notification_count_ajax")
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

    /**
     * @Route("/ajax/posts/get/all", name="post_ajax", methods={"GET", "POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function searchPost(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $title = trim($data['PostTitle']);
        $posts = $this->postRepository->findBy([], ['created_at' => 'DESC']);
        $foundedPosts = [];
        if ($title !== '') {
            $title = strtolower($title);
            $len = strlen($title);
            foreach ($posts as $post)
            {
                if (stristr($title, substr(strtolower($post->getTitle()), 0, $len))) $foundedPosts[] = $post;
            }

            return $this->json($foundedPosts, 200, [], ['groups' => 'post:ajax']);
        }

        if ( count($foundedPosts) === 0 ) {
            $foundedPosts = $posts;
        }

        return $this->json($foundedPosts , 200, [], ['groups' => 'post:ajax']);
    }
}
