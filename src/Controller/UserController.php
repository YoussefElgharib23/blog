<?php

namespace App\Controller;

use App\Entity\Report;
use App\Entity\User;
use App\Form\ReportFormType;
use App\Repository\CommentRepository;
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
 * @Route("/admin/user", name="app_admin_user_")
 * @IsGranted ("ROLE_ADMIN")
 */
class UserController extends AbstractController
{
    const STATUS = [
        'suspended',
        'deleted',
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

    public function __construct(CommentRepository $commentRepository, FlashyNotifier $flashyNotifier, EntityManagerInterface $entityManager, UserRepository $userRepository, ReportRepository $reportRepository)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->flashyNotifier = $flashyNotifier;
        $this->reportRepository = $reportRepository;
        $this->commentRepository = $commentRepository;
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
     * @Route("/suspend/{id}", name="suspend_user", methods={"GET", "POST"}, requirements={"id": "\d+"})
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
            'form' => $form->createView()
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
     *
     * @Route("/{firstName?}", name="search_user", methods={"GET", "POST"}, requirements={"firstName": "[a-z\ ]*"})
     * @param User|null $user
     * @param Request $request
     * @param string|null $firstName
     * @param string|null $lastName
     * @return Response
     */
    public function searchUserByUserName(User $user = null, Request $request, string $firstName = null, string $lastName = null)
    {
        $username = $firstName ?? $lastName;
        if ( $request->getMethod() === 'POST' AND $request->request->has('_username') ) {
            $username = strtolower($request->request->get('_username'));
            if ( '' === trim($username) ) return $this->redirectToRoute('app_admin_user_index');
            $user = $this->userRepository->findOneBy(['firstName' => $username]);
            if ( null === $user ) $user = $this->userRepository->findOneBy(['lastName' => $username]);

            return $this->redirectToRoute('app_admin_user_search_user', [
                'firstName' => $user !== null ? strtolower($user->getFirstName() ): $username
            ]);
        }
        if ( $user === $this->getUser() ) $user = null;

        return $this->render('admin/user/index.html.twig',[
            'user' => $user,
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
        foreach ( $reports as $report ) $this->entityManager->remove($report);

        $comments = $this->commentRepository->findBy(['user' => $user]);
        foreach ( $comments as $comment ) $this->entityManager->remove($comment);

        $this->entityManager->flush();
    }
}
