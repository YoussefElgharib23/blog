<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SystemController extends AbstractController
{
    /**
     * @Route("/system", name="system")
     */
    public function index(): Response
    {
        return new Response(system('getmac')); 
    }
}
