<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AjaxController extends AbstractController
{
    /**
     * @var CategoryRepository
     */
    private $repository;
    /**
     * @var PostRepository
     */
    private $postRepository;

    public function __construct(CategoryRepository $repository, PostRepository $postRepository)
    {
        $this->repository = $repository;
        $this->postRepository = $postRepository;
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
        $data = json_decode($request->getContent(), true);
        $name = $data['Name'];
        $categories = $this->repository->findAll();
        $foundCategory = [];
        if ($name !== "") {
            $name = strtolower($name);
            $len = strlen($name);
            foreach($categories as $category) {
                if (stristr($name, substr(strtolower($category->getName()), 0, $len))) {
                    $foundCategory[] = $category;
                }
            }
        }
        elseif (trim($name) === '') {
            $foundCategory = $categories;
        }

        return $this->json($foundCategory);
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
}
