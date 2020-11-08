<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class UserController
 * @package App\Controller
 * @Route("/admin/user", name="app_admin_user_")
 */
class UserController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;
    /**
     * @var UserRepository
     */
    private UserRepository $userRepository;

    public function __construct(EntityManagerInterface $entityManager, UserRepository $userRepository)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
    }

    /**
     * GET ALL THE USERS
     *
     * @Route("/", name="index", methods={"GET"})
     */
    public function index()
    {
        $users = null;
        foreach ($this->userRepository->findAll() as $user) {
            if ( !in_array("ROLE_ADMIN", $user->getRoles() ) ) {
                $users[] = $user;
            }
        }
        return $this->render('admin/user/index.html.twig', compact('users'));
    }
}
