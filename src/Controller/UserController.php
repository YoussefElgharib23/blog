<?php

namespace App\Controller;

use App\Entity\Report;
use App\Entity\User;
use App\Form\ReportFormType;
use App\Repository\CommentRepository;
use App\Repository\DislikeRepository;
use App\Repository\LikeRepository;
use App\Repository\NotificationRepository;
use App\Repository\ReportRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use MercurySeries\FlashyBundle\FlashyNotifier;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class UserController
 * @package App\Controller
 * @Route("/admin/users", name="app_admin_user_")
 * @IsGranted ("ROLE_ADMIN")
 */
class UserController extends AbstractController
{
    const STATUS = [
        'suspended',
        'deleted',
        'active'
    ];
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;
    /**
     * @var UserRepository
     */
    private UserRepository $userRepository;
    private FlashyNotifier $flashyNotifier;
    /**
     * @var ReportRepository
     */
    private ReportRepository $reportRepository;
    /**
     * @var CommentRepository
     */
    private CommentRepository $commentRepository;
    /**
     * @var LikeRepository
     */
    private LikeRepository $likeRepository;
    /**
     * @var DislikeRepository
     */
    private DislikeRepository $dislikeRepository;
    /**
     * @var NotificationRepository
     */
    private NotificationRepository $notificationRepository;

