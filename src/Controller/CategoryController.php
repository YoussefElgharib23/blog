<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/category", name="app_category_")
 * Class CategoryController
 * @package App\Controller
 */
class CategoryController extends AbstractController
{
    /**
     * @var CategoryRepository
     */
    private $repository;
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * CategoryController constructor.
     * @param CategoryRepository $repository
     * @param EntityManagerInterface $em
     */
    public function __construct(CategoryRepository $repository, EntityManagerInterface $em)
    {
        $this->repository = $repository;
        $this->em = $em;
    }

    /**
     * @Route(path="/", name="index", methods={"GET"})
     * @return Response
     */
    public function index()
    {
        $categories = $this->repository->findAll();
        return $this->render('Category/index.html.twig', compact('categories'));
    }

    public function create()
    {}

    public function edit(Category $category) {}

    public function delete(Category $category)
    {}
}
