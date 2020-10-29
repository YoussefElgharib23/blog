<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryFormType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use MercurySeries\FlashyBundle\FlashyNotifier;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
     * @var FlashyNotifier
     */
    private $flashy;

    /**
     * CategoryController constructor.
     * @param CategoryRepository $repository
     * @param EntityManagerInterface $em
     */
    public function __construct(FlashyNotifier $flashy, CategoryRepository $repository, EntityManagerInterface $em)
    {
        $this->repository = $repository;
        $this->em = $em;
        $this->flashy = $flashy;
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

    /**
     * Undocumented function
     *
     * @Route("/create", name="create", methods={"GET", "POST"})
     */
    public function create(Request $request)
    {
        $category = new Category();
        $form = $this->createForm(CategoryFormType::class, $category);
        $form->handleRequest($request);
        if ($form->isSubmitted() AND $form->isValid()) {
            $this->em->persist($category);
            $this->em->flush();

            $this->flashy->success("The category was created with success");

            return $this->redirectToRoute('app_category_index');
        }
        return $this->render('category/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * EDIT THE CATEGORY
     *
     * @Route("/{id}/edit", name="edit", methods={"GET", "PUT"}, requirements={"id": "\d+"})
     * @param Category $category
     * @param Request $request
     * @return Response
     */
    public function edit(Request $request, Category $category): Response
    {
        $form = $this->createForm(CategoryFormType::class, $category, ['method' => 'PUT']);
        $form->handleRequest($request);
        if ($form->isSubmitted() AND $form->isValid()) {
            $this->em->flush();
            
            $this->flashy->success("The cateogry was updated with success !");

            return $this->redirectToRoute('app_category_index');
        }
        return $this->render('category/edit.html.twig', [
            'form' => $form->createView(),
            'category' => $category
        ]);
    }

    /**
     * DELTET THE GIVEN POST
     *
     * @Route("/{id}/delete", name="delete", methods={"DELETE"}, requirements={"id": "\d+"})
     * @param Category $category
     * @param Request $request
     * @return Response
     */
    public function delete(Request $request, Category $category): Response
    {
        if ($this->isCsrfTokenValid('delete_category' . $category->getId(), $request->request->get('_token'))) 
        {
            $this->em->remove($category);
            $this->em->flush();

            $this->flashy->success("The category was deleted with success");
        }
        return $this->redirectToRoute('app_category_index');
    }
}