    public function __construct(NotificationRepository $notificationRepository, LikeRepository $likeRepository, DislikeRepository $dislikeRepository, CommentRepository $commentRepository, FlashyNotifier $flashyNotifier, EntityManagerInterface $entityManager, UserRepository $userRepository, ReportRepository $reportRepository)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->flashyNotifier = $flashyNotifier;
        $this->reportRepository = $reportRepository;
        $this->commentRepository = $commentRepository;
        $this->likeRepository = $likeRepository;
        $this->dislikeRepository = $dislikeRepository;
        $this->notificationRepository = $notificationRepository;
    }

    /**
     * GET ALL THE USERS
     *
     * @Route("/", name="index", methods={"GET"})
     */
    public function index()
    {
        $activeUsers = null;
        $suspendedUsers = null;
        $deletedUsers = null;
        foreach ($this->userRepository->findBy([], ['created_at' => 'DESC']) as $user) {
            if ( !in_array("ROLE_ADMIN", $user->getRoles()) AND $user->getStatus() === 'active' ) {
                $activeUsers[] = $user;
            }
            elseif ( !in_array("ROLE_ADMIN", $user->getRoles()) AND $user->getStatus() === 'suspended') {
                $suspendedUsers[] = $user;
            }
            elseif ( !in_array("ROLE_ADMIN", $user->getRoles()) AND $user->getStatus() === 'deleted')
            {
                $deletedUsers[] = $user;
            }
        }
        return $this->render('admin/user/index.html.twig', [
            'activeUsers'    => $activeUsers,
            'suspendedUsers' => $suspendedUsers,
            'deletedUsers' => $deletedUsers,
        ]);
    }

    /**
     * GET THE USER ANS SUSPEND HIM AND SENT THE EMAIL TO HIS MAIL BOX
     *
     * @Route("/suspend/{id}", name="suspend", methods={"GET", "POST"}, requirements={"id": "\d+"})
     * @param Request $request
     * @param User $user
     * @return Response
     */
    public function suspendUser(Request $request, User $user): Response
    {
        $this->denyAccessUnlessGranted('suspend', $user);
        $report = ( new Report() )->setUser($user);
        $form = $this->createForm(ReportFormType::class, $report);
        $form->handleRequest($request);

        if ( $this->suspendOrDeleteUser($report, $form, $user) ) return $this->redirectToRoute('app_admin_user_index');

        return $this->render('admin/user/suspend.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    /**
     * THIS METHOD WILL DELETE THE USER BUT IT WILL BE KEPT IN THE DATABASE SO YOU CAN RECOVER IT
     *
     * @Route("/{id}/delete", name="delete", methods={"GET", "POST"}, requirements={"id": "\d+"})
     * @param Request $request
     * @param User $user
     * @return Response
     */
    public function deleteUser(Request $request, User $user)
    {
        $this->denyAccessUnlessGranted('delete', $user);
        $report = ( new Report() )->setUser($user);
        $form = $this->createForm(ReportFormType::class, $report);
        $form->handleRequest($request);

        if ( $this->suspendOrDeleteUser($report, $form, $user, 1) ) return $this->redirectToRoute('app_admin_user_index');

        return $this->render('admin/user/delete.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * THIS METHOD WILL BE DELETE THE USER FROM THE DATABASE PERMANENTLY SO IF YOU DELETE AN USER U CAN'T RECOVER IT ACCOUNT
     *
     * @Route("/{id}/delete/force", name="delete_force", methods={"GET"}, requirements={"id": "\d+"})
     * @param User $user
     * @return RedirectResponse
     */
    public function deleteUserPermanently(User $user)
    {
        $this->denyAccessUnlessGranted('delete', $this->getUser());
        $this->deleteAllTheRelatedActionToUser($user);

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        // DON'T FORGET TO SEND AN EMAIL TO USER EXPLAIN THE REASON

        $this->flashyNotifier->success('The account was deleted with successfully !');

        return $this->redirectToRoute('app_admin_user_index');
    }

    /**
     * SEARCH FOR USER
     *
     * @Route("/{firstName?|lastName?|username?}", name="search_user", methods={"GET", "POST"}, requirements={"firstName": "[a-z\ ]*"})
     * @param Request $request
     * @return Response
     */
    public function searchUser(Request $request)
    {
        $username = null;
        $_users = [];
        if ( $request->isMethod('POST') ) {
            $username = strtolower($request->request->get('_username'));
            $username = trim($username);
            if ( $username === '' ) return $this->redirectToRoute('app_admin_user_index');
            $users = $this->userRepository->findAll();
            if ( [] === $_users ) {
                $len = strlen($username);
                $users = $this->userRepository->findAll();
                foreach($users as $_user) {
                    if (stristr($username, substr($_user->getFirstName(), 0, $len)) AND !in_array($_user, $_users)) {
                        $_users[] = $_user;
                    }
                    if (stristr($username, substr($_user->getLastName(), 0, $len)) AND !in_array($_user, $_users)) {
                        $_users[] = $_user;
                    }
                    if (stristr($username, substr($_user->getFullName(), 0, $len)) AND !in_array($_user, $_users)) {
                        $_users[] = $_user;
                    }
                }
            }
        }

        return $this->render('admin/user/index.html.twig',[
            'users' => $_users,
            'username' => $username,
        ]);
    }

    private function suspendOrDeleteUser($report, $form, User $user, $action = 0)
    {
        if ( $form->isSubmitted() AND $form->isValid() )
        {
            $user->setStatus(self::STATUS[$action]);
            $this->entityManager->persist($user);
            $this->entityManager->persist($report);

            // DON'T FORGET TO SEND THE USER EMAIL TO EXPLAIN THE CAUSE OF THE SUSPENSION

            $this->entityManager->flush();

            $action === 0 ?
                $this->flashyNotifier->success('The user was suspended with success !') :
                $this->flashyNotifier->success('The user was deleted with success !')
            ;

            return true;
        }
        return false;
    }

    private function deleteAllTheRelatedActionToUser(User $user)
    {
        $reports = $this->reportRepository->findBy(['user' => $user]);
        if ( null !== $reports)
            foreach ( $reports as $report ) $this->entityManager->remove($report);

        $comments = $this->commentRepository->findBy(['user' => $user]);
        if ( null !== $comments)
            foreach ( $comments as $comment ) $this->entityManager->remove($comment);

        $likes = $this->likeRepository->findBy(['User' => $user]);
        if ( null !== $likes)
        foreach ( $likes as $like ) $this->entityManager->remove($like);

        $dislikes = $this->dislikeRepository->findOneBy(['User' => $user]);
        if ( null !== $dislikes )
            foreach ( $dislikes as $dislike ) $this->entityManager->remove($dislike);

        $notifications = $this->notificationRepository->findBy(['User' => $user]);
        if ( null !== $notifications )
            foreach ( $notifications as $notification ) $this->entityManager->remove($notification);

        $this->entityManager->flush();
    }

    /**
     * @Route("/{id}", name="profile", methods={"GET"}, requirements={"id": "\d+"})
     * @param User $user
     * @return Response
     */
    public function profileUser(User $user): Response
    {
        return $this->render('admin/user/user.html.twig', [
            'user' => $user
        ]);
    }
}