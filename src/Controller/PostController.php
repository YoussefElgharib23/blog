<?php

namespace App\Controller;

use App\Entity\Ip;
use App\Entity\Post;
use App\Form\PostFormType;
use App\Repository\IpRepository;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use MercurySeries\FlashyBundle\FlashyNotifier;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class PostController
 * @package App\Controller
 * @Route(path="/post", name="app_post_")
 * @IsGranted("ROLE_USER")
 */
class PostController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var PostRepository
     */
    private $repository;
    /**
     * @var IpRepository
     */
    private $ipRepository;
    /**
     * @var FlashyNotifier
     */
    private $flashy;

    public function __construct(FlashyNotifier $flashy, EntityManagerInterface $em, PostRepository $repository, IpRepository $ipRepository)
    {
        $this->em = $em;
        $this->repository = $repository;
        $this->ipRepository = $ipRepository;
        $this->flashy = $flashy;
    }

    /**
     * @Route(path="/create", name="create", methods={"GET", "POST"});
     * @param Request $request
     * @return Response
     */
    public function createNewPost(Request $request)
    {
        $post = (new Post())->setViews(0);
        $form = $this->createForm(PostFormType::class, $post);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($post);
            $this->em->flush();

            $this->flashy->success('The post was created with success', $this->generateUrl('app_post_show', ['id' => $post->getId(), 'slug' => $post->getSlug()]));

            return $this->redirectToRoute('app_post_create');
        }

        return $this->render('Post/index.html.twig', [
            'cm'   => 'create',
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route(path="/", name="show_list")
     * @return Response
     */
    public function listPosts()
    {
        $post = $this->repository->findOneBy([], ['created_at' => 'DESC']);
        $posts = NULL;
        if (NULL !== $post) { $posts = $this->repository->findExcept($post); }
        return $this->render('Post/list.html.twig', [
            'posts' => $posts,
            'post' => $post,
            'cm' => 'list'
        ]);
    }

    /**
     * SHOW THE POST USING THE ID
     *
     * @Route(path="/post/{id}-{slug}", name="show", methods={"GET"}, requirements={"id"="\d+"})
     * @param Post $post
     * @param string $slug
     * @param Request $request
     * @return Response
     */
    public function show(Post $post, string $slug, Request $request)
    {
        if ( $post->getSlug() !== $slug )
            return $this->redirectToRoute('app_post_show', [ 'id' => $post->getId(), 'slug' => $post->getSlug() ]);
        $ip = $this->ipRepository->findOneBy(['ipAddress' => $request->getClientIp()]);
        if (!$ip)
        {
            $ip = ( new Ip() )->setIpAddress($request->getClientIp())->setIpType('USER');
            $this->em->persist($ip);
            $this->em->flush();

            $post->incrementViews();
        }
        return $this->render('Post/show.html.twig', compact('post'));
    }

    /**
     *
     * @Route(path="/edit/{id}", methods={"GET", "PUT"}, requirements={"id": "\d+"})
     * @param Request $request
     * @param Post $post
     * @return Response
     */
    public function edit(Request $request, Post $post)
    {
        $form = $this->createForm(PostFormType::class, $post, ['method' => 'PUT']);
        $form->handleRequest($request);
        if ($form->isSubmitted() AND $form->isValid()) {
            $this->em->flush();
            $this->flashy->success('Your post was updated with success');

            return $this->redirectToRoute('app_post_show_list');
        }

        return $this->render('Post/edit.html.twig', [
            'form' => $form->createView(),
            'post' => $post
        ]);
    }

    /**
     * @Route(path="/{id}/delete", name="post_delete", methods={"DELETE"}, requirements={"id": "\d+"})
     * @param Post $post
     * @param Request $request
     * @return Response
     */
    public function deletePost(Post $post, Request $request)
    {
        if ($this->isCsrfTokenValid('delete_post' . $post->getId(), $request->query->get('_token'))) {
            $this->em->remove($post);
            $this->em->flush();
        }
        return $this->redirectToRoute('app_post_show_list');
    }
}
