<?php

namespace App\Controller;

use App\Repository\PostRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PageController extends AbstractController
{
    /**
     * @var PostRepository
     */
    private $repository;

    public function __construct(PostRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @Route("/", name="app_home", methods={"GET"})
     * @IsGranted("ROLE_USER")
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $count = $this->repository->count([]);
        $views = 0;
        foreach ($this->repository->findAll() as $post) $views += $post->getViews();
        return $this->render('page/index.html.twig', [
            'cm' => 'dashboard',
            'PostCount' => $count,
            'views' => $views
        ]);
    }
}